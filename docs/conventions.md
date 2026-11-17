# Petbook — Convenciones de diseño

Decisiones transversales de diseño que no viven en una historia de usuario ni en un issue puntual. Cada sección es un registro tipo ADR corto: decisión, por qué, alternativas descartadas y cómo se usa. Las guías (`guia.md`, `guia_desarrollo.md`) referencian este documento cuando una convención aplica a varios componentes.

---

## Transacciones en la capa de acceso a datos

**Estado:** aceptada · **Origen:** issue transaccional HU-08/09/10 (bloqueante) · **Afecta:** `Database`, `Model`, todos los modelos hijo.

### Decisión

Toda escritura que abarca más de una tabla se ejecuta dentro de `Database::transactional(callable $fn)` (o `$this->transactional(...)` cuando se llama desde dentro de un modelo, que es un alias protegido que delega en el primero). **Nunca** se escriben `beginTransaction()` / `commit()` / `rollBack()` a mano en código nuevo.

```php
// Desde un modelo:
public function crear(array $datos): int
{
    return $this->transactional(function () use ($datos) {
        $idEvento = $this->insert([...]);
        $this->execute('INSERT INTO HistorialEstadoEvento ...', [...]);
        $this->execute('INSERT INTO Publicacion ...', [...]);
        return $idEvento;
    });
}

// Desde un controlador orquestando varios modelos:
$idEvento = Database::transactional(function () use (...) {
    (new Mascota())->registrar(...);
    (new Ubicacion())->crear(...);
    return (new EventoMascota())->crear(...);
});
```

El wrapper hace `beginTransaction()` → ejecuta `$fn` → `commit()`; si `$fn` lanza cualquier `Throwable`, hace `rollBack()` y re-lanza la excepción original. El valor de retorno de `$fn` se devuelve desde `transactional()`, que por eso está tipado `mixed`.

### Por qué

El flujo de creación de evento (HU-08/09/10) escribe en `Mascota`, `Ubicacion`, `EventoMascota`, `HistorialEstadoEvento` y `Publicacion` como una sola operación lógica. Sin transacción, un fallo en la quinta escritura deja las cuatro primeras confirmadas y viola la regla "toda creación de evento genera automáticamente una publicación" — un evento visible sin publicación está prohibido explícitamente.

### Alternativa descartada

El modelo que orquesta abre y cierra la transacción a mano (`beginTransaction()` antes, `try`/`commit`, `catch`/`rollBack`/`throw`). Es el patrón que existía hasta este issue (`Usuario::registrar`, `Mascota::registrar`, `EventoMascota::cambiarEstado`, sección 2.5 de `guia_desarrollo.md`). Se descartó porque depende de que **cada desarrollador** se acuerde del `catch` con `rollBack()`: un `return` temprano, un `throw` nuevo o un merge descuidado dejan la transacción abierta o parcialmente confirmada, y el error no se manifiesta hasta que aparece un registro huérfano en producción. El wrapper hace el rollback de forma mecánica e imposible de olvidar.

### Reglas de uso

1. **Método multi-tabla ⇒ `transactional()`.** El closure devuelve siempre el resultado del método (el ID generado, `true`), y el `return` va **dentro** del closure, nunca entre `beginTransaction()` y `commit()`.
2. **Las validaciones de negocio van antes del `transactional()`**, no adentro: si la validación falla, no hay nada que revertir. (Ej.: `EventoMascota::cambiarEstado()` valida que el evento exista y no esté en estado terminal antes de abrir la transacción.)
3. **El `catch` envolvente solo si hay contrato de excepción que preservar.** Si el controlador consume un tipo específico (ej.: `Usuario::registrar()` envuelve en `RuntimeException` porque `AuthController` catchea ese tipo), el `try`/`catch` va alrededor de la llamada a `transactional()`, no adentro del closure. Si no hay contrato, no se agrega `try`/`catch`: el wrapper ya relanza la excepción original tal cual.
4. **Composición vía join.** Si `transactional()` se llama con una transacción ya activa, `$fn` se ejecuta dentro de ella sin abrir otra. Esto permite que un método transaccional existente (ej.: `Mascota::registrar()`) sea llamado desde otro bloque transaccional (ej.: el flujo de evento que crea la mascota si no existe) sin errores ni savepoints. Semántica: **sin savepoints** — un fallo dentro revierte la transacción entera, que es el comportamiento deseado en todos los casos actuales del sistema.
5. **Desde el controlador, `Database::transactional()` es público.** El orquestador de varias entidades (típicamente un controlador, dado que hoy no hay capa de servicio — sección 2.4 de `guia_desarrollo.md`) puede abrir la transacción; todos los modelos comparten el mismo PDO singleton, por lo que las escrituras de todos quedan dentro.
6. **`Database::beginTransaction()/commit()/rollBack()` existen** para casos excepcionales que no encajan en un callable, pero se consideran de bajo nivel: su docblock desaconseja el uso directo y cualquier uso nuevo merece justificación en el PR.

### Contrato de excepciones

- `transactional()` **no lanza excepciones propias**: relanza la original que lanzó `$fn` (o la del `beginTransaction()` si la conexión falla).
- `rollBack()` solo se invoca si hay transacción activa; un fallo dentro del rollback (conexión caída) se propaga como `PDOException` normal y la excepción original se pierde — comportamiento heredado de PDO, aceptado para este sistema.

---

## Límite de intentos repetidos en acciones sensibles (rate limiting)

**Estado:** aceptada · **Origen:** issue de límites sobre login/registro/recuperación · **Afecta:** `IntentoAcceso`, `App\Core\RateLimiter`, `AuthController`, toda acción de autenticación futura.

### Decisión

Toda acción sensible (login, registro, recuperación de contraseña) limita sus intentos repetidos a través de `App\Core\RateLimiter`, que persiste cada intento en la tabla `IntentoAcceso`. Los límites aplican **por cuenta** (email normalizado a lowercase + trim) y **por origen** (IP del cliente), dentro de una ventana deslizante de `RATE_LIMIT_VENTANA_MINUTOS`. Los máximos por acción viven en `RateLimiter::LIMITES` y se alimentan de constantes de `config/app.php` (a su vez del `.env`, con defaults razonables).

El flujo estándar en el controlador:

```php
// 1. Antes de procesar: ¿sigue bloqueado?
if ($espera = RateLimiter::verificar('login', $email)) {
    Session::flash('error', 'Demasiados intentos fallidos. Volvé a intentarlo en ' . RateLimiter::formatearEspera($espera) . '.');
    $this->redirect('login');
}

// 2. En cada fallo: registrar el intento.
RateLimiter::registrar('login', $email);

// 3. Al éxito: limpiar el conteo de esa cuenta y origen.
RateLimiter::resetear('login', $email);
```

### Por qué

Sin límite, login y registro son explotables por fuerza bruta y por spam de cuentas (un atacante prueba contraseñas de una cuenta hasta agotar un diccionario, o genera cuentas masivamente desde una misma IP). Limitar por cuenta frena el ataque dirigido a una víctima; limitar por origen frena el barrido distribuido sobre muchas cuentas y el abuso de registro. La respuesta bloqueada es controlada (flash con tiempo de espera estimado), nunca un 500 ni un bloqueo permanente.

### Alternativa descartada

Limitar en memoria (sesión o archivo) con `APCu`/`$_SESSION`. Se descartó porque el conteo debe sobrevivir al cambio de sesión (session_regenerate_id en login), ser visible para un futuro panel de moderación, y no depender de extensiones no garantizadas en el hosting compartido. La tabla `IntentoAcceso` es simple, índice por `(Accion, Tipo, Identificador, Fecha)` y se autodepura: cada `registrar()` borra las filas salidas de la ventana.

### Reglas de uso

1. **La verificación va antes de tocar la base de datos de dominio** (`verificar()` primera consulta del POST, después de CSRF y validaciones de campos obligatorios).
2. **Solo cuentan los intentos de requests bien formados**: en registro, `registrar()` se invoca después de las validaciones de formato, para no bloquear a alguien que se equivoca de campo tres veces.
3. **El bloqueo nunca revela información de la cuenta**: el mensaje es el mismo si el email existe o no.
4. **Para una acción nueva**: agregar su entrada en `RateLimiter::LIMITES` (y sus constantes en `config/app.php` + `.env_example`), luego integrar el flujo de tres pasos en el controlador. Una acción sin entrada en `LIMITES` no se limita (no-op).
5. **`IntentoAcceso` no tiene FK a Usuario**: cubre también intentos con emails inexistentes, que son precisamente los que hay que frenar.

---

## Mensajes de autenticación no reveladores

**Estado:** aceptada · **Origen:** issue de límites sobre login/registro/recuperación · **Afecta:** `AuthController::login()`, `Auth::verifyDummy()`.

### Decisión

Todo fallo de login responde el mismo mensaje — "Credenciales incorrectas." — sin importar el motivo real: email inexistente, contraseña incorrecta, cuenta desactivada (`Activo = 0`) o eliminada (`Eliminado = 1`). Además, las rutas de fallo que no ejecutan `password_verify()` (email inexistente, cuenta inhabilitada) lo simulan con `Auth::verifyDummy()` contra un hash bcrypt real pero sin dueño, para que el tiempo de respuesta sea indistinguible del de una verificación real.

La unicidad del email en registro tampoco depende del pre-check: la UNIQUE `uq_usuario_email` es la última línea de defensa ante registros simultáneos. `Usuario::registrar()` detecta la violación (error de driver 1062 / SQLSTATE 23000 sobre `uq_usuario_email`) y la convierte en `RuntimeException` con código `23000`, que el controlador traduce a "El email ya está registrado.". Cualquier otro error sigue el camino genérico.

### Por qué

Un mensaje que distingue "no existe" de "contraseña incorrecta" o "cuenta desactivada" es un oráculo de enumeración de cuentas: permite a un atacante descubrir qué emails están registrados y cuáles fueron inhabilitados. El tiempo de respuesta es el mismo oráculo en versión silenciosa — `password_hash` cuesta ~100 ms y una consulta no — por eso el dummy. El pre-check de duplicado no basta como garantía de unicidad porque dos requests simultáneos pueden pasar ambos la verificación antes de que ninguno inserte; solo la constraint de la base es atómica.

### Reglas de uso

1. **Un solo camino de fallo** (`AuthController::falloLogin()`): registrar intento + mensaje genérico + redirect. No agregar flashes distintos por caso.
2. **Nunca propagar al log el mensaje crudo del PDOException de duplicado**: incluye el email en claro; el mensaje de la excepción envuelta es el genérico.
3. **Nunca loguear la contraseña en claro**, ni en error_log ni en ningún almacenamiento; los mensajes de PDO no incluyen valores de parámetros enlazados, así que loguear `$e->getMessage()` es seguro.
4. **Las contraseñas se hashean con `PASSWORD_DEFAULT`** (algoritmo adaptativo por defecto de PHP, cost 12) y el rehash oportunista en login exitoso (`password_needs_rehash` → `Usuario::actualizarPasswordHash()`) migra los hashes viejos sin migración de datos: la contraseña en claro solo está disponible en ese momento, y nunca se persiste ni se loguea.

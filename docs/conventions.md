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

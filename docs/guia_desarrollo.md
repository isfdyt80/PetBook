# Petbook — Guía Técnica de Componentes

Este documento es complementario a `GUIDE.md`. Aquél define estructura general, convenciones y flujo Git; este define **el procedimiento exacto** para agregar cada tipo de componente al sistema — tabla, modelo, controlador, ruta, vista, relación N:M, catálogo, extensión de roles — usando como referencia el código real de `app/core/` (`Model.php`, `Controller.php`, `Router.php`, `Auth.php`, `Session.php`) y el schema de `petbook.sql`.


---

## 1. Agregar una tabla nueva

### 1.1 Diseño — checklist antes de escribir SQL

1. **PK**: `Id_NombreTabla`, `INT NOT NULL AUTO_INCREMENT` (excepción: tablas de relación pura con PK compuesta, ej. `Reaccion`, donde la combinación de FKs ya garantiza unicidad y no hace falta surrogate key).
2. **FKs**: `Id_TablaReferenciada`, con `CONSTRAINT fk_tablaorigen_tabladestino FOREIGN KEY (...) REFERENCES ...`.
3. **`Eliminado BOOLEAN NOT NULL DEFAULT FALSE`**: obligatorio en toda entidad principal (algo que un usuario crea o posee: mascotas, publicaciones, comentarios, eventos, usuarios). **No** se agrega en tablas catálogo cerradas — `Especie`, `Raza`, `TipoEvento`, `EstadoEvento`, `EstadoPublicacion`, `TipoReaccion`, `EstadoReporte` no tienen `Eliminado` porque no se borran lógicamente, se gestionan por completo (agregar/quitar filas es una operación de administración, no de usuario final). Esta es la misma razón por la que `Model::hasSoftDelete()` consulta `information_schema` en vez de asumir la columna: el framework ya espera que existan tablas sin ese campo.
4. **Timestamps**: `FechaCreacion` / `Fecha_X DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` en toda tabla con relevancia temporal.
5. **Engine y charset**: siempre `ENGINE=InnoDB` (por FKs) — el `CREATE TABLE` no necesita repetir charset si la base ya se creó con `utf8mb4_unicode_ci`.
6. **Naming**: tabla en PascalCase singular (`Publicacion`, no `Publicaciones`); columnas en PascalCase.

### 1.2 Dónde se escribe

Agregar el `CREATE TABLE` al final de la sección de dominio correspondiente en `petbook.sql` (Identidad, Mascotas, Ubicación, Eventos, Publicaciones, Multimedia, Moderación — o un dominio nuevo si la tabla no encaja en ninguno). Si el dominio es nuevo, agregar el bloque `-- DOMINIO: X` siguiendo el mismo formato de comentario que ya usa el archivo.

Agregar también:
- Los índices relevantes en la sección `ÍNDICES` al final del archivo (toda FK usada en `WHERE` frecuente necesita índice explícito, más allá del índice implícito que MySQL crea sobre FKs en InnoDB — el patrón del archivo es indexar también columnas de filtrado como `Fecha_Creacion` o `Id_EstadoX`).
- Si la tabla es catálogo cerrado, el `INSERT` de seed correspondiente en la sección `DATOS DE CATÁLOGO`.

### 1.3 Ejemplo — tabla `Notificacion` (dominio nuevo: Notificaciones)

```sql
-- =============================================================================
-- DOMINIO: NOTIFICACIONES
-- =============================================================================

CREATE TABLE TipoNotificacion (
    Id_TipoNotificacion INT         NOT NULL AUTO_INCREMENT,
    Nombre               VARCHAR(50) NOT NULL,
    CONSTRAINT pk_tiponotificacion PRIMARY KEY (Id_TipoNotificacion),
    CONSTRAINT uq_tiponotificacion_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE Notificacion (
    Id_Notificacion      INT      NOT NULL AUTO_INCREMENT,
    Id_Usuario           INT      NOT NULL,
    Id_TipoNotificacion  INT      NOT NULL,
    Id_Publicacion       INT      NULL,
    Leida                BOOLEAN  NOT NULL DEFAULT FALSE,
    Fecha_Creacion       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado            BOOLEAN  NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_notificacion PRIMARY KEY (Id_Notificacion),
    CONSTRAINT fk_notificacion_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_notificacion_tipo
        FOREIGN KEY (Id_TipoNotificacion) REFERENCES TipoNotificacion (Id_TipoNotificacion),
    CONSTRAINT fk_notificacion_publicacion
        FOREIGN KEY (Id_Publicacion) REFERENCES Publicacion (Id_Publicacion)
) ENGINE=InnoDB;
```

Índices:
```sql
CREATE INDEX idx_notificacion_usuario ON Notificacion (Id_Usuario);
CREATE INDEX idx_notificacion_leida   ON Notificacion (Leida);
```

Seed de catálogo:
```sql
INSERT INTO TipoNotificacion (Nombre) VALUES
    ('COMENTARIO'), ('REACCION'), ('COINCIDENCIA'), ('CAMBIO_ESTADO');
```
---

## 2. Agregar un modelo nuevo

### 2.1 Contrato de la clase base (`App\Core\Model`)

Todo modelo hijo:

```php
namespace App\Models;

use App\Core\Model;

class Notificacion extends Model
{
    protected string $table = 'Notificacion';
    protected string $pk    = 'Id_Notificacion';
}
```

Con esto solo, ya hereda:

| Método | Firma | Uso |
|---|---|---|
| `all()` | `(): array` | Todos los registros activos (filtra `Eliminado = 0` si la tabla tiene esa columna) |
| `find(int $id)` | `array\|false` | Un registro por PK |
| `insert(array $data)` | `int` | Inserta, devuelve el ID generado |
| `update(int $id, array $data)` | `bool` | Actualiza por PK |
| `softDelete(int $id)` | `bool` | `Eliminado = 1` |

Estos cinco métodos alcanzan para un CRUD simple sobre una tabla sin reglas de negocio (típicamente catálogos gestionados desde el panel de admin, o cualquier tabla nueva antes de que se le agreguen métodos de dominio propios).

### 2.2 Cuándo NO alcanza con los métodos heredados

En cuanto la tabla necesita un `WHERE` distinto de "por PK", un `JOIN`, o una regla de negocio antes de escribir, el modelo hijo agrega **métodos propios** usando los tres helpers protegidos:

| Helper | Devuelve | Cuándo |
|---|---|---|
| `query(string $sql, array $params = [])` | `array` | Varias filas |
| `queryOne(string $sql, array $params = [])` | `array\|false` | Una fila — **convención: usar siempre esto para "buscar un solo registro", nunca `query()[0]`** |
| `execute(string $sql, array $params = [])` | `PDOStatement` | INSERT/UPDATE/DELETE custom — **convención: el método público del modelo hijo devuelve `->rowCount() > 0`, nunca el `PDOStatement` crudo** |

Ejemplo — `Notificacion::obtenerNoLeidasPorUsuario()`:

```php
public function obtenerNoLeidasPorUsuario(int $idUsuario): array
{
    return $this->query(
        'SELECT n.*, tn.Nombre AS TipoNotificacion
         FROM Notificacion n
         JOIN TipoNotificacion tn ON tn.Id_TipoNotificacion = n.Id_TipoNotificacion
         WHERE n.Id_Usuario = :id
           AND n.Leida = 0
           AND n.Eliminado = 0
         ORDER BY n.Fecha_Creacion DESC',
        [':id' => $idUsuario]
    );
}

public function marcarLeida(int $idNotificacion): bool
{
    $stmt = $this->execute(
        'UPDATE Notificacion SET Leida = 1 WHERE Id_Notificacion = :id',
        [':id' => $idNotificacion]
    );
    return $stmt->rowCount() > 0;
}
```

Notar los tres puntos de convención que aparecen en cada línea de este ejemplo:
- placeholder con dos puntos (`:id`), nunca `id` sin prefijo;
- filtro `Eliminado = 0` explícito porque `Notificacion` tiene esa columna (no aplica en el `JOIN` contra `TipoNotificacion`, que no la tiene);
- retorno `rowCount() > 0`, no el `PDOStatement`.

### 2.3 Catálogos: nunca IDs hardcodeados

Si un modelo necesita el ID de un valor de catálogo (ej. "el tipo NOTIFICACION llamado COMENTARIO"), se resuelve con subquery por nombre, nunca con una constante numérica, porque el orden de inserción del seed no está garantizado como estable a largo plazo:

```php
public function crearPorComentario(int $idUsuario, int $idPublicacion): int
{
    return $this->insert([
        'Id_Usuario'          => $idUsuario,
        'Id_Publicacion'      => $idPublicacion,
        'Id_TipoNotificacion' => $this->idTipoPorNombre('COMENTARIO'),
    ]);
}

private function idTipoPorNombre(string $nombre): int
{
    $tipo = $this->queryOne(
        'SELECT Id_TipoNotificacion FROM TipoNotificacion WHERE Nombre = :nombre',
        [':nombre' => $nombre]
    );

    if ($tipo === false) {
        throw new \RuntimeException("TipoNotificacion '{$nombre}' no existe en catálogo.");
    }

    return (int) $tipo['Id_TipoNotificacion'];
}
```

### 2.4 Prohibido: instanciar otro modelo dentro de un método de modelo

Si `Notificacion::crearPorComentario()` necesitara validar que el usuario existe, **no** hace `(new Usuario())->find($idUsuario)`. Esa validación es responsabilidad del controlador (que sí puede instanciar ambos modelos) antes de llamar a `crearPorComentario()`. La razón de esta regla no es estilística: dos modelos instanciándose entre sí abre la puerta a dependencias circulares y hace imposible testear un modelo de forma aislada. Si dos modelos necesitan compartir una consulta con frecuencia, la señal es que falta una capa de servicio (ver sección 0) — mientras esa capa no exista, la lógica que junta dos entidades vive en el controlador.

### 2.5 Transacciones multi-tabla

El caso central del dominio (sección 4.3 de la documentación técnica: crear evento → crea `EventoMascota` + `HistorialEstadoEvento` + `Publicacion`) requiere atomicidad que `Model.php` no da por defecto. El modelo que orquesta esa escritura usa `$this->db` directamente (heredado como `protected PDO $db`) para envolver la operación:

```php
public function crear(array $datos): int
{
    $this->db->beginTransaction();

    try {
        $idEvento = $this->insert([
            'Id_Mascota'      => $datos['idMascota'],
            'Id_Usuario'      => $datos['idUsuario'],
            'Id_TipoEvento'   => $datos['idTipoEvento'],
            'Id_EstadoEvento' => $this->idEstadoPorNombre('ACTIVO'),
            'Descripcion'     => $datos['descripcion'] ?? null,
            'Id_Ubicacion'    => $datos['idUbicacion'] ?? null,
        ]);

        $this->execute(
            'INSERT INTO HistorialEstadoEvento (Id_Evento, Id_EstadoEvento, Id_Usuario)
             VALUES (:evento, :estado, :usuario)',
            [
                ':evento'  => $idEvento,
                ':estado'  => $this->idEstadoPorNombre('ACTIVO'),
                ':usuario' => $datos['idUsuario'],
            ]
        );

        $this->execute(
            'INSERT INTO Publicacion (Id_Evento, Id_Usuario, Id_EstadoPublicacion)
             VALUES (:evento, :usuario, :estado)',
            [
                ':evento'  => $idEvento,
                ':usuario' => $datos['idUsuario'],
                ':estado'  => $this->idEstadoPublicacionPorNombre('ACTIVA'),
            ]
        );

        $this->db->commit();
        return $idEvento;

    } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

Cualquier método de modelo que escriba en más de una tabla debe seguir este patrón — `beginTransaction()` / `commit()` / `rollBack()` en `catch`. No hacerlo deja abierta la posibilidad de un `EventoMascota` sin `Publicacion`, que la sección 4.4 de la documentación técnica prohíbe explícitamente ("no puede existir un evento visible sin publicación").

### 2.6 Dónde va el archivo

`app/models/NombreClase.php`, namespace `App\Models`, autoload PSR-4 vía Composer — no requiere `require` manual una vez que el archivo está commiteado (recordar: el error de Copilot marcando `use App\Core\Model` como inválido fue, en un caso anterior, simplemente el archivo del modelo sin commitear, no un problema de namespace).

---

## 3. Agregar un controlador nuevo

### 3.1 Contrato de la clase base (`App\Core\Controller`)

```php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Notificacion;

class NotificacionController extends \App\Core\Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $usuario = Session::user();
        $modelo  = new Notificacion();
        $items   = $modelo->obtenerNoLeidasPorUsuario($usuario['id']);

        $this->view('notificacion.index', ['notificaciones' => $items]);
    }
}
```

Helpers heredados y su propósito exacto:

| Método | Uso |
|---|---|
| `view(string $view, array $data = [])` | Carga `app/views/{ruta con puntos → slashes}.php`, extrae `$data` al scope de la vista |
| `partial(string $partial, array $data = [])` | Atajo para `view('layouts/'.$partial, ...)` |
| `redirect(string $path)` | `header('Location: '.APP_URL.'/'.$path)` + `exit` |
| `back()` | Redirige a `HTTP_REFERER` o `APP_URL` |
| `input(string $key, $default = null)` | `$_POST[$key]` con `trim()` |
| `query(string $key, $default = null)` | `$_GET[$key]` con `trim()` |
| `json(mixed $data, int $status = 200)` | Responde JSON y corta ejecución — para endpoints AJAX |

### 3.2 Estructura estándar de un método de escritura (POST)

Todo método que procesa un formulario sigue el mismo esqueleto, en este orden — el orden importa: CSRF antes que nada, porque si el token es inválido no tiene sentido gastar una consulta de validación de negocio:

```php
public function comentar(string $idPublicacion): void
{
    Auth::requireAuth();
    Session::validateCsrf();

    $contenido = $this->input('contenido');

    if ($contenido === '' || $contenido === null) {
        Session::flash('error', 'El comentario no puede estar vacío.');
        $this->back();
    }

    $usuario = Session::user();
    $modelo  = new Comentario();

    $modelo->crear([
        'Id_Publicacion' => (int) $idPublicacion,
        'Id_Usuario'     => $usuario['id'],
        'Contenido'      => $contenido,
    ]);

    Session::flash('success', 'Comentario publicado.');
    $this->redirect('publicacion/' . $idPublicacion);
}
```

Validación de tipo y presencia va en el controlador (sección 0: no hay validación declarativa). Validación de regla de negocio (ej. "el usuario no puede reaccionar dos veces", que ya está garantizada a nivel de PK compuesta en `Reaccion`, o "el evento no puede volver a ACTIVO") va en el modelo.

### 3.3 Control de acceso por rol

```php
public function index(): void
{
    Auth::requireRole(['MODERADOR', 'ADMIN']);
    // ...
}
```

`requireRole()` ya llama internamente a `requireAuth()` — no hace falta llamarlo dos veces. Para acciones que exigen "es el dueño del recurso O tiene rol MODERADOR/ADMIN" (patrón muy común: un usuario puede editar su propia publicación, un moderador puede suspender cualquiera), esa comparación no existe en `Auth` y se resuelve a mano en el controlador:

```php
public function eliminar(string $idPublicacion): void
{
    Auth::requireAuth();
    Session::validateCsrf();

    $modelo      = new Publicacion();
    $publicacion = $modelo->find((int) $idPublicacion);

    if ($publicacion === false) {
        http_response_code(404);
        $this->view('errors.404');
        return;
    }

    $esDueno = $publicacion['Id_Usuario'] === Session::user()['id'];

    if (!$esDueno && !Auth::isAny(['MODERADOR', 'ADMIN'])) {
        http_response_code(403);
        $this->view('errors.403');
        return;
    }

    $modelo->softDelete((int) $idPublicacion);
    Session::flash('success', 'Publicación eliminada.');
    $this->redirect('feed');
}
```

### 3.4 Endpoints AJAX

Si la interacción no recarga la página (ej. reaccionar a una publicación desde el feed sin recargar), el método usa `$this->json()` en vez de `redirect()`/`view()`. CSRF se sigue validando igual — el token viaja en el body del `fetch()`, no cambia el mecanismo:

```php
public function reaccionar(string $idPublicacion): void
{
    Auth::requireAuth();
    Session::validateCsrf();

    $tipo = $this->input('tipo');
    // ...

    $this->json(['success' => true, 'totalReacciones' => $total]);
}
```

### 3.5 Dónde va el archivo

`app/controllers/NombreController.php`, namespace `App\Controllers`. El `Router` resuelve la clase como `App\Controllers\{$controllerName}` literal — el string pasado en `routes.php` (`'PublicacionController'`) tiene que ser exactamente el nombre de la clase, sin el namespace.

---

## 4. Agregar rutas nuevas

Todo en `app/routes.php`, usando el objeto `$router` ya instanciado en `public/index.php`.

```php
$router->get( '/notificacion',             'NotificacionController', 'index');
$router->post('/notificacion/:id/leer',    'NotificacionController', 'marcarLeida');
```

Reglas del `Router` (`addRoute()` en `Router.php`) a tener en cuenta al diseñar el path:

- `:param` se convierte en un grupo de captura con nombre — `/evento/:id` matchea `/evento/42` y pasa `'42'` (string, no int) como argumento posicional al método del controlador. **Cast explícito a `int` dentro del controlador o del modelo**, el router nunca tipa automáticamente.
- Un mismo path puede tener método `GET` y `POST` registrados por separado apuntando a métodos de controlador distintos (patrón mostrado por `/login` en `routes.php`: `GET` → `index()` para mostrar el formulario, `POST` → `login()` para procesarlo). Es el patrón estándar para cualquier formulario.
- `any()` existe para rutas que aceptan cualquier verbo — en la práctica no se usa en el proyecto hasta ahora; usarlo solo si un mismo método realmente maneja tanto `GET` como `POST` de forma condicional internamente (`$_SERVER['REQUEST_METHOD']`), lo cual generalmente es peor que separar en dos rutas explícitas.
- El orden de registro importa cuando dos patrones podrían matchear la misma URI (no es el caso actual porque los prefijos no colisionan, pero al agregar rutas nuevas con parámetros hay que verificar que no se superpongan con una ruta más específica ya registrada antes en el archivo — el router usa la primera coincidencia, no la más específica).
- Rutas que requieren rol específico **no se restringen en el router** — el router no sabe nada de roles. La restricción vive exclusivamente en el primer llamado del método del controlador (`Auth::requireRole(...)`). Esto es deliberado dado el diseño de dos capas, pero significa que **olvidar el `Auth::requireRole()` en el método dado de alta es un 403 ausente**, no un error que el router detecte.

---

## 5. Agregar una vista nueva

### 5.1 Patrón de layout

`main.php` (`app/views/layouts/main.php`) es el shell completo: navbar, flash messages, `<main><?= $content ?? '' ?></main>`, footer. Toda vista de página completa sigue el patrón de `login.php`/`registro.php`:

```php
<?php
use App\Core\Session;
ob_start();
?>

<!-- HTML específico de esta vista -->
<div class="...">
    ...
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Título de la página';
require_once APP_PATH . '/views/layouts/main.php';
?>
```

Es decir: la vista **no se invoca a través de `$this->partial()`** para el contenido de página — se auto-incluye a `main.php` al final, después de capturar su propio HTML en el buffer (`ob_start()`/`ob_get_clean()`). El scope de variables que llegan desde el controlador vía `extract($data)` (en `Controller::view()`) está disponible tanto en la parte de arriba (el HTML específico) como se necesite, y `$titulo` viaja implícitamente al `main.php` incluido al final porque PHP comparte el scope local del archivo — no hace falta pasarlo explícito.

### 5.2 Ejemplo — vista de feed (mockup limitado a `<main>`)

Como ya está anotado en el histórico del proyecto: el mockup del feed **no reimplementa navbar/footer**, solo el contenido de `<main>`, porque `main.php` ya los provee. Al escribir `app/views/publicacion/index.php`:

```php
<?php
use App\Core\Session;
ob_start();
?>

<div class="container py-4">
    <h1 class="mb-4">Feed</h1>

    <?php foreach ($publicaciones as $pub): ?>
        <div class="card mb-3">
            <div class="card-body">
                <p><?= htmlspecialchars($pub['Contenido'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Feed';
require_once APP_PATH . '/views/layouts/main.php';
?>
```

### 5.3 Reglas de contenido de vista (de `GUIDE.md`, reforzadas acá)

El único PHP permitido dentro de una vista es `if`, `foreach` y `htmlspecialchars()` de salida. Concretamente, **nunca**:
- `new Modelo()` dentro de una vista — todos los datos llegan ya resueltos por el controlador vía `$data` en `view()`.
- Lógica de formato de negocio compleja (ej. calcular "hace cuánto se publicó esto" con lógica condicional extensa) — eso es un helper, no código inline en la vista. Si el proyecto todavía no tiene una capa de helpers de vista, la solución de mínima fricción es un método estático en una clase `App\Core\ViewHelpers` (a crear cuando la primera necesidad real aparezca), nunca lógica ad-hoc repetida vista por vista.

### 5.4 Naming y ubicación

`app/views/{controlador sin sufijo}/{accion}.php` en snake_case: `app/views/publicacion/index.php`, `app/views/evento/crear.php`. La notación con puntos que usa `Controller::view()` (`'publicacion.index'`) se traduce a slashes internamente — al escribir `$this->view(...)` en el controlador, usar puntos; al crear el archivo en disco, usar la carpeta real.

---

## 6. Agregar una tabla de relación N:M

Dos patrones distintos según si la relación necesita metadata propia:

### 6.1 Relación pura (sin atributos propios más allá de las FKs)

Ejemplo existente: `Reaccion` — PK compuesta `(Id_Usuario, Id_Publicacion)`, sin surrogate key, porque la combinación ya es la unicidad de negocio ("un usuario, una reacción por publicación"). Modelo correspondiente **no puede usar `find(int $id)` heredado** porque no hay PK simple — se le agregan métodos propios sobre PK compuesta:

```php
class Reaccion extends Model
{
    protected string $table = 'Reaccion';
    // sin $pk — no aplica con PK compuesta

    public function buscar(int $idUsuario, int $idPublicacion): array|false
    {
        return $this->queryOne(
            'SELECT * FROM Reaccion WHERE Id_Usuario = :usuario AND Id_Publicacion = :publicacion',
            [':usuario' => $idUsuario, ':publicacion' => $idPublicacion]
        );
    }

    public function reaccionar(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        $existente = $this->buscar($idUsuario, $idPublicacion);

        if ($existente !== false) {
            $stmt = $this->execute(
                'UPDATE Reaccion SET Id_TipoReaccion = :tipo
                 WHERE Id_Usuario = :usuario AND Id_Publicacion = :publicacion',
                [':tipo' => $idTipoReaccion, ':usuario' => $idUsuario, ':publicacion' => $idPublicacion]
            );
            return $stmt->rowCount() > 0;
        }

        $stmt = $this->execute(
            'INSERT INTO Reaccion (Id_Usuario, Id_Publicacion, Id_TipoReaccion)
             VALUES (:usuario, :publicacion, :tipo)',
            [':usuario' => $idUsuario, ':publicacion' => $idPublicacion, ':tipo' => $idTipoReaccion]
        );
        return $stmt->rowCount() > 0;
    }
}
```

### 6.2 Relación con metadata e historial (surrogate PK)

Ejemplo existente: `MascotaUsuario` — mismo par `(mascota, usuario)` puede repetirse con `FechaDesde`/`FechaHasta` distintos, por eso tiene `Id_MascotaUsuario` propio en vez de PK compuesta. Se modela con `$table`/`$pk` normales (hereda `find()`, `insert()` sin problema) más un método propio para la consulta "vínculo activo":

```php
public function vinculoActivo(int $idMascota, int $idUsuario): array|false
{
    return $this->queryOne(
        'SELECT * FROM MascotaUsuario
         WHERE Id_Mascota = :mascota AND Id_Usuario = :usuario AND FechaHasta IS NULL',
        [':mascota' => $idMascota, ':usuario' => $idUsuario]
    );
}
```

**Criterio para elegir uno u otro patrón**: si la relación puede repetirse en el tiempo entre el mismo par de entidades (histórico), usar PK surrogate. Si es estrictamente "como máximo una fila por par", usar PK compuesta — es más barato en índices y hace la restricción de unicidad explícita a nivel de motor en vez de depender de lógica de aplicación.

---

## 7. Agregar un catálogo nuevo (tabla tipo `Especie`, `TipoEvento`)

1. Tabla simple `Id_X INT AUTO_INCREMENT PK`, `Nombre VARCHAR(50) NOT NULL UNIQUE`. Sin `Eliminado` (sección 1.1).
2. Seed en `petbook.sql`, sección `DATOS DE CATÁLOGO`.
3. Modelo: extiende `Model` sin necesidad de métodos propios en la mayoría de los casos — `all()` heredado ya sirve para poblar un `<select>` en un formulario.
4. **Nunca referenciar el catálogo por ID literal** en ningún modelo o controlador — siempre subquery por nombre (patrón de la sección 2.3). Esto es lo que permite reordenar o insertar nuevos valores de catálogo sin romper código existente.
5. Si el catálogo necesita administración desde el panel de ADMIN (HU-A05), el `AdminController` correspondiente reutiliza `all()`, `insert()`, `update()` heredados directamente — no hace falta un modelo custom para esto.

---

## 8. Auditoría / historial (patrón `HistorialEstadoEvento`)

Toda entidad con ciclo de vida de estados (actualmente solo `EventoMascota`, pero el patrón es reutilizable para, por ejemplo, un futuro historial de cambios de rol de usuario) sigue esta estructura:

- Tabla `Historial{Entidad}` con FK a la entidad, FK al nuevo estado, FK al usuario responsable del cambio, y `Fecha DATETIME DEFAULT CURRENT_TIMESTAMP`. Sin `Eliminado` — un historial de auditoría no se soft-deletea, es inmutable por definición.
- El modelo de la entidad principal es responsable de escribir en la tabla de historial **dentro de la misma transacción** que el cambio de estado (sección 2.5) — nunca queda a cargo del controlador insertar el historial por separado, porque eso permite que un bug en el controlador cambie el estado sin dejar rastro.
- Regla de negocio de "estados terminales no revierten" (`RESUELTO`/`CANCELADO` → `ACTIVO` prohibido) se valida **dentro del método `cambiarEstado()` del modelo**, no en el controlador, antes de escribir nada:

```php
public function cambiarEstado(int $idEvento, string $nuevoEstadoNombre, int $idUsuarioResponsable): bool
{
    $evento = $this->find($idEvento);

    if ($evento === false) {
        throw new \RuntimeException('Evento no encontrado.');
    }

    $estadoActual = $this->queryOne(
        'SELECT Nombre FROM EstadoEvento WHERE Id_EstadoEvento = :id',
        [':id' => $evento['Id_EstadoEvento']]
    );

    if (in_array($estadoActual['Nombre'], ['RESUELTO', 'CANCELADO'], true)) {
        throw new \RuntimeException('El evento ya está en un estado terminal y no puede modificarse.');
    }

    $idNuevoEstado = $this->idEstadoPorNombre($nuevoEstadoNombre);

    $this->db->beginTransaction();
    try {
        $this->update($idEvento, ['Id_EstadoEvento' => $idNuevoEstado]);

        $this->execute(
            'INSERT INTO HistorialEstadoEvento (Id_Evento, Id_EstadoEvento, Id_Usuario)
             VALUES (:evento, :estado, :usuario)',
            [':evento' => $idEvento, ':estado' => $idNuevoEstado, ':usuario' => $idUsuarioResponsable]
        );

        $this->db->commit();
        return true;
    } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

El controlador que llama a esto se limita a capturar la excepción y convertirla en un flash de error — no repite la validación:

```php
public function cambiarEstado(string $idEvento): void
{
    Auth::requireAuth();
    Session::validateCsrf();

    try {
        (new EventoMascota())->cambiarEstado(
            (int) $idEvento,
            $this->input('estado'),
            Session::user()['id']
        );
        Session::flash('success', 'Estado actualizado.');
    } catch (\RuntimeException $e) {
        Session::flash('error', $e->getMessage());
    }

    $this->redirect('evento/' . $idEvento);
}
```

---

## 9. Extender roles y permisos

`Auth` trabaja sobre un único rol string en sesión (`$_SESSION['user']['rol']`), poblado en `Session::login()` desde el resultado de `Usuario::buscarPorEmail()` (o equivalente). Si en algún momento un usuario necesita **más de un rol simultáneo** (el schema ya lo permite — `UsuarioRol` es N:M real), hace falta:

1. Cambiar `Session::login()` para guardar un array de roles en vez de un string.
2. Cambiar `Auth::role()` → `Auth::roles(): array`, y `Auth::is()`/`Auth::isAny()` para comparar contra el array en vez de igualdad simple.

Esto **no está implementado hoy** — el modelo de datos ya lo soporta (`UsuarioRol` es una tabla de relación, no una columna en `Usuario`) pero la capa de sesión/Auth asume un solo rol activo por simplicidad. Cualquier historia de usuario que dependa de multi-rol simultáneo (no está en `historias_usurio.md` actualmente) requiere este refactor antes de poder implementarse — no es un caso que se pueda resolver parcheando el controlador.

Agregar un rol nuevo al catálogo (ej. `VETERINARIO`) no requiere tocar `Auth.php`: alcanza con un `INSERT INTO Rol (Nombre) VALUES ('VETERINARIO')` y usar `Auth::requireRole('VETERINARIO')` o `Auth::isAny(['VETERINARIO', 'ADMIN'])` donde corresponda — el sistema de roles ya es dato, no código.

---

## 10. Manejo de errores específicos de un componente nuevo

`Router::serverError()` captura cualquier `\Throwable` no atrapado dentro de `callController()` y responde 500 (con stack trace si `APP_DEBUG`, con `views/errors/500.php` si no). Esto significa que un modelo nuevo **no necesita try/catch propio para errores inesperados** — alcanza con dejar que la excepción suba. Sí necesita manejo explícito cuando el error es una condición de negocio esperable (evento en estado terminal, catálogo inexistente, CSRF inválido) que debe traducirse en un mensaje de usuario en vez de una página 500 — ese es el criterio para decidir si un método de modelo lanza excepción (para que el controlador la traduzca a flash) o devuelve `false`/`array vacío` (para que el controlador maneje el caso silenciosamente). No hay una regla mecánica única; el criterio es: **si el estado es "el usuario hizo algo que no tiene sentido" → excepción con mensaje claro; si el estado es "no hay resultados, y eso es normal" → retorno vacío**.

---

## 11. Checklist end-to-end para una funcionalidad vertical nueva

Siguiendo el criterio de tareas ya establecido (vertical, no horizontal — cada issue termina demostrable en el navegador), el orden de dependencia real para cualquier funcionalidad nueva es:

1. **Tabla(s)** — sección 1. Si hay relación N:M o historial, sección 6/8.
2. **Modelo** — sección 2. Empezar solo con lo heredado; agregar métodos propios a medida que el controlador los necesita, no antes (evita métodos muertos sin caller).
3. **Ruta(s)** — sección 4. Registrar GET antes que POST si la funcionalidad incluye un formulario.
4. **Controlador** — sección 3. `Auth::requireAuth()`/`requireRole()` como primera línea de cada método sin excepción; `Session::validateCsrf()` como segunda línea de todo método POST sin excepción.
5. **Vista** — sección 5. Solo contenido de `<main>`; nunca reimplementar navbar/footer.

El issue no se considera terminado hasta que los cinco pasos están commiteados y la funcionalidad es clickeable de punta a punta en `localhost/petbook/public` — un modelo con métodos sin controlador que los llame, o un controlador sin ruta registrada, no es un incremento demostrable y no cierra el issue aunque el código individual esté correcto.
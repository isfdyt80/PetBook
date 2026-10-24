# Petbook — Guía de Desarrollo

Documento de referencia para el equipo. Define la estructura del proyecto, convenciones de código, flujo de trabajo con Git y estándares de seguridad. Su cumplimiento es obligatorio para mantener consistencia en la base de código.

---

## Tabla de contenidos

1. [Stack tecnológico](#1-stack-tecnológico)
2. [Estructura del proyecto](#2-estructura-del-proyecto)
3. [Arquitectura MVC](#3-arquitectura-mvc)
4. [Convenciones de nomenclatura](#4-convenciones-de-nomenclatura)
5. [Configuración del entorno](#5-configuración-del-entorno)
6. [Composer y autoload](#6-composer-y-autoload)
7. [Estrategia de ramas Git](#7-estrategia-de-ramas-git)
8. [Flujo de trabajo Git](#8-flujo-de-trabajo-git)
9. [Convención de commits](#9-convención-de-commits)
10. [Seguridad y buenas prácticas](#10-seguridad-y-buenas-prácticas)


---

## 1. Stack tecnológico

| Componente     | Tecnología                        |
|----------------|-----------------------------------|
| Backend        | PHP 8.1+                          |
| Base de datos  | MySQL 8.0+ · InnoDB               |
| Servidor       | Apache (WampServer)               |
| Dependencias   | Composer                          |
| Variables de entorno | vlucas/phpdotenv            |
| Frontend       | Bootstrap 5 · jQuery              |
| Control de versiones | Git                         |

---

## 2. Estructura del proyecto

```
petbook/
│
├── app/
│   ├── controllers/        # Reciben la request, delegan al modelo, retornan la vista
│   │   ├── AuthController.php
│   │   ├── EventoController.php
│   │   ├── MascotaController.php
│   │   └── PublicacionController.php
│   │
│   ├── models/             # Lógica de negocio y acceso a datos (PDO)
│   │   ├── Usuario.php
│   │   ├── Mascota.php
│   │   ├── EventoMascota.php
│   │   └── Publicacion.php
│   │
│   ├── views/              # Presentación (HTML + PHP mínimo)
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── registro.php
│   │   ├── evento/
│   │   │   ├── index.php
│   │   │   ├── crear.php
│   │   │   └── detalle.php
│   │   ├── mascota/
│   │   │   ├── index.php
│   │   │   └── crear.php
│   │   └── errors/
│   │       ├── 404.php
│   │       └── 500.php
│   │
│   └── core/               # Infraestructura base del framework casero
│       ├── Database.php    # Singleton PDO
│       ├── Controller.php  # Clase base de controladores
│       └── Router.php      # Enrutador
│
├── config/
│   └── database.php        # Lee variables del .env y configura la conexión
│
├── public/                 # Único directorio expuesto al servidor web
│   ├── index.php           # Front controller — punto de entrada único
│   ├── .htaccess           # Rewrite rules hacia index.php
│   └── assets/
│       ├── css/
│       │   └── main.css
│       ├── js/
│       │   └── main.js
│       ├── images/
│       └── vendor/         # Bootstrap, jQuery (no versionar si se usan CDN)
│
├── docs/                   # Documentación técnica del proyecto
│   └── Petbook_Documentacion.docx
│
├── vendor/                 # Generado por Composer — NO versionar
├── storage/
│   └── uploads/            # Archivos subidos en desarrollo — NO versionar
│
├── .env                    # Variables de entorno locales — NO versionar
├── .env.example            # Plantilla pública del .env — SÍ versionar
├── .gitignore
├── composer.json
├── composer.lock           # SÍ versionar — garantiza reproducibilidad
├── GUIDE.md                # Este archivo
└── README.md               # Descripción pública del proyecto
```

> **Regla crítica:** el servidor web solo debe tener acceso al directorio `public/`.
> Todo lo que esté fuera de `public/` (modelos, configuración, `.env`) no debe ser accesible desde el navegador.
> Esto se configura en Apache apuntando el `DocumentRoot` a `petbook/public/`.

---

## 3. Arquitectura MVC

El proyecto usa **MVC clásico de dos capas**: Controller y Model. 

### Responsabilidades

**Controller**
- Recibe la request HTTP.
- Valida y sanitiza los parámetros de entrada.
- Llama al método correspondiente del modelo.
- Pasa los datos a la vista o redirige.
- No contiene lógica de negocio ni consultas SQL.

**Model**
- Contiene la lógica de negocio del dominio.
- Contiene todas las consultas SQL (prepared statements con PDO).
- Valida reglas de negocio antes de escribir en la base de datos.
- No conoce nada sobre HTTP, sesiones ni vistas.

**View**
- Solo presenta datos.
- No ejecuta lógica de negocio.
- El único PHP permitido en una vista es: condicionales simples (`if`), bucles (`foreach`) y escape de salida (`htmlspecialchars()`).
- No hace llamadas a modelos directamente.

### Ejemplo de flujo correcto

```
Request GET /evento/crear
    └── Router → EventoController::mostrarFormulario()
                    └── Pasa datos mínimos a la vista
                    └── View: evento/crear.php

Request POST /evento/crear
    └── Router → EventoController::crear()
                    └── Valida inputs
                    └── EventoMascota::crear($datos)   ← lógica en el modelo
                    └── Redirect o vista con resultado
```

---

## 4. Convenciones de nomenclatura

### PHP

| Elemento       | Convención              | Ejemplo                        |
|----------------|-------------------------|--------------------------------|
| Clases         | PascalCase              | `EventoMascota`, `AuthController` |
| Métodos        | camelCase               | `crearEvento()`, `buscarPorId()` |
| Variables      | camelCase               | `$idUsuario`, `$fechaCreacion` |
| Constantes     | UPPER_SNAKE_CASE        | `MAX_INTENTOS_LOGIN`           |
| Archivos de clase | PascalCase.php       | `EventoMascota.php`            |
| Archivos de vista | snake_case.php       | `crear_evento.php`             |

### Base de datos

| Elemento   | Convención      | Ejemplo                        |
|------------|-----------------|--------------------------------|
| Tablas     | PascalCase      | `EventoMascota`, `Publicacion` |
| Columnas   | PascalCase      | `Id_Evento`, `Fecha_Creacion`  |
| PK         | `Id_` + Entidad | `Id_Evento`, `Id_Usuario`      |
| FK         | `Id_` + Referencia | `Id_Mascota`, `Id_TipoEvento` |

### Frontend

| Elemento   | Convención  | Ejemplo              |
|------------|-------------|----------------------|
| Archivos JS  | kebab-case | `event-handler.js`   |
| Archivos CSS | kebab-case | `main.css`           |
| Clases CSS   | kebab-case | `.card-mascota`      |
| IDs HTML     | camelCase  | `formCrearEvento`    |

---

## 5. Configuración del entorno

El proyecto usa `vlucas/phpdotenv` para cargar variables de entorno desde un archivo `.env`.

### Pasos para configurar un entorno nuevo

```bash
# 1. Clonar el repositorio
git clone <URL_REPO>
cd petbook

# 2. Instalar dependencias PHP
composer install

# 3. Copiar la plantilla de variables de entorno
cp .env.example .env

# 4. Editar .env con los valores del entorno local
#    (credenciales de base de datos, etc.)

# 5. Crear la base de datos
#    Ejecutar el script: docs/petbook_schema.sql en MySQL

# 6. Configurar Apache para que el DocumentRoot apunte a public/
```

### Archivo `.env.example`

Ver el archivo `.env.example` en la raíz del proyecto. Contiene todas las variables necesarias con valores de ejemplo. **Nunca** copiar credenciales reales en `.env.example`.

---

## 6. Composer y autoload

El proyecto usa **PSR-4** para el autoload de clases. No es necesario incluir archivos manualmente con `require` o `include`.

### Namespaces registrados

```json
{
    "autoload": {
        "psr-4": {
            "App\\Controllers\\": "app/controllers/",
            "App\\Models\\":      "app/models/",
            "App\\Core\\":        "app/core/"
        }
    }
}
```

### Uso en el código

```php
// En index.php o donde se inicializa la app
require_once __DIR__ . '/../vendor/autoload.php';

// A partir de ahí, las clases se cargan automáticamente
use App\Controllers\EventoController;
use App\Models\EventoMascota;
```

### Comandos frecuentes

```bash
# Instalar dependencias (primera vez o luego de un pull con cambios en composer.json)
composer install

# Agregar una nueva dependencia
composer require nombre/paquete

# Regenerar el autoload después de agregar clases nuevas
composer dump-autoload
```

> `composer.lock` **debe versionarse**. Garantiza que todo el equipo instale exactamente las mismas versiones de dependencias.

---

## 7. Estrategia de ramas Git

```
main
 └── develop
      ├── feature/nombre-feature
      ├── feature/otro-feature
      └── fix/nombre-fix

hotfix/nombre-hotfix  (sale de main, mergea en main y develop)
```

| Rama          | Propósito                                          | Merge hacia        |
|---------------|----------------------------------------------------|--------------------|
| `main`        | Código en producción. Siempre estable.             | —                  |
| `develop`     | Integración continua. Base para nuevas features.   | `main`             |
| `feature/*`   | Desarrollo de una funcionalidad nueva.             | `develop`          |
| `fix/*`       | Corrección de bug detectado en develop.            | `develop`          |
| `hotfix/*`    | Corrección urgente de bug en producción.           | `main` y `develop` |

### Reglas de ramas

- `main` y `develop` son ramas protegidas. **Nadie hace push directo.**
- Todo cambio entra por Pull Request.
- Un PR requiere al menos una revisión de otro miembro del equipo antes del merge.
- Las ramas `feature/*` y `fix/*` se eliminan después del merge.
- Los nombres de rama usan **kebab-case** en minúsculas.

---

## 8. Flujo de trabajo Git

### Inicialización del repositorio (solo una vez)

```bash
git init
git add .
git commit -m "chore: initial project structure"
git remote add origin <URL_REPO>
git push -u origin main

# Crear rama develop
git checkout -b develop
git push -u origin develop
```

---

### Flujo para una feature nueva

```bash
# 1. Asegurarse de tener develop actualizado
git checkout develop
git pull origin develop

# 2. Crear la rama de feature desde develop
git checkout -b feature/login-usuario

# 3. Desarrollar. Commits atómicos y descriptivos
git add app/controllers/AuthController.php
git commit -m "feat: agregar método login en AuthController"

git add app/models/Usuario.php
git commit -m "feat: agregar método autenticar en modelo Usuario"

# 4. Antes de abrir el PR, sincronizar con develop
git fetch origin
git rebase origin/develop
# Si hay conflictos, resolverlos y continuar con: git rebase --continue

# 5. Subir la rama
git push -u origin feature/login-usuario

# 6. Abrir Pull Request hacia develop en GitHub/GitLab
#    Título: "feat: implementar login de usuario"
#    Descripción: qué hace, cómo probarlo, capturas si aplica

# 7. Después del merge: limpiar
git checkout develop
git pull origin develop
git branch -d feature/login-usuario
git push origin --delete feature/login-usuario
```

---

### Flujo para un hotfix

```bash
# 1. Partir desde main
git checkout main
git pull origin main
git checkout -b hotfix/token-expiracion

# 2. Corregir y commitear
git commit -m "fix: corregir validación de expiración en TokenRecuperacion"

# 3. Mergear en main
git checkout main
git merge --no-ff hotfix/token-expiracion
git push origin main

# 4. Mergear también en develop para no perder el fix
git checkout develop
git merge --no-ff hotfix/token-expiracion
git push origin develop

# 5. Limpiar
git branch -d hotfix/token-expiracion
git push origin --delete hotfix/token-expiracion
```

---

## 9. Convención de commits

Formato:

```
<tipo>: <descripción en imperativo, minúsculas, sin punto final>
```

La descripción responde a: *"Si aplico este commit, el proyecto va a..."*

### Tipos

| Tipo       | Cuándo usarlo                                              |
|------------|------------------------------------------------------------|
| `feat`     | Se agrega una funcionalidad nueva                          |
| `fix`      | Se corrige un bug                                          |
| `refactor` | Se reorganiza código sin cambiar comportamiento            |
| `docs`     | Solo se modifica documentación                             |
| `style`    | Formato, indentación, sin cambios de lógica               |
| `test`     | Se agregan o modifican pruebas                             |
| `chore`    | Mantenimiento: dependencias, configuración, gitignore      |
| `db`       | Cambios en el schema de la base de datos                   |

### Ejemplos correctos

```
feat: agregar formulario de creación de evento
fix: corregir validación de email en registro
refactor: extraer lógica de paginación al modelo Publicacion
docs: actualizar GUIDE.md con flujo de hotfix
chore: agregar vlucas/phpdotenv a composer.json
db: agregar índice en EventoMascota.Id_EstadoEvento
```

### Ejemplos incorrectos

```
fix cosas
WIP
cambios varios
arreglé el login
```

---

## 10. Seguridad y buenas prácticas

### Contraseñas

```php
// Siempre hashear con password_hash — nunca MD5 ni SHA1
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verificar
if (password_verify($inputPassword, $hashGuardado)) { ... }
```

### Consultas SQL

```php
// Siempre prepared statements — nunca concatenar inputs en SQL
$stmt = $pdo->prepare('SELECT * FROM Usuario WHERE Email = :email AND Eliminado = 0');
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
```

### Salida HTML

```php
// Siempre escapar antes de imprimir en HTML
echo htmlspecialchars($usuario['Nombre'], ENT_QUOTES, 'UTF-8');
```

### Variables de entorno

- Las credenciales de base de datos, claves de sesión y cualquier valor sensible van en `.env`.
- `.env` está en `.gitignore` y **nunca se sube al repositorio**.
- `.env.example` es la plantilla pública con valores vacíos o de ejemplo.

### Sesiones

```php
// Iniciar sesión siempre al comienzo del request, antes de cualquier output
session_start();

// Al hacer login, regenerar el ID de sesión para prevenir session fixation
session_regenerate_id(true);
```

### Inputs del usuario

- Validar tipo, longitud y formato en el **controlador** antes de pasarlos al modelo.
- El modelo puede aplicar una segunda validación de reglas de negocio.
- Nunca confiar en validaciones del lado del cliente (JavaScript) como única barrera.

### Archivos

- El directorio `storage/uploads/` no debe ser ejecutable por Apache.
- Validar tipo MIME real del archivo (no solo la extensión).
- Renombrar archivos subidos con un nombre generado internamente; nunca usar el nombre original del usuario.

---

---

*Cualquier duda sobre estas convenciones debe resolverse en el equipo antes de escribir código, no después.*
# Petbook

Petbook es una red social para mascotas. Permite registrar mascotas y publicar eventos de pérdida, encuentro y adopción para compartirlos en un feed.

## Funcionalidades implementadas

- Registro, inicio y cierre de sesión, con protección contra intentos repetidos.
- Alta, consulta y edición de las mascotas asociadas a una cuenta.
- Creación de eventos de pérdida, encuentro y adopción, con detalle y ubicación cuando corresponde.
- Feed de publicaciones y listado de las publicaciones propias.
- Cambio de estado y baja de un evento por parte de su dueño.

El detalle por historia de usuario y las funciones pendientes están en [Estado y continuidad](docs/estado_y_continuidad.md).

## Requisitos

- PHP 8.1 o superior, con PDO y el controlador `pdo_mysql` habilitados.
- MySQL 8.0 o compatible.
- Composer.
- Apache con `mod_rewrite` habilitado si se usa WampServer o LAMP.

## Instalación

### Windows con WampServer

1. Instalar WampServer con PHP 8.1 o superior y MySQL; confirmar que `pdo_mysql` y `mod_rewrite` estén habilitados.
2. Clonar o copiar este proyecto dentro del directorio de sitios de WampServer.
3. Abrir una terminal en la carpeta del proyecto y ejecutar:

   ```powershell
   composer install
   Copy-Item .env_example .env
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

4. Crear una base de datos local e importar `database/petbook.sql` con phpMyAdmin o el cliente MySQL. El script crea y selecciona la base `petbook`.
5. Editar `.env`: configurar `APP_URL`, `APP_KEY` (el valor generado), y `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` según WampServer.
6. Configurar un VirtualHost de Apache cuyo `DocumentRoot` apunte a la carpeta `petbook/public`. Luego iniciar/reiniciar los servicios de WampServer y abrir la URL local definida en `APP_URL`.

### Linux con el servidor local de PHP

Desde la raíz del repositorio:

```bash
composer install
cp .env_example .env
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Crear la base e importar el esquema (el usuario debe tener permisos para crear bases):

```bash
mysql -u root -p < database/petbook.sql
```

Completar `.env` con la configuración de MySQL, asignar la clave generada a `APP_KEY` y establecer `APP_URL=http://localhost:8080`. Levantar la aplicación:

```bash
composer serve
```

Abrir <http://localhost:8080>.

### Linux con LAMP

1. Instalar Apache, PHP 8.1 o superior con `pdo_mysql`, MySQL y Composer. Habilitar `mod_rewrite` en Apache.
2. Clonar el proyecto, instalar dependencias, crear `.env` desde `.env_example` e importar `database/petbook.sql` como en los pasos de Linux anteriores.
3. Configurar un VirtualHost con `DocumentRoot` apuntando a `petbook/public` y permitir las reglas `.htaccess` (por ejemplo, `AllowOverride All`). El directorio publicado debe ser `public`, no la raíz del proyecto.
4. Configurar `APP_URL` con la URL local del VirtualHost y completar la conexión de base de datos en `.env`; reiniciar Apache y abrir esa URL.

## Comandos Composer

- `composer serve`: servidor local de PHP en `localhost:8080`, con `public/` como document root.
- `composer test`: ejecuta PHPUnit.
- `composer lint`: ejecuta PHP_CodeSniffer sobre `app/`.

## Documentación

- [Estado de historias y próximos pasos](docs/estado_y_continuidad.md)
- [Historias de usuario](docs/historias_usurio.md)
- [Guía de desarrollo](docs/guia.md)
- [Guía técnica de componentes](docs/guia_desarrollo.md)
- [Convenciones de diseño](docs/conventions.md)

## Seguridad de configuración

`.env` contiene configuración local y no debe subirse al repositorio. `.env_example` es la plantilla versionada; copiarla como `.env` y completar los valores del entorno. No incluir credenciales reales en documentación ni en la plantilla.


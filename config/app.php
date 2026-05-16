<?php

// config/app.php
// Carga las variables del .env y define constantes globales de la aplicación.



// ── Rutas del sistema de archivos ─────────────────────────────────────────
// Se definen primero porque phpdotenv las necesita.
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ── Carga del .env con phpdotenv ──────────────────────────────────────────
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Variables obligatorias — si alguna falta, la app no arranca.
$dotenv->required([
    'APP_ENV',
    'APP_URL',
    'APP_KEY',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'SESSION_NAME',
    'SESSION_LIFETIME',
])->notEmpty();

// ── Helper para leer variables de entorno con valor por defecto ───────────
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== '') ? $value : $default;
}

// ── Entorno y debug ───────────────────────────────────────────────────────
define('APP_ENV',   env('APP_ENV',   'production'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));
define('APP_NAME',  env('APP_NAME',  'Petbook'));
define('APP_URL',   rtrim(env('APP_URL', ''), '/'));

// ── Zona horaria ──────────────────────────────────────────────────────────
date_default_timezone_set(env('APP_TIMEZONE', 'America/Argentina/Buenos_Aires'));

// ── Reporte de errores según entorno ──────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── Sesión ────────────────────────────────────────────────────────────────
define('SESSION_NAME',     env('SESSION_NAME',     'petbook_session'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 3600));

// ── Seguridad ─────────────────────────────────────────────────────────────
define('APP_KEY', env('APP_KEY', ''));
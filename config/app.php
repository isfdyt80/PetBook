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
    'SESSION_NAME',
    'SESSION_LIFETIME',
])->notEmpty();

// DB_PASSWORD se requiere pero puede estar vacía
$dotenv->required(['DB_PASSWORD']);

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

// ── Gate de producción ────────────────────────────────────────────────────
// Con APP_ENV=production la configuración crítica debe estar completa:
// debug desactivado, APP_KEY fuerte y URL bajo HTTPS. Si algo falla,
// la aplicación no arranca y solo se emite una respuesta genérica.
if (APP_ENV === 'production' && PHP_SAPI !== 'cli') {
    $motivos = [];

    if (APP_DEBUG) {
        $motivos[] = 'APP_DEBUG debe estar deshabilitado en producción.';
    }
    if (!preg_match('/^[0-9a-f]{64}$/i', (string) env('APP_KEY', ''))) {
        $motivos[] = 'APP_KEY debe tener 64 caracteres hexadecimales.';
    }
    if (!str_starts_with(APP_URL, 'https://')) {
        $motivos[] = 'APP_URL debe comenzar con https://.';
    }

    if ($motivos !== []) {
        error_log('[CONFIG] Configuración de producción inválida: ' . implode(' ', $motivos));
        http_response_code(500);
        exit('Error de configuración del servidor.');
    }
}

// ── Zona horaria ──────────────────────────────────────────────────────────
date_default_timezone_set(env('APP_TIMEZONE', 'America/Argentina/Buenos_Aires'));

// ── Reporte de errores según entorno ──────────────────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    // En producción los errores nunca se muestran al usuario, pero sí se
    // registran: error_reporting(0) silenciaría también el log del servidor.
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

// ── Sesión ────────────────────────────────────────────────────────────────
define('SESSION_NAME',     env('SESSION_NAME',     'petbook_session'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 3600));

// Endurecer el manejo de sesión antes de que index.php la inicie: modo
// estricto (PHP rechaza IDs no generados por él) y solo cookies (el ID
// nunca se propaga por URL).
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

// ── Seguridad ─────────────────────────────────────────────────────────────
define('APP_KEY', env('APP_KEY', ''));

// Detección de HTTPS, directa o detrás del proxy del despliegue (Cloudflare).
// APP_TRUST_PROXY=true habilita confiar en X-Forwarded-Proto, el header que
// Cloudflare agrega al pasar la request al vhost de Apache. Fuera de un
// proxy debe quedar en false: un cliente podría enviar ese header por su
// cuenta y falsear la detección.
$confiaProxy = filter_var(env('APP_TRUST_PROXY', false), FILTER_VALIDATE_BOOLEAN);
$protoProxy  = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
$scheme      = strtolower($_SERVER['REQUEST_SCHEME'] ?? '');

define('APP_HTTPS',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off')
    || ($confiaProxy && $protoProxy === 'https')
    || $scheme === 'https'
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
);

// ── Límites de intentos (rate limiting) ───────────────────────────────────
// Consumidos por App\Core\RateLimiter para login, registro y recuperación.
// Todos los valores tienen defaults: ninguna variable es obligatoria.
define('RATE_LIMIT_VENTANA_MINUTOS',       (int) env('RATE_LIMIT_VENTANA_MINUTOS', 15));
define('LOGIN_MAX_INTENTOS',               (int) env('LOGIN_MAX_INTENTOS', 5));
define('LOGIN_MAX_INTENTOS_ORIGEN',        (int) env('LOGIN_MAX_INTENTOS_ORIGEN', 15));
define('REGISTRO_MAX_POR_ORIGEN',          (int) env('REGISTRO_MAX_POR_ORIGEN', 3));
define('RECUPERACION_MAX_INTENTOS',        (int) env('RECUPERACION_MAX_INTENTOS', 3));
define('RECUPERACION_MAX_INTENTOS_ORIGEN', (int) env('RECUPERACION_MAX_INTENTOS_ORIGEN', 10));
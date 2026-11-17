<?php

// public/index.php
// Front controller: punto de entrada único de la aplicación.
// El .htaccess redirige todas las requests aquí.

// ── Autoload de Composer (PSR-4) ──────────────────────────────────────────
// Debe cargarse PRIMERO para que phpdotenv y todas las clases estén disponibles.
// Ejecutar `composer install` antes de levantar el proyecto.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── Configuración de la aplicación ───────────────────────────────────────
// Carga el .env con phpdotenv y define constantes globales.
// Requiere que vendor/autoload.php ya esté cargado.
require_once dirname(__DIR__) . '/config/app.php';

// ── Encabezados de seguridad ──────────────────────────────────────────────
// Se envían antes de cualquier salida. La CSP restringe el embebido en
// iframes externos; HSTS solo se emite cuando la conexión es HTTPS real.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header_remove('X-Powered-By');
if (APP_HTTPS) {
    header('Strict-Transport-Security: max-age=31536000');
}

// ── Manejo global de excepciones no capturadas ────────────────────────────
// Captura cualquier Throwable que ocurra antes o fuera del router.
// El detalle técnico va al log del servidor; al usuario solo la vista 500.
set_exception_handler(function (\Throwable $e): void {
    http_response_code(500);

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<h1>Error no capturado</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n\n"
            . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        error_log('[EXCEPTION] ' . get_class($e) . ': ' . $e->getMessage()
            . ' en ' . $e->getFile() . ':' . $e->getLine()
            . ' desde ' . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
        require_once APP_PATH . '/views/errors/500.php';
    }

    exit;
});

// ── Sesión ────────────────────────────────────────────────────────────────
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => (int) SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => APP_HTTPS,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ── Router ────────────────────────────────────────────────────────────────
$router = new \App\Core\Router();

// Carga las definiciones de rutas (get, post, any)
require_once APP_PATH . '/routes.php';

// Parsea la URL entrante y despacha al controlador correspondiente
$router->dispatch();
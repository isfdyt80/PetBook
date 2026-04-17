<?php

// public/index.php
// Front controller: punto de entrada único de la aplicación.
// El .htaccess redirige todas las requests aquí.

// ── Configuración de la aplicación ───────────────────────────────────────
// Define constantes: APP_PATH, APP_URL, CONFIG_PATH, APP_ENV, APP_DEBUG, etc.
require_once dirname(__DIR__) . '/config/app.php';

// ── Autoload de Composer (PSR-4) ──────────────────────────────────────────
// Carga automáticamente todas las clases declaradas en composer.json.
// Ejecutar `composer install` antes de levantar el proyecto.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── Manejo global de excepciones no capturadas ────────────────────────────
// Captura cualquier Throwable que ocurra antes o fuera del router.
set_exception_handler(function (\Throwable $e): void {
    http_response_code(500);

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<h1>Error no capturado</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n\n"
            . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        error_log('[EXCEPTION] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
        require_once APP_PATH . '/views/errors/500.php';
    }

    exit;
});

// ── Sesión ────────────────────────────────────────────────────────────────
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => (int) SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => (APP_ENV === 'production'),
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
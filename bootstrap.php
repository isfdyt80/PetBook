<?php
// bootstrap.php
require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/database/Conexion.php';
use Core\Env;

// Autoload básico para clases
spl_autoload_register(function ($class) {
    $base_dirs = [
        __DIR__ . '/backend/modelos',
        __DIR__ . '/dal',
        __DIR__ . '/database',
        __DIR__ . '/core'
    ];
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    foreach ($base_dirs as $base_dir) {
        $file = $base_dir . DIRECTORY_SEPARATOR . basename($class);
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Cargar variables de entorno
Env::load(__DIR__ . '/.env');

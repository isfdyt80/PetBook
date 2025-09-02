<?php
// bootstrap.php
require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/database/Conexion.php';
use Core\Env;

// Autoload básico para clases
spl_autoload_register(function ($class) {
    $base_dir = __DIR__;
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = $base_dir . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Cargar variables de entorno
Env::load(__DIR__ . '/.env');

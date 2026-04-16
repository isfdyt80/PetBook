<?php
// bootstrap.php
require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/database/Conexion.php';
use Core\Env;

// PSR-4 style autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\Models\\'  => __DIR__ . '/App/Models/',
        'Modelos\\'      => __DIR__ . '/backend/modelos/',
        'DAL\\'          => __DIR__ . '/dal/',
        'Database\\'     => __DIR__ . '/database/',
        'Core\\'         => __DIR__ . '/core/',
    ];

    foreach ($prefixes as $prefix => $base_dir) {
        if (strncmp($prefix, $class, strlen($prefix)) === 0) {
            $relative = substr($class, strlen($prefix));
            $file     = $base_dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Cargar variables de entorno
Env::load(__DIR__ . '/.env');

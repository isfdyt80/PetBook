<?php

// config/database.php
// Parámetros de conexión a la base de datos.
// Se leen desde las variables de entorno cargadas por config/app.php.
// Este archivo NO contiene credenciales hardcodeadas.

return [
    'host'    => env('DB_HOST',     '127.0.0.1'),
    'port'    => env('DB_PORT',     '3306'),
    'dbname'  => env('DB_DATABASE', 'petbook'),
    'user'    => env('DB_USERNAME', 'root'),
    'pass'    => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',

    // DSN armado para PDO
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST',     '127.0.0.1'),
        env('DB_PORT',     '3306'),
        env('DB_DATABASE', 'petbook')
    ),

    // Opciones de PDO
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Fuerza charset y collation a nivel de conexión
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];
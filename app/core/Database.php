<?php

// app/core/Database.php
// Singleton de conexión PDO. Asegura una única conexión durante toda la request.

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // Constructor privado: nadie puede instanciar esta clase directamente
    private function __construct() {}
    private function __clone() {}

    /**
     * Devuelve la instancia única de PDO.
     * La crea la primera vez que se llama.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }

        return self::$instance;
    }

    /**
     * Crea la conexión PDO usando los parámetros de config/database.php.
     *
     * @throws \RuntimeException Si el archivo de configuración está incompleto.
     * @throws PDOException      Si la conexión a MySQL falla (solo en modo debug).
     */
    private static function connect(): PDO
    {
        $config = require CONFIG_PATH . '/database.php';

        if (empty($config['dsn']) || empty($config['user']) || !isset($config['pass'])) {
            throw new \RuntimeException(
                'El archivo config/database.php está incompleto. Revisá las claves: dsn, user, pass.'
            );
        }

        try {
            return new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass'],
                $config['options'] ?? []
            );
        } catch (PDOException $e) {
            // En producción nunca mostramos el mensaje real al usuario
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new PDOException(
                    'Error de conexión a la base de datos: ' . $e->getMessage(),
                    (int) $e->getCode()
                );
            }

            // En producción: loguear y mostrar mensaje genérico
            error_log('[DB ERROR] ' . $e->getMessage());
            http_response_code(500);
            die('Error interno del servidor. Por favor intentá más tarde.');
        }
    }

    /**
     * Cierra la conexión explícitamente.
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
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

    // ── Transacciones ────────────────────────────────────────────────────

    /**
     * Ejecuta $fn dentro de una transacción con commit automático
     * y rollback ante cualquier excepción.
     *
     * Es el mecanismo obligatorio para escrituras multi-tabla
     * (ver docs/conventions.md — decisión "Transacciones").
     * No usar beginTransaction()/commit()/rollBack() a mano en código nuevo:
     * el wrapper no depende de que cada dev se acuerde del rollBack().
     *
     * Semántica de anidamiento: si ya hay una transacción activa
     * (inTransaction() === true), $fn se ejecuta dentro de ella sin abrir
     * otra — "join", sin savepoints. Un fallo dentro revierte la
     * transacción entera, que es el comportamiento deseado en Petbook.
     *
     * Uso (desde un modelo, se lee más natural con $this->transactional()):
     *   $id = $this->transactional(function () use ($datos) {
     *       $idEvento = $this->insert([...]);
     *       $this->execute('INSERT INTO Publicacion ...', [...]);
     *       return $idEvento;
     *   });
     *
     * Uso (desde un controlador orquestando varios modelos):
     *   $id = Database::transactional(function () use ($datos) {
     *       $idMascota = (new Mascota())->registrar($datos);
     *       return (new Publicacion())->crear([...]);
     *   });
     *   Nota: el flujo HU-08/09/10 ya está encapsulado en
     *   EventoMascota::crear($datos, $tipoNombre), que abre su propia
     *   transacción y no requiere orquestación desde el controlador.
     *
     * @param  callable $fn  Bloque de escritura. Su valor de retorno se
     *                       devuelve desde commit(); si lanza, se revierte.
     * @return mixed        Lo que devuelva $fn.
     * @throws \Throwable   Re-lanza la excepción original tras el rollBack().
     */
    public static function transactional(callable $fn): mixed
    {
        $pdo = self::getInstance();

        // Transacción ya activa: sumarse a ella en vez de anidar.
        if ($pdo->inTransaction()) {
            return $fn();
        }

        $pdo->beginTransaction();

        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Abre una transacción a mano. Prefiere Database::transactional():
     * estos métodos existen para los casos excepcionales que no encajan
     * en un callable, y obligan a que el llamador gestione commit y
     * rollBack() correctamente por su cuenta.
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Confirma la transacción activa. Ver beginTransaction().
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Revierte la transacción activa. Ver beginTransaction().
     */
    public static function rollBack(): void
    {
        self::getInstance()->rollBack();
    }
}

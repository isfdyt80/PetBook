<?php

// app/core/Model.php
// Clase base para todos los modelos.
// Provee acceso a PDO y helpers genéricos de consulta.

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected PDO $db;

    /**
     * Nombre de la tabla en la base de datos.
     * Cada modelo hijo debe sobreescribir esta propiedad.
     *
     * Ejemplo en Usuario.php:
     *   protected string $table = 'Usuario';
     */
    protected string $table = '';

    /**
     * Nombre de la clave primaria de la tabla.
     * Sobreescribir en cada modelo hijo siguiendo la convención Id_Tabla.
     *
     * Ejemplo en Usuario.php:
     *   protected string $pk = 'Id_Usuario';
     */
    protected string $pk = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Métodos genéricos ────────────────────────────────────────────────

    /**
     * Devuelve todos los registros activos de la tabla.
     * Filtra Eliminado = 0 si la tabla tiene ese campo.
     *
     * Para consultas con condiciones específicas usar query().
     */
    public function all(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";

        // Aplica soft delete si la tabla lo soporta
        if ($this->hasSoftDelete()) {
            $sql .= ' WHERE Eliminado = 0';
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Devuelve un registro por su clave primaria o false si no existe.
     * Filtra Eliminado = 0 si la tabla tiene ese campo.
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->pk}` = :id";

        if ($this->hasSoftDelete()) {
            $sql .= ' AND Eliminado = 0';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Inserta un registro y devuelve el ID generado.
     * Recibe un array asociativo columna => valor.
     *
     * Uso:
     *   $id = $this->insert(['Nombre' => 'Rex', 'Id_Especie' => 1]);
     */
    public function insert(array $data): int
    {
        $cols         = array_keys($data);
        // Placeholders posicionales: PDO no acepta nombres con caracteres
        // fuera de [a-zA-Z0-9_], y las columnas pueden llamarse 'Tamaño'.
        $placeholders = array_fill(0, count($cols), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $cols)),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un registro por su clave primaria.
     * Devuelve true si se modificó al menos una fila.
     *
     * Uso:
     *   $this->update(5, ['Nombre' => 'Max', 'Color' => 'Negro']);
     */
    public function update(int $id, array $data): bool
    {
        // Placeholders posicionales por la misma razón que insert():
        // 'Tamaño' no es un nombre de parámetro PDO válido.
        $sets = array_map(fn($c) => "`{$c}` = ?", array_keys($data));

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = ?',
            $this->table,
            implode(', ', $sets),
            $this->pk
        );

        $params   = array_values($data);
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete: marca el registro como eliminado (Eliminado = 1).
     * En Petbook NUNCA se usa delete() físico en entidades principales.
     *
     * Uso:
     *   $this->softDelete(5);
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET Eliminado = 1 WHERE `{$this->pk}` = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    // ── Helpers de consulta ──────────────────────────────────────────────

    /**
     * Ejecuta $fn dentro de una transacción con commit automático
     * y rollback ante cualquier excepción.
     *
     * Alias protegido de Database::transactional() para lectura natural
     * dentro de los modelos: $this->transactional(...).
     * Es el mecanismo obligatorio para escrituras multi-tabla — ver
     * docs/conventions.md. No escribir begin/commit/rollBack a mano.
     *
     * Semántica de anidamiento: si ya hay una transacción activa, $fn se
     * ejecuta dentro de ella sin abrir otra (join, sin savepoints).
     *
     * Uso:
     *   $id = $this->transactional(function () use ($datos) {
     *       $idEvento = $this->insert([...]);
     *       $this->execute('INSERT INTO Publicacion ...', [...]);
     *       return $idEvento;
     *   });
     *
     * @param  callable $fn  Bloque de escritura; su retorno se devuelve.
     * @return mixed        Lo que devuelva $fn.
     * @throws \Throwable   Re-lanza la excepción original tras el rollBack().
     */
    protected function transactional(callable $fn): mixed
    {
        return Database::transactional($fn);
    }

    /**
     * Ejecuta una consulta preparada y devuelve todos los resultados.
     *
     * Uso:
     *   $this->query(
     *       'SELECT * FROM EventoMascota WHERE Id_Mascota = :id AND Eliminado = 0',
     *       [':id' => $idMascota]
     *   );
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta preparada y devuelve el primer resultado.
     *
     * Uso:
     *   $this->queryOne(
     *       'SELECT * FROM Usuario WHERE Email = :email AND Eliminado = 0',
     *       [':email' => $email]
     *   );
     */
    protected function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Ejecuta una consulta sin retorno de filas (INSERT, UPDATE, DELETE custom).
     * Devuelve el PDOStatement para poder llamar rowCount() si se necesita.
     *
     * Uso:
     *   $stmt = $this->execute(
     *       'UPDATE EventoMascota SET Id_EstadoEvento = :estado WHERE Id_Evento = :id',
     *       [':estado' => $idEstado, ':id' => $id]
     *   );
     *   return $stmt->rowCount() > 0;
     */
    protected function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── Helpers internos ─────────────────────────────────────────────────

    /**
     * Verifica si la tabla tiene el campo Eliminado consultando el esquema.
     * Resultado cacheado en memoria para no repetir la consulta por request.
     */
    private array $softDeleteCache = [];

    private function hasSoftDelete(): bool
    {
        if (isset($this->softDeleteCache[$this->table])) {
            return $this->softDeleteCache[$this->table];
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = :table
               AND COLUMN_NAME  = 'Eliminado'"
        );
        $stmt->execute([':table' => $this->table]);

        $this->softDeleteCache[$this->table] = (bool) $stmt->fetchColumn();
        return $this->softDeleteCache[$this->table];
    }
}
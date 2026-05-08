<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;

    protected string $table;

    protected string $pk = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los registros no eliminados.
     */
    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE Eliminado = 0";

        return $this->query($sql);
    }

    /**
     * Busca por clave primaria.
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE {$this->pk} = :id
                AND Eliminado = 0";

        $resultado = $this->query($sql, [
            ':id' => $id
        ]);

        return $resultado[0] ?? false;
    }

    /**
     * Inserta un registro.
     */
    public function insert(array $datos): int
    {
        $columnas = array_keys($datos);

        $campos = implode(', ', $columnas);

        $params = implode(', ', array_map(
            fn($col) => ':' . $col,
            $columnas
        ));

        $sql = "INSERT INTO {$this->table}
                ({$campos})
                VALUES ({$params})";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($datos);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un registro.
     */
    public function update(
        int $id,
        array $datos
    ): bool {

        $set = implode(', ', array_map(
            fn($col) => "{$col} = :{$col}",
            array_keys($datos)
        ));

        $datos[$this->pk] = $id;

        $sql = "UPDATE {$this->table}
                SET {$set}
                WHERE {$this->pk} = :{$this->pk}";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($datos);
    }

    /**
     * Soft delete.
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET Eliminado = 1
                WHERE {$this->pk} = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Ejecuta consultas SELECT.
     */
    public function query(
        string $sql,
        array $params = []
    ): array {

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE personalizados.
     */
    public function execute(
        string $sql,
        array $params = []
    ): bool {

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }
}
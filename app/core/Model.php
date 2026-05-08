<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $pk;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$this->pk} = :id 
                AND Eliminado = 0";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function all(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE Eliminado = 0";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns)
                VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    protected function update(int $id, array $data): bool
    {
        $set = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }

        $sql = "UPDATE {$this->table}
                SET " . implode(', ', $set) . "
                WHERE {$this->pk} = :id
                AND Eliminado = 0";

        $stmt = $this->db->prepare($sql);

        $data['id'] = $id;

        return $stmt->execute($data);
    }

    protected function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table}
                SET Eliminado = 1
                WHERE {$this->pk} = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
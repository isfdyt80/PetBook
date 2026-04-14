<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Persona 
{

    public static function crear(array $datos): int 
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO persona (nombre, apellido, eliminado)
                VALUES (:nombre, :apellido, 0)";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':apellido' => $datos['apellido']
        ]);

        return (int)$db->lastInsertId();
    }

    public static function buscarPorId(int $id): ?array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM persona 
                WHERE id = :id AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        return $persona ?: null;
    }

    public static function anonimizar(int $id): bool 
    {
        $db = Database::getConnection();

        $sql = "UPDATE persona 
                SET nombre = 'Anonimo',
                    apellido = '',
                    eliminado = 1
                WHERE id = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Rol 
{

    public static function listar(): array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM rol WHERE eliminado = 0";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorNombre(string $nombre): ?array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM rol 
                WHERE nombre = :nombre AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre
        ]);

        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rol ?: null;
    }
}
<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class EstadoPublicacion
{
    /**
     * lista todos los estados posibles de publicacion
     */
    public static function listar(): array
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM EstadoPublicacion";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un estado por su nombre 
     */
    public static function buscarPorNombre(string $nombre): ?array
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM EstadoPublicacion
                WHERE Nombre = :nombre";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }
}
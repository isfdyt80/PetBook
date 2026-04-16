<?php

namespace App\Models;

use Database\Conexion;
use PDO;

class EstadoPublicacion
{
    public function listar(): array
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare("SELECT * FROM EstadoPublicacion WHERE Eliminado = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM EstadoPublicacion WHERE Nombre = :nombre AND Eliminado = 0"
        );
        $stmt->execute([':nombre' => $nombre]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }
}

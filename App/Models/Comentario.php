<?php

namespace App\Models;

use Database\Conexion;
use PDO;

class Comentario
{
    public function crear(array $datos): int
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "INSERT INTO Comentario (IdPublicacion, IdUsuario, Contenido, FechaCreacion, Eliminado)
             VALUES (:idPublicacion, :idUsuario, :contenido, NOW(), 0)"
        );
        $stmt->execute([
            ':idPublicacion' => $datos['IdPublicacion'],
            ':idUsuario'     => $datos['IdUsuario'],
            ':contenido'     => $datos['Contenido'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function listarPorPublicacion(int $idPublicacion, int $pagina, int $porPagina): array
    {
        $pdo    = Conexion::getConexion();
        $offset = ($pagina - 1) * $porPagina;
        $stmt   = $pdo->prepare(
            "SELECT * FROM Comentario
             WHERE IdPublicacion = :idPublicacion AND Eliminado = 0
             ORDER BY FechaCreacion ASC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':idPublicacion', $idPublicacion, PDO::PARAM_INT);
        $stmt->bindValue(':limit',         $porPagina,     PDO::PARAM_INT);
        $stmt->bindValue(':offset',        $offset,        PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar(int $id): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Comentario SET Eliminado = 1 WHERE Id = :id AND Eliminado = 0"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

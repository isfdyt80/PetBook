<?php

namespace App\Models;

use Database\Conexion;
use PDO;

class Publicacion
{
    public function crear(array $datos): int
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "INSERT INTO Publicacion (IdUsuario, IdEstado, Contenido, FechaCreacion, Eliminado)
             VALUES (:idUsuario, :idEstado, :contenido, NOW(), 0)"
        );
        $stmt->execute([
            ':idUsuario' => $datos['IdUsuario'],
            ':idEstado'  => $datos['IdEstado'],
            ':contenido' => $datos['Contenido'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "SELECT * FROM Publicacion WHERE Id = :id AND Eliminado = 0"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function listarActivas(int $pagina, int $porPagina): array
    {
        $pdo    = Conexion::getConexion();
        $offset = ($pagina - 1) * $porPagina;
        $stmt   = $pdo->prepare(
            "SELECT * FROM Publicacion WHERE Eliminado = 0
             ORDER BY FechaCreacion DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar(int $id, string $contenido): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Publicacion SET Contenido = :contenido WHERE Id = :id AND Eliminado = 0"
        );
        $stmt->execute([':contenido' => $contenido, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function cambiarEstado(int $id, int $idEstado): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Publicacion SET IdEstado = :idEstado WHERE Id = :id AND Eliminado = 0"
        );
        $stmt->execute([':idEstado' => $idEstado, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Publicacion SET Eliminado = 1 WHERE Id = :id AND Eliminado = 0"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}

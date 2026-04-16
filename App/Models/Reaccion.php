<?php

namespace App\Models;

use Database\Conexion;
use PDO;

class Reaccion
{
    public function crear(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "INSERT INTO Reaccion (IdUsuario, IdPublicacion, IdTipoReaccion, Eliminado)
             VALUES (:idUsuario, :idPublicacion, :idTipoReaccion, 0)"
        );
        $stmt->execute([
            ':idUsuario'      => $idUsuario,
            ':idPublicacion'  => $idPublicacion,
            ':idTipoReaccion' => $idTipoReaccion,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function actualizar(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Reaccion SET IdTipoReaccion = :idTipoReaccion
             WHERE IdUsuario = :idUsuario AND IdPublicacion = :idPublicacion AND Eliminado = 0"
        );
        $stmt->execute([
            ':idTipoReaccion' => $idTipoReaccion,
            ':idUsuario'      => $idUsuario,
            ':idPublicacion'  => $idPublicacion,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $idUsuario, int $idPublicacion): bool
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "UPDATE Reaccion SET Eliminado = 1
             WHERE IdUsuario = :idUsuario AND IdPublicacion = :idPublicacion AND Eliminado = 0"
        );
        $stmt->execute([
            ':idUsuario'     => $idUsuario,
            ':idPublicacion' => $idPublicacion,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function contarPorPublicacion(int $idPublicacion): array
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare(
            "SELECT tr.Nombre AS TipoReaccion, COUNT(*) AS Total
             FROM Reaccion r
             INNER JOIN TipoReaccion tr ON r.IdTipoReaccion = tr.Id
             WHERE r.IdPublicacion = :idPublicacion AND r.Eliminado = 0 AND tr.Eliminado = 0
             GROUP BY r.IdTipoReaccion, tr.Nombre"
        );
        $stmt->execute([':idPublicacion' => $idPublicacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

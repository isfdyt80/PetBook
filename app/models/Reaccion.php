<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Reaccion
{
    public static function crear(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO Reaccion 
                (Id_Usuario, Id_Publicacion, Id_TipoReaccion)
                VALUES (:idUsuario, :idPublicacion, :idTipoReaccion)
                ON DUPLICATE KEY UPDATE 
                Id_TipoReaccion = VALUES(Id_TipoReaccion)";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':idPublicacion' => $idPublicacion,
            ':idTipoReaccion' => $idTipoReaccion
        ]);
    }

    public static function actualizar(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        $db = Database::getConnection();

        $sql = "UPDATE Reaccion
                SET Id_TipoReaccion = :idTipoReaccion
                WHERE Id_Usuario = :idUsuario
                AND Id_Publicacion = :idPublicacion";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idTipoReaccion' => $idTipoReaccion,
            ':idUsuario' => $idUsuario,
            ':idPublicacion' => $idPublicacion
        ]);
    }

    public static function eliminar(int $idUsuario, int $idPublicacion): bool
    {
        $db = Database::getConnection();

        $sql = "DELETE FROM Reaccion
                WHERE Id_Usuario = :idUsuario
                AND Id_Publicacion = :idPublicacion";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':idPublicacion' => $idPublicacion
        ]);
    }

    public static function contarPorPublicacion(int $idPublicacion): array
    {
        $db = Database::getConnection();

        $sql = "SELECT tr.Nombre, COUNT(*) as cantidad
                FROM Reaccion r
                JOIN TipoReaccion tr ON r.Id_TipoReaccion = tr.Id_TipoReaccion
                WHERE r.Id_Publicacion = :idPublicacion
                GROUP BY tr.Nombre";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':idPublicacion' => $idPublicacion
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
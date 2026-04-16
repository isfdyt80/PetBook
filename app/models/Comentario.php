<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Comentario
{
    public static function crear(array $datos): int
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO Comentario 
                (Id_Publicacion, Id_Usuario, Contenido, Eliminado)
                VALUES (:idPublicacion, :idUsuario, :contenido, 0)";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':idPublicacion' => $datos['id_publicacion'],
            ':idUsuario' => $datos['id_usuario'],
            ':contenido' => $datos['contenido']
        ]);

        return (int)$db->lastInsertId();
    }

    public static function listarPorPublicacion(int $idPublicacion, int $pagina, int $porPagina): array
    {
        $db = Database::getConnection();

        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);

        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT * FROM Comentario
                WHERE Id_Publicacion = :idPublicacion
                AND Eliminado = 0
                ORDER BY Fecha ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':idPublicacion', $idPublicacion, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function eliminar(int $id): bool
    {
        $db = Database::getConnection();

        $sql = "UPDATE Comentario
                SET Eliminado = 1
                WHERE Id_Comentario = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
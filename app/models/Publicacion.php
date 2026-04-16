<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Publicacion
{
    /**
     * Crea una publicación asociada a un evento.
     * Se inicializa como no eliminada (soft delete).
     */
    public static function crear(array $datos): int
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO Publicacion 
                (Id_Evento, Id_Usuario, Id_EstadoPublicacion, Contenido, Eliminado)
                VALUES (:idEvento, :idUsuario, :idEstado, :contenido, 0)";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':idEvento' => $datos['id_evento'],
            ':idUsuario' => $datos['id_usuario'],
            ':idEstado' => $datos['id_estado'],
            ':contenido' => $datos['contenido']
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Busca una publicación por ID, solo si no está eliminada.
     */
    public static function buscarPorId(int $id): ?array
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM Publicacion
                WHERE Id_Publicacion = :id AND Eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    /**
     * Lista publicaciones activas (Estado = ACTIVA) con paginación.
     */
    public static function listarActivas(int $pagina, int $porPagina): array
    {
        $db = Database::getConnection();

        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);

        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT * FROM Publicacion
                WHERE Eliminado = 0
                AND Id_EstadoPublicacion = (
                    SELECT Id_EstadoPublicacion 
                    FROM EstadoPublicacion 
                    WHERE Nombre = 'ACTIVA'
                )
                ORDER BY Fecha_Publicacion DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el contenido de una publicación.
     * No permite modificar publicaciones eliminadas.
     */
    public static function actualizar(int $id, string $contenido): bool
    {
        $db = Database::getConnection();

        $sql = "UPDATE Publicacion
                SET Contenido = :contenido,
                    Editado = 1
                WHERE Id_Publicacion = :id
                AND Eliminado = 0";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':contenido' => $contenido,
            ':id' => $id
        ]);
    }

    /**
     * Cambia el estado de una publicación 
     * No permite modificar publicaciones eliminadas.
     */
    public static function cambiarEstado(int $id, int $idEstado): bool
    {
        $db = Database::getConnection();

        $sql = "UPDATE Publicacion
                SET Id_EstadoPublicacion = :idEstado
                WHERE Id_Publicacion = :id
                AND Eliminado = 0";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idEstado' => $idEstado,
            ':id' => $id
        ]);
    }

    /**
     * Soft delete: marca la publicación como eliminada
     */
    public static function eliminar(int $id): bool
    {
        $db = Database::getConnection();

        $sql = "UPDATE Publicacion
                SET Eliminado = 1
                WHERE Id_Publicacion = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
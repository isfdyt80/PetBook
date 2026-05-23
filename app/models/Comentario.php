<?php

namespace App\Models;

use App\Core\Model;

class Comentario extends Model
{
    protected string $table = 'Comentario';
    protected string $pk    = 'Id_Comentario';

    /**
     * Crea un nuevo comentario.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Id_Publicacion' => $datos['Id_Publicacion'],
            'Id_Usuario'     => $datos['Id_Usuario'],
            'Contenido'      => $datos['Contenido'],
            'Eliminado'      => 0
        ]);
    }

    /**
     * Lista comentarios activos de una publicación con paginación.
     */
    public function listarPorPublicacion(
        int $idPublicacion,
        int $pagina,
        int $porPagina
    ): array {

        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);

        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT
                    Id_Comentario,
                    Id_Publicacion,
                    Id_Usuario,
                    Contenido,
                    Fecha
                FROM Comentario
                WHERE Id_Publicacion = :idPublicacion
                AND Eliminado = 0
                ORDER BY Fecha ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':idPublicacion',
            $idPublicacion,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $porPagina,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            \PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Realiza soft delete de un comentario.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
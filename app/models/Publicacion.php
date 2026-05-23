<?php

namespace App\Models;

use App\Core\Model;

class Publicacion extends Model
{
    protected string $table = 'Publicacion';
    protected string $pk    = 'Id_Publicacion';

    /**
     * Crea una nueva publicación.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Id_Evento'             => $datos['Id_Evento'],
            'Id_Usuario'            => $datos['Id_Usuario'],
            'Id_EstadoPublicacion'  => $datos['Id_EstadoPublicacion'],
            'Contenido'             => $datos['Contenido'],
            'Eliminado'             => 0
        ]);
    }

    /**
     * Busca una publicación por ID.
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT
                    Id_Publicacion,
                    Id_Evento,
                    Id_Usuario,
                    Id_EstadoPublicacion,
                    Contenido,
                    Fecha_Publicacion,
                    Editado
                FROM Publicacion
                WHERE Id_Publicacion = :id
                AND Eliminado = 0";

        $resultado = $this->query($sql, [
            ':id' => $id
        ]);

        return $resultado[0] ?? false;
    }

    /**
     * Lista publicaciones activas con paginación.
     */
    public function listarActivas(
        int $pagina,
        int $porPagina
    ): array {

        $pagina = max(1, $pagina);
        $porPagina = max(1, $porPagina);

        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT
                    Id_Publicacion,
                    Id_Evento,
                    Id_Usuario,
                    Id_EstadoPublicacion,
                    Contenido,
                    Fecha_Publicacion,
                    Editado
                FROM Publicacion
                WHERE Eliminado = 0
                AND Id_EstadoPublicacion = (
                    SELECT Id_EstadoPublicacion
                    FROM EstadoPublicacion
                    WHERE Nombre = 'ACTIVA'
                )
                ORDER BY Fecha_Publicacion DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

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
     * Actualiza el contenido de una publicación.
     */
    public function actualizar(
        int $id,
        string $contenido
    ): bool {

        return $this->update($id, [
            'Contenido' => $contenido,
            'Editado'   => 1
        ]);
    }

    /**
     * Cambia el estado de una publicación.
     */
    public function cambiarEstado(
        int $id,
        int $idEstado
    ): bool {

        return $this->update($id, [
            'Id_EstadoPublicacion' => $idEstado
        ]);
    }

    /**
     * Realiza soft delete.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
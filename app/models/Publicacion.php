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
            'Id_Evento'            => $datos['id_evento'],
            'Id_Usuario'           => $datos['id_usuario'],
            'Id_EstadoPublicacion' => $datos['id_estado'],
            'Contenido'            => $datos['contenido'],
            'Eliminado'            => 0
        ]);
    }

    /**
     * Busca una publicación por ID.
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->find($id);
    }

    /**
     * Lista publicaciones activas con paginación.
     */
    public function listarActivas(int $pagina, int $porPagina): array
    {
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

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el contenido de una publicación.
     */
    public function actualizar(int $id, string $contenido): bool
    {
        return $this->update($id, [
            'Contenido' => $contenido,
            'Editado'   => 1
        ]);
    }

    /**
     * Cambia el estado de una publicación.
     */
    public function cambiarEstado(int $id, int $idEstado): bool
    {
        return $this->update($id, [
            'Id_EstadoPublicacion' => $idEstado
        ]);
    }

    /**
     * Realiza soft delete de una publicación.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
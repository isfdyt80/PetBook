<?php

namespace App\Models;

use App\Core\Model;

class Publicacion extends Model
{
    protected string $table = 'Publicacion';
    protected string $pk    = 'Id_Publicacion';

    /**
     * Crea una nueva publicación y devuelve el ID generado.
     *
     * @param  array $datos  Debe contener Id_Evento, Id_Usuario,
     *                       Id_EstadoPublicacion, Contenido.
     * @return int           ID del registro recién insertado.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Id_Evento'            => $datos['Id_Evento'],
            'Id_Usuario'           => $datos['Id_Usuario'],
            'Id_EstadoPublicacion' => $datos['Id_EstadoPublicacion'],
            'Contenido'            => $datos['Contenido'],
            'Eliminado'            => 0,
        ]);
    }

    /**
     * Busca una publicación por su clave primaria.
     * Devuelve false si no existe o está eliminada.
     *
     * @param  int         $id  Id_Publicacion a buscar.
     * @return array|false
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->queryOne(
            'SELECT
                 Id_Publicacion,
                 Id_Evento,
                 Id_Usuario,
                 Id_EstadoPublicacion,
                 Contenido,
                 Fecha_Publicacion,
                 Editado
             FROM Publicacion
             WHERE Id_Publicacion = :id AND Eliminado = 0',
            [':id' => $id]
        ) ?: false;
    }

    /**
     * Lista publicaciones activas con paginación.
     * Resuelve el ID del estado ACTIVA por nombre para evitar números mágicos.
     * Usa bindValue con PDO::PARAM_INT para LIMIT y OFFSET.
     *
     * @param  int   $pagina     Número de página (mínimo 1).
     * @param  int   $porPagina  Registros por página (mínimo 1).
     * @return array
     */
    public function listarActivas(int $pagina, int $porPagina): array
    {
        $pagina    = max(1, $pagina);
        $porPagina = max(1, $porPagina);
        $offset    = ($pagina - 1) * $porPagina;

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
        $stmt->bindValue(':limit',  $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Obtiene las publicaciones que se mostrarán en el feed principal.
     * @return array
     */
    public function obtenerFeed(): array
    {
        return $this->query(
            'SELECT
                Id_Publicacion,
                Id_Evento,
                Id_Usuario,
                Contenido,
                Fecha_Publicacion
            FROM Publicacion
            WHERE Eliminado = 0
            ORDER BY Fecha_Publicacion DESC'
        );
    }
    /**
     * Actualiza el contenido de una publicación y marca Editado = 1.
     *
     * @param  int    $id        Id_Publicacion a modificar.
     * @param  string $contenido Nuevo contenido.
     * @return bool              true si se modificó al menos una fila.
     */
    public function actualizar(int $id, string $contenido): bool
    {
        return $this->update($id, [
            'Contenido' => $contenido,
            'Editado'   => 1,
        ]);
    }

    /**
     * Cambia el estado de una publicación.
     *
     * @param  int  $id       Id_Publicacion a modificar.
     * @param  int  $idEstado Id_EstadoPublicacion destino.
     * @return bool           true si se modificó al menos una fila.
     */
    public function cambiarEstado(int $id, int $idEstado): bool
    {
        return $this->update($id, [
            'Id_EstadoPublicacion' => $idEstado,
        ]);
    }

    /**
     * Marca la publicación como eliminada (soft delete).
     *
     * @param  int  $id  Id_Publicacion a eliminar.
     * @return bool      true si se modificó al menos una fila.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
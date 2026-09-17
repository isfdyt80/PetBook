<?php

namespace App\Models;

use App\Core\Model;

class Imagen extends Model
{
    protected string $table = 'Imagen';
    protected string $pk    = 'Id_Imagen';

    /**
     * Crea una nueva imagen.
     *
     * @param  string $url  URL pública de la imagen.
     * @return int          ID de la imagen recién insertada.
     */
    public function crear(string $url): int
    {
        return $this->insert([
            'Url'       => $url,
            'Eliminado' => 0,
        ]);
    }

    /**
     * Busca una imagen activa por su ID.
     *
     * @param  int         $id  Id_Imagen a buscar.
     * @return array|false      Fila de la imagen o false si no existe.
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->queryOne(
            'SELECT Id_Imagen, Url, Fecha_Creacion
             FROM Imagen
             WHERE Id_Imagen = :id
               AND Eliminado = 0',
            [':id' => $id]
        ) ?: false;
    }

    /**
     * Marca la imagen como eliminada (soft delete).
     *
     * @param  int  $id  Id_Imagen a eliminar.
     * @return bool      true si se modificó al menos una fila.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
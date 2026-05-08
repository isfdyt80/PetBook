<?php

namespace App\Models;

use App\Core\Model;

class Imagen extends Model
{
    protected string $table = 'Imagen';
    protected string $pk    = 'Id_Imagen';

    /**
     * Inserta una nueva imagen y devuelve el ID generado.
     *
     * @param  string $url  URL de la imagen.
     * @return int          ID del registro recién insertado.
     */
    public function crear(string $url): int
    {
        return $this->insert(['Url' => $url]);
    }

    /**
     * Busca una imagen por su clave primaria.
     * Devuelve null si no existe o está eliminada.
     *
     * @param  int        $id  Id_Imagen a buscar.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->queryOne(
            'SELECT Id_Imagen, Url, Fecha_Creacion
             FROM Imagen
             WHERE Id_Imagen = :id AND Eliminado = 0',
            [':id' => $id]
        ) ?: null;
    }

    /**
     * Marca la imagen como eliminada (soft delete).
     * Nunca borra físicamente el registro de la BD.
     *
     * @param  int  $id  Id_Imagen a eliminar.
     * @return bool      true si se modificó al menos una fila.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
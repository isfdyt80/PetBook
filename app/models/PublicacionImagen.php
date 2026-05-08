<?php

namespace App\Models;

use App\Core\Model;

class PublicacionImagen extends Model
{
    protected string $table = 'PublicacionImagen';
    protected string $pk    = 'Id_PublicacionImagen';

    /**
     * Asocia una imagen a una publicación.
     *
     * @param  int  $idPublicacion  Id_Publicacion a asociar.
     * @param  int  $idImagen       Id_Imagen a asociar.
     * @return bool                 true si se insertó correctamente.
     */
    public function asociar(int $idPublicacion, int $idImagen): bool
    {
        $stmt = $this->execute(
            'INSERT INTO PublicacionImagen (Id_Publicacion, Id_Imagen)
             VALUES (:publicacion, :imagen)',
            [
                ':publicacion' => $idPublicacion,
                ':imagen'      => $idImagen,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista las imágenes activas de una publicación.
     * Filtra Eliminado = 0 en Imagen para no traer imágenes dadas de baja.
     *
     * @param  int   $idPublicacion  Id_Publicacion a consultar.
     * @return array
     */
    public function listarPorPublicacion(int $idPublicacion): array
    {
        return $this->query(
            'SELECT pi.Id_PublicacionImagen, pi.Id_Publicacion, i.Id_Imagen, i.Url
             FROM PublicacionImagen pi
             INNER JOIN Imagen i ON i.Id_Imagen = pi.Id_Imagen
             WHERE pi.Id_Publicacion = :id AND i.Eliminado = 0',
            [':id' => $idPublicacion]
        );
    }
}
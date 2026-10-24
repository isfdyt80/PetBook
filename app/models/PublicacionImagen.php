<?php

namespace App\Models;

use App\Core\Model;

class PublicacionImagen extends Model
{
    protected string $table = 'PublicacionImagen';
    protected string $pk    = 'Id_PublicacionImagen';

    /**
     * Asocia una imagen a una publicación.
     * La unicidad del par (Id_Publicacion, Id_Imagen) está garantizada
     * por la constraint uq_publicacionimagen.
     *
     * @param  int  $idPublicacion  Id_Publicacion de la publicación.
     * @param  int  $idImagen       Id_Imagen de la imagen.
     * @return bool                 true si se insertó al menos una fila.
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
     * Lista las imágenes de una publicación, excluyendo imágenes eliminadas.
     *
     * @param  int   $idPublicacion  Id_Publicacion a consultar.
     * @return array
     */
    public function listarPorPublicacion(int $idPublicacion): array
    {
        return $this->query(
            'SELECT pi.Id_PublicacionImagen, i.Id_Imagen, i.Url
             FROM PublicacionImagen pi
             INNER JOIN Imagen i ON i.Id_Imagen = pi.Id_Imagen
             WHERE pi.Id_Publicacion = :id
               AND i.Eliminado = 0',
            [':id' => $idPublicacion]
        );
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class MascotaImagen extends Model
{
    protected string $table = 'MascotaImagen';
    protected string $pk    = 'Id_MascotaImagen';

    /**
     * Asocia una imagen a una mascota.
     *
     * @param  int  $idMascota  Id_Mascota a asociar.
     * @param  int  $idImagen   Id_Imagen a asociar.
     * @return bool             true si se insertó correctamente.
     */
    public function asociar(int $idMascota, int $idImagen): bool
    {
        $stmt = $this->execute(
            'INSERT INTO MascotaImagen (Id_Mascota, Id_Imagen)
             VALUES (:mascota, :imagen)',
            [
                ':mascota' => $idMascota,
                ':imagen'  => $idImagen,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista las imágenes activas de una mascota.
     * Filtra Eliminado = 0 en Imagen para no traer imágenes dadas de baja.
     *
     * @param  int   $idMascota  Id_Mascota a consultar.
     * @return array
     */
    public function listarPorMascota(int $idMascota): array
    {
        return $this->query(
            'SELECT mi.Id_MascotaImagen, mi.Id_Mascota, i.Id_Imagen, i.Url
             FROM MascotaImagen mi
             INNER JOIN Imagen i ON i.Id_Imagen = mi.Id_Imagen
             WHERE mi.Id_Mascota = :id AND i.Eliminado = 0',
            [':id' => $idMascota]
        );
    }
}
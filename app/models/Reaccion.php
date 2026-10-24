<?php

namespace App\Models;

use App\Core\Model;

class Reaccion extends Model
{
    protected string $table = 'Reaccion';

    /**
     * $pk se deja vacía porque Reaccion tiene PK compuesta
     * (Id_Usuario, Id_Publicacion). Los métodos heredados find()
     * y softDelete() no aplican a este modelo.
     */
    protected string $pk = '';

    /**
     * Crea o actualiza una reacción.
     * Si el par (Id_Usuario, Id_Publicacion) ya existe, actualiza
     * el tipo de reacción. La unicidad está garantizada por la PK
     * compuesta de la tabla.
     *
     * @param  int  $idUsuario       Id_Usuario que reacciona.
     * @param  int  $idPublicacion   Id_Publicacion sobre la que reacciona.
     * @param  int  $idTipoReaccion  Id_TipoReaccion seleccionado.
     * @return bool                  true si se insertó o actualizó al menos una fila.
     */
    public function crear(int $idUsuario, int $idPublicacion, int $idTipoReaccion): bool
    {
        return $this->execute(
            'INSERT INTO Reaccion (Id_Usuario, Id_Publicacion, Id_TipoReaccion)
             VALUES (:idUsuario, :idPublicacion, :idTipoReaccion)
             ON DUPLICATE KEY UPDATE Id_TipoReaccion = VALUES(Id_TipoReaccion)',
            [
                ':idUsuario'      => $idUsuario,
                ':idPublicacion'  => $idPublicacion,
                ':idTipoReaccion' => $idTipoReaccion,
            ]
        )->rowCount() > 0;
    }

    /**
     * Elimina una reacción.
     * El DELETE físico es aceptable en Reaccion: no hay requisito
     * de auditoría sobre reacciones individuales.
     *
     * @param  int  $idUsuario      Id_Usuario de la reacción a eliminar.
     * @param  int  $idPublicacion  Id_Publicacion de la reacción a eliminar.
     * @return bool                 true si se eliminó al menos una fila.
     */
    public function eliminar(int $idUsuario, int $idPublicacion): bool
    {
        return $this->execute(
            'DELETE FROM Reaccion
             WHERE Id_Usuario = :idUsuario AND Id_Publicacion = :idPublicacion',
            [
                ':idUsuario'     => $idUsuario,
                ':idPublicacion' => $idPublicacion,
            ]
        )->rowCount() > 0;
    }

    /**
     * Cuenta reacciones agrupadas por tipo para una publicación.
     *
     * @param  int   $idPublicacion  Id_Publicacion a consultar.
     * @return array                 Filas con Nombre y Cantidad.
     */
    public function contarPorPublicacion(int $idPublicacion): array
    {
        return $this->query(
            'SELECT tr.Nombre, COUNT(*) AS Cantidad
             FROM Reaccion r
             INNER JOIN TipoReaccion tr ON r.Id_TipoReaccion = tr.Id_TipoReaccion
             WHERE r.Id_Publicacion = :idPublicacion
             GROUP BY tr.Nombre',
            [':idPublicacion' => $idPublicacion]
        );
    }
}
<?php

namespace App\Models;

use App\Core\Model;

class MascotaUsuario extends Model
{
    protected string $table = 'MascotaUsuario';
    protected string $pk    = 'Id_MascotaUsuario';

    /**
     * Asocia una mascota a un usuario.
     * Fecha_Desde se setea con NOW() en la query.
     *
     * @param  array $datos  Debe contener Id_Mascota, Id_Usuario, EsDueno.
     * @return bool          true si se insertó correctamente.
     */
    public function asociar(array $datos): bool
    {
        $stmt = $this->execute(
            'INSERT INTO MascotaUsuario (Id_Mascota, Id_Usuario, EsDueno, FechaDesde)
             VALUES (:mascota, :usuario, :esDueno, NOW())',
            [
                ':mascota' => $datos['Id_Mascota'],
                ':usuario' => $datos['Id_Usuario'],
                ':esDueno' => $datos['EsDueno'],
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista todas las relaciones activas de una mascota.
     *
     * @param  int   $idMascota  Id_Mascota a consultar.
     * @return array
     */
    public function listarPorMascota(int $idMascota): array
    {
        return $this->query(
            'SELECT Id_MascotaUsuario, Id_Mascota, Id_Usuario, EsDueno, FechaDesde, FechaHasta
             FROM MascotaUsuario
             WHERE Id_Mascota = :id AND FechaHasta IS NULL',
            [':id' => $idMascota]
        );
    }

    /**
     * Lista todas las relaciones activas de un usuario.
     *
     * @param  int   $idUsuario  Id_Usuario a consultar.
     * @return array
     */
    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->query(
            'SELECT Id_MascotaUsuario, Id_Mascota, Id_Usuario, EsDueno, FechaDesde, FechaHasta
             FROM MascotaUsuario
             WHERE Id_Usuario = :id AND FechaHasta IS NULL',
            [':id' => $idUsuario]
        );
    }

    /**
     * Cierra la relación mascota-usuario seteando Fecha_Hasta.
     *
     * @param  int    $id         Id_MascotaUsuario a cerrar.
     * @param  string $fechaHasta Fecha de cierre (formato Y-m-d).
     * @return bool               true si se modificó al menos una fila.
     */
    public function cerrarRelacion(int $id, string $fechaHasta): bool
    {
        $stmt = $this->execute(
            'UPDATE MascotaUsuario
             SET FechaHasta = :fecha
             WHERE Id_MascotaUsuario = :id',
            [
                ':fecha' => $fechaHasta,
                ':id'    => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
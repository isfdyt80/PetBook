<?php

namespace App\Models;

use App\Core\Model;

class Reaccion extends Model
{
    protected string $table = 'Reaccion';
    protected string $pk    = 'Id_Reaccion';

    /**
     * Crea o actualiza una reacción.
     */
    public function crear(int $idUsuario,int $idPublicacion,int $idTipoReaccion): bool 
        {

        $sql = "INSERT INTO Reaccion
                    (
                        Id_Usuario,
                        Id_Publicacion,
                        Id_TipoReaccion
                    )
                VALUES
                    (
                        :idUsuario,
                        :idPublicacion,
                        :idTipoReaccion
                    )
                ON DUPLICATE KEY UPDATE
                    Id_TipoReaccion = VALUES(Id_TipoReaccion)";

        return $this->execute($sql, [
            ':idUsuario'      => $idUsuario,
            ':idPublicacion'  => $idPublicacion,
            ':idTipoReaccion' => $idTipoReaccion
        ]);
    }

    /**
     * Actualiza una reacción existente.
     */
    public function actualizar(int $idUsuario,int $idPublicacion,int $idTipoReaccion): bool 
    {

        $sql = "UPDATE Reaccion
                SET Id_TipoReaccion = :idTipoReaccion
                WHERE Id_Usuario = :idUsuario
                AND Id_Publicacion = :idPublicacion";

        return $this->execute($sql, [
            ':idTipoReaccion' => $idTipoReaccion,
            ':idUsuario'      => $idUsuario,
            ':idPublicacion'  => $idPublicacion
        ]);
    }

    /**
     * Elimina una reacción.
     */
    public function eliminar(int $idUsuario,int $idPublicacion): bool 
    {

        $sql = "DELETE FROM Reaccion
                WHERE Id_Usuario = :idUsuario
                AND Id_Publicacion = :idPublicacion";

        return $this->execute($sql, [
            ':idUsuario'     => $idUsuario,
            ':idPublicacion' => $idPublicacion
        ]);
    }

    /**
     * Cuenta reacciones agrupadas por tipo.
     */
    public function contarPorPublicacion(
        int $idPublicacion
    ): array {

        $sql = "SELECT tr.Nombre,COUNT(*) AS Cantidad
                FROM Reaccion r
                INNER JOIN TipoReaccion tr
                    ON r.Id_TipoReaccion = tr.Id_TipoReaccion
                WHERE r.Id_Publicacion = :idPublicacion
                GROUP BY tr.Nombre";

        return $this->query($sql, [
            ':idPublicacion' => $idPublicacion
        ]);
    }
}
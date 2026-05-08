<?php

namespace App\Models;

use App\Core\Model;

class Reporte extends Model
{
    protected string $table = 'Reporte';
    protected string $pk    = 'Id_Reporte';

    /**
     * Crea un nuevo reporte.
     * Valida que exactamente uno de Id_Publicacion o Id_Comentario sea no null.
     *
     * @param  array $datos  Debe contener Id_Usuario, Motivo, Id_EstadoReporte
     *                       y exactamente uno de Id_Publicacion o Id_Comentario.
     * @return bool          true si se insertó correctamente.
     * @throws \InvalidArgumentException  Si ambos o ninguno de los IDs están presentes.
     */
    public function crear(array $datos): bool
    {
        $tienePublicacion = !empty($datos['Id_Publicacion']);
        $tieneComentario  = !empty($datos['Id_Comentario']);

        if ($tienePublicacion === $tieneComentario) {
            throw new \InvalidArgumentException(
                'El reporte debe referenciar exactamente uno de Id_Publicacion o Id_Comentario.'
            );
        }

        $stmt = $this->execute(
            'INSERT INTO Reporte (Id_Usuario, Id_Publicacion, Id_Comentario, Id_EstadoReporte, Motivo)
             VALUES (:usuario, :publicacion, :comentario, :estado, :motivo)',
            [
                ':usuario'     => $datos['Id_Usuario'],
                ':publicacion' => $datos['Id_Publicacion'] ?? null,
                ':comentario'  => $datos['Id_Comentario']  ?? null,
                ':estado'      => $datos['Id_EstadoReporte'],
                ':motivo'      => $datos['Motivo'],
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista los reportes pendientes de resolución.
     * Filtra por Id_EstadoReporte = 1 (pendiente).
     *
     * @return array
     */
    public function listarPendientes(): array
    {
        return $this->query(
            'SELECT Id_Reporte, Id_Usuario, Id_Publicacion, Id_Comentario,
                    Id_EstadoReporte, Id_Moderador, Motivo, Fecha
             FROM Reporte
             WHERE Id_EstadoReporte = 1'
        );
    }

    /**
     * Asigna un moderador al reporte.
     *
     * @param  int  $id           Id_Reporte a modificar.
     * @param  int  $idModerador  Id_Usuario del moderador asignado.
     * @return bool               true si se modificó al menos una fila.
     */
    public function asignarModerador(int $id, int $idModerador): bool
    {
        $stmt = $this->execute(
            'UPDATE Reporte
             SET Id_Moderador = :moderador
             WHERE Id_Reporte = :id',
            [
                ':moderador' => $idModerador,
                ':id'        => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Resuelve el reporte actualizando su estado y seteando Fecha_Resolucion.
     *
     * @param  int  $id       Id_Reporte a resolver.
     * @param  int  $idEstado Id_EstadoReporte final (ej: resuelto, descartado).
     * @return bool           true si se modificó al menos una fila.
     */
    public function resolver(int $id, int $idEstado): bool
    {
        $stmt = $this->execute(
            'UPDATE Reporte
             SET Id_EstadoReporte = :estado, Fecha_Resolucion = NOW()
             WHERE Id_Reporte = :id',
            [
                ':estado' => $idEstado,
                ':id'     => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
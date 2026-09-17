<?php

namespace App\Models;

use App\Core\Model;

class Reporte extends Model
{
    protected string $table = 'Reporte';
    protected string $pk    = 'Id_Reporte';

    /**
     * Crea un nuevo reporte.
     * Valida que la denuncia apunte exactamente a una publicación o a un
     * comentario (nunca a ambos). El estado inicial se resuelve por nombre
     * (PENDIENTE) para evitar IDs hardcodeados.
     *
     * @param  array $datos  Debe contener Id_Usuario, Motivo y exactamente
     *                       uno de Id_Publicacion o Id_Comentario.
     * @return int           ID del reporte recién insertado.
     * @throws \InvalidArgumentException Si no hay un único destinatario.
     */
    public function crear(array $datos): int
    {
        $tienePublicacion = isset($datos['Id_Publicacion']) && $datos['Id_Publicacion'] !== null;
        $tieneComentario  = isset($datos['Id_Comentario'])  && $datos['Id_Comentario']  !== null;

        if ($tienePublicacion === $tieneComentario) {
            throw new \InvalidArgumentException('Debe indicar una publicación o un comentario, no ambos.');
        }

        return $this->insert([
            'Id_Usuario'       => $datos['Id_Usuario'],
            'Id_Publicacion'   => $datos['Id_Publicacion'] ?? null,
            'Id_Comentario'    => $datos['Id_Comentario'] ?? null,
            'Id_EstadoReporte' => $this->idEstadoPorNombre('PENDIENTE'),
            'Motivo'           => $datos['Motivo'],
        ]);
    }

    /**
     * Lista reportes pendientes de revisión, del más antiguo al más nuevo.
     * Resuelve el ID del estado PENDIENTE por nombre para evitar números mágicos.
     *
     * @return array
     */
    public function listarPendientes(): array
    {
        return $this->query(
            "SELECT
                r.Id_Reporte,
                r.Id_Usuario,
                r.Id_Publicacion,
                r.Id_Comentario,
                r.Id_EstadoReporte,
                r.Id_Moderador,
                r.Motivo,
                r.Fecha,
                r.Fecha_Resolucion,
                er.Nombre AS EstadoNombre
             FROM Reporte r
             INNER JOIN EstadoReporte er ON er.Id_EstadoReporte = r.Id_EstadoReporte
             WHERE r.Id_EstadoReporte = (
                 SELECT Id_EstadoReporte
                 FROM EstadoReporte
                 WHERE Nombre = 'PENDIENTE'
             )
             ORDER BY r.Fecha ASC"
        );
    }

    /**
     * Asigna un moderador al reporte.
     *
     * @param  int  $id           Id_Reporte a modificar.
     * @param  int  $idModerador  Id_Usuario del moderador.
     * @return bool               true si se modificó al menos una fila.
     */
    public function asignarModerador(int $id, int $idModerador): bool
    {
        return $this->update($id, ['Id_Moderador' => $idModerador]);
    }

    /**
     * Resuelve el reporte cambiando su estado y dejando constancia de la fecha.
     *
     * @param  int  $id       Id_Reporte a modificar.
     * @param  int  $idEstado Id_EstadoReporte destino.
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

    /**
     * Resuelve el Id_EstadoReporte a partir del nombre del catálogo.
     * Evita IDs hardcodeados (convención de la guía de desarrollo, sección 2.3).
     *
     * @param  string $nombre  Nombre del estado según el catálogo.
     * @return int             Id_EstadoReporte correspondiente.
     * @throws \RuntimeException Si el estado no existe en el catálogo.
     */
    private function idEstadoPorNombre(string $nombre): int
    {
        $estado = $this->queryOne(
            'SELECT Id_EstadoReporte
             FROM EstadoReporte
             WHERE Nombre = :nombre',
            [':nombre' => strtoupper($nombre)]
        );

        if ($estado === false) {
            throw new \RuntimeException("EstadoReporte '{$nombre}' no existe en el catálogo de estados.");
        }

        return (int) $estado['Id_EstadoReporte'];
    }
}

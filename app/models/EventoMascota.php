<?php

namespace App\Models;
use App\Core\Model;

class EventoMascota extends Model
{
    protected $table = 'EventoMascota';
    protected $pk = 'Id_Evento';

    // Crea un nuevo evento asociado a una mascota
    public function crear(array $datos): int{
        $sql = "INSERT INTO EventoMascota
                (Id_Mascota, Id_Usuario, Id_TipoEvento, Id_EstadoEvento, 
                 Descripcion, Id_Ubicacion, Recompensa)
                VALUES
                (:mascota, :usuario, :tipo, :estado, 
                 :descripcion, :ubicacion, :recompensa)";

        $this->execute($sql, [
            ':mascota'     => $datos['mascota'],
            ':usuario'     => $datos['usuario'],
            ':tipo'        => $datos['tipo'],
            ':estado'      => $datos['estado'],
            ':descripcion' => $datos['descripcion'],
            ':ubicacion'   => $datos['ubicacion'] ?? null,
            ':recompensa'  => $datos['recompensa'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Busca un evento por su ID
    public function buscarPorId(int $id){
        $sql = "SELECT 
                    Id_Evento,
                    Id_Mascota,
                    Id_Usuario,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Id_Ubicacion,
                    Fecha_Creacion,
                    Fecha_Resolucion,
                    Fecha_UltimaVista,
                    Recompensa
                FROM EventoMascota
                WHERE Id_Evento = :id
                AND Eliminado = 0";

        return $this->queryOne($sql, [':id' => $id]);
    }

    // Lista todos los eventos de una mascota
    public function listarPorMascota(int $idMascota){
        $sql = "SELECT 
                    Id_Evento,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Fecha_Creacion,
                    Recompensa
                FROM EventoMascota
                WHERE Id_Mascota = :id
                AND Eliminado = 0";

        return $this->query($sql, ['id' => $idMascota]);
    }

    // Lista eventos considerados "activos" (no resueltos)
    public function listarActivos(){
        $sql = "SELECT 
                    Id_Evento,
                    Id_Mascota,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Fecha_Creacion,
                    Recompensa
                FROM EventoMascota
                WHERE Id_EstadoEvento = 1
                AND Eliminado = 0";

        return $this->query($sql);
    }

    /**
     * Cambia el estado de un evento y registra el cambio en el historial.
     * El UPDATE y el historial se escriben dentro de la misma transacción
     * (convención de la guía de desarrollo, sección 8).
     *
     * Un evento en estado terminal (RESUELTO/CANCELADO) no puede volver a ACTIVO.
     * El estado destino se resuelve por nombre de catálogo, nunca con IDs hardcodeados.
     *
     * @param  int    $idEvento              Id_Evento a modificar.
     * @param  string $nuevoEstadoNombre     Nombre del estado destino ('ACTIVO', 'RESUELTO', 'CANCELADO').
     * @param  int    $idUsuarioResponsable  Id_Usuario que realiza el cambio.
     * @return bool                          true si la transacción se completó.
     * @throws \RuntimeException             Si el evento no existe, está en estado terminal
     *                                       o el estado destino no está en el catálogo.
     */
    public function cambiarEstado(int $idEvento, string $nuevoEstadoNombre, int $idUsuarioResponsable): bool
    {
        $evento = $this->find($idEvento);

        if ($evento === false) {
            throw new \RuntimeException('Evento no encontrado.');
        }

        $estadoActual = $this->queryOne(
            'SELECT Nombre
             FROM EstadoEvento
             WHERE Id_EstadoEvento = :id',
            [':id' => $evento['Id_EstadoEvento']]
        );

        if (in_array($estadoActual['Nombre'], ['RESUELTO', 'CANCELADO'], true)) {
            throw new \RuntimeException('El evento ya está en un estado terminal y no puede modificarse.');
        }

        $estadoDestino = strtoupper($nuevoEstadoNombre);
        $idNuevoEstado = $this->idEstadoPorNombre($estadoDestino);

        $this->db->beginTransaction();

        try {
            $this->update($idEvento, ['Id_EstadoEvento' => $idNuevoEstado]);

            if (in_array($estadoDestino, ['RESUELTO', 'CANCELADO'], true)) {
                $this->execute(
                    'UPDATE EventoMascota
                     SET Fecha_Resolucion = NOW()
                     WHERE Id_Evento = :id',
                    [':id' => $idEvento]
                );
            }

            $this->execute(
                'INSERT INTO HistorialEstadoEvento (Id_Evento, Id_EstadoEvento, Id_Usuario)
                 VALUES (:evento, :estado, :usuario)',
                [
                    ':evento'   => $idEvento,
                    ':estado'   => $idNuevoEstado,
                    ':usuario'  => $idUsuarioResponsable,
                ]
            );

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Resuelve el Id_EstadoEvento a partir del nombre del catálogo.
     * Evita IDs hardcodeados (convención de la guía de desarrollo, sección 2.3).
     *
     * @param  string $nombre  Nombre del estado según el catálogo.
     * @return int             Id_EstadoEvento correspondiente.
     * @throws \RuntimeException Si el estado no existe en el catálogo.
     */
    private function idEstadoPorNombre(string $nombre): int
    {
        $estado = $this->queryOne(
            'SELECT Id_EstadoEvento
             FROM EstadoEvento
             WHERE Nombre = :nombre',
            [':nombre' => strtoupper($nombre)]
        );

        if ($estado === false) {
            throw new \RuntimeException("El estado '{$nombre}' no existe en el catálogo de estados.");
        }

        return (int) $estado['Id_EstadoEvento'];
    }

    // Soft delete: marca el evento como eliminado
    public function eliminar(int $id): bool
    {
       
        return $this->softDelete($id);
    }
}
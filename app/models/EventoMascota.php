<?php

namespace App\Models;
use App\Core\Model;
use App\Core\Database;

class EventoMascota extends Model
{
    protected $table = 'EventoMascota';
    protected $pk = 'Id_Evento';

    // Crea un nuevo evento asociado a una mascota
   /**
 * Crea mascota (si no existe), ubicación, evento, historial y publicación
 * en una sola transacción. Si cualquier paso falla, rollback completo.
 *
 * @param  array  $datos  id_mascota_existente (opcional), mascota (datos para
 *                        Mascota::crear), ubicacion (datos para Ubicacion::crear),
 *                        id_usuario y descripcion.
 * @param  string $tipo   PERDIDA, ENCONTRADA o ADOPCION.
 * @return int            Id_Evento creado.
 */
    public function crear(array $datos, string $tipo): int
    {
        return (int) Database::transactional(function () use ($datos, $tipo): int {
            // 1. Mascota existente o nueva (sin deduplicar: eso es de PosibleCoincidencia)
            $idMascota = $datos['id_mascota_existente'] ?? null;

            if ($idMascota === null) {
                $idMascota = (new Mascota())->crear($datos['mascota']);
            }

            // 2. Ubicación
            $idUbicacion = (new Ubicacion())->crear($datos['ubicacion']);

            // 3. Evento con el tipo recibido y estado ACTIVO
            $idEstado = $this->idEstadoPorNombre('ACTIVO');

            $this->execute(
                'INSERT INTO EventoMascota
                    (Id_Mascota, Id_Usuario, Id_TipoEvento, Id_EstadoEvento, Descripcion, Id_Ubicacion)
                VALUES
                    (:mascota, :usuario,
                    (SELECT Id_TipoEvento FROM TipoEvento WHERE Nombre = :tipo),
                    :estado, :descripcion, :ubicacion)',
                [
                    ':mascota'     => $idMascota,
                    ':usuario'     => $datos['id_usuario'],
                    ':tipo'        => strtoupper($tipo),
                    ':estado'      => $idEstado,
                    ':descripcion' => $datos['descripcion'] ?? null,
                    ':ubicacion'   => $idUbicacion,
                ]
            );

            // Se captura acá, antes de los otros inserts, que cambian lastInsertId().
            $idEvento = (int) $this->db->lastInsertId();

            // 4. Historial con el estado inicial y el usuario creador
            $this->execute(
                'INSERT INTO HistorialEstadoEvento (Id_Evento, Id_EstadoEvento, Id_Usuario)
                VALUES (:evento, :estado, :usuario)',
                [
                    ':evento'  => $idEvento,
                    ':estado'  => $idEstado,
                    ':usuario' => $datos['id_usuario'],
                ]
            );

            // 5. Publicación ACTIVA referenciando el evento (contenido = descripción)
            $this->execute(
                "INSERT INTO Publicacion (Id_Evento, Id_Usuario, Id_EstadoPublicacion, Contenido)
                VALUES (:evento, :usuario,
                        (SELECT Id_EstadoPublicacion FROM EstadoPublicacion WHERE Nombre = 'ACTIVA'),
                        :contenido)",
                [
                    ':evento'    => $idEvento,
                    ':usuario'   => $datos['id_usuario'],
                    ':contenido' => $datos['descripcion'] ?? null,
                ]
            );

            return $idEvento;
        });
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

    // Trae el evento con tipo, estado, mascota, ubicación y publicador (para la vista de detalle)
public function detalle(int $id): array|false
{
    return $this->queryOne(
        "SELECT ev.Id_Evento, ev.Id_Usuario, ev.Descripcion, ev.Fecha_Creacion,
                te.Nombre AS Tipo_Evento, ee.Nombre AS Estado_Evento,
                m.Nombre AS Nombre_Mascota, m.Color, m.Descripcion_Fisica,
                sp.Nombre AS Nombre_Especie, r.Nombre AS Nombre_Raza,
                u.Direccion, u.Ciudad, u.Provincia, u.Pais, u.Latitud, u.Longitud,
                pe.Nombre AS Nombre_Publicador
         FROM EventoMascota ev
         JOIN TipoEvento   te ON te.Id_TipoEvento   = ev.Id_TipoEvento
         JOIN EstadoEvento ee ON ee.Id_EstadoEvento = ev.Id_EstadoEvento
         JOIN Usuario      us ON us.Id_Usuario      = ev.Id_Usuario
         JOIN Persona      pe ON pe.Id_Persona      = us.Id_Persona
         LEFT JOIN Mascota   m  ON m.Id_Mascota  = ev.Id_Mascota AND m.Eliminado = 0
         LEFT JOIN Especie   sp ON sp.Id_Especie = m.Id_Especie
         LEFT JOIN Raza      r  ON r.Id_Raza     = m.Id_Raza
         LEFT JOIN Ubicacion u  ON u.Id_Ubicacion = ev.Id_Ubicacion
         WHERE ev.Id_Evento = :id AND ev.Eliminado = 0",
        [':id' => $id]
    ) ?: false;
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

    /**
     * Lista eventos considerados "activos" (no resueltos).
     * Resuelve el ID del estado ACTIVO por nombre para evitar números mágicos.
     *
     * @return array
     */
    public function listarActivos(): array
    {
        $sql = "SELECT
                    Id_Evento,
                    Id_Mascota,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Fecha_Creacion,
                    Recompensa
                FROM EventoMascota
                WHERE Eliminado = 0
                  AND Id_EstadoEvento = (
                      SELECT Id_EstadoEvento
                      FROM EstadoEvento
                      WHERE Nombre = 'ACTIVO'
                  )";

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
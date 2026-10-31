<?php

namespace App\Models;
use App\Core\Model;

class EventoMascota extends Model
{
    protected string $table = 'EventoMascota';
    protected string $pk = 'Id_Evento';

    // ── Creación (HU-08, HU-09, HU-10) ────────────────────────────────────

    /**
     * Busca un evento con los datos completos para la vista de detalle.
     * Une evento + tipo + estado + mascota (con especie y raza) + ubicación
     * en una sola consulta. Considera el soft delete (Eliminado = 0).
     *
     * @param  int         $id  Id_Evento.
     * @return array|false      false si el evento no existe o está eliminado.
     */
    public function buscarDetalle(int $id): array|false
    {
        $sql = "SELECT
                    e.Id_Evento,
                    e.Id_Mascota,
                    e.Id_Usuario,
                    e.Descripcion,
                    e.Fecha_Creacion,
                    e.Fecha_Resolucion,
                    e.Fecha_UltimaVista,
                    e.Recompensa,
                    t.Nombre  AS Tipo_Evento,
                    s.Nombre  AS Estado_Evento,
                    m.Nombre  AS Mascota_Nombre,
                    s2.Nombre AS Especie,
                    r.Nombre  AS Raza,
                    u.Direccion,
                    u.Ciudad,
                    u.Provincia,
                    u.Pais
                FROM EventoMascota e
                JOIN TipoEvento   t ON t.Id_TipoEvento   = e.Id_TipoEvento
                JOIN EstadoEvento s ON s.Id_EstadoEvento = e.Id_EstadoEvento
                JOIN Mascota      m ON m.Id_Mascota      = e.Id_Mascota
                JOIN Especie      s2 ON s2.Id_Especie    = m.Id_Especie
                LEFT JOIN Raza     r ON r.Id_Raza        = m.Id_Raza
                LEFT JOIN Ubicacion u ON u.Id_Ubicacion  = e.Id_Ubicacion
                WHERE e.Id_Evento = :id
                AND e.Eliminado = 0";

        return $this->queryOne($sql, [':id' => $id]);
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
     * (wrapper Model::transactional — ver docs/conventions.md).
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

        // Estado terminal implica archivar la publicación asociada (si existe)
        // para que el evento resuelto/cancelado salga del feed. Resuelto por
        // nombre de catálogo, antes de abrir la transacción (convención regla 2).
        $idEstadoArchivada = null;
        if (in_array($estadoDestino, ['RESUELTO', 'CANCELADO'], true)) {
            $idEstadoArchivada = $this->idEstadoPublicacionPorNombre('ARCHIVADA');
        }

        // transactional() hace el rollback y re-lanza la excepción tal cual.
        return $this->transactional(function () use (
            $idEvento,
            $idNuevoEstado,
            $estadoDestino,
            $idUsuarioResponsable,
            $idEstadoArchivada
        ) {
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

            // Estado terminal → la publicación automática se archiva para
            // salir del feed (toda modificación multi-tabla vive en esta
            // misma transacción, docs/conventions.md).
            if ($idEstadoArchivada !== null) {
                $this->execute(
                    'UPDATE Publicacion
                     SET Id_EstadoPublicacion = :estado
                     WHERE Id_Evento = :evento
                     AND Eliminado = 0',
                    [
                        ':estado' => $idEstadoArchivada,
                        ':evento' => $idEvento,
                    ]
                );
            }

            return true;
        });
    }

    /**
     * Crea un evento de mascota completo (HU-08, HU-09 y HU-10).
     *
     * El tipo no llega como ID: llega como nombre del catálogo TipoEvento
     * (ej: 'PERDIDA', 'ENCONTRADA', 'ADOPCION') y se resuelve por nombre,
     * de modo que la validación 404 de la capa HTTP y esta validación de
     * negocio compartan la misma fuente de verdad.
     *
     * Dentro de una sola transacción (wrapper Model::transactional, ver
     * docs/conventions.md) escribe:
     *   1. Ubicacion          (si el formulario aporta datos de ubicación).
     *   2. EventoMascota      con estado ACTIVO resuelto por catálogo.
     *   3. HistorialEstadoEvento (primer registro de estado).
     *   4. Publicacion        con estado ACTIVA resuelto por catálogo
     *                         ("toda creación de evento genera automáticamente
     *                         una publicación", HU-08/09/10).
     *
     * Las validaciones de negocio van antes del transactional(); si fallan
     * no hay nada que revertir (convención regla 2).
     *
     * @param  array  $datos                  Debe contener Id_Mascota,
     *                                        Id_Usuario y Descripcion.
     *                                        Opcionales: ubicacion (array con
     *                                        claves direccion/ciudad/...),
     *                                        Recompensa, Fecha_UltimaVista.
     * @param  string $tipoNombre             Nombre del tipo según catálogo.
     * @return int                            Id_Evento generado.
     * @throws \RuntimeException              Si el tipo o el estado no figuran
     *                                        en su catálogo, o si la
     *                                        transacción falla.
     */
    public function crear(array $datos, string $tipoNombre): int
    {
        // ── Validaciones de negocio (antes de abrir la transacción) ──────
        $tipoEvento = (new TipoEvento())->buscarPorNombre($tipoNombre);

        if ($tipoEvento === false) {
            throw new \RuntimeException(
                "El tipo de evento '{$tipoNombre}' no existe en el catálogo."
            );
        }

        $estadoActivo = (new EstadoEvento())->buscarPorNombre('ACTIVO');

        if ($estadoActivo === false) {
            throw new \RuntimeException(
                "El estado 'ACTIVO' no existe en el catálogo de estados."
            );
        }

        try {
            return $this->transactional(function () use ($datos, $tipoEvento, $estadoActivo) {
                // Una mascota encontrada no tiene por qué pertenecer a quien
                // reporta. Se registra una ficha sin vínculo MascotaUsuario,
                // dentro de la misma transacción del evento y su publicación.
                $idMascota = isset($datos['mascota_nueva'])
                    ? (new Mascota())->crear($datos['mascota_nueva'])
                    : (int) $datos['Id_Mascota'];

                // 1. Ubicación, si el formulario la aporta (opcional).
                $idUbicacion = null;
                if (!empty($datos['ubicacion'])) {
                    $idUbicacion = (new Ubicacion())->crear($datos['ubicacion']);
                }

                // 2. EventoMascota en estado ACTIVO.
                $idEvento = $this->insert([
                    'Id_Mascota'       => $idMascota,
                    'Id_Usuario'       => $datos['Id_Usuario'],
                    'Id_TipoEvento'    => (int) $tipoEvento['Id_TipoEvento'],
                    'Id_EstadoEvento'  => (int) $estadoActivo['Id_EstadoEvento'],
                    'Descripcion'      => $datos['Descripcion']                    ?? null,
                    'Id_Ubicacion'     => $idUbicacion,
                    'Fecha_UltimaVista' => $datos['Fecha_UltimaVista']             ?? null,
                    'Recompensa'       => $datos['Recompensa']                     ?? null,
                    'Eliminado'        => 0,
                ]);

                // 3. Primer registro de historial de estado.
                (new HistorialEstadoEvento())->crear(
                    $idEvento,
                    (int) $estadoActivo['Id_EstadoEvento'],
                    (int) $datos['Id_Usuario']
                );

                // 4. Publicación automática en estado ACTIVA.
                (new Publicacion())->crear([
                    'Id_Evento'            => $idEvento,
                    'Id_Usuario'           => $datos['Id_Usuario'],
                    'Id_EstadoPublicacion' => $this->idEstadoPublicacionPorNombre('ACTIVA'),
                    'Contenido'            => $datos['Descripcion'] ?? '',
                ]);

                return $idEvento;
            });
        } catch (\Throwable $e) {
            // El rollback lo hace transactional(); acá solo se preserva el
            // contrato de RuntimeException que consume EventoController.
            throw new \RuntimeException('Error al crear el evento: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Resuelve el Id_EstadoPublicacion a partir del nombre del catálogo.
     * Evita IDs hardcodeados (convención de la guía de desarrollo, 2.3).
     *
     * Se usa al crear la publicación automática (ACTIVA) y al archivar la
     * publicación de un evento que llega a estado terminal (ARCHIVADA).
     *
     * @param  string $nombre  Nombre del estado según el catálogo.
     * @return int             Id_EstadoPublicacion correspondiente.
     * @throws \RuntimeException Si el estado no existe en el catálogo.
     */
    private function idEstadoPublicacionPorNombre(string $nombre): int
    {
        $estado = (new EstadoPublicacion())->buscarPorNombre($nombre);

        if ($estado === false) {
            throw new \RuntimeException(
                "El estado '{$nombre}' no existe en el catálogo de estados de publicación."
            );
        }

        return (int) $estado['Id_EstadoPublicacion'];
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

    /**
     * Da de baja un evento y su publicación asociada (soft delete).
     *
     * El evento queda marcado Eliminado = 1 y la publicación automática
     * también, para que no quede en el feed apuntando a un evento muerto.
     * Ambas escrituras se hacen en una sola transacción (wrapper
     * Model::transactional, docs/conventions.md).
     *
     * La validación de propiedad del evento es responsabilidad del
     * controlador (tiene los datos de sesión); acá solo se valida que
     * exista y no esté ya eliminado.
     *
     * @param  int  $idEvento  Id_Evento a dar de baja.
     * @return bool            true si la transacción se completó.
     * @throws \RuntimeException Si el evento no existe o ya está eliminado.
     */
    public function darBaja(int $idEvento): bool
    {
        $evento = $this->buscarPorId($idEvento);

        if ($evento === false) {
            throw new \RuntimeException('El evento no existe o ya fue eliminado.');
        }

        return $this->transactional(function () use ($idEvento) {
            $this->softDelete($idEvento);

            $this->execute(
                'UPDATE Publicacion
                 SET Eliminado = 1
                 WHERE Id_Evento = :evento
                 AND Eliminado = 0',
                [':evento' => $idEvento]
            );

            return true;
        });
    }

    // Soft delete: marca el evento como eliminado
    public function eliminar(int $id): bool
    {
       
        return $this->softDelete($id);
    }
}

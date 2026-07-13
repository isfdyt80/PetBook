<?php

namespace App\Models;

use App\Core\Model;

class Ubicacion extends Model
{
    protected string $table = 'Ubicacion';
    protected string $pk    = 'Id_Ubicacion';

    /**
     * Busca una ubicación por su clave primaria.
     * Devuelve false si no existe.
     *
     * @param  int         $id  Id_Ubicacion a buscar.
     * @return array|false
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->find($id);
    }

    /**
     * Inserta una nueva ubicación y devuelve el ID generado.
     * Todos los campos son opcionales en el schema; la validación
     * de que al menos uno tenga valor es responsabilidad del controlador.
     *
     * @param  array $datos  Campos de la ubicación.
     * @return int           ID del registro recién insertado.
     */
    public function crear(array $datos): int
    {
        return $this->insert($datos);
    }

    /**
     * Actualiza los campos de una ubicación existente.
     * Solo se actualizan los campos presentes en $datos.
     *
     * @param  int   $id    Id_Ubicacion a modificar.
     * @param  array $datos Campos a actualizar.
     * @return bool         true si se modificó al menos una fila.
     */
    public function actualizar(int $id, array $datos): bool
    {
        return $this->update($id, $datos);
    }

    /**
     * Devuelve ubicaciones dentro de un radio dado en kilómetros
     * usando la fórmula Haversine.
     *
     * Se usan dos placeholders distintos para la latitud (:lat y :lat_sin)
     * porque PDO no permite reusar el mismo placeholder en una misma query.
     *
     * @param  float $latitud   Latitud del punto central.
     * @param  float $longitud  Longitud del punto central.
     * @param  float $radioKm   Radio de búsqueda en kilómetros (default 10).
     * @return array
     */
    public function buscarEnRadio(float $latitud, float $longitud, float $radioKm = 10): array
    {
        return $this->query(
            'SELECT *,
                (6371 * ACOS(
                    COS(RADIANS(:lat)) *
                    COS(RADIANS(Latitud)) *
                    COS(RADIANS(Longitud) - RADIANS(:lon)) +
                    SIN(RADIANS(:lat_sin)) *
                    SIN(RADIANS(Latitud))
                )) AS distancia_km
             FROM Ubicacion
             WHERE Latitud IS NOT NULL
               AND Longitud IS NOT NULL
             HAVING distancia_km <= :radio
             ORDER BY distancia_km ASC',
            [
                ':lat'     => $latitud,
                ':lon'     => $longitud,
                ':lat_sin' => $latitud,
                ':radio'   => $radioKm,
            ]
        );
    }
}
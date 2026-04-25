<?php

// app/models/Ubicacion.php

namespace App\Models;

use App\Core\Model;

class Ubicacion extends Model
{
    protected string $table = 'Ubicacion';
    protected string $pk    = 'Id_Ubicacion';


    /**
     * Devuelve una ubicación por su ID o false si no existe.
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->find($id);
    }

   
    /**
     * Inserta una nueva ubicación y devuelve el ID generado.
     * Todos los campos son opcionales excepto que al menos uno debe tener valor.
     */
    public function crear(array $datos): int
    {
        return $this->insert($datos);
    }

    /**
     * Actualiza los campos de una ubicación existente.
     * Solo se actualizan los campos presentes en $datos.
     */
    public function actualizar(int $id, array $datos): bool
    {
        return $this->update($id, $datos);
    }

   
    /**
     * Devuelve ubicaciones dentro de un radio dado en kilómetros
     *
     * @param float $latitud   Latitud del punto central
     * @param float $longitud  Longitud del punto central
     * @param float $radioKm   Radio de búsqueda en kilómetros
     */
    public function buscarEnRadio(float $latitud, float $longitud, float $radioKm = 10): array
    {
        $sql = "SELECT *,
                    (6371 * ACOS(
                        COS(RADIANS(:lat)) *
                        COS(RADIANS(Latitud)) *
                        COS(RADIANS(Longitud) - RADIANS(:lon)) +
                        SIN(RADIANS(:lat2)) *
                        SIN(RADIANS(Latitud))
                    )) AS distancia_km
                FROM Ubicacion
                WHERE Latitud IS NOT NULL
                  AND Longitud IS NOT NULL
                HAVING distancia_km <= :radio
                ORDER BY distancia_km ASC";

        return $this->query($sql, [
            ':lat'   => $latitud,
            ':lon'   => $longitud,
            ':lat2'  => $latitud,
            ':radio' => $radioKm,
        ]);
    }
}
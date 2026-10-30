<?php

namespace App\Models;

use App\Core\Model;

class Ubicacion extends Model
{
    protected string $table = 'Ubicacion';
    protected string $pk = 'Id_Ubicacion';

    // Inserta una nueva ubicacion en la base de datos
    // Devuelve el ID generado automaticamente
    public function crear(array $datos): int
    {
        return $this->insert([
            'Direccion' => $datos['direccion']   ?? null,
            'Ciudad'    => $datos['ciudad']      ?? null,
            'Provincia' => $datos['provincia']   ?? null,
            'Pais'      => $datos['pais']        ?? null,
            'Latitud'   => $datos['latitud']     ?? null,
            'Longitud'  => $datos['longitud']    ?? null,
        ]);
    }

    // Busca una ubicacion por su ID
    public function buscarPorId(int $id){
        $sql = "SELECT 
                    Id_Ubicacion,
                    Direccion,
                    Ciudad,
                    Provincia,
                    Pais,
                    Latitud,
                    Longitud
                FROM Ubicacion
                WHERE Id_Ubicacion = :id";

        return $this->queryOne($sql, ['id' => $id]);
    }
}
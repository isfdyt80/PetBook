<?php

namespace App\Models;
use Core\Model;

class Ubicacion extends Model
{
    // Inserta una nueva ubicacion en la base de datos
    public function crear(array $datos): int{
        $sql = "INSERT INTO Ubicacion 
                (Direccion, Ciudad, Provincia, Pais, Latitud, Longitud)
                VALUES (:direccion, :ciudad, :provincia, :pais, :latitud, :longitud)";

        // Ejecuta el INSERT
        $this->execute($sql, $datos);

        // Devuelve el ID generado automaticamente
        return $this->lastInsertId();
    }

    // Busca una ubicacion por ID
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
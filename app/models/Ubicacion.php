<?php

namespace App\Models;
use App\Core\Model;

class Ubicacion extends Model
{
    protected $table = 'Ubicacion';
    protected $pk = 'Id_Ubicacion';

    // Inserta una nueva ubicacion en la base de datos
    public function crear(array $datos): int{
        $sql = "INSERT INTO Ubicacion 
                (Direccion, Ciudad, Provincia, Pais, Latitud, Longitud)
                VALUES (:direccion, :ciudad, :provincia, :pais, :latitud, :longitud)";

        $this->execute($sql, [
            ':direccion' => $datos['direccion'],
            ':ciudad'    => $datos['ciudad'],
            ':provincia' => $datos['provincia'],
            ':pais'      => $datos['pais'],
            ':latitud'   => $datos['latitud'] ?? null,
            ':longitud'  => $datos['longitud'] ?? null,
        ]);

        // Devuelve el ID generado automaticamente
        return (int) $this->db->lastInsertId();
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
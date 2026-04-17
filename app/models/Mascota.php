<?php

namespace App\Models;

use PDO;
use Exception;

class Mascota
{
    private $db;

     private $id_mascota;
    private $nombre;
    private $id_especie;
    private $id_raza;
    private $color;
    private $tamano; 
    private $sexo;
    private $fecha_nacimiento;
    private $edad_aproximada;
    private $descripcion_fisica;
    private $eliminado;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // crea una mascota
    public function crear($datos)
    {
        // VALIDACION IMPORTANTE
        // si hay raza, verificamos que pertenezca a la especie indicada
        if (!empty($datos['Id_Raza'])) {

            $stmt = $this->db->prepare("
                SELECT Id_Especie
                FROM Raza
                WHERE Id_Raza = :raza AND Eliminado = 0
            ");

            $stmt->execute(['raza' => $datos['Id_Raza']]);

            $raza = $stmt->fetch(PDO::FETCH_ASSOC);

            // si no existe o no coincide da error
            if (!$raza || $raza['Id_Especie'] != $datos['Id_Especie']) {
                throw new Exception("La raza no pertenece a la especie indicada");
            }
        }

        // inserta la mascota
        $stmt = $this->db->prepare("
            INSERT INTO Mascota 
            (
                Nombre,
                Id_Especie,
                Id_Raza,
                Color,
                Tamaño,
                Sexo,
                Fecha_Nacimiento,
                Edad_Aproximada,
                Descripcion_Fisica,
                Eliminado
            )
            VALUES 
            (
                :nombre,
                :especie,
                :raza,
                :color,
                :tamano,
                :sexo,
                :fecha,
                :edad,
                :descripcion,
                0
            )
        ");

        $stmt->execute([
            'nombre' => $datos['Nombre'] ?? null,
            'especie' => $datos['Id_Especie'],
            'raza' => $datos['Id_Raza'] ?? null,
            'color' => $datos['Color'] ?? null,
            'tamano' => $datos['Tamaño'] ?? null,
            'sexo' => $datos['Sexo'] ?? null,
            'fecha' => $datos['Fecha_Nacimiento'] ?? null,
            'edad' => $datos['Edad_Aproximada'] ?? null,
            'descripcion' => $datos['Descripcion_Fisica'] ?? null
        ]);

        // devuelve el ID generado
        return $this->db->lastInsertId();
    }

    // busca mascota por ID
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("
           SELECT 
                Id_Mascota,
                Nombre,
                Id_Especie,
                Id_Raza,
                Color,
                Tamaño,
                Sexo,
                Fecha_Nacimiento,
                Edad_Aproximada,
                Descripcion_Fisica
            FROM Mascota
            WHERE Id_Mascota = :id AND Eliminado = 0
        ");

        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // actualiza datos de una mascota
    public function actualizar($id, $datos)
    {
        $stmt = $this->db->prepare("
            UPDATE Mascota
            SET 
                Nombre = :nombre,
                Id_Especie = :especie,
                Id_Raza = :raza,
                Color = :color,
                Tamaño = :tamano,
                Sexo = :sexo,
                Fecha_Nacimiento = :fecha,
                Edad_Aproximada = :edad,
                Descripcion_Fisica = :descripcion
            WHERE Id_Mascota = :id
        ");

        return $stmt->execute([
            'nombre' => $datos['Nombre'] ?? null,
            'especie' => $datos['Id_Especie'],
            'raza' => $datos['Id_Raza'] ?? null,
            'color' => $datos['Color'] ?? null,
            'tamano' => $datos['Tamaño'] ?? null,
            'sexo' => $datos['Sexo'] ?? null,
            'fecha' => $datos['Fecha_Nacimiento'] ?? null,
            'edad' => $datos['Edad_Aproximada'] ?? null,
            'descripcion' => $datos['Descripcion_Fisica'] ?? null,
            'id' => $id
        ]);
    }

    // soft delete (no borra, marca eliminado)
    public function eliminar($id)
    {
        $stmt = $this->db->prepare("
            UPDATE Mascota
            SET Eliminado = 1
            WHERE Id_Mascota = :id
        ");

        return $stmt->execute(['id' => $id]);
    }
}
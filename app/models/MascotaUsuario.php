<?php

namespace App\Models;

use PDO;

class MascotaUsuario
{
    private $db;

    private $id_mascota_usuario;
    private $id_mascota;
    private $id_usuario;
    private $esDueno; 
    private $fecha_desde;
    private $fecha_hasta;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // asocia una mascota a un usuario
    public function asociar($datos)
    {
        $stmt = $this->db->prepare("
            INSERT INTO MascotaUsuario 
            (
            Id_Mascota,
            Id_Usuario, 
            EsDueno, 
            Fecha_Desde
            )
            VALUES 
            (:mascota, 
            :usuario, 
            :esDueno, 
            NOW()
            )
        ");

        return $stmt->execute([
            'mascota' => $datos['Id_Mascota'],
            'usuario' => $datos['Id_Usuario'],
            'esDueno' => $datos['EsDueno']
        ]);
    }

    // lista usuarios de una mascota
    public function listarPorMascota($idMascota)
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id_MascotaUsuario, 
                Id_Usuario, 
                EsDueno,
                Fecha_Desde, 
                Fecha_Hasta
            FROM MascotaUsuario
            WHERE Id_Mascota = :id
        ");

        $stmt->execute(['id' => $idMascota]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // lista mascotas de un usuario
    public function listarPorUsuario($idUsuario)
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id_MascotaUsuario, 
                Id_Mascota, 
                EsDueno,
                Fecha_Desde, 
                Fecha_Hasta
            FROM MascotaUsuario
            WHERE Id_Usuario = :id
        ");

        $stmt->execute(['id' => $idUsuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // cierra la relacion (deja de ser dueño)
    public function cerrarRelacion($id, $fechaHasta)
    {
        $stmt = $this->db->prepare("
            UPDATE MascotaUsuario
            SET Fecha_Hasta = :fecha
            WHERE Id_MascotaUsuario = :id
        ");

        return $stmt->execute([
            'fecha' => $fechaHasta,
            'id' => $id
        ]);
    }
}
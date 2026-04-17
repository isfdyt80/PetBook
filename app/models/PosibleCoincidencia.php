<?php

namespace App\Models;

use PDO;

class PosibleCoincidencia
{
    private $db;

    private $id;
    private $id_mascotaA;
    private $id_mascotaB;
    private $nivel_confianza;
    private $revisado;
    private $resultado;
    private $id_usuario;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // crea una coincidencia (ej: mascota encontrada vs perdida)
    public function crear($datos)
    {
        $stmt = $this->db->prepare("
            INSERT INTO PosibleCoincidencia 
            (
            Id_MascotaA, 
            Id_MascotaB, 
            Nivel_Confianza
            )
            VALUES 
            (
            :a, 
            :b, 
            :nivel)
        ");

        return $stmt->execute([
            'mascota' => $datos['Id_Mascota']
        ]);
    }

    // lista coincidencias pendientes
    public function listarPendientes()
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id,
                Id_MascotaA,
                Id_MascotaB,
                Nivel_Confianza,
                Resultado,
                Revisado
            FROM PosibleCoincidencia
            WHERE Resultado = 'pendiente'
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // actualiza el resultado (aceptado / rechazado)
    public function actualizar($id, $resultado, $idUsuario)
    {
        $stmt = $this->db->prepare("
            UPDATE PosibleCoincidencia
            SET Resultado = :resultado,
                Id_Usuario = :usuario
            WHERE Id_PosibleCoincidencia = :id
        ");

        return $stmt->execute([
            'resultado' => $resultado,
            'usuario' => $idUsuario,
            'id' => $id
        ]);
    }
}
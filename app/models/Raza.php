<?php

namespace App\Models;

use PDO;

class Raza
{
    private $db;

    private $id_raza;
    private $nombre;
    private $id_especie;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // lista todas las razas activas
    public function listar()
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id_Raza, 
                Nombre, 
                Id_Especie 
            FROM Raza
            WHERE Eliminado = 0
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // lista razas filtradas por especie
    public function listarPorEspecie($idEspecie)
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id_Raza, 
                Nombre
            FROM Raza
            WHERE Id_Especie = :id AND Eliminado = 0
        ");

        $stmt->execute(['id' => $idEspecie]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // busca una raza por ID
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                Id_Raza, 
                Nombre, 
                Id_Especie 
            FROM Raza
            WHERE Id_Raza = :id AND Eliminado = 0
        ");

        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
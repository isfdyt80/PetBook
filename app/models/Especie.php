<?php

namespace App\Models;

use PDO;

class Especie
{
    private $db;

    private $id_especie;
    private $nombre;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // devuelve todas las especies activas 
    public function listar()
    {
        $stmt = $this->db->prepare("
            SELECT Id_Especie, Nombre
            FROM Especie
            WHERE Eliminado = 0
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // busca una especie por su ID
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("
            SELECT Id_Especie, Nombre
            FROM Especie
            WHERE Id_Especie = :id AND Eliminado = 0
        ");

        // ejecuta la consulta con parametro seguro
        $stmt->execute(['id' => $id]);

        // devuelve una sola fila o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
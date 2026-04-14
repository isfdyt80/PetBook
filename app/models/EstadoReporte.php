<?php
class EstadoReporte
{
    private $db;

    
    private $id_estado_reporte;
    private $nombre;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // lista todos los estados
    public function listar()
    {
        $stmt = $this->db->prepare("SELECT * FROM EstadoReporte");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // busca un estado por nombre
    public function buscarPorNombre($nombre)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM EstadoReporte
            WHERE Nombre = :nombre
        ");

        $stmt->execute(['nombre' => $nombre]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
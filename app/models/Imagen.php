<?php
class Imagen
{
    private $db;

    private $id_imagen;
    private $url;
    private $fecha_creacion;
    private $eliminado;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // inserta una nueva imagen
    public function crear($url)
    {
        $stmt = $this->db->prepare("
            INSERT INTO Imagen (Url)
            VALUES (:url)
        ");

        $stmt->execute(['url' => $url]);

        return $this->db->lastInsertId();
    }

    // busca una imagen por ID (solo si no está eliminada)
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM Imagen
            WHERE Id_Imagen = :id AND Eliminado = 0
        ");

        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // soft delete
    public function eliminar($id)
    {
        $stmt = $this->db->prepare("
            UPDATE Imagen
            SET Eliminado = 1
            WHERE Id_Imagen = :id
        ");

        return $stmt->execute(['id' => $id]);
    }
}
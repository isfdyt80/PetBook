<?php

namespace App\Models;
use PDO;
class MascotaImagen
{
    private $db;

  
    private $id_mascota_imagen;
    private $id_mascota;
    private $id_imagen;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // relaciona mascota con imagen
    public function asociar($idMascota, $idImagen)
    {
        $stmt = $this->db->prepare("
            INSERT INTO MascotaImagen (Id_Mascota, Id_Imagen)
            VALUES (:mascota, :imagen)
        ");

        return $stmt->execute([
            'mascota' => $idMascota,
            'imagen' => $idImagen
        ]);
    }

    // lista imagenes de una mascota
    public function listarPorMascota($idMascota)
    {
        $stmt = $this->db->prepare("
            SELECT 
                mi.Id_MascotaImagen,
                mi.Id_Mascota,
                i.Id_Imagen,
                i.Url
            FROM MascotaImagen mi
            INNER JOIN Imagen i ON i.Id_Imagen = mi.Id_Imagen
            WHERE mi.Id_Mascota = :id
            AND i.Eliminado = 0
        ");

        $stmt->execute(['id' => $idMascota]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
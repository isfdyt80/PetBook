<?php

namespace App\Models;
use PDO;

class PublicacionImagen
{
    private $db;


    private $id_publicacion_imagen;
    private $id_publicacion;
    private $id_imagen;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // asocia imagen a publicacion
    public function asociar($idPublicacion, $idImagen)
    {
        $stmt = $this->db->prepare("
            INSERT INTO PublicacionImagen (Id_Publicacion, Id_Imagen)
            VALUES (:pub, :img)
        ");

        return $stmt->execute([
            'pub' => $idPublicacion,
            'img' => $idImagen
        ]);
    }

    // lista imagenes de una publicacion
    public function listarPorPublicacion($idPublicacion)
    {
        $stmt = $this->db->prepare("
            SELECT 
                 pi.Id_PublicacionImagen,
                 pi.Id_Publicacion,
                 i.Id_Imagen,
                 i.Url
            FROM PublicacionImagen pi
            INNER JOIN Imagen i ON i.Id_Imagen = pi.Id_Imagen
            WHERE pi.Id_Publicacion = :id
            AND i.Eliminado = 0
        ");

        $stmt->execute(['id' => $idPublicacion]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
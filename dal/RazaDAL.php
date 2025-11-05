<?php
namespace DAL;

use Database\Conexion;
use Modelos\Mascota;
use PDO;
use PDOException;

class RazaDAL
{
    public static function listar(): array
    {
        $pdo = Conexion::getConexion(); 
        $sql = "SELECT raza_id AS id, nombre AS nombre FROM razas ORDER BY nombre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorId(int $id)
    {
        if ($id <= 0) return null;
        $pdo = Conexion::getConexion();
        $sql = "SELECT raza_id AS id, especie_id, nombre, descripcion FROM razas WHERE raza_id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
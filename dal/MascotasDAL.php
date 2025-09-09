<?php
namespace DAL;

use Database\Conexion;
use Modelos\Mascota;
use PDO;
use PDOException;

class MascotaDAL {
    public static function crear(Mascota $mascotas) {
        $pdo = Conexion::getConexion();

        $sql = "INSERT INTO mascotas (estado, nombre, fecha_nacimiento, raza_id, usuario_id)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            $mascotas->estado,
            $mascotas->nombre,
            $mascotas->fecha_nacimiento,
            $mascotas->raza_id,
            $mascotas->usuario_id
        ]);
    }
    public static function buscarPorId($id) {
        $pdo = Conexion::getConexion();
        $sql = "SELECT * FROM usuarios WHERE usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Mascota(
                $data['id'],
                $data['estado'],
                $data['nombre'],
                $data['fecha_nacimiento'],
                $data['raza_id'],
                $data['usuario_id'] 
            );
        }

        return null;
    }
}

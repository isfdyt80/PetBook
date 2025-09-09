<?php
namespace DAL;

use Database\Conexion;
use Modelos\Mascota;
use PDO;
use PDOException;

class MascotaDAL {
    public static function crear(Mascota $mascotas) {
        $pdo = Conexion::getConexion();

        $sql = "INSERT INTO mascotas (estado, nombre, fecha_nacimiento, foto, activo, raza_id, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        var_dump($mascotas);
        return $stmt->execute([
            $mascotas->estado,
            $mascotas->nombre,
            $mascotas->fecha_nacimiento,
            $mascotas->foto,
            $mascotas->activo ?? 1,
            $mascotas->raza_id,
            $mascotas->usuario_id
        ]);
    }
    public static function buscarPorId($id) {
        $pdo = Conexion::getConexion();
        $sql = "SELECT * FROM mascotas WHERE mascota_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Mascota(
                $data['mascota_id'],
                $data['estado'],
                $data['nombre'],
                $data['fecha_nacimiento'],
                $data['foto'],
                $data['fecha_creacion'],
                $data['activo'],
                $data['raza_id'],
                $data['usuario_id']
            );
        }

        return null;
    }
}

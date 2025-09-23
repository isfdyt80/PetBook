<?php
namespace DAL;

use Database\Conexion;
use Modelos\Publicacion;
use PDO;
use PDOException;

class PublicacionDAL {
    public static function crear(Publicacion $publicacion) {
        $pdo = Conexion::getConexion();

        $sql = "INSERT INTO publicaciones (descripcion, estado, mascota_id, usuario_id, foto, recompensa, ubicacion, activo)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        var_dump($publicacion);
        return $stmt->execute([
            $publicacion->descripcion,
            $publicacion->estado,
            $publicacion->mascota_id,
            $publicacion->usuario_id,
            $publicacion->foto,
            $publicacion->recompensa,
            $publicacion->ubicacion,
            $publicacion->activo ?? 1
        ]);
    }
    public static function buscarPorId($id) {
        $pdo = Conexion::getConexion();
        $sql = "SELECT * FROM publicaciones WHERE publicacion_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Publicacion(
                $data['publicacion_id'],
                $data['descripcion'],
                $data['estado'],
                $data['mascota_id'],
                $data['usuario_id'],
                $data['foto'],
                $data['recompensa'],
                $data['ubicacion'],
                $data['activo']
            );
        }

        return null;
    }
}
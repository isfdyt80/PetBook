<?php
namespace DAL;

use Database\Conexion;
use Modelos\Publicacion;
use PDO;
use PDOException;

class PublicacionDAL {

    public static function crear(Publicacion $publicacion) {
        $pdo = Conexion::getConexion();

        $sql = "INSERT INTO publicaciones (
                    descripcion,
                    estado,
                    mascota_id,
                    usuario_id,
                    foto,
                    recompensa,
                    ubicacion,
                    activo
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $pdo->prepare($sql);

            $resultado = $stmt->execute([
                $publicacion->descripcion,
                $publicacion->estado,
                $publicacion->mascota_id,
                $publicacion->usuario_id,
                $publicacion->foto,
                $publicacion->recompensa,
                $publicacion->ubicacion,
                $publicacion->activo ?? 1
            ]);

            return $resultado;

        } catch (PDOException $e) {
            // 🔹 Log interno (en el archivo de errores del servidor)
            error_log("Error al crear publicación: " . $e->getMessage());

            // 🔹 O podés lanzar una excepción más genérica
            throw new \Exception("Error al guardar la publicación en la base de datos.");
        }
    }

    public static function buscarPorId($id) {
        $pdo = Conexion::getConexion();
        $sql = "SELECT * FROM publicaciones WHERE publicacion_id = ?";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                return new Publicacion(
                    $data['descripcion'],
                    $data['estado'],
                    $data['mascota_id'],
                    $data['usuario_id'],
                    $data['publicacion_id'],
                    $data['activo'],
                    $data['fecha_creacion'],
                    $data['foto'],
                    $data['recompensa'],
                    $data['ubicacion']
                );
            }

            return null;

        } catch (PDOException $e) {
            error_log("Error al buscar publicación: " . $e->getMessage());
            throw new \Exception("Error al consultar la base de datos.");
        }
    }
}

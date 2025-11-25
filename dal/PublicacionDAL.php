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
                $publicacion->usuario_id ?? 2,
                $publicacion->foto ?? 'Sin imagen',
                $publicacion->recompensa,
                $publicacion->ubicacion ?? 'Laguna los pisos',
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
    public static function eliminar($publicacion_id) {
        $pdo = Conexion::getConexion();
        $sql = "UPDATE publicaciones SET activo = 0 WHERE publicacion_id = ?";

        try {
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([(int)$publicacion_id]);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al eliminar publicación: " . $e->getMessage());
            throw new \Exception("Error al eliminar la publicación en la base de datos.");
        }
    }

    public static function modificar($publicacion_id, array $fields) {
        $pdo = Conexion::getConexion();

        // Campos permitidos para actualizar
        $allowed = ['descripcion', 'estado', 'ubicacion', 'recompensa', 'foto'];
        $set = [];
        $params = [];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $fields)) {
                $set[] = "$col = ?";
                $params[] = $fields[$col];
            }
        }

        if (empty($set)) {
            // Nothing to update
            return false;
        }

        $params[] = (int)$publicacion_id;

        $sql = "UPDATE publicaciones SET " . implode(', ', $set) . " WHERE publicacion_id = ?";

        try {
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute($params);
            return $ok;
        } catch (PDOException $e) {
            error_log("Error al modificar publicación: " . $e->getMessage());
            throw new \Exception("Error al modificar la publicación en la base de datos.");
        }
    }

    public static function traerPublicaciones() {
        $pdo = Conexion::getConexion();

        // Seleccionamos publicaciones junto a algunos datos de mascota y usuario para el front-end
        $sql = "
            SELECT
                p.publicacion_id AS id,
                p.descripcion,
                p.foto AS foto,
                p.estado,
                p.recompensa,
                p.ubicacion,
                p.fecha_creacion,
                p.mascota_id,
                m.nombre AS nombre_mascota,
                m.foto AS mascota_foto,
                u.usuario_id AS usuario_id,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre
            FROM publicaciones p
            LEFT JOIN mascotas m ON m.mascota_id = p.mascota_id
            LEFT JOIN usuarios u ON u.usuario_id = p.usuario_id
            WHERE p.activo = 1
            ORDER BY p.fecha_creacion DESC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Normalizar resultado: si no hay filas, devolver array vacío
            if (!$rows) return [];

            // Ajustes mínimos: construir rutas completas para las imágenes
            foreach ($rows as &$r) {
                $pubFoto = isset($r['foto']) ? trim($r['foto']) : '';
                $mascFoto = isset($r['mascota_foto']) ? trim($r['mascota_foto']) : '';

                // Si la publicación tiene foto válida, usarla y asegurar prefijo
                if ($pubFoto !== '' && strtolower($pubFoto) !== 'sin imagen') {
                    if (strpos($pubFoto, 'uploads/') === 0 || preg_match('/^https?:\/\//', $pubFoto)) {
                        $r['foto'] = $pubFoto;
                    } else {
                        $r['foto'] = 'uploads/publicaciones/' . $pubFoto;
                    }
                }
                // Si no, pero la mascota tiene foto, usar la de la mascota con su prefijo
                else if ($mascFoto !== '' && strtolower($mascFoto) !== 'sin imagen') {
                    if (strpos($mascFoto, 'uploads/') === 0 || preg_match('/^https?:\/\//', $mascFoto)) {
                        $r['foto'] = $mascFoto;
                    } else {
                        $r['foto'] = 'uploads/mascotas/' . $mascFoto;
                    }
                } else {
                    $r['foto'] = 'assets/img/default_pet.jpg';
                }

                // Asegurar campos esperados por el front
                $r['id'] = (int)$r['id'];
                $r['mascota_id'] = isset($r['mascota_id']) ? (int)$r['mascota_id'] : null;
                $r['recompensa'] = $r['recompensa'] !== null ? (float)$r['recompensa'] : null;
            }

            return $rows;

        } catch (PDOException $e) {
            error_log("Error al traer publicaciones: " . $e->getMessage());
            throw new \Exception("Error al consultar publicaciones en la base de datos.");
        }
    }
}

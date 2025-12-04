<?php
namespace DAL;

use Database\Conexion;
use Modelos\Mascota;
use PDO;
use PDOException;

class MascotaDAL {
    public static function crear(Mascota $mascotas) {
        try {
            $pdo = Conexion::getConexion();

            $sql = "INSERT INTO mascotas (nombre, fecha_nacimiento, foto, activo, raza_id, usuario_id, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, 2, NOW())";

            $stmt = $pdo->prepare($sql);

            $ok = $stmt->execute([
                $mascotas->nombre,
                $mascotas->fecha_nacimiento,
                $mascotas->foto,
                $mascotas->activo ?? 1,
                $mascotas->raza_id,
            ]);

            if ($ok) {
                return (int)$pdo->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            // log para debugging local
            file_put_contents(__DIR__ . '/mascota_dal_errors.log', "[".date('Y-m-d H:i:s')."] crear: ".$e->getMessage()."\n", FILE_APPEND);
            return false;
        }
    }
        public static function buscarPorUsuario($usuario_id) {
        try {
            $pdo = Conexion::getConexion();
            $sql = "
                SELECT
                  m.mascota_id AS id,
                  m.nombre,
                  m.raza_id,
                  m.foto,
                  m.fecha_nacimiento,
                  r.nombre AS raza_nombre
                FROM mascotas m
                LEFT JOIN razas r ON r.raza_id = m.raza_id
                WHERE m.usuario_id = 2 AND m.activo = 1
                ORDER BY m.nombre
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/mascota_dal_errors.log', "[".date('Y-m-d H:i:s')."] buscarPorUsuario: ".$e->getMessage()."\n", FILE_APPEND);
            return [];
        }
    }

    public static function buscarPorId($id) {
        try {
            $pdo = Conexion::getConexion();
            $sql = "
              SELECT
                m.mascota_id AS id,
                m.usuario_id,
                m.nombre,
                m.fecha_nacimiento,
                m.foto,
                m.activo,
                m.raza_id,
                r.nombre AS raza_nombre,
                m.fecha_creacion
              FROM mascotas m
              LEFT JOIN razas r ON r.raza_id = m.raza_id
              WHERE m.mascota_id = ?
              LIMIT 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/mascota_dal_errors.log', "[".date('Y-m-d H:i:s')."] buscarPorId: ".$e->getMessage()."\n", FILE_APPEND);
            return null;
        }
    }
}

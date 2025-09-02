<?php
namespace DAL;

use Database\Conexion;
use Modelos\Usuario;
use PDO;
use PDOException;

class UsuarioDAL {
    public static function crear(Usuario $usuario) {
        $pdo = Conexion::getConexion();

        $sql = "INSERT INTO usuarios (rol_id, nombre, apellido, email, clave, domicilio)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            $usuario->rol_id,
            $usuario->nombre,
            $usuario->apellido,
            $usuario->email,
            $usuario->clave,
            $usuario->domicilio
        ]);
    }

    public static function buscarPorEmail($email) {
        $pdo = Conexion::getConexion();

        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Usuario(
                $data['rol_id'] = 2, // rol_id por defecto = usuario
                $data['nombre'],
                $data['apellido'],
                $data['email'],
                $data['clave'],
                $data['domicilio'],
                $data['usuario_id']
            );
        }

        return null;
    }
}

<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UsuarioRol 
{

    public static function asignar(int $idUsuario, int $idRol): bool 
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO usuario_rol (id_usuario, id_rol, eliminado)
                VALUES (:idUsuario, :idRol, 0)";
                //tengo que chequear que exista eliminado en la bd 

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':idRol' => $idRol
        ]);
    }

    public static function listarPorUsuario(int $idUsuario): array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM usuario_rol 
                WHERE id_usuario = :idUsuario AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':idUsuario' => $idUsuario
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function revocar(int $id): bool 
    {
        $db = Database::getConnection();

        $sql = "DELETE FROM usuario_rol 
                WHERE id = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
         
        ]);
    }
}
<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class TokenRecuperacion 
{

    public static function crear(int $idUsuario, string $token, string $fechaExpiracion): bool 
    {
        $db = Database::getConnection();

        $sql = "INSERT INTO token_recuperacion (id_usuario, token, fecha_expiracion, usado)
                VALUES (:idUsuario, :token, :fechaExpiracion, 0)";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':token' => $token,
            ':fechaExpiracion' => $fechaExpiracion
        ]);
    }

    public static function buscarPorToken(string $token): ?array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM token_recuperacion 
                WHERE token = :token AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':token' => $token
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    public static function validar(string $token): bool 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM token_recuperacion 
                WHERE token = :token 
                AND usado = 0 
                AND fecha_expiracion > NOW()
                AND eliminado = 0";
                //necesito ver si la columna eliminado existe en la bd

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':token' => $token
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ? true : false;
    }

    public static function marcarUsado(string $token): bool 
    {
        $db = Database::getConnection();

        $sql = "UPDATE token_recuperacion 
                SET usado = 1 
                WHERE token = :token";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':token' => $token
        ]);
    }
}
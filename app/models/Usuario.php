<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario 
{

    public static function crear(array $datos): int 
    {
        $db = Database::getConnection();

        $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (email, password, eliminado)
                VALUES (:email, :password, 0)";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':email' => $datos['email'],
            ':password' => $passwordHash
        ]);

        return (int)$db->lastInsertId();
    }

    public static function buscarPorEmail(string $email): ?array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM usuario 
                WHERE email = :email AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public static function buscarPorId(int $id): ?array 
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM usuario 
                WHERE id = :id AND eliminado = 0";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public static function autenticar(string $email, string $password): ?array 
    {
        $usuario = self::buscarPorEmail($email);

        if (!$usuario) {
            return null;
        }

        if (password_verify($password, $usuario['password'])) {
            return $usuario;
        }

        return null;
    }

    public static function desactivar(int $id): bool 
    {
        $db = Database::getConnection();

        $sql = "UPDATE usuario 
                SET eliminado = 1 
                WHERE id = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    public static function eliminar(int $id): bool 
    {
        $db = Database::getConnection();

        $sql = "DELETE FROM usuario 
                WHERE id = :id";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
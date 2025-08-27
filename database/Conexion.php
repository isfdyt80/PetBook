<?php
namespace Database;

use PDO;
use PDOException;
use Core\Env;

class Conexion {
    public static function getConexion() {
        $host = Env::get('DB_HOST');
        $port = Env::get('DB_PORT', 3306);
        $db   = Env::get('DB_DATABASE');
        $user = Env::get('DB_USERNAME');
        $pass = Env::get('DB_PASSWORD');

        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
}

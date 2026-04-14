<?php

namespace App\Core;

use PDO;

class Database
{
    private static $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn === null) {
            self::$conn = new PDO(
                "mysql:host=localhost;dbname=mascotas;charset=utf8mb4",
                "root",
                ""
            );

            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$conn;
    }
}

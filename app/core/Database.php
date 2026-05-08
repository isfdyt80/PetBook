<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn === null) {

            self::$conn = new PDO(
                "mysql:host=localhost;dbname=petbook;charset=utf8mb4",
                "root",
                ""
            );

            self::$conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        }

        return self::$conn;
    }
}
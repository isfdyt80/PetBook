<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class TipoReaccion
{
    public static function listar(): array
    {
        $db = Database::getConnection();

        $sql = "SELECT * FROM TipoReaccion";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
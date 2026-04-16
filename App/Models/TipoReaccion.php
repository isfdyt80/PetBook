<?php

namespace App\Models;

use Database\Conexion;
use PDO;

class TipoReaccion
{
    public function listar(): array
    {
        $pdo  = Conexion::getConexion();
        $stmt = $pdo->prepare("SELECT * FROM TipoReaccion WHERE Eliminado = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

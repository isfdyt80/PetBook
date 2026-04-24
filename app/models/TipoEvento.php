<?php

namespace App\Models;
use Core\Model;

class TipoEvento extends Model
{
    // Lista todos los tipos de eventos (ej: perdido, adopcion, etc.)
    public function listar(){
        $sql = "SELECT 
                    Id_TipoEvento,
                    Nombre
                FROM TipoEvento";

        return $this->query($sql);
    }

    // Busca un tipo de evento por ID
    public function buscarPorId(int $id){
        $sql = "SELECT 
                    Id_TipoEvento,
                    Nombre
                FROM TipoEvento
                WHERE Id_TipoEvento = :id";

        return $this->queryOne($sql, ['id' => $id]);
    }
}
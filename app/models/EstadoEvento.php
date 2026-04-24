<?php

namespace App\Models;
use Core\Model;

class EstadoEvento extends Model
{
    // Devuelve todos los estados posibles de eventos
    public function listar(){
        $sql = "SELECT 
                    Id_EstadoEvento,
                    Nombre
                FROM EstadoEvento";

        return $this->query($sql);
    }

    // Busca un estado especifico por su ID
    public function buscarPorId(int $id){
        $sql = "SELECT 
                    Id_EstadoEvento,
                    Nombre
                FROM EstadoEvento
                WHERE Id_EstadoEvento = :id";

        return $this->queryOne($sql, ['id' => $id]);
    }
}
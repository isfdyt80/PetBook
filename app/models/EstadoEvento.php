<?php

namespace App\Models;
use App\Core\Model;


class EstadoEvento extends Model
{
    protected $table = 'EstadoEvento';
    protected $pk = 'Id_EstadoEvento';
    
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

        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Busca un estado de evento por nombre del catálogo.
     * Devuelve false si no existe.
     *
     * Complemento de EstadoEvento::listar() para resolver estados por nombre
     * en vez de IDs hardcodeados (convención de la guía de desarrollo, 2.3).
     *
     * @param  string      $nombre  Nombre según el catálogo (ej: 'ACTIVO').
     * @return array|false
     */
    public function buscarPorNombre(string $nombre): array|false
    {
        return $this->queryOne(
            'SELECT Id_EstadoEvento, Nombre
             FROM EstadoEvento
             WHERE UPPER(Nombre) = :nombre',
            [':nombre' => strtoupper($nombre)]
        ) ?: false;
    }
}
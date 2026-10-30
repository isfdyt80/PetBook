<?php

namespace App\Models;
use App\Core\Model;

class TipoEvento extends Model
{
    protected $table = 'TipoEvento';
    protected $pk = 'Id_TipoEvento';
    
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

        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Busca un tipo de evento por nombre del catálogo.
     * Devuelve false si no existe.
     *
     * Uso: validar/normalizar el tipo antes de persistir sin hardcodear IDs
     * (convención de la guía de desarrollo, sección 2.3).
     *
     * @param  string      $nombre  Nombre según el catálogo (ej: 'PERDIDA').
     * @return array|false
     */
    public function buscarPorNombre(string $nombre): array|false
    {
        return $this->queryOne(
            'SELECT Id_TipoEvento, Nombre
             FROM TipoEvento
             WHERE UPPER(Nombre) = :nombre',
            [':nombre' => strtoupper($nombre)]
        ) ?: false;
    }
}
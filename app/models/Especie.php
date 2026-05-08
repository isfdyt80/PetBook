<?php

namespace App\Models;

use App\Core\Model;

class Especie extends Model
{
    protected string $table = 'Especie';
    protected string $pk    = 'Id_Especie';

    /**
     * Devuelve todas las especies activas.
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_Especie, Nombre
             FROM Especie
             WHERE Eliminado = 0'
        );
    }

    /**
     * Busca una especie por su clave primaria.
     * Devuelve null si no existe o está eliminada.
     *
     * @param  int        $id  Id_Especie a buscar.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->queryOne(
            'SELECT Id_Especie, Nombre
             FROM Especie
             WHERE Id_Especie = :id AND Eliminado = 0',
            [':id' => $id]
        ) ?: null;
    }
}
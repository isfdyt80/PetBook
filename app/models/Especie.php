<?php

namespace App\Models;

use App\Core\Model;

class Especie extends Model
{
    protected string $table = 'Especie';
    protected string $pk    = 'Id_Especie';

    /**
     * Devuelve todas las especies .
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_Especie, Nombre
             FROM Especie'
        );
    }

    /**
     * Busca una especie por su clave primaria.
     * Devuelve null si no existe .
     *
     * @param  int        $id  Id_Especie a buscar.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->queryOne(
            'SELECT Id_Especie, Nombre
             FROM Especie
             WHERE Id_Especie = :id',
            [':id' => $id]
        ) ?: null;
    }
}
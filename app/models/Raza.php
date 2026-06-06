<?php

namespace App\Models;

use App\Core\Model;

class Raza extends Model
{
    protected string $table = 'Raza';
    protected string $pk    = 'Id_Raza';

    /**
     * Devuelve todas las razas activas.
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_Raza, Nombre, Id_Especie
             FROM Raza
             WHERE Eliminado = 0'
        );
    }

    /**
     * Devuelve las razas activas de una especie.
     *
     * @param  int   $idEspecie  Id_Especie a filtrar.
     * @return array
     */
    public function listarPorEspecie(int $idEspecie): array
    {
        return $this->query(
            'SELECT Id_Raza, Nombre
             FROM Raza
             WHERE Id_Especie = :id',
            [':id' => $idEspecie]
        );
    }

    /**
     * Busca una raza por su clave primaria.
     * Devuelve null si no existe o está eliminada.
     *
     * @param  int        $id  Id_Raza a buscar.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->queryOne(
            'SELECT Id_Raza, Nombre, Id_Especie
             FROM Raza
             WHERE Id_Raza = :id',
            [':id' => $id]
        ) ?: null;
    }
}
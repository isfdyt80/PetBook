<?php

namespace App\Models;

use App\Core\Model;

class Persona extends Model
{
    protected string $table = 'Persona';
    protected string $pk    = 'Id_Persona';

    /**
     * Crea una nueva persona.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Nombre'     => $datos['Nombre'],
            'Apellido'   => $datos['Apellido'] ?? null,
            'Eliminado'  => 0
        ]);
    }

    /**
     * Busca una persona por ID.
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->find($id);
    }

    /**
     * Anonimiza una persona.
     */
    public function anonimizar(int $id): bool
    {
        return $this->update($id, [
            'Nombre'    => 'Anonimo',
            'Apellido'  => '',
            'Eliminado' => 1
        ]);
    }
}
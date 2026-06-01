<?php

namespace App\Models;

use App\Core\Model;

class Persona extends Model
{
    protected string $table = 'Persona';
    protected string $pk    = 'Id_Persona';

    /**
     * Crea una nueva persona y devuelve el ID generado.
     *
     * @param  array $datos  Debe contener Nombre. Apellido es opcional.
     * @return int           ID del registro recién insertado.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Nombre'    => $datos['Nombre'],
            'Apellido'  => $datos['Apellido'] ?? null,
            'Eliminado' => 0,
        ]);
    }

    /**
     * Busca una persona por su clave primaria.
     * Devuelve false si no existe o está eliminada.
     *
     * @param  int         $id  Id_Persona a buscar.
     * @return array|false
     */
    public function buscarPorId(int $id): array|false
    {
        return $this->queryOne(
            'SELECT Id_Persona, Nombre, Apellido
             FROM Persona
             WHERE Id_Persona = :id AND Eliminado = 0',
            [':id' => $id]
        ) ?: false;
    }

    /**
     * Anonimiza una persona nullificando sus datos reales.
     * Se usa al eliminar lógicamente una cuenta de usuario.
     * Los campos quedan en null, no en strings vacíos, para
     * reflejar que la información fue borrada, no reemplazada.
     *
     * @param  int  $id  Id_Persona a anonimizar.
     * @return bool      true si se modificó al menos una fila.
     */
    public function anonimizar(int $id): bool
    {
        return $this->update($id, [
            'Nombre'           => null,
            'Apellido'         => null,
            'Fecha_Nacimiento' => null,
            'Telefono'         => null,
            'Eliminado'        => 1,
        ]);
    }
}
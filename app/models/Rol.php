<?php

namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    protected string $table = 'Rol';
    protected string $pk    = 'Id_Rol';

    /**
     * Lista todos los roles.
     */
    public function listar(): array
    {
        return $this->all();
    }

    /**
     * Busca un rol por nombre.
     */
    public function buscarPorNombre(string $nombre): array|false
    {
        $sql = "SELECT * FROM Rol
                WHERE Nombre = :nombre
                AND Eliminado = 0";

        $resultado = $this->query($sql, [
            ':nombre' => $nombre
        ]);

        return $resultado[0] ?? false;
    }
}
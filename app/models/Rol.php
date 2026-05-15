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
    $sql = "SELECT
                Id_Rol,
                Nombre
            FROM Rol
            WHERE Eliminado = 0";

    return $this->query($sql);
    }
    /**
     * Busca un rol por nombre.
     */
    public function buscarPorNombre(string $nombre): array|false
    {
    $sql = "SELECT
                Id_Rol,
                Nombre
            FROM Rol
            WHERE Nombre = :nombre
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':nombre' => $nombre
    ]);

    return $resultado[0] ?? false;
    }
}
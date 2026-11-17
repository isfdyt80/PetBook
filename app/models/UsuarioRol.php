<?php

namespace App\Models;

use App\Core\Model;

class UsuarioRol extends Model
{
    protected string $table = 'UsuarioRol';
    protected string $pk    = 'Id_UsuarioRol';

    /**
     * Asigna un rol a un usuario.
     */
    public function asignar(
        int $idUsuario,
        int $idRol
    ): int {
        return $this->insert([
            'Id_Usuario' => $idUsuario,
            'Id_Rol'     => $idRol,
            'Eliminado'  => 0
        ]);
    }

    /**
     * Lista roles de un usuario.
     */
    public function listarPorUsuario(int $idUsuario): array
    {
    $sql = "SELECT
                Id_UsuarioRol,
                Id_Usuario,
                Id_Rol
            FROM UsuarioRol
            WHERE Id_Usuario = :idUsuario
            AND Eliminado = 0";

    return $this->query($sql, [
        ':idUsuario' => $idUsuario
    ]);
    }

    /**
     * Revoca un rol.
     */
    public function revocar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
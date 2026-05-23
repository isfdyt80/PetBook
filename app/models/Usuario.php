<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'Usuario';
    protected string $pk    = 'Id_Usuario';

    /**
     * Crea un nuevo usuario.
     */
    public function crear(array $datos): int
    {
        return $this->insert([
            'Id_Persona'    => $datos['Id_Persona'],
            'Email'         => $datos['Email'],
            'Password_Hash' => $datos['Password_Hash'],
            'Activo'        => 1,
            'Eliminado'     => 0
        ]);
    }

    /**
     * Busca un usuario por email.
     */
    public function buscarPorEmail(string $email): array|false
{
    $sql = "SELECT
                Id_Usuario,
                Id_Persona,
                Email,
                Password_Hash,
                Activo
            FROM Usuario
            WHERE Email = :email
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':email' => $email
    ]);

    return $resultado[0] ?? false;
    }

    /**
     * Busca un usuario por ID.
     */
    public function buscarPorId(int $id): array|false
    {
    $sql = "SELECT
                Id_Usuario,
                Id_Persona,
                Email,
                Activo
            FROM Usuario
            WHERE Id_Usuario = :id
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':id' => $id
    ]);

    return $resultado[0] ?? false;
    }

    /**
     * Verifica credenciales.
     */
    public function autenticar(string $email,string $password): array|false 
    {

    $sql = "SELECT
                Id_Usuario,
                Id_Persona,
                Email,
                Password_Hash,
                Activo
            FROM Usuario
            WHERE Email = :email
            AND Activo = 1
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':email' => $email
    ]);

    $usuario = $resultado[0] ?? false;

    if (!$usuario) {
        return false;
    }

    if (
        password_verify(
            $password,
            $usuario['Password_Hash']
        )
    ) {
        return $usuario;
    }

    return false;
    }

    /**
     * Desactiva un usuario.
     */
    public function desactivar(int $id): bool
    {
        return $this->update($id, [
            'Activo' => 0
        ]);
    }

    /**
     * Soft delete.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}
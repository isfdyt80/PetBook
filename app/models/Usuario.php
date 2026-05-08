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
        $sql = "SELECT * FROM Usuario
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
        return $this->find($id);
    }

    /**
     * Verifica credenciales.
     */
    public function autenticar(
        string $email,
        string $password
    ): array|false {
        $usuario = $this->buscarPorEmail($email);

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
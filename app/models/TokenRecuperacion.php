<?php

namespace App\Models;

use App\Core\Model;

class TokenRecuperacion extends Model
{
    protected string $table = 'TokenRecuperacion';
    protected string $pk    = 'Id_TokenRecuperacion';

    /**
     * Crea un token de recuperación.
     */
    public function crear(
        int $idUsuario,
        string $token,
        string $fechaExpiracion
    ): int {
        return $this->insert([
            'Id_Usuario'       => $idUsuario,
            'Token'            => $token,
            'Fecha_Expiracion' => $fechaExpiracion,
            'Usado'            => 0,
            'Eliminado'        => 0
        ]);
    }

    /**
     * Busca token.
     */
    public function buscarPorToken(string $token): array|false
    {
    $sql = "SELECT
                Id_TokenRecuperacion,
                Id_Usuario,
                Token,
                Fecha_Expiracion,
                Usado
            FROM TokenRecuperacion
            WHERE Token = :token
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':token' => $token
    ]);

    return $resultado[0] ?? false;
    }

    /**
     * Valida token.
     */
    public function validar(string $token): bool
    {
    $sql = "SELECT
                Id_TokenRecuperacion
            FROM TokenRecuperacion
            WHERE Token = :token
            AND Usado = 0
            AND Fecha_Expiracion > NOW()
            AND Eliminado = 0";

    $resultado = $this->query($sql, [
        ':token' => $token
    ]);

    return !empty($resultado);
    }

    /**
     * Marca token como usado.
     */
    public function marcarUsado(string $token): bool
    {
        $sql = "UPDATE TokenRecuperacion
                SET Usado = 1
                WHERE Token = :token";

        return $this->execute($sql, [
            ':token' => $token
        ]);
    }
}
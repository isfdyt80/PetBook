<?php

namespace App\Models;

use App\Core\Model;

class TokenRecuperacion extends Model
{
    protected string $table = 'TokenRecuperacion';
    protected string $pk    = 'Id_Token';

    /**
     * Crea un token de recuperación y devuelve el ID generado.
     *
     * @param  int    $idUsuario       Id_Usuario al que pertenece el token.
     * @param  string $token           Token único generado externamente.
     * @param  string $fechaExpiracion Fecha de expiración en formato Y-m-d H:i:s.
     * @return int                     ID del registro recién insertado.
     */
    public function crear(int $idUsuario, string $token, string $fechaExpiracion): int
    {
        return $this->insert([
            'Id_Usuario'       => $idUsuario,
            'Token'            => $token,
            'Fecha_Expiracion' => $fechaExpiracion,
            'Usado'            => 0,
            'Eliminado'        => 0,
        ]);
    }

    /**
     * Busca un token por su valor.
     * Devuelve false si no existe o está eliminado.
     * No valida expiración ni uso: eso lo hace validar().
     *
     * @param  string      $token  Valor del token a buscar.
     * @return array|false
     */
    public function buscarPorToken(string $token): array|false
    {
        return $this->queryOne(
            'SELECT Id_Token, Id_Usuario, Token, Fecha_Expiracion, Usado
             FROM TokenRecuperacion
             WHERE Token = :token AND Eliminado = 0',
            [':token' => $token]
        ) ?: false;
    }

    /**
     * Valida que el token sea utilizable:
     * no usado, no eliminado y con fecha de expiración futura.
     *
     * @param  string $token  Valor del token a validar.
     * @return bool           true si el token es válido.
     */
    public function validar(string $token): bool
    {
        $resultado = $this->queryOne(
            'SELECT Id_Token
             FROM TokenRecuperacion
             WHERE Token = :token
               AND Usado = 0
               AND Fecha_Expiracion > NOW()
               AND Eliminado = 0',
            [':token' => $token]
        );

        return $resultado !== false;
    }

    /**
     * Marca el token como usado.
     *
     * @param  string $token  Valor del token a marcar.
     * @return bool           true si se modificó al menos una fila.
     */
    public function marcarUsado(string $token): bool
    {
        return $this->execute(
            'UPDATE TokenRecuperacion SET Usado = 1 WHERE Token = :token',
            [':token' => $token]
        )->rowCount() > 0;
    }
}
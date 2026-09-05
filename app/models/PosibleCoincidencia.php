<?php

namespace App\Models;

use App\Core\Model;

class PosibleCoincidencia extends Model
{
    protected string $table = 'PosibleCoincidencia';
    protected string $pk    = 'Id';

    /**
     * Registra una posible coincidencia entre dos mascotas.
     *
     * @param  array $datos  Debe contener Id_MascotaA, Id_MascotaB, Nivel_Confianza.
     * @return bool          true si se insertó correctamente.
     */
    public function crear(array $datos): bool
    {
        $stmt = $this->execute(
            'INSERT INTO PosibleCoincidencia (Id_MascotaA, Id_MascotaB, Nivel_Confianza)
             VALUES (:a, :b, :nivel)',
            [
                ':a'     => $datos['Id_MascotaA'],
                ':b'     => $datos['Id_MascotaB'],
                ':nivel' => $datos['Nivel_Confianza'],
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista las coincidencias pendientes de revisión.
     *
     * @return array
     */
    public function listarPendientes(): array
    {
        return $this->query(
            "SELECT Id_PosibleCoincidencia, Id_MascotaA, Id_MascotaB, Nivel_Confianza, Resultado, Id_Usuario
             FROM PosibleCoincidencia
             WHERE Resultado = 'PENDIENTE'"
        );
    }

    /**
     * Actualiza el resultado de una coincidencia y registra qué usuario la revisó.
     *
     * @param  int    $id         Id_PosibleCoincidencia a actualizar.
     * @param  string $resultado  Valor del resultado (ej: 'CONFIRMADO', 'DESCARTADO').
     * @param  int    $idUsuario  Id_Usuario que revisó la coincidencia.
     * @return bool               true si se modificó al menos una fila.
     */

    public function actualizar(int $id, string $resultado, int $idUsuario): bool
    {
        $resultado  = strtoupper($resultado);
        $permitidos = ['PENDIENTE', 'CONFIRMADO', 'DESCARTADO'];

        if (!in_array($resultado, $permitidos, true)) {
            throw new \InvalidArgumentException(
                'Resultado inválido. Valores permitidos: PENDIENTE, CONFIRMADO, DESCARTADO.'
            );
        }

        $stmt = $this->execute(
            'UPDATE PosibleCoincidencia
            SET Resultado = :resultado,
                Id_Usuario = :usuario,
                Revisado = 1
            WHERE Id_PosibleCoincidencia = :id',
            [
                ':resultado' => $resultado,
                ':usuario'   => $idUsuario,
                ':id'        => $id
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
<?php

namespace App\Models;

use App\Core\Model;

class Mascota extends Model
{
    protected string $table = 'Mascota';
    protected string $pk    = 'Id_Mascota';

    /**
     * Campos de la tabla que pueden ser escritos desde el exterior.
     * Se usa en actualizar() para ignorar cualquier clave ajena al dominio.
     */
    private const CAMPOS_PERMITIDOS = [
        'Nombre', 'Id_Especie', 'Id_Raza', 'Color', 'Tamaño',
        'Sexo', 'Fecha_Nacimiento', 'Edad_Aproximada', 'Descripcion_Fisica',
    ];

    // ── Público ──────────────────────────────────────────────────────────

    /**
     * Crea una nueva mascota y devuelve el ID generado.
     *
     * Valida que, si se informa Id_Raza, esta pertenezca a la misma
     * especie que Id_Especie. Lanza \InvalidArgumentException si no.
     *
     * @param  array $datos  Campos de la mascota (Id_Especie requerido).
     * @return int           ID del registro recién insertado.
     */
    public function crear(array $datos): int
    {
        $this->validarRazaEspecie($datos);

        return $this->insert([
            'Nombre'             => $datos['Nombre']             ?? null,
            'Id_Especie'         => $datos['Id_Especie'],
            'Id_Raza'            => $datos['Id_Raza']            ?? null,
            'Color'              => $datos['Color']              ?? null,
            'Tamaño'             => $datos['Tamaño']             ?? null,
            'Sexo'               => $datos['Sexo']               ?? null,
            'Fecha_Nacimiento'   => $datos['Fecha_Nacimiento']   ?? null,
            'Edad_Aproximada'    => $datos['Edad_Aproximada']    ?? null,
            'Descripcion_Fisica' => $datos['Descripcion_Fisica'] ?? null,
            'Eliminado'          => 0,
        ]);
    }

    /**
     * Busca una mascota por su clave primaria.
     * Devuelve null si no existe o está eliminada.
     *
     * @param  int        $id  Id_Mascota a buscar.
     * @return array|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->queryOne(
            'SELECT Id_Mascota, Nombre, Id_Especie, Id_Raza, Color, Tamaño,
                    Sexo, Fecha_Nacimiento, Edad_Aproximada, Descripcion_Fisica
             FROM Mascota
             WHERE Id_Mascota = :id AND Eliminado = 0',
            [':id' => $id]
        ) ?: null;
    }

    /**
     * Actualiza solo los campos presentes en $datos para la mascota indicada.
     * Ignora claves que no estén en CAMPOS_PERMITIDOS (evita mass-assignment).
     * Si se informa Id_Raza valida que pertenezca a la especie.
     *
     * @param  int   $id     Id_Mascota a modificar.
     * @param  array $datos  Campos a actualizar (parcial o completo).
     * @return bool          true si se modificó al menos una fila.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $this->validarRazaEspecie($datos);

        // Filtramos solo los campos permitidos para no enviar columnas extrañas al UPDATE.
        // No usamos array_filter para no descartar nulls válidos (ej: borrar la raza).
        $payload = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));

        if (empty($payload)) {
            return false;
        }

        return $this->update($id, $payload);
    }

    /**
     * Marca la mascota como eliminada (soft delete).
     * Nunca borra físicamente el registro de la BD.
     *
     * @param  int  $id  Id_Mascota a eliminar.
     * @return bool      true si se modificó al menos una fila.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }

    // ── Privado ──────────────────────────────────────────────────────────

    /**
     * Verifica que la raza informada pertenezca a la especie indicada.
     * Si Id_Raza está vacío o es null, no hace nada.
     *
     * @param  array $datos  Debe contener Id_Raza e Id_Especie.
     * @throws \InvalidArgumentException  Si la raza no corresponde a la especie.
     */
    private function validarRazaEspecie(array $datos): void
    {
        if (empty($datos['Id_Raza'])) {
            return;
        }

        $raza = $this->queryOne(
            'SELECT Id_Especie
             FROM Raza
             WHERE Id_Raza = :raza AND Eliminado = 0',
            [':raza' => $datos['Id_Raza']]
        );

        if (!$raza || $raza['Id_Especie'] != $datos['Id_Especie']) {
            throw new \InvalidArgumentException('La raza no pertenece a la especie indicada.');
        }
    }
}
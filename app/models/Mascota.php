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
     * Registra una nueva mascota y la asocia a un usuario.
     *
     * Escribe en Mascota y en MascotaUsuario dentro de la misma transacción,
     * dejando la relación activa (FechaHasta NULL) con EsDueno = 1, porque
     * quien registra es el propietario. Valida antes de guardar que la raza
     * pertenezca a la especie indicada.
     *
     * @param  array $datos  Debe contener Id_Usuario (propietario) e Id_Especie.
     *                       Opcionales: Id_Raza, Nombre, Color, Tamaño, Sexo,
     *                       Fecha_Nacimiento, Edad_Aproximada.
     * @return int           ID de la mascota recién registrada.
     * @throws \InvalidArgumentException Si la raza no corresponde a la especie.
     * @throws \RuntimeException         Si la transacción falla.
     */
    public function registrar(array $datos): int
    {
        $this->validarRazaEspecie($datos);

        $this->db->beginTransaction();

        try {
            $idMascota = $this->insert([
                'Nombre'            => $datos['Nombre']            ?? null,
                'Id_Especie'        => $datos['Id_Especie'],
                'Id_Raza'           => $datos['Id_Raza']           ?? null,
                'Color'             => $datos['Color']             ?? null,
                'Tamaño'            => $datos['Tamaño']            ?? null,
                'Sexo'              => $datos['Sexo']              ?? null,
                'Fecha_Nacimiento'  => $datos['Fecha_Nacimiento']  ?? null,
                'Edad_Aproximada'   => $datos['Edad_Aproximada']   ?? null,
                'Eliminado'         => 0,
            ]);

            $this->execute(
                'INSERT INTO MascotaUsuario (Id_Mascota, Id_Usuario, EsDueno, FechaDesde)
                 VALUES (:mascota, :usuario, 1, NOW())',
                [
                    ':mascota' => $idMascota,
                    ':usuario' => $datos['Id_Usuario'],
                ]
            );

            $this->db->commit();

            return $idMascota;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('Error al registrar la mascota: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Lista las mascotas activas asociadas a un usuario, ordenadas por nombre.
     *
     * Solo considera relaciones vigentes (FechaHasta IS NULL) y mascotas no
     * eliminadas (Eliminado = 0). Resuelve los nombres de especie y raza.
     *
     * @param  int   $idUsuario  Id_Usuario del propietario.
     * @return array
     */
    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->query(
            'SELECT m.Id_Mascota, m.Nombre, e.Nombre AS Especie, r.Nombre AS Raza, m.Fecha_Nacimiento
             FROM Mascota m
             JOIN MascotaUsuario mu ON mu.Id_Mascota = m.Id_Mascota
             JOIN Especie e         ON e.Id_Especie = m.Id_Especie
             LEFT JOIN Raza r       ON r.Id_Raza = m.Id_Raza
             WHERE mu.Id_Usuario = :id
               AND mu.FechaHasta IS NULL
               AND m.Eliminado = 0
             ORDER BY m.Nombre ASC',
            [':id' => $idUsuario]
        );
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
            'SELECT m.Id_Mascota, m.Nombre, m.Id_Especie, e.Nombre AS Especie,
                    m.Id_Raza, r.Nombre AS Raza, m.Color, m.Tamaño, m.Sexo,
                    m.Fecha_Nacimiento, m.Edad_Aproximada, m.Descripcion_Fisica
             FROM Mascota m
             JOIN Especie e ON e.Id_Especie = m.Id_Especie
             LEFT JOIN Raza r ON r.Id_Raza = m.Id_Raza
             WHERE m.Id_Mascota = :id AND m.Eliminado = 0',
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
             WHERE Id_Raza = :raza',
            [':raza' => $datos['Id_Raza']]
        );

        if (!$raza || $raza['Id_Especie'] != $datos['Id_Especie']) {
            throw new \InvalidArgumentException('La raza no pertenece a la especie indicada.');
        }
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class EstadoPublicacion extends Model
{
    protected string $table = 'EstadoPublicacion';
    protected string $pk    = 'Id_EstadoPublicacion';

    /**
     * Lista todos los estados activos.
     */
    public function listar(): array
    {
        $sql = "SELECT
                    Id_EstadoPublicacion,
                    Nombre
                FROM EstadoPublicacion
                WHERE Eliminado = 0";

        return $this->query($sql);
    }

    /**
     * Busca un estado por nombre.
     */
    public function buscarPorNombre(
        string $nombre
    ): array|false {

        $sql = "SELECT
                    Id_EstadoPublicacion,
                    Nombre
                FROM EstadoPublicacion
                WHERE Nombre = :nombre
                AND Eliminado = 0";

        $resultado = $this->query($sql, [
            ':nombre' => $nombre
        ]);

        return $resultado[0] ?? false;
    }
}
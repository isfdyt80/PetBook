<?php

namespace App\Models;

use App\Core\Model;

class EstadoPublicacion extends Model
{
    protected string $table = 'EstadoPublicacion';
    protected string $pk    = 'Id_EstadoPublicacion';

    /**
     * Lista todos los estados de publicación.
     * EstadoPublicacion es una tabla de catálogo sin campo Eliminado.
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_EstadoPublicacion, Nombre
             FROM EstadoPublicacion'
        );
    }

    /**
     * Busca un estado por nombre.
     * Devuelve false si no existe.
     *
     * @param  string      $nombre  Nombre del estado a buscar.
     * @return array|false
     */
    public function buscarPorNombre(string $nombre): array|false
    {
        return $this->queryOne(
            'SELECT Id_EstadoPublicacion, Nombre
             FROM EstadoPublicacion
             WHERE Nombre = :nombre',
            [':nombre' => $nombre]
        ) ?: false;
    }
}
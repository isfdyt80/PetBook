<?php

namespace App\Models;

use App\Core\Model;

class EstadoReporte extends Model
{
    protected string $table = 'EstadoReporte';
    protected string $pk    = 'Id_EstadoReporte';

    /**
     * Devuelve todos los estados de reporte.
     * EstadoReporte es una tabla de catálogo, no tiene soft delete.
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_EstadoReporte, Nombre
             FROM EstadoReporte'
        );
    }

    /**
     * Busca un estado de reporte por su nombre.
     * Devuelve null si no existe.
     *
     * @param  string     $nombre  Nombre del estado a buscar.
     * @return array|null
     */
    public function buscarPorNombre(string $nombre): ?array
    {
        return $this->queryOne(
            'SELECT Id_EstadoReporte, Nombre
             FROM EstadoReporte
             WHERE Nombre = :nombre',
            [':nombre' => $nombre]
        ) ?: null;
    }
}
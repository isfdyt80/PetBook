<?php

namespace App\Models;

use App\Core\Model;

class TipoReaccion extends Model
{
    protected string $table = 'TipoReaccion';
    protected string $pk    = 'Id_TipoReaccion';

    /**
     * Lista todos los tipos de reacción.
     * TipoReaccion es una tabla de catálogo sin campo Eliminado.
     *
     * @return array
     */
    public function listar(): array
    {
        return $this->query(
            'SELECT Id_TipoReaccion, Nombre
             FROM TipoReaccion'
        );
    }
}
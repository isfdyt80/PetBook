<?php

namespace App\Models;

use App\Core\Model;

class TipoReaccion extends Model
{
    protected string $table = 'TipoReaccion';
    protected string $pk    = 'Id_TipoReaccion';

    /**
     * Lista todos los tipos de reacción.
     */
    public function listar(): array
    {
        return $this->all();
    }
}
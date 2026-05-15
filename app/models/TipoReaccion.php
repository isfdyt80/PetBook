<?php

namespace App\Models;

use App\Core\Model;

class TipoReaccion extends Model
{
    protected string $table = 'TipoReaccion';
    protected string $pk    = 'Id_TipoReaccion';

    /**
     * Lista todos los tipos de reacción activos.
     */
    public function listar(): array
    {
        $sql = "SELECT
                    Id_TipoReaccion,
                    Nombre
                FROM TipoReaccion
                WHERE Eliminado = 0";

        return $this->query($sql);
    }
}
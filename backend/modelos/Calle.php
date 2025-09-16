<?php
namespace Modelos;

class Calle {
    public $calle_id;
    public $nombre;
    public $localidad_id;

    public function __construct($nombre, $localidad_id, $calle_id = null) {
        $this->calle_id = $calle_id;
        $this->nombre   = $nombre;
        $this->localidad_id = $localidad_id;
    }
}
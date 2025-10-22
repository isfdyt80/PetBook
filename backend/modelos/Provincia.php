<?php
namespace Modelos;

class Provincia {
    public $provincia_id;
    public $nombre;
    public $pais_id;

    public function __construct($nombre, $pais_id, $provincia_id = null) {
        $this->provincia_id = $provincia_id;
        $this->nombre      = $nombre;
        $this->pais_id = $pais_id;
    }
}
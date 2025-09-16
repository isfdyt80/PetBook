<?php
namespace Modelos;

class pais {
    public $pais_id;
    public $nombre;

    public function __construct($nombre, $pais_id = null) {
        $this->pais_id = $pais_id;
        $this->nombre = $nombre;
    }
}
<?php
namespace Modelos; 

class Localidad {
    public $localidad_id;
    public $nombre;
    public $partido_id;

    public function __construct($nombre, $partido_id, $localidad_id = null) {
        $this->localidad_id = $localidad_id;
        $this->nombre      = $nombre;
        $this->partido_id = $partido_id;
    }
}
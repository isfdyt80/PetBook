<?php
namespace Modelos;

class Partido{
    public $partido_id;
    public $nombre;
    public $provincia_id;

    public function __construct($nombre, $provincia_id, $partido_id = null) {
        $this->partido_id = $partido_id;
        $this->nombre = $nombre;
        $this->provincia_id = $provincia_id;
    }

}
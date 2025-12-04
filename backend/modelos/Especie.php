<?php
namespace Modelos;
class Especie {
    public $especie_id;
    public $nombre;
    public $descripcion;

    public function __construct($nombre, $descripcion, $especie_id = null) {
        $this->especie_id = $especie_id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }
}
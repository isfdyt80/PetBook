<?php
namespace Modelos;
class Raza {
    public $raza_id;
    public $especie_id;
    public $nombre;
    public $descripcion;

    public function __construct($especie_id, $nombre, $descripcion, $raza_id = null) {
        $this->raza_id = $raza_id;
        $this->especie_id = $especie_id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }
}
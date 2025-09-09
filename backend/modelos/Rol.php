<?php
namespace Modelos;
class Rol {
    public $rol_id;
    public $nombre;
    public $descripcion;

    public function __construct($nombre, $descripcion, $rol_id = null) {
        $this->rol_id = $rol_id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }
}
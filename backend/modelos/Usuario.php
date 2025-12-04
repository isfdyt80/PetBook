<?php
namespace Modelos;

class Usuario {
    public $id;
    public $rol_id;
    public $nombre;
    public $apellido;
    public $email;
    public $clave;
    public $domicilio;

    public function __construct($rol_id, $nombre, $apellido, $email, $clave, $domicilio, $id = null) {
        $this->id        = $id;
        $this->rol_id    = $rol_id;
        $this->nombre    = $nombre;
        $this->apellido  = $apellido;
        $this->email     = $email;
        $this->clave     = $clave;
        $this->domicilio = $domicilio;
    }
}

<?php
namespace Modelos;

class Mascotas {
    public $id;
    public $estado;
    public $nombre;
    public $fecha_nacimiento;
    public $raza_id;
    public $usuario_id;

    public function __construct($estado, $nombre, $fecha_nacimiento, $raza_id, $usuario_id, $id = null) {
        $this->id        = $id;
        $this->estado    = $estado;
        $this->nombre    = $nombre;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->raza_id  = $raza_id;
        $this->usuario_id = $usuario_id;
    }
}
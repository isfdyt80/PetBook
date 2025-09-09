<?php
namespace Modelos;

class Mascota {
    public $mascota_id;
    public $estado;
    public $nombre;
    public $fecha_nacimiento;
    public $foto;
    public $fecha_creacion;
    public $activo;
    public $raza_id;
    public $usuario_id;

    public function __construct($estado, $nombre, $fecha_nacimiento, $raza_id,  $usuario_id, $mascota_id = null, $foto = null, $fecha_creacion = null, $activo = 1) {
        $this->mascota_id = $mascota_id;
        $this->estado    = $estado;
        $this->nombre    = $nombre;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->foto      = $foto;
        $this->fecha_creacion = $fecha_creacion;
        $this->activo    = $activo;
        $this->raza_id  = $raza_id;
        $this->usuario_id = $usuario_id;
    }
}
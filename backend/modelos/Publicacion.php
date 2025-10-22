<?php
namespace Modelos;

class Publicacion{
    public $publicacion_id;
    public $activo;
    public $descripcion;
    public $estado;
    public $fecha_creacion;
    public $foto;
    public $recompensa;
    public $ubicacion;  
    public $mascota_id;
    public $usuario_id;

    public function __construct($descripcion, $estado, $mascota_id, $usuario_id, $publicacion_id = null, $activo = 1, $fecha_creacion = null, $foto = null, $recompensa = null, $ubicacion = null) {
        $this->publicacion_id = $publicacion_id;
        $this->activo    = $activo;
        $this->descripcion = $descripcion;
        $this->estado    = $estado;
        $this->fecha_creacion = $fecha_creacion;
        $this->foto      = $foto;
        $this->recompensa = $recompensa;
        $this->ubicacion = $ubicacion;
        $this->mascota_id  = $mascota_id;
        $this->usuario_id = $usuario_id;
    }
}
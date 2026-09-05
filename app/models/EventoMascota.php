<?php

namespace App\Models;
use App\Core\Model;

class EventoMascota extends Model
{
    protected $table = 'EventoMascota';
    protected $pk = 'Id_Evento';

    // Crea un nuevo evento asociado a una mascota
    public function crear(array $datos): int{
        $sql = "INSERT INTO EventoMascota
                (Id_Mascota, Id_Usuario, Id_TipoEvento, Id_EstadoEvento, 
                 Descripcion, Id_Ubicacion, Recompensa)
                VALUES
                (:mascota, :usuario, :tipo, :estado, 
                 :descripcion, :ubicacion, :recompensa)";

        $this->execute($sql, [
            ':mascota'     => $datos['mascota'],
            ':usuario'     => $datos['usuario'],
            ':tipo'        => $datos['tipo'],
            ':estado'      => $datos['estado'],
            ':descripcion' => $datos['descripcion'],
            ':ubicacion'   => $datos['ubicacion'] ?? null,
            ':recompensa'  => $datos['recompensa'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    // Busca un evento por su ID
    public function buscarPorId(int $id){
        $sql = "SELECT 
                    Id_Evento,
                    Id_Mascota,
                    Id_Usuario,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Id_Ubicacion,
                    Fecha_Creacion,
                    Fecha_Resolucion,
                    Fecha_UltimaVista,
                    Recompensa
                FROM EventoMascota
                WHERE Id_Evento = :id
                AND Eliminado = 0";

        return $this->queryOne($sql, [':id' => $id]);
    }

    // Lista todos los eventos de una mascota
    public function listarPorMascota(int $idMascota){
        $sql = "SELECT 
                    Id_Evento,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Fecha_Creacion,
                    Recompensa
                FROM EventoMascota
                WHERE Id_Mascota = :id
                AND Eliminado = 0";

        return $this->query($sql, ['id' => $idMascota]);
    }

    // Lista eventos considerados "activos" (no resueltos)
    public function listarActivos(){
        $sql = "SELECT 
                    Id_Evento,
                    Id_Mascota,
                    Id_TipoEvento,
                    Id_EstadoEvento,
                    Descripcion,
                    Fecha_Creacion,
                    Recompensa
                FROM EventoMascota
                WHERE Id_EstadoEvento = 1
                AND Eliminado = 0";

        return $this->query($sql);
    }

    // Cambia el estado del evento y registra el cambio en el historial
    public function cambiarEstado(int $id, int $idEstado, int $idUsuario): bool
    {
        $sql = "UPDATE EventoMascota
                SET Id_EstadoEvento = :estado";


       //RESUELTO=2
       //CANCELADO=3    

        if($idEstado == 2 || $idEstado == 3){
          $sql .= ", Fecha_Resolucion = NOW()";
        }

        $sql .= " WHERE Id_Evento = :id
                  AND Eliminado = 0";

       $resultado = $this->execute($sql, [
            ':estado' => $idEstado,
            ':id'     => $id
        ])->rowCount() > 0;

         // Registrar en historial
        if($resultado) {
            $historial = new HistorialEstadoEvento();
            $historial->crear($id, $idEstado, $idUsuario);
        }
        
        return $resultado;
    }

    // Soft delete: marca el evento como eliminado
    public function eliminar(int $id): bool
    {
       
        return $this->softDelete($id);
    }
}
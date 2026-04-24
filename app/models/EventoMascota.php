<?php

namespace App\Models;
use Core\Model;

class EventoMascota extends Model
{
    // Crea un nuevo evento asociado a una mascota
    public function crear(array $datos): int{
        $sql = "INSERT INTO EventoMascota
                (Id_Mascota, Id_Usuario, Id_TipoEvento, Id_EstadoEvento, 
                 Descripcion, Id_Ubicacion, Recompensa)
                VALUES
                (:mascota, :usuario, :tipo, :estado, 
                 :descripcion, :ubicacion, :recompensa)";

        $this->execute($sql, $datos);

        return $this->lastInsertId();
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
                WHERE Id_Evento = :id";

        return $this->queryOne($sql, ['id' => $id]);
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
                WHERE Id_Mascota = :id";

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
                WHERE Id_EstadoEvento != 3"; // 3 = resuelto (según lógica asumida)

        return $this->query($sql);
    }

    // Cambia el estado del evento y registra el cambio en el historial
    public function cambiarEstado(int $id, int $idEstado, int $idUsuario): bool{
        $sql = "UPDATE EventoMascota
                SET Id_EstadoEvento = :estado,
                    Fecha_UltimaVista = NOW()
                WHERE Id_Evento = :id";

        $ok = $this->execute($sql, [
            'estado' => $idEstado,
            'id' => $id
        ]);

        // Si el cambio fue exitoso, se guarda en el historial
        if($ok){
            $historial = new HistorialEstadoEvento();
            $historial->crear($id, $idEstado, $idUsuario);
        }

        return $ok;
    }

    // Soft delete: marca el evento como eliminado
    public function eliminar(int $id): bool{
        $sql = "UPDATE EventoMascota
                SET Eliminado = 1
                WHERE Id_Evento = :id";

        return $this->execute($sql, ['id' => $id]);
    }
}
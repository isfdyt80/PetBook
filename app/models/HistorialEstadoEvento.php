<?php

namespace App\Models;
use App\Core\Model;

class HistorialEstadoEvento extends Model
{
    protected $table = 'HistorialEstadoEvento';
    protected $pk = 'Id_Historial';

    // Registra un cambio de estado de un evento
    public function crear(int $idEvento, int $idEstado, int $idUsuario): bool{
        $sql = "INSERT INTO HistorialEstadoEvento
                    (Id_Evento, Id_EstadoEvento, Id_Usuario, Fecha)
                VALUES
                    (:idEvento, :idEstado, :idUsuario, NOW())";

        return $this->execute($sql, [
            ':idEvento' => $idEvento,
            ':idEstado' => $idEstado,
            ':idUsuario' => $idUsuario
        ])->rowCount() > 0;
    }

    // Lista el historial de cambios de estado de un evento
    public function listarPorEvento(int $idEvento){
        $sql = "SELECT 
                    Id_Historial,
                    Id_Evento,
                    Id_EstadoEvento,
                    Id_Usuario,
                    Fecha
                FROM HistorialEstadoEvento
                WHERE Id_Evento = :idEvento
                ORDER BY Fecha DESC";

        return $this->query($sql, [':idEvento' => $idEvento]);
    }
}
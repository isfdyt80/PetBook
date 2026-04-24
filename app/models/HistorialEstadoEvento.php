<?php

namespace App\Models;
use Core\Model;

class HistorialEstadoEvento extends Model
{
    // Registra un cambio de estado de un evento
    public function crear(int $idEvento, int $idEstado, int $idUsuario): bool{
        $sql = "INSERT INTO HistorialEstadoEvento
                (Id_Evento, Id_EstadoEvento, Id_Usuario)
                VALUES (:evento, :estado, :usuario)";

        return $this->execute($sql, [
            'evento' => $idEvento,
            'estado' => $idEstado,
            'usuario' => $idUsuario
        ]);
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

        return $this->query($sql, ['idEvento' => $idEvento]);
    }
}
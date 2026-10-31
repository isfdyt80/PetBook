<?php

namespace App\Models;
use App\Core\Model;

class HistorialEstadoEvento extends Model
{
    protected string $table = 'HistorialEstadoEvento';
    protected string $pk = 'Id_Historial';

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
    public function listarPorEvento(int $idEvento): array
    {
        $sql = "SELECT 
                    h.Id_Historial,
                    h.Id_Evento,
                    h.Id_EstadoEvento,
                    h.Id_Usuario,
                    h.Fecha,
                    e.Nombre AS Estado,
                    p.Nombre AS Nombre_Usuario
                FROM HistorialEstadoEvento h
                JOIN EstadoEvento e ON e.Id_EstadoEvento = h.Id_EstadoEvento
                JOIN Usuario u ON u.Id_Usuario = h.Id_Usuario
                JOIN Persona p ON p.Id_Persona = u.Id_Persona
                WHERE h.Id_Evento = :idEvento
                ORDER BY h.Fecha ASC, h.Id_Historial ASC";

        return $this->query($sql, [':idEvento' => $idEvento]);
    }
}

<?php
class Reporte
{
    private $db;

    
    private $id_reporte;
    private $id_usuario;
    private $id_publicacion;
    private $id_comentario;
    private $id_estado_reporte;
    private $id_moderador;
    private $motivo;
    private $fecha;
    private $fecha_resolucion;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // crea un reporte
    public function crear($datos)
    {
        // Validacion: solo uno debe existir
        if (
            (empty($datos['Id_Publicacion']) && empty($datos['Id_Comentario'])) ||
            (!empty($datos['Id_Publicacion']) && !empty($datos['Id_Comentario']))
        ) {
            throw new Exception("Debe haber solo un Id_Publicacion o Id_Comentario");
        }

        $stmt = $this->db->prepare("
            INSERT INTO Reporte 
            (Id_Usuario, Id_Publicacion, Id_Comentario, Id_EstadoReporte, Motivo)
            VALUES (:usuario, :pub, :com, :estado, :motivo)
        ");

        return $stmt->execute([
            'usuario' => $datos['Id_Usuario'],
            'pub' => $datos['Id_Publicacion'] ?? null,
            'com' => $datos['Id_Comentario'] ?? null,
            'estado' => $datos['Id_EstadoReporte'],
            'motivo' => $datos['Motivo']
        ]);
    }

    // lista reportes pendientes
    public function listarPendientes()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM Reporte
            WHERE Id_EstadoReporte = 1
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // asigna moderador
    public function asignarModerador($id, $idModerador)
    {
        $stmt = $this->db->prepare("
            UPDATE Reporte
            SET Id_Moderador = :mod
            WHERE Id_Reporte = :id
        ");

        return $stmt->execute([
            'mod' => $idModerador,
            'id' => $id
        ]);
    }

    // resolver reporte
    public function resolver($id, $idEstado)
    {
        $stmt = $this->db->prepare("
            UPDATE Reporte
            SET Id_EstadoReporte = :estado,
                Fecha_Resolucion = NOW()
            WHERE Id_Reporte = :id
        ");

        return $stmt->execute([
            'estado' => $idEstado,
            'id' => $id
        ]);
    }
}
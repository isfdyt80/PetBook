<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Publicacion;
use DAL\PublicacionDAL;

header ('Content-Type: application/json');

ini_set('display_errors',1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(["error" => "Datos inválidos"]);
            exit;
        }

        $descripcion   = $data['descripcion'] ?? null;
        $estado        = $data['estado'] ?? null;
        $mascota_id    = $data['mascota_id'] ?? null;
        $usuario_id    = $data['usuario_id'] ?? null;
        $foto          = $data['foto'] ?? null;
        $recompensa    = $data['recompensa'] ?? null;
        $ubicacion     = $data['ubicacion'] ?? null;

        if (!$descripcion || !$estado || !$mascota_id || !$usuario_id) {
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            exit;
        }

        // Crear publicacion
        $publicacion = new Publicacion(
            $descripcion,
            $estado,
            $mascota_id,
            $usuario_id,
            null, // publicacion_id
            1,    // activo
            null, // fecha_creacion
            $foto,
            $recompensa,
            $ubicacion
        );

        if (PublicacionDAL::crear($publicacion)) {
            echo json_encode(["success" => true, "message" => "Publicación creada correctamente"]);
        } else {
            echo json_encode(["error" => "Error al crear publicación"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["error" => "Excepción: " . $e->getMessage()]);
}
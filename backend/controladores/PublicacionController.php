<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Publicacion;
use DAL\PublicacionDAL;
use DAL\MascotaDAL;

header('Content-Type: application/json');

// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Solo aceptar POST (crear publicación)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Leer datos (puede venir como JSON o form-data)
        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true) ?? $_POST;

        // Iniciar sesión para identificar usuario
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        if (!$usuario_id) {
            echo json_encode(["error" => "Usuario no autenticado"]);
            exit;
        }

        // Datos esperados desde el frontend
        $descripcion = $data['descripcion'] ?? null;
        $estado      = $data['estado'] ?? null;
        $mascota_id  = $data['mascota_id'] ?? null;
        $recompensa  = $data['recompensa'] ?? null;
        $ubicacion   = $data['ubicacion'] ?? null;

        // Validación mínima
        if (!$descripcion || !$estado || !$mascota_id) {
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            exit;
        }

        // Validar que la mascota exista y pertenezca al usuario
        $mascota = MascotaDAL::buscarPorId($mascota_id);
        if (!$mascota) {
            echo json_encode(["error" => "La mascota seleccionada no existe"]);
            exit;
        }
        if ($mascota->usuario_id != $usuario_id) {
            echo json_encode(["error" => "La mascota no pertenece al usuario autenticado"]);
            exit;
        }

        // Manejar foto si viene por formulario multipart/form-data
        $fotoRuta = null;
        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $nombreArchivo = 'pub_' . uniqid() . '_' . basename($_FILES['foto']['name']);
            $rutaDestino = $uploadDir . $nombreArchivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $fotoRuta = 'uploads/publicaciones/' . $nombreArchivo;
            } else {
                echo json_encode(["error" => "Error al subir la imagen"]);
                exit;
            }
        }

        // Crear objeto Publicacion
        $publicacion = new Publicacion(
            $descripcion,
            $estado,
            $mascota_id,
            $usuario_id,
            null, // publicacion_id (autoincrement)
            1,    // activo
            null, // fecha_creacion (default en BD)
            $fotoRuta,
            $recompensa,
            $ubicacion
        );

        // Insertar en BD
        if (PublicacionDAL::crear($publicacion)) {
            echo json_encode(["success" => true, "message" => "Publicación creada correctamente"]);
        } else {
            echo json_encode(["error" => "Error al crear la publicación"]);
        }
    } else {
        echo json_encode(["error" => "Método no permitido"]);
    }

} catch (Throwable $e) {
    echo json_encode(["error" => "Excepción: " . $e->getMessage()]);
}

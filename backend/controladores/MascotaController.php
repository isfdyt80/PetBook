<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Mascota;
use DAL\MascotaDAL;

header('Content-Type: application/json');

// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["error" => "Datos inválidos"]);
            exit;
        }
        // Recibir datos del formulario
        $nombre              = $data['nombre'] ?? null;
        $estado              = $data['estado'] ?? null;
        $fecha_nacimiento    = $data['fecha_nacimiento'] ?? null;
        $raza_id             = $data['raza_id'] ?? null;
        $usuario_id          = $data['usuario_id'] ?? null;

        // Validación
        if (!$nombre || !$estado || !$raza_id || !$usuario_id) {
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            exit;
        }

        // Manejo de la foto
        $fotoRuta = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $directorio = __DIR__ . "/../../uploads/mascotas/";
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            $nombreArchivo = uniqid("mascota_") . "_" . basename($_FILES["foto"]["name"]);
            $rutaDestino = $directorio . $nombreArchivo;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino)) {
                $fotoRuta = "uploads/mascotas/" . $nombreArchivo; // Ruta relativa para la BD
            } else {
                echo json_encode(["error" => "Error al guardar la foto"]);
                exit;
            }
        }

        // Crear objeto Mascota
        $mascota = new Mascota(
            $estado,
            $nombre,
            $fecha_nacimiento,
            $raza_id,
            $usuario_id,
            null,
            $fotoRuta ?? null,
            null,
            1
        );

        if (MascotaDAL::crear($mascota)) {
            echo json_encode(["success" => true, "message" => "Mascota registrada correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar la mascota"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["error" => "Excepción: " . $e->getMessage()]);
}

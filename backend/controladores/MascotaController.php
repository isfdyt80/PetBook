<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../DAL/RazaDAL.php';

use DAL\RazaDAL;
use Modelos\Mascota;
use DAL\MascotaDAL;

// Mostrar errores en desarrollo
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

try {
    $usuario_id = 2;

    // === GET ?action=razas
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'razas') {
        try {
            $razas = RazaDAL::listar();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($razas);
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Error al obtener razas', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === GET ?action=mis_mascotas
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'mis_mascotas') {
        if (!$usuario_id) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit;
        }
        try {
            $mascotas = MascotaDAL::buscarPorUsuario((int)$usuario_id);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($mascotas);
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Error al obtener mascotas', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === GET (compatibilidad previa) listar mascotas
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!$usuario_id) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["error" => "Usuario no autenticado"]);
            exit;
        }
        $mascotas = MascotaDAL::buscarPorUsuario($usuario_id);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($mascotas);
        exit;
    }

    // === POST: crear mascota (soporta JSON y multipart/form-data)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Detectar multipart (form-data con archivos)
        $isMultipart = !empty($_FILES) || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false);

        if ($isMultipart) {
            // datos vienen en $_POST y $_FILES
            $nombre = $_POST['nombre'] ?? null;
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
            $raza_id = $_POST['raza_id'] ?? null;
            $usuario_id = $_SESSION['usuario_id'] ?? $_POST['usuario_id'] ?? null;
        } else {
            // JSON puro en body
            $data = json_decode(file_get_contents("php://input"), true);
            if (!is_array($data)) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["error" => "Datos inválidos"]);
                exit;
            }
            $nombre = $data['nombre'] ?? null;
            $fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
            $raza_id = $data['raza_id'] ?? null;
            $usuario_id = $_SESSION['usuario_id'] ?? $data['usuario_id'] ?? null;
        }

        // Validación
        if (!$nombre || !$raza_id) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            exit;
        }

        // Manejo de la foto (solo si multipart y archivo presente)
        $fotoRuta = "assets/img/default_pet.jpg"; // Valor por defecto
        if ($isMultipart && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $directorio = __DIR__ . "../../uploads/mascotas/";
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            $nombreArchivo = uniqid("mascota_") . "_" . basename($_FILES["foto"]["name"]);
            $rutaDestino = $directorio . $nombreArchivo;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $rutaDestino)) {
                $fotoRuta = "uploads/mascotas/" . $nombreArchivo; // Ruta relativa para la BD
            } else {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["error" => "Error al guardar la foto"]);
                exit;
            }
        }

        // Crear objeto Mascota usando el nuevo constructor sin el parámetro estado
        $mascota = new Mascota(
            $nombre,
            $fecha_nacimiento,
            $raza_id,
            $usuario_id,
            null,              // mascota_id
            $fotoRuta,         // foto
            null,              // fecha_creacion
            1                  // activo
        );

        try {
            $newId = MascotaDAL::crear($mascota); // usar el alias importado arriba
            if ($newId !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Mascota registrada correctamente',
                    'mascota' => [
                        'id' => $newId,
                        'nombre' => $nombre,
                        'raza_id' => $raza_id,
                        'foto' => $fotoRuta
                    ]
                ]);
            } else {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Error al registrar la mascota']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Excepción al crear mascota', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Método no permitido
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["error" => "Método no permitido"]);
} catch (Throwable $e) {
    // log a archivo para debugging local
    /*file_put_contents(__DIR__ . '/debug_php_errors.log',
        "[".date('Y-m-d H:i:s')."] ".$e->getMessage()."\n".$e->getTraceAsString()."\n\n",
        FILE_APPEND
    );*/
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["error" => "Excepción: " . $e->getMessage()]);
}
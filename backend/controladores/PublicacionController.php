<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Publicacion;
use DAL\PublicacionDAL;
use DAL\MascotaDAL;

try {
    // DEV ONLY: no usar sesión; usar query param o default = 2
    // Para producción reemplazar por session-based auth
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 2;

    // Detectar método
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

    // GET: devolver mascotas del usuario (útil para pruebas y para llenar select)
    if ($method === 'GET') {
        header('Content-Type: application/json; charset=utf-8');

        if (!$usuario_id) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado (dev)']);
            exit;
        }

        $mascotas = MascotaDAL::buscarPorUsuario((int)$usuario_id);
        echo json_encode($mascotas);
        exit;
    }

    // POST: crear nueva publicación (soporta JSON y multipart/form-data)
    if ($method === 'POST') {
        // Detectar multipart/form-data
        $isMultipart = !empty($_FILES) || stripos($contentType, 'multipart/form-data') !== false;

        // Leer campos desde multipart ($_POST) o JSON (php://input)
        if ($isMultipart) {
            $descripcion = $_POST['descripcion'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $mascota_id = isset($_POST['mascota_id']) ? (int)$_POST['mascota_id'] : null;
            $recompensa = $_POST['recompensa'] ?? null;
            $ubicacion = $_POST['ubicacion'] ?? null;
            $useMascotaFoto = $_POST['use_mascota_foto'] ?? '1';
        } else {
            $raw = file_get_contents('php://input') ?: '';
            $data = json_decode($raw, true) ?? [];
            $descripcion = $data['descripcion'] ?? null;
            $estado = $data['estado'] ?? null;
            $mascota_id = isset($data['mascota_id']) ? (int)$data['mascota_id'] : null;
            $recompensa = $data['recompensa'] ?? null;
            $ubicacion = $data['ubicacion'] ?? null;
            $useMascotaFoto = $data['use_mascota_foto'] ?? '1';
        }

        // Validación mínima
        if (empty($descripcion) || empty($estado) || empty($mascota_id)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Faltan datos obligatorios (descripcion, estado, mascota_id)']);
            exit;
        }

        // Obtener mascota y validar pertenencia al usuario (en DEV asumimos usuario_id)
        $mascota = MascotaDAL::buscarPorId($mascota_id);
        if (!$mascota) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'La mascota seleccionada no existe']);
            exit;
        }

        // MascotaDAL::buscarPorId devuelve array asociativo en tu DAL modificado;
        // adaptá si devuelve objeto.
        $mascotaUsuarioId = isset($mascota['usuario_id']) ? (int)$mascota['usuario_id'] : (isset($mascota->usuario_id) ? (int)$mascota->usuario_id : null);
        if ($mascotaUsuarioId !== (int)$usuario_id) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'La mascota no pertenece al usuario (dev)']);
            exit;
        }

        // Normalizar recompensa (opcional)
        if ($recompensa !== null && $recompensa !== '') {
            $recompensa = is_numeric(str_replace([',',' '], ['','.'], $recompensa)) ? floatval(str_replace([',',' '], ['','.'], $recompensa)) : $recompensa;
        } else {
            $recompensa = null;
        }

        // Manejo de la imagen para la publicación
        $fotoRuta = null;

        // 1) Si llegó un archivo para la publicación, procesarlo
        if ($isMultipart && isset($_FILES['foto_publicacion']) && $_FILES['foto_publicacion']['error'] === UPLOAD_ERR_OK) {
            // Validaciones de seguridad: MIME y tamaño
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $tmpName = $_FILES['foto_publicacion']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Tipo de archivo no permitido. Solo JPEG, PNG, WEBP.']);
                exit;
            }

            $maxBytes = 5 * 1024 * 1024; // 5 MB
            if ($_FILES['foto_publicacion']['size'] > $maxBytes) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Archivo demasiado grande (máx 5MB).']);
                exit;
            }

            $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['foto_publicacion']['name'], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
            $nuevoNombre = 'pub_' . uniqid() . ($safeExt ? '.' . $safeExt : '');
            $destino = $uploadDir . $nuevoNombre;

            if (!move_uploaded_file($tmpName, $destino)) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Error al guardar la imagen de la publicación (move_uploaded_file).']);
                exit;
            }

            // ruta pública relativa para guardar en BD
            $fotoRuta = 'uploads/publicaciones/' . $nuevoNombre;
        } else {
            // 2) No llegó upload de publicación: si use_mascota_foto === '1' usar foto de mascota (si existe)
            $useMascotaFoto = (string)$useMascotaFoto;
            if ($useMascotaFoto === '1') {
                // Mascota puede devolver ['foto'] o objeto->foto
                $mascotaFoto = isset($mascota['foto']) ? $mascota['foto'] : (isset($mascota->foto) ? $mascota->foto : null);
                if (!empty($mascotaFoto)) {
                    // Usamos la ruta existente de la mascota (NO movemos/renombramos)
                    $fotoRuta = $mascotaFoto;
                } else {
                    $fotoRuta = null;
                }
            } else {
                $fotoRuta = null;
            }
        }

        // Crear objeto Publicacion (ajusta el orden/constructor a tu modelo)
        $publicacion = new Publicacion(
            $descripcion,     // descripcion
            $estado,          // estado
            $mascota_id,      // mascota_id
            $usuario_id,      // usuario_id
            null,             // publicacion_id (autoincrement)
            1,                // activo
            null,             // fecha_creacion (BD)
            $fotoRuta,        // foto (ruta relativa o NULL)
            $recompensa,      // recompensa (float|null o string)
            $ubicacion        // ubicacion
        );

        // Persistir en BD
        $ok = PublicacionDAL::crear($publicacion);

        if ($ok) {
            http_response_code(201);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Publicación creada correctamente']);
            exit;
        } else {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Error al crear la publicación (DAL).']);
            exit;
        }
    }

    // Método no permitido
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Método no permitido']);
    exit;

} catch (Throwable $e) {
    // log local para debug
    //file_put_contents(__DIR__ . '/debug_php_errors.log', "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
    exit;
}
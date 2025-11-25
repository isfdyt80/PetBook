<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Publicacion;
use DAL\PublicacionDAL;
use DAL\MascotaDAL;

try {
    // DEV ONLY: no usar sesión; usar query param o default = 2
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 2;

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

    // GET: devolver publicaciones
    if ($method === 'GET') {
        header('Content-Type: application/json; charset=utf-8');
        $publicaciones = PublicacionDAL::traerPublicaciones();
        echo json_encode($publicaciones);
        exit;
    }

    // POST acting as PUT: multipart edit with file upload
    if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
        header('Content-Type: application/json; charset=utf-8');

        $publicacion_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$publicacion_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de publicación no proporcionado']);
            exit;
        }

        $pub = PublicacionDAL::buscarPorId($publicacion_id);
        if (!$pub) {
            http_response_code(404);
            echo json_encode(['error' => 'Publicación no encontrada']);
            exit;
        }

        // validar pertenencia
        $pubUsuarioId = is_array($pub) ? ($pub['usuario_id'] ?? null) : ($pub->usuario_id ?? null);
        if ($pubUsuarioId !== (int)$usuario_id) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para modificar esta publicación']);
            exit;
        }

        $allowed = ['descripcion', 'estado', 'ubicacion', 'recompensa'];
        $fields = [];
        foreach ($allowed as $k) {
            if (isset($_POST[$k])) $fields[$k] = $_POST[$k];
        }

        // archivo foto
        if (isset($_FILES['foto_publicacion']) && $_FILES['foto_publicacion']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['foto_publicacion']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            $allowedMimes = ['image/jpeg','image/png','image/webp'];
            if (!in_array($mime, $allowedMimes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de archivo no permitido para foto.']);
                exit;
            }
            $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['foto_publicacion']['name'], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/','',$ext);
            $nuevo = 'pub_' . uniqid() . ($safeExt ? '.' . $safeExt : '');
            $dest = $uploadDir . $nuevo;
            if (!move_uploaded_file($tmp, $dest)) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar la imagen de la publicación']);
                exit;
            }
            $fields['foto'] = 'uploads/publicaciones/' . $nuevo;
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay campos para actualizar']);
            exit;
        }

        $ok = PublicacionDAL::modificar($publicacion_id, $fields);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Publicación actualizada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar la publicación (DAL)']);
        }
        exit;
    }

    // POST: crear nueva publicación
    if ($method === 'POST') {
        $isMultipart = !empty($_FILES) || stripos($contentType, 'multipart/form-data') !== false;

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

        if (empty($descripcion) || empty($estado) || empty($mascota_id)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Faltan datos obligatorios (descripcion, estado, mascota_id)']);
            exit;
        }

        $mascota = MascotaDAL::buscarPorId($mascota_id);
        if (!$mascota) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'La mascota seleccionada no existe']);
            exit;
        }
        $mascotaUsuarioId = is_array($mascota) ? ($mascota['usuario_id'] ?? null) : ($mascota->usuario_id ?? null);
        if ($mascotaUsuarioId !== (int)$usuario_id) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'La mascota no pertenece al usuario (dev)']);
            exit;
        }

        // normalizar recompensa
        if ($recompensa !== null && $recompensa !== '') {
            $recompensa = is_numeric(str_replace([',',' '], ['','.'], $recompensa)) ? floatval(str_replace([',',' '], ['','.'], $recompensa)) : $recompensa;
        } else {
            $recompensa = null;
        }

        // manejo imagen
        $fotoRuta = null;
        if ($isMultipart && isset($_FILES['foto_publicacion']) && $_FILES['foto_publicacion']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto_publicacion']['tmp_name'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowed)) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Tipo de archivo no permitido. Solo JPEG, PNG, WEBP.']);
                exit;
            }
            $maxBytes = 5 * 1024 * 1024;
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
            $fotoRuta = 'uploads/publicaciones/' . $nuevoNombre;
        } else {
            $useMascotaFoto = (string)$useMascotaFoto;
            if ($useMascotaFoto === '1') {
                $mascotaFoto = is_array($mascota) ? ($mascota['foto'] ?? null) : ($mascota->foto ?? null);
                if (!empty($mascotaFoto)) {
                    // Si la foto de la mascota ya viene con prefijo 'uploads/' o es una URL, conservarla.
                    if (strpos($mascotaFoto, 'uploads/') === 0 || preg_match('/^https?:\/\//', $mascotaFoto)) {
                        $fotoRuta = $mascotaFoto;
                    } else {
                        // Normalizar: la foto de mascota debe apuntar a uploads/mascotas/
                        $fotoRuta = 'uploads/mascotas/' . ltrim($mascotaFoto, '\\/');
                    }
                }
            }
        }

        $publicacion = new Publicacion(
            $descripcion,
            $estado,
            $mascota_id,
            $usuario_id,
            null,
            1,
            null,
            $fotoRuta,
            $recompensa,
            $ubicacion
        );

        $ok = PublicacionDAL::crear($publicacion);
        if ($ok) {
            http_response_code(201);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Publicación creada correctamente']);
            exit;
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Error al crear la publicación (DAL).']);
        exit;
    }

    // PUT: modificar publicación (JSON payload)
    if ($method === 'PUT') {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Payload inválido']);
            exit;
        }
        $publicacion_id = isset($data['id']) ? (int)$data['id'] : null;
        if (!$publicacion_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de publicación no proporcionado']);
            exit;
        }
        $pub = PublicacionDAL::buscarPorId($publicacion_id);
        if (!$pub) {
            http_response_code(404);
            echo json_encode(['error' => 'Publicación no encontrada']);
            exit;
        }
        $pubUsuarioId = is_array($pub) ? ($pub['usuario_id'] ?? null) : ($pub->usuario_id ?? null);
        if ($pubUsuarioId !== (int)$usuario_id) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para modificar esta publicación']);
            exit;
        }
        $allowed = ['descripcion', 'estado', 'ubicacion', 'recompensa', 'foto'];
        $fields = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) $fields[$k] = $data[$k];
        }
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay campos para actualizar']);
            exit;
        }
        $ok = PublicacionDAL::modificar($publicacion_id, $fields);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Publicación actualizada correctamente']);
            exit;
        }
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la publicación']);
        exit;
    }

    // DELETE: eliminar publicación (soft delete - set activo=0)
    if ($method === 'DELETE') {
        header('Content-Type: application/json; charset=utf-8');
        $publicacion_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$publicacion_id) {
            $raw = file_get_contents('php://input');
            $delete_params = [];
            parse_str($raw, $delete_params);
            if (isset($delete_params['id'])) $publicacion_id = (int)$delete_params['id'];
        }
        if (!$publicacion_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de publicación no proporcionado']);
            exit;
        }
        $pub = PublicacionDAL::buscarPorId($publicacion_id);
        if (!$pub) {
            http_response_code(404);
            echo json_encode(['error' => 'Publicación no encontrada']);
            exit;
        }
        $pubUsuarioId = is_array($pub) ? ($pub['usuario_id'] ?? null) : ($pub->usuario_id ?? null);
        if ($pubUsuarioId !== (int)$usuario_id) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permiso para eliminar esta publicación']);
            exit;
        }
        $ok = PublicacionDAL::eliminar($publicacion_id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Publicación eliminada correctamente']);
            exit;
        }
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar la publicación']);
        exit;
    }

    // Método no permitido
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Método no permitido']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
    exit;
}
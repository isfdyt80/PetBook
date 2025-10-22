<?php
require_once __DIR__ . '/../../bootstrap.php';

use Modelos\Usuario;
use DAL\UsuarioDAL;

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

        $nombre    = $data['nombre'] ?? null;
        $apellido  = $data['apellido'] ?? null;
        $email     = $data['email'] ?? null;
        $clave     = $data['clave'] ?? null;
        $domicilio = $data['domicilio'] ?? null;

        if (!$nombre || !$apellido || !$email || !$clave) {
            echo json_encode(["error" => "Faltan datos obligatorios"]);
            exit;
        }

        // ¿Ya existe el usuario?
        if (UsuarioDAL::buscarPorEmail($email)) {
            echo json_encode(["error" => "El email ya está registrado"]);
            exit;
        }

        // Crear usuario
        $usuario = new Usuario(
            2, // rol_id por defecto = usuario
            $nombre,
            $apellido,
            $email,
            password_hash($clave, PASSWORD_BCRYPT),
            $domicilio
        );

        if (UsuarioDAL::crear($usuario)) {
            echo json_encode(["success" => true, "message" => "Usuario registrado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar usuario"]);
        }
    }
} catch (Throwable $e) {
    echo json_encode(["error" => "Excepción: " . $e->getMessage()]);
}

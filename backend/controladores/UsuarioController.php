<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/petbook/PetBook/bootstrap.php';

use Modelos\Usuario;
use DAL\UsuarioDAL;

header('Content-Type: application/json');

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

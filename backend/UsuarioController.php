<?php
// backend/UsuarioController.php
require_once __DIR__ . '/../bootstrap.php';

use Database\Conexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre']    ?? null;
    $apellido  = $_POST['apellido']  ?? null;
    $email     = $_POST['email']     ?? null;
    $clave     = $_POST['clave']     ?? null;
    $domicilio = $_POST['domicilio'] ?? null;

    if (!$nombre || !$apellido || !$email || !$clave) {
        echo "Todos los campos obligatorios deben completarse.";
        exit;
    }

    try {
        $pdo = Conexion::getConexion();

        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT usuario_id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "El correo ya está registrado.";
            exit;
        }

        // Insertar usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, apellido, email, clave, domicilio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $rol_id = 2,
            $nombre,
            $apellido,
            $email,
            password_hash($clave, PASSWORD_BCRYPT), // clave encriptada
            $domicilio
        ]);

        echo "Registro exitoso 🎉";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

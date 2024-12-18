<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../php/send_mail.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

include 'config_dev_mariani.php';

$response = [];
$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_error) {
    $response = ['status' => 'error', 'message' => 'Conexión fallida: ' . $conexion->connect_error];
    echo json_encode($response);
    exit;
}

// Obtener la clave secreta desde la base de datos
$stmt_secret = $conexion->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
$nombre_variable = 'secret_key';
$stmt_secret->bind_param("s", $nombre_variable);
$stmt_secret->execute();
$resultado_secret = $stmt_secret->get_result();

if ($fila_secret = $resultado_secret->fetch_assoc()) {
    $secret_key = $fila_secret['value'];
} else {
    $response = ['status' => 'error', 'message' => 'No se encontró la clave secreta.'];
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['i_signin_name'] ?? null;
    $apellido = $_POST['i_signin_lastname'] ?? null;
    $telefono = $_POST['i_signin_telephone'] ?? null;
    $localidad = $_POST['i_signin_place'] ?? null;
    $email = $_POST['i_signin_mail'] ?? null;
    $password = $_POST['i_signin_password'] ?? null;

    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($localidad) || empty($email) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'Todos los datos son obligatorios.'];
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'El correo electrónico no es válido.'];
        echo json_encode($response);
        exit;
    }

    if (!ctype_digit($telefono)) {
        $response = ['status' => 'error', 'message' => 'El teléfono debe contener solo números.'];
        echo json_encode($response);
        exit;
    }

    $email_exists_query = "SELECT * FROM usuarios WHERE email = ?";
    $email_statement = $conexion->prepare($email_exists_query);
    $email_statement->bind_param("s", $email);
    $email_statement->execute();
    $result = $email_statement->get_result();

    if ($result->num_rows > 0) {
        $response = ['status' => 'error', 'message' => 'El correo ya fue registrado.'];
        echo json_encode($response);
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    $token_payload = [
        'user_mail' => $email,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60)
    ];
    $tokenverificacion = JWT::encode($token_payload, $secret_key, 'HS256');
    $verification_url = "http://petbooklocal/php/form_signin.php?token=" . urlencode($tokenverificacion);

    try {
        $resultadoEnvio = enviarCorreoVerificacion($email, $verification_url);
        if ($resultadoEnvio['status'] === 'envio exitoso') {
            $insert_query = "INSERT INTO usuarios (nombre, apellido, telefono, ubicacion, email, contraseña, verificado, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
            $insert_statement = $conexion->prepare($insert_query);
            $insert_statement->bind_param("ssssss", $nombre, $apellido, $telefono, $localidad, $email, $password_hashed);

            if ($insert_statement->execute()) {
                $response = ['status' => 'success', 'message' => 'Usuario creado exitosamente. Por favor, verifica tu correo.', 'redirect' => 'http://petbooklocal/log_in.html'];
            } else {
                $response = ['status' => 'error', 'message' => 'Hubo un error al crear el usuario.'];
            }
        } else {
            $response = ['status' => 'error', 'message' => $resultadoEnvio['message']];
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()];
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $decoded_token = JWT::decode($token, new Key($secret_key, 'HS256'));
        $email = $decoded_token->user_mail;

        $update_query = "UPDATE usuarios SET verificado = 1 WHERE email = ? AND verificado = 0";
        $update_statement = $conexion->prepare($update_query);
        $update_statement->bind_param("s", $email);

        if ($update_statement->execute() && $update_statement->affected_rows > 0) {
            $new_token_payload = [
                'user_mail' => $email,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            ];
            $new_token = JWT::encode($new_token_payload, $secret_key, 'HS256');
            setcookie('jwt', $new_token, time() + (7 * 24 * 60 * 60), '/', $_SERVER['HTTP_HOST'], false, true);

            header("Location: http://petbooklocal");
            exit;
        } else {
            $response = ['status' => 'error', 'message' => 'El usuario ya está verificado o no existe.'];
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Token inválido o expirado: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Método no permitido.'];
}

$conexion->close();
echo json_encode($response);
exit;

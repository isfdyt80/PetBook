<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

include 'config_dev_mariani.php';

// Conexión a la base de datos
$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_error) {
    $response = ['status' => 'error', 'message' => 'Conexión fallida: ' . $conexion->connect_error];
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['i_login_mail'] ?? null;
    $password = $_POST['i_login_password'] ?? null;

    if (empty($mail) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'Todos los campos son obligatorios.'];
        echo json_encode($response);
        exit;
    }

    // Verificar si el email existe
    $email_notexists_query = "SELECT * FROM usuarios WHERE email = ?";
    $email_statement = $conexion->prepare($email_notexists_query);
    $email_statement->bind_param("s", $mail);
    $email_statement->execute();
    $result = $email_statement->get_result();

    if ($result->num_rows === 0) {
        $response = ['status' => 'not_found', 'message' => 'El correo ingresado no fue registrado.'];
        echo json_encode($response);
        exit;
    }
    $user = $result->fetch_assoc();

    if (!$user['verificado']) {
        $response = ['status' => 'not_verified', 'message' => 'El correo no fue verificado.'];
        echo json_encode($response);
        exit;
    }

    $password_hashed = $user['contraseña'];

    // Obtener la clave secreta
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

    // Obtener el tiempo de vida del token
    $stmt_expiration = $conexion->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
    $nombre_variable_expiration = 'JWT_COOCKIE_DIE';
    $stmt_expiration->bind_param("s", $nombre_variable_expiration);
    $stmt_expiration->execute();
    $resultado_expiration = $stmt_expiration->get_result();

    if ($fila_expiration = $resultado_expiration->fetch_assoc()) {
        $cookie_expiration_days = (int)$fila_expiration['value'];
    } else {
        $response = ['status' => 'error', 'message' => 'No se encontró el tiempo de vida del token.'];
        echo json_encode($response);
        exit;
    }

    // Verificar contraseña y generar token
    if (password_verify($password, $password_hashed)) {
        $token = JWT::encode(
            ['user_mail' => $mail, 'iat' => time(), 'exp' => time() + (7 * 24 * 60 * 60)],
            $secret_key,
            'HS256'
        );

        $expiration = time() + ($cookie_expiration_days * 24 * 60 * 60);
        setcookie('jwt', $token, $expiration, '/', $_SERVER['HTTP_HOST'], false, false);

        $response = ['status' => 'success', 'message' => 'Contraseña verificada correctamente.', 'redirect' => 'http://petbooklocal/index.php'];
    } else {
        $response = ['status' => 'error', 'message' => 'Error al iniciar sesión.'];
    }

    echo json_encode($response);
    $conexion->close();
    exit;
}

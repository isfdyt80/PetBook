<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// include 'config_dev_mariani.php';
include_once '../conexion.php';


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
    $email_statement = $pdo->prepare($email_notexists_query);
    $email_statement->execute([$mail]);
    $result = $email_statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) === 0) {
        $response = ['status' => 'not_found', 'message' => 'El correo ingresado no fue registrado.'];
        echo json_encode($response);
        exit;
    }

    $user = $result[0]; // Accede al primer registro del resultado


    if (!$user['verificado']) {
        $response = ['status' => 'not_verified', 'message' => 'El correo no fue verificado.'];
        echo json_encode($response);
        exit;
    }

    $password_hashed = $user['contraseña'];

    // Obtener la clave secreta
    $stmt_secret = $pdo->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
    $nombre_variable = 'secret_key';
    $stmt_secret->execute([$nombre_variable]);
    $fila_secret = $stmt_secret->fetch(PDO::FETCH_ASSOC);
    
    if ($fila_secret) {
        $secret_key = $fila_secret['value'];
    } else {
        $response = ['status' => 'error', 'message' => 'No se encontró la clave secreta.'];
        echo json_encode($response);
        exit;
    }


    // Obtener el tiempo de vida del token
    $stmt_expiration = $pdo->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
    $nombre_variable_expiration = 'JWT_COOCKIE_DIE';
    $stmt_expiration->execute([$nombre_variable_expiration]);
    $fila_expiration = $stmt_expiration->fetch(PDO::FETCH_ASSOC);

    if ($fila_expiration) {
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
    exit;
}

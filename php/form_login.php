<?php

header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta como JSON
require_once __DIR__ . '/../vendor/autoload.php'; // Carga el autoloader de Composer

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

// Carga las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

include_once '../conexion.php'; // Incluye el archivo de conexión a la base de datos

// Verifica si la solicitud es de tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST['i_login_mail'] ?? null; // Obtiene el correo del formulario
    $password = $_POST['i_login_password'] ?? null; // Obtiene la contraseña del formulario

    // Verifica si el correo o la contraseña están vacíos
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

    // Si el correo no está registrado
    if (count($result) === 0) {
        $response = ['status' => 'not_found', 'message' => 'El correo ingresado no fue registrado.'];
        echo json_encode($response);
        exit;
    }

    $user = $result[0]; // Accede al primer registro del resultado

    // Verifica si el usuario ha verificado su correo
    if (!$user['verificado']) {
        $response = ['status' => 'not_verified', 'message' => 'El correo no fue verificado.'];
        echo json_encode($response);
        exit;
    }

    $password_hashed = $user['contraseña']; // Obtiene la contraseña hasheada del usuario

    // Obtener la clave secreta
    $stmt_secret = $pdo->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
    $nombre_variable = 'secret_key';
    $stmt_secret->execute([$nombre_variable]);
    $fila_secret = $stmt_secret->fetch(PDO::FETCH_ASSOC);
    
    // Verifica si se encontró la clave secreta
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

    // Verifica si se encontró el tiempo de vida del token
    if ($fila_expiration) {
        $cookie_expiration_days = (int)$fila_expiration['value'];
    } else {
        $response = ['status' => 'error', 'message' => 'No se encontró el tiempo de vida del token.'];
        echo json_encode($response);
        exit;
    }

    // Verificar contraseña y generar token
    if (password_verify($password, $password_hashed)) {
        // Genera el token JWT
        $token = JWT::encode(
            ['user_mail' => $mail, 'iat' => time(), 'exp' => time() + (7 * 24 * 60 * 60)],
            $secret_key,
            'HS256'
        );

        // Establece la expiración de la cookie
        $expiration = time() + ($cookie_expiration_days * 24 * 60 * 60);
        setcookie('jwt', $token, $expiration, '/', $_SERVER['HTTP_HOST'], false, false);

        $response = ['status' => 'success', 'message' => 'Contraseña verificada correctamente.', 'redirect' => 'http://petbooklocal/index.php'];
    } else {
        $response = ['status' => 'error', 'message' => 'Error al iniciar sesión.'];
    }

    echo json_encode($response); // Envía la respuesta en formato JSON
    exit;
}

<?php
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta como JSON

require_once __DIR__ . '/../vendor/autoload.php'; // Carga el autoloader de Composer
require_once __DIR__ . '/../php/send_mail.php'; // Incluye el archivo para enviar correos

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Carga las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

include_once '../conexion.php'; // Incluye el archivo de conexión a la base de datos

$response = []; // Array para almacenar la respuesta

// Obtener la clave secreta desde la base de datos
$stmt_secret = $pdo->prepare("SELECT value FROM configuración WHERE nombre_variable = ?");
$nombre_variable = 'secret_key';
$stmt_secret->execute([$nombre_variable]);
$fila_secret = $stmt_secret->fetch(PDO::FETCH_ASSOC);

if ($fila_secret) {
    $secret_key = $fila_secret['value']; // Asigna la clave secreta
} else {
    $response = ['status' => 'error', 'message' => 'No se encontró la clave secreta.'];
    echo json_encode($response);
    exit;
}

// Verifica si la solicitud es de tipo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['i_signin_name'] ?? null; // Obtiene el nombre del formulario
    $apellido = $_POST['i_signin_lastname'] ?? null; // Obtiene el apellido del formulario
    $telefono = $_POST['i_signin_telephone'] ?? null; // Obtiene el teléfono del formulario
    $localidad = $_POST['i_signin_place'] ?? null; // Obtiene la localidad del formulario
    $email = $_POST['i_signin_mail'] ?? null; // Obtiene el correo del formulario
    $password = $_POST['i_signin_password'] ?? null; // Obtiene la contraseña del formulario

    // Verifica si algún campo está vacío
    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($localidad) || empty($email) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'Todos los datos son obligatorios.'];
        echo json_encode($response);
        exit;
    }

    // Verifica si el correo es válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'El correo electrónico no es válido.'];
        echo json_encode($response);
        exit;
    }

    // Verifica si el teléfono contiene solo números
    if (!ctype_digit($telefono)) {
        $response = ['status' => 'error', 'message' => 'El teléfono debe contener solo números.'];
        echo json_encode($response);
        exit;
    }

    // Verifica si el correo ya está registrado
    $email_exists_query = "SELECT * FROM usuarios WHERE email = ?";
    $email_statement = $pdo->prepare($email_exists_query);
    $email_statement->execute([$email]);
    $result = $email_statement->fetch(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        $response = ['status' => 'error', 'message' => 'El correo ya fue registrado.'];
        echo json_encode($response);
        exit;
    }

    // Hashea la contraseña
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Genera el token de verificación
    $token_payload = [
        'user_mail' => $email,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60)
    ];
    $tokenverificacion = JWT::encode($token_payload, $secret_key, 'HS256');
    $verification_url = "http://" . $_SERVER['HTTP_HOST'] ."/php/form_signin.php?token=" . urlencode($tokenverificacion);

    try {
        // Envía el correo de verificación
        $resultadoEnvio = enviarCorreoVerificacion($email, $verification_url);
        if ($resultadoEnvio['status'] === 'envio exitoso') {
            // Inserta el nuevo usuario en la base de datos
            $insert_query = "INSERT INTO usuarios (nombre, apellido, telefono, ubicacion, email, contraseña, verificado, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
            $insert_statement = $pdo->prepare($insert_query);
            $insert_statement->execute([$nombre, $apellido, $telefono, $localidad, $email, $password_hashed]);

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
        // Decodifica el token
        $decoded_token = JWT::decode($token, new Key($secret_key, 'HS256'));
        $email = $decoded_token->user_mail;

        // Actualiza el estado de verificación del usuario
        $update_query = "UPDATE usuarios SET verificado = 1 WHERE email = ? AND verificado = 0";
        $update_statement = $pdo->prepare($update_query);
        $update_statement->execute([$email]);

        if ($update_statement->execute() && $update_statement->rowCount() > 0) {
            // Genera un nuevo token JWT
            $new_token_payload = [
                'user_mail' => $email,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            ];
            $new_token = JWT::encode($new_token_payload, $secret_key, 'HS256');
            setcookie('jwt', $new_token, time() + (7 * 24 * 60 * 60), '/', $_SERVER['HTTP_HOST'], false, true);

            header("Location: http://" . $_SERVER['HTTP_HOST']);
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

echo json_encode($response); // Envía la respuesta en formato JSON
exit;

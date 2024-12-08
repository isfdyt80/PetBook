<?php


header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


$secreto_key = $_ENV['secret_key'];
$cookie_expitation_key = $_ENV['JWT_COOCKIE_DIE'];

include 'config_dev_mariani.php';

$conexión = new mysqli($servername, $username, $password, $dbname);

if ($conexión->connect_error) {
    $response = ['status' => 'error', 'message' => 'Conexión fallida: ' . $conexión->connect_error];
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

    $email_notexists_query = "SELECT * FROM usuarios WHERE email = ?";
    $email_statement = $conexión->prepare($email_notexists_query);
    $email_statement->bind_param("s", $mail);
    $email_statement->execute();
    $result = $email_statement->get_result();

    if ($result->num_rows === 0) {
        $response = ['status' => 'not_found', 'message' => 'El correo ingresado no fue registrado.'];
        echo json_encode($response);
        exit;
    }

    $row = $result->fetch_assoc();
    $password_hashed = $row['contraseña'];

    if (password_verify($password, $password_hashed)) {
       
        $token = JWT::encode(['user_mail' => $mail, 'iat' => time(), 'exp' => time() + (7 * 24 * 60 * 60)], $secreto_key, 'HS256');
        $expiration = time() + ($cookie_expitation_key * 24 * 60 * 60);
        setcookie('jwt', $token, $expiration, '/', 'petbooklocal', false, false);

        $response = ['status' => 'success', 'message' => 'Contraseña verificada correctamente.', 'redirect' => 'http://petbooklocal/index.php'];
    } else {
        $response = ['status' => 'error', 'message' => 'Error al iniciar sesión.'];
    }

    $conexión->close();
    echo json_encode($response);
    exit;
}

<?php
header('Content-Type: application/json');

include 'config_dev_mariani.php';

$conexión = new mysqli($servername, $username, $password, $dbname);

if ($conexión->connect_error) {
    $response = ['status' => 'error', 'message' => 'Conexión fallida: ' . $conexión->connect_error];
    echo json_encode($response);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $email_exists_query = "SELECT * FROM usuarios WHERE email = ?";
    $email_statement = $conexión->prepare($email_exists_query);
    $email_statement->bind_param("s", $email);
    $email_statement->execute();
    $result = $email_statement->get_result();


    if ($result->num_rows > 0) {
        $response = ['status' => 'error', 'message' => 'El correo ya fue registrado.'];
        echo json_encode($response);
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $insert_query = "INSERT INTO usuarios (nombre, apellido, telefono, ubicacion, email, contraseña, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";


    if ($insert_statement = $conexión->prepare($insert_query)) {
        $insert_statement->bind_param("ssssss", $nombre, $apellido, $telefono, $localidad, $email, $password_hashed);

        if ($insert_statement->execute()) {
            $response = ['status' => 'success', 'message' => 'Usuario creado exitosamente.', 'redirect' => 'http://petbooklocal/log_in.html'];
        } else {
            $response = ['status' => 'error', 'message' => 'Hubo un error al crear el usuario. Inténtelo nuevamente.'];
        }
        $insert_statement->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conexión->error];
        exit;
    }


    $conexión->close();
    echo json_encode($response);
    exit;
}
?>
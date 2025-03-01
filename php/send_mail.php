<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Carga el autoloader de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Carga las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');  
$dotenv->load();

function enviarCorreoVerificacion($destinatario, $url)
{
    $mail = new PHPMailer(true); // Crea una nueva instancia de PHPMailer

    try {
        $mail->SMTPDebug = 0; // Desactiva la depuración SMTP
        $mail->isSMTP(); // Configura el correo para usar SMTP
        $mail->Host       = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth   = true; // Habilita la autenticación SMTP
        $mail->Username   = $_ENV['SMTP_USER']; // Usuario SMTP desde las variables de entorno
        $mail->Password   = $_ENV['SMTP_PASS']; // Contraseña SMTP desde las variables de entorno
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilita el cifrado TLS
        $mail->Port       = 587; // Puerto SMTP para TLS

        // Configura el remitente del correo
        $mail->setFrom($_ENV['SMTP_USER'], 'PetBook');
        $mail->addAddress($destinatario); // Añade el destinatario

        // Configura el contenido del correo
        $mail->isHTML(true); // Establece el formato del correo como HTML
        $mail->Subject = 'Verificacion de Registro'; // Asunto del correo
        $mail->Body = 
        '<html>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <body>
                <p>Hola,</p>
                <p>Gracias por registrarte en PetBook. Para activar tu cuenta, por favor haz clic en el siguiente enlace:</p>
                <p><a href="' . $url . '">Activar cuenta</a></p>
                <p>Si no fuiste tu quien solicito esta cuenta, por favor ignora este mensaje.</p>
                <p>Saludos,</p>
                <p>El equipo de PetBook</p>
            </body>
        </html>';

        // Envía el correo
        $mail->send();
        return [
            'status' => 'envio exitoso',
            'message' => 'Correo enviado exitosamente.',
        ];
    } catch (Exception $e) {
        // Log de errores
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return [
            'status' => 'envio fallido',
            'message' => "Error al enviar correo: {$mail->ErrorInfo}",
        ];
    }
}
?>

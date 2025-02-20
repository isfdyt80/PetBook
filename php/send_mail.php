<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');  
$dotenv->load();

function enviarCorreoVerificacion($destinatario, $url)
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;  
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; 
        $mail->Password   = $_ENV['SMTP_PASS']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        
        $mail->setFrom($_ENV['SMTP_USER'], 'PetBook');
        $mail->addAddress($destinatario);

        
        $mail->isHTML(true);
        $mail->Subject = 'Verificacion de Registro';
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

        
        $mail->send();
        return [
            'status' => 'envio exitoso',
            'message' => 'Correo enviado exitosamente.jejejeje',
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

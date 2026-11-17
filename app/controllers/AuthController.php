<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Usuario;

class AuthController extends Controller
{
    // ── GET /login ────────────────────────────────────────────────────────

    public function index(): void
    {
        Auth::requireGuest();
        $this->view('auth.login');
    }

    // ── POST /login ───────────────────────────────────────────────────────

    public function login(): void
    {
        // El CSRF de este POST ya fue validado por el Router.

        $email    = $this->input('email');
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Completá todos los campos.');
            $this->redirect('login');
        }

        // Límite de intentos por cuenta y por origen antes de tocar la base.
        if ($espera = RateLimiter::verificar('login', $email)) {
            Session::flash('error', 'Demasiados intentos fallidos. Volvé a intentarlo en ' . RateLimiter::formatearEspera($espera) . '.');
            $this->redirect('login');
        }

        $usuario = (new Usuario())->buscarPorEmail($email);

        // Mensaje único para todo fallo: no se revela si el email no existe,
        // si la contraseña es incorrecta o si la cuenta está desactivada.
        // verifyDummy() iguala el tiempo de respuesta entre rutas para no
        // filtrar la existencia de la cuenta por el tiempo de procesamiento.
        if (!$usuario) {
            Auth::verifyDummy($password);
            $this->falloLogin($email);
        }

        if ((int) $usuario['Eliminado'] === 1 || (int) $usuario['Activo'] === 0) {
            Auth::verifyDummy($password);
            $this->falloLogin($email);
        }

        if (!Auth::verifyPassword($password, $usuario['Password_Hash'])) {
            $this->falloLogin($email);
        }

        // Rehash oportunista: solo al iniciar sesión (cuando la contraseña
        // en claro está disponible y nunca se persiste ni se loguea).
        if (password_needs_rehash($usuario['Password_Hash'], PASSWORD_DEFAULT, ['cost' => 12])) {
            (new Usuario())->actualizarPasswordHash((int) $usuario['Id_Usuario'], Auth::hashPassword($password));
        }

        RateLimiter::resetear('login', $email);
        Session::login($usuario);
        Auth::redirectAfterLogin();
    }

    /**
     * Camino único de fallo de login: registra el intento para el límite
     * por cuenta y origen, y responde con un solo mensaje para el usuario,
     * sin detalles sobre el motivo (email inexistente, contraseña errónea
     * o cuenta desactivada/eliminada).
     */
    private function falloLogin(string $email): void
    {
        RateLimiter::registrar('login', $email);
        Session::flash('error', 'Credenciales incorrectas.');
        $this->redirect('login');
    }

    // ── GET /registro ─────────────────────────────────────────────────────

    public function showRegistro(): void
    {
        Auth::requireGuest();
        $this->view('auth.registro');
    }

    // ── POST /registro ────────────────────────────────────────────────────

    public function registro(): void
    {
        // El CSRF de este POST ya fue validado por el Router.

        $nombre   = $this->input('nombre');
        $apellido = $this->input('apellido');
        $email    = $this->input('email');
        $password = $this->input('password');

        // Obligatoriedad y longitudes del esquema: Persona.Nombre/Apellido
        // son VARCHAR(100) y Usuario.Email VARCHAR(191). Sin estos límites
        // el INSERT falla en la base con un mensaje genérico.
        try {
            $nombre   = Validator::texto($nombre, 'El nombre', 100, true);
            $apellido = Validator::texto($apellido, 'El apellido', 100, true);
            $email    = Validator::texto($email, 'El email', 191, true);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('registro');
        }

        // Validación de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'El email ingresado no es válido.');
            $this->redirect('registro');
        }

        // Límite de la contraseña: 8 mínimo, 72 máximo. Bcrypt solo
        // considera los primeros 72 bytes: un valor mayor sería truncado
        // silenciosamente al hashear.
        if (strlen($password) < 8 || strlen($password) > 72) {
            Session::flash('error', 'La contraseña debe tener entre 8 y 72 caracteres.');
            $this->redirect('registro');
        }

        // Límite de solicitudes de registro por origen (IP), después de las
        // validaciones de formato: solo cuentan los envíos bien formados.
        if ($espera = RateLimiter::verificar('registro')) {
            Session::flash('error', 'Demasiadas solicitudes de registro. Volvé a intentarlo en ' . RateLimiter::formatearEspera($espera) . '.');
            $this->redirect('registro');
        }

        $modelo = new Usuario();

        RateLimiter::registrar('registro');

        // Validación de email duplicado
        if ($modelo->buscarPorEmail($email)) {
            Session::flash('error', 'El email ya está registrado.');
            $this->redirect('registro');
        }

        try {
            $modelo->registrar([
                'Nombre'        => $nombre,
                'Apellido'      => $apellido,
                'Email'         => $email,
                'Password_Hash' => Auth::hashPassword($password),
            ]);
        } catch (\RuntimeException $e) {
            // Duplicado confirmado por la UNIQUE uq_usuario_email (INSERT
            // simultáneo que ganó la carrera después del pre-check).
            if ($e->getCode() === 23000) {
                Session::flash('error', 'El email ya está registrado.');
                $this->redirect('registro');
            }

            // Log sin datos sensibles: la contraseña en claro nunca forma
            // parte del mensaje (los mensajes de PDO no incluyen valores
            // de parámetros enlazados).
            error_log('[REGISTRO ERROR] ' . $e->getMessage());
            Session::flash('error', 'Ocurrió un error al registrarte. Intentá de nuevo.');
            $this->redirect('registro');
        }

        Session::flash('success', 'Cuenta creada correctamente. Ya podés iniciar sesión.');
        $this->redirect('login');
    }

    // ── POST /logout ──────────────────────────────────────────────────────

    /**
     * Cierra la sesión del usuario.
     * Solo accesible por POST con token CSRF validado por el Router;
     * una petición GET a /logout no matchea ninguna ruta (404).
     */
    public function logout(): void
    {
        Session::logout();
        $this->redirect('login');
    }
}
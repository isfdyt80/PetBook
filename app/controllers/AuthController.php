<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
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
        Session::validateCsrf();

        $email    = $this->input('email');
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Completá todos los campos.');
            $this->redirect('login');
        }

        $usuario = (new Usuario())->buscarPorEmail($email);

        // Mensaje único para todo fallo: no se revela si el email no existe,
        // si la contraseña es incorrecta o si la cuenta está desactivada.
        // verifyDummy() iguala el tiempo de respuesta entre rutas para no
        // filtrar la existencia de la cuenta por el tiempo de procesamiento.
        if (!$usuario) {
            Auth::verifyDummy($password);
            $this->falloLogin();
        }

        if ((int) $usuario['Eliminado'] === 1 || (int) $usuario['Activo'] === 0) {
            Auth::verifyDummy($password);
            $this->falloLogin();
        }

        if (!Auth::verifyPassword($password, $usuario['Password_Hash'])) {
            $this->falloLogin();
        }

        Session::login($usuario);
        Auth::redirectAfterLogin();
    }

    /**
     * Camino único de fallo de login: un solo mensaje para el usuario,
     * sin detalles sobre el motivo (email inexistente, contraseña errónea
     * o cuenta desactivada/eliminada).
     */
    private function falloLogin(): void
    {
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
        Session::validateCsrf();

        $nombre   = $this->input('nombre');
        $apellido = $this->input('apellido');
        $email    = $this->input('email');
        $password = $this->input('password');

        // Validación de campos obligatorios
        if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
            Session::flash('error', 'Completá todos los campos.');
            $this->redirect('registro');
        }

        // Validación de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'El email ingresado no es válido.');
            $this->redirect('registro');
        }

        // Validación de longitud mínima de contraseña
        if (strlen($password) < 8) {
            Session::flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('registro');
        }

        $modelo = new Usuario();

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
            error_log('[REGISTRO ERROR] ' . $e->getMessage());
            Session::flash('error', 'Ocurrió un error al registrarte. Intentá de nuevo.');
            $this->redirect('registro');
        }

        Session::flash('success', 'Cuenta creada correctamente. Ya podés iniciar sesión.');
        $this->redirect('login');
    }

    // ── GET /logout ───────────────────────────────────────────────────────

    public function logout(): void
    {
        Session::logout();
        $this->redirect('login');
    }
}
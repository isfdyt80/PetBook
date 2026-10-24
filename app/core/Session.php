<?php

// app/core/Session.php
// Helpers para manejo de sesión, mensajes flash y protección CSRF.

namespace App\Core;

class Session
{
    // ── Autenticación ─────────────────────────────────────────────────────

    /**
     * Inicia la sesión del usuario autenticado.
     * Regenera el ID de sesión para prevenir session fixation.
     *
     * Uso en AuthController luego de validar credenciales:
     *   Session::login($usuario);
     *
     * La estructura esperada de $user proviene de Usuario::buscarPorEmail():
     *   Id_Usuario, Id_Persona, Nombre, Email, rol
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'         => $user['Id_Usuario'],
            'id_persona' => $user['Id_Persona'],
            'nombre'     => $user['Nombre'],
            'email'      => $user['Email'],
            'rol'        => $user['rol'],
        ];
    }

    /**
     * Destruye la sesión completamente.
     * Limpia el array, invalida la cookie y destruye los datos del servidor.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Devuelve el array del usuario logueado o null si no hay sesión activa.
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // ── Flash ─────────────────────────────────────────────────────────────

    /**
     * Guarda un mensaje flash en sesión para mostrarlo en la próxima request.
     *
     * Tipos válidos: 'success' | 'error' | 'warning' | 'info'
     *
     * Uso:
     *   Session::flash('success', 'Evento creado correctamente.');
     *   Session::flash('error', 'El email ya está registrado.');
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Recupera todos los mensajes flash y los elimina de la sesión.
     * Llamar desde el layout para mostrarlos una sola vez.
     *
     * Uso en views/layouts/main.php:
     *   $flashes = Session::getFlash();
     *   foreach ($flashes as $type => $message) { ... }
     */
    public static function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    // ── CSRF ──────────────────────────────────────────────────────────────

    /**
     * Genera y almacena un token CSRF en sesión.
     * Si ya existe uno válido, lo reutiliza.
     *
     * Uso en vistas con formularios POST:
     *   <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF enviado en el formulario.
     * Aborta con 403 si el token es inválido o no existe.
     * Rota el token luego de validarlo.
     *
     * Llamar al inicio de cualquier método que procese un POST:
     *   Session::validateCsrf();
     */
    public static function validateCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recargá la página e intentá de nuevo.');
        }

        // Rotar el token para que no pueda reutilizarse
        unset($_SESSION['csrf_token']);
    }
}
<?php

// app/core/Auth.php
// Verificación de roles, permisos y utilidades de contraseña.
// Complementa Session.php.

namespace App\Core;

class Auth
{
    // ── Verificación de sesión ────────────────────────────────────────────

    /**
     * Devuelve true si hay un usuario logueado.
     */
    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    /**
     * Devuelve el rol del usuario activo o null si no hay sesión.
     */
    public static function role(): ?string
    {
        return $_SESSION['user']['rol'] ?? null;
    }

    /**
     * Devuelve true si el usuario tiene exactamente el rol indicado.
     *
     * Uso:
     *   Auth::is('ADMIN')
     */
    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    /**
     * Devuelve true si el usuario tiene alguno de los roles indicados.
     * Acepta un rol como string o varios como array.
     *
     * Uso:
     *   Auth::isAny(['MODERADOR', 'ADMIN'])
     */
    public static function isAny(string|array $roles): bool
    {
        return in_array(self::role(), (array) $roles, true);
    }

    // ── Control de acceso ─────────────────────────────────────────────────

    /**
     * Aborta con redirect al login si no hay sesión activa.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Debés iniciar sesión para acceder a esa página.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Verifica que el usuario tenga uno de los roles requeridos.
     * Acepta un rol como string o varios como array.
     * Aborta con 403 si no tiene el rol necesario.
     *
     * Uso:
     *   Auth::requireRole('ADMIN');
     *   Auth::requireRole(['MODERADOR', 'ADMIN']);
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireAuth();

        if (!self::isAny($roles)) {
            http_response_code(403);
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('Acceso no autorizado. Tu rol: ' . self::role());
            }
            require_once APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Redirige al feed si el usuario ya está logueado.
     * Usar en login y registro para evitar acceso redundante.
     */
    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . APP_URL . '/feed');
            exit;
        }
    }

    /**
     * Redirige al destino correspondiente según el rol del usuario.
     * Llamar inmediatamente después de Session::login().
     *
     * Roles de Petbook → todos van al feed.
     * MODERADOR y ADMIN tienen acceso a secciones adicionales desde el feed.
     */
    public static function redirectAfterLogin(): void
    {
        header('Location: ' . APP_URL . '/feed');
        exit;
    }

    // ── Contraseñas ───────────────────────────────────────────────────────

    /**
     * Genera el hash bcrypt de una contraseña.
     * Cost 12: balance entre seguridad y rendimiento en servidores compartidos.
     *
     * Uso en registro:
     *   $hash = Auth::hashPassword($passwordPlano);
     */
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verifica una contraseña contra su hash almacenado.
     *
     * Uso en login:
     *   if (Auth::verifyPassword($input, $usuario['Password_Hash'])) { ... }
     */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
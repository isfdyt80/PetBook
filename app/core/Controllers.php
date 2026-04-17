<?php

// app/core/Controller.php
// Clase base para todos los controladores.
// Provee helpers de vista, redirección, sesión, request y mensajes flash.

namespace App\Core;

abstract class Controller
{
    // ── Vistas ────────────────────────────────────────────────────────────

    /**
     * Carga una vista pasando variables al scope de la plantilla.
     * Usa notación con puntos para separar carpetas.
     *
     * Uso desde un controlador hijo:
     *   $this->view('evento/crear', ['tipos' => $tipos]);
     */
    protected function view(string $view, array $data = []): void
    {
        // Hace disponibles las variables dentro de la vista
        extract($data, EXTR_SKIP);

        $path = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("Vista no encontrada: {$path}");
        }

        require_once $path;
    }

    /**
     * Carga un fragmento parcial desde views/layouts/.
     * Útil para incluir header, footer o componentes reutilizables.
     *
     * Uso:
     *   $this->partial('header', ['titulo' => 'Inicio']);
     */
    protected function partial(string $partial, array $data = []): void
    {
        $this->view('layouts/' . $partial, $data);
    }

    // ── Redirección ───────────────────────────────────────────────────────

    /**
     * Redirige a una ruta relativa a APP_URL.
     *
     * Uso:
     *   $this->redirect('evento/crear');
     */
    protected function redirect(string $path): void
    {
        header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Redirige de vuelta a la página anterior.
     * Si no hay referer disponible, redirige a APP_URL.
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        header("Location: {$referer}");
        exit;
    }

    // ── Sesión y autenticación ────────────────────────────────────────────

    /**
     * Devuelve el usuario logueado desde la sesión, o null si no hay sesión.
     *
     * La estructura de $_SESSION['user'] es:
     *   [
     *     'id'     => int,
     *     'nombre' => string,
     *     'email'  => string,
     *     'rol'    => string,  // 'USUARIO' | 'MODERADOR' | 'ADMIN'
     *   ]
     */
    protected function authUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Verifica que el usuario esté logueado.
     * Si no lo está, redirige al login.
     */
    protected function requireAuth(): void
    {
        if (!$this->authUser()) {
            $this->flash('error', 'Debés iniciar sesión para acceder a esa página.');
            $this->redirect('login');
        }
    }

    /**
     * Verifica que NO haya sesión activa.
     * Redirige al feed si el usuario ya está logueado.
     * Útil en login y registro para evitar acceso redundante.
     */
    protected function requireGuest(): void
    {
        if ($this->authUser()) {
            $this->redirect('feed');
        }
    }

    /**
     * Verifica que el usuario tenga uno de los roles indicados.
     * Acepta un rol como string o varios como array.
     *
     * Roles válidos: 'USUARIO' | 'MODERADOR' | 'ADMIN'
     *
     * Uso:
     *   $this->requireRole('ADMIN');
     *   $this->requireRole(['MODERADOR', 'ADMIN']);
     */
    protected function requireRole(string|array $roles): void
    {
        $this->requireAuth();

        $rolesPermitidos = (array) $roles;
        $rolUsuario      = $this->authUser()['rol'] ?? '';

        if (!in_array($rolUsuario, $rolesPermitidos, true)) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    // ── Helpers de request ────────────────────────────────────────────────

    /**
     * Devuelve un campo del POST con trim aplicado.
     * Retorna $default si la clave no existe.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Devuelve un campo del GET con trim aplicado.
     * Retorna $default si la clave no existe.
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Responde en formato JSON y termina la ejecución.
     * Usado para endpoints AJAX.
     *
     * Uso:
     *   $this->json(['success' => true, 'mensaje' => 'Evento creado.']);
     *   $this->json(['error' => 'No autorizado.'], 403);
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── Mensajes flash ────────────────────────────────────────────────────

    /**
     * Guarda un mensaje flash en sesión para mostrarlo en la próxima request.
     *
     * Tipos válidos: 'success' | 'error' | 'warning' | 'info'
     *
     * Uso:
     *   $this->flash('success', 'Evento creado correctamente.');
     *   $this->flash('error', 'El email ya está registrado.');
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
}
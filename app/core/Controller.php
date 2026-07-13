<?php

// app/core/Controller.php
// Clase base para todos los controladores.
// Provee helpers de vista, redirección y request.
// Sesión, autenticación y flash se manejan desde Auth y Session.

namespace App\Core;

abstract class Controller
{
    // ── Vistas ────────────────────────────────────────────────────────────

    /**
     * Carga una vista pasando variables al scope de la plantilla.
     * Usa notación con puntos para separar carpetas.
     *
     * Uso desde un controlador hijo:
     *   $this->view('evento.crear', ['tipos' => $tipos]);
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $path = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("Vista no encontrada: {$path}");
        }

        require_once $path;
    }

    /**
     * Carga un fragmento parcial desde views/layouts/.
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
}
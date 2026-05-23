<?php

// app/core/Router.php
// Parsea la URL entrante y despacha al controlador y método correspondiente.

namespace App\Core;

class Router
{
    private array $routes = [];

    // ── Registro de rutas ────────────────────────────────────────────────

    /**
     * Registra una ruta GET.
     *
     * Uso en routes.php:
     *   $router->get('/evento/ver/:id', 'EventoController', 'ver');
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }

    /**
     * Registra una ruta POST.
     *
     * Uso en routes.php:
     *   $router->post('/evento/crear', 'EventoController', 'store');
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }

    /**
     * Registra una ruta para cualquier verbo HTTP.
     * Útil para rutas que aceptan tanto GET como POST.
     */
    public function any(string $path, string $controller, string $method): void
    {
        $this->addRoute('ANY', $path, $controller, $method);
    }

    private function addRoute(string $verb, string $path, string $controller, string $method): void
    {
        // Convierte :param en un grupo de captura nombrado
        // Ejemplo: /evento/ver/:id → #^/evento/ver/(?P<id>[^/]+)$#
        $pattern = preg_replace('/:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = compact('verb', 'pattern', 'controller', 'method');
    }

    // ── Despacho ─────────────────────────────────────────────────────────

    /**
     * Busca la ruta que coincide con la request actual y ejecuta el controlador.
     * Si no hay coincidencia, responde con 404.
     */
    public function dispatch(): void
    {
        $verb = $_SERVER['REQUEST_METHOD'];
        $uri  = $this->parseUri();

        foreach ($this->routes as $route) {
            $verbMatch = $route['verb'] === 'ANY' || $route['verb'] === $verb;

            if (!$verbMatch) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extrae solo los parámetros nombrados (ej: ['id' => '42'])
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callController($route['controller'], $route['method'], $params);
                return;
            }
        }

        // Sin coincidencia → 404
        $this->notFound();
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Limpia la URI eliminando query string y subdirectorio base.
     * Necesario cuando la app corre en un subdirectorio (ej: localhost/petbook/public).
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Eliminar query string (?foo=bar)
        if (str_contains($uri, '?')) {
            $uri = strstr($uri, '?', before_needle: true);
        }

        // Eliminar el subdirectorio base si la app no está en la raíz del servidor
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        return '/' . trim($uri, '/') ?: '/';
    }

    /**
     * Instancia el controlador y ejecuta el método con los parámetros de ruta.
     * Los parámetros se pasan como argumentos individuales al método.
     *
     * Ejemplo: /evento/ver/:id llama a $controller->ver('42')
     */
    private function callController(string $controllerName, string $method, array $params): void
    {
        $class = "App\\Controllers\\{$controllerName}";

        if (!class_exists($class)) {
            $this->notFound("Controlador '{$class}' no encontrado.");
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            $this->notFound("Método '{$method}' no existe en '{$class}'.");
            return;
        }

        try {
            // Pasa los parámetros de ruta como argumentos individuales
            call_user_func_array([$controller, $method], array_values($params));
        } catch (\Throwable $e) {
            $this->serverError($e);
        }
    }

    /**
     * Responde con 404.
     * En desarrollo muestra el mensaje técnico.
     * En producción carga la vista de error.
     */
    private function notFound(string $message = 'Página no encontrada.'): void
    {
        http_response_code(404);

        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<h1>404 — {$message}</h1>";
        } else {
            require_once APP_PATH . '/views/errors/404.php';
        }

        exit;
    }

    /**
     * Responde con 500 ante una excepción no capturada en un controlador.
     * En desarrollo muestra el stack trace.
     * En producción loguea y carga la vista de error.
     */
    private function serverError(\Throwable $e): void
    {
        http_response_code(500);

        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<h1>500 — Error interno</h1>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            error_log('[500 ERROR] ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            require_once APP_PATH . '/views/errors/500.php';
        }

        exit;
    }
}
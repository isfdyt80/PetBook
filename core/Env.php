<?php
// core/Env.php
namespace Core;
use Exception;
class Env {
    private static $vars = [];

    public static function load($path) {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue; // Ignorar comentarios
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            self::$vars[$name] = $value;
            putenv("$name=$value"); // opcional: también lo carga en getenv()
        }
    }

    public static function get($key, $default = null) {
        return self::$vars[$key] ?? $default;
    }
}

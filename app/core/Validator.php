<?php

// app/core/Validator.php
// Reglas de validación de entrada compartidas por los controladores.
//
// Toda regla rechaza lanzando \InvalidArgumentException con un mensaje
// pensado para mostrarse al usuario (Session::flash), de modo que el dato
// inválido se corta antes de llegar a la base de datos. Los métodos
// opcionales devuelven null cuando el campo viene vacío.

namespace App\Core;

class Validator
{
    /** Rango de un INT con signo de MySQL (4 bytes), clave de las tablas. */
    public const INT_MAX = 2147483647;

    // ── Texto ────────────────────────────────────────────────────────────

    /**
     * Valida un texto por obligatoriedad y longitud máxima.
     *
     * @param  string $campo     Etiqueta para el mensaje (ej: 'El nombre').
     * @param  int    $max       Longitud máxima en caracteres (columna VARCHAR).
     * @param  bool   $requerido Si es true, el vacío también rechaza.
     * @return string|null       El valor, o null si es opcional y vino vacío.
     * @throws \InvalidArgumentException
     */
    public static function texto(mixed $valor, string $campo, int $max, bool $requerido = false): ?string
    {
        $valor = $valor === null ? '' : (string) $valor;

        if ($valor === '') {
            if ($requerido) {
                throw new \InvalidArgumentException("{$campo} es un campo obligatorio.");
            }

            return null;
        }

        if (mb_strlen($valor) > $max) {
            throw new \InvalidArgumentException("{$campo} no puede superar {$max} caracteres.");
        }

        return $valor;
    }

    // ── Enteros e IDs ────────────────────────────────────────────────────

    /**
     * Valida un identificador de formulario que debe ser entero positivo.
     *
     * Acepta solo dígitos: nada de "12abc", "1e5" o "-3" (ninguna conversión
     * silenciosa). El vacío es opcional (null); el llamador decide el
     * mensaje de obligatoriedad. Fuera de rango o no numérico rechaza.
     *
     * @param  string $campo Etiqueta para el mensaje (ej: 'La especie').
     * @return int|null
     * @throws \InvalidArgumentException
     */
    public static function enteroPositivo(mixed $valor, string $campo, int $max = self::INT_MAX): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = is_int($valor) ? (string) $valor : $valor;

        if (!is_string($valor) || !ctype_digit($valor) || (int) $valor < 1 || (int) $valor > $max) {
            throw new \InvalidArgumentException("{$campo} no es un valor válido.");
        }

        return (int) $valor;
    }

    /**
     * Valida un parámetro de ruta con forma de ID (el :id del Router).
     *
     * Acepta solo 1 a 10 dígitos dentro del rango de un INT de MySQL.
     * Devuelve null ante cualquier otra cosa ("12abc", "0", "-5",
     * "99999999999"): el llamador responde 404 en lugar de seleccionar un
     * registro equivocado por el cast silencioso (int).
     */
    public static function idDeRuta(string $valor): ?int
    {
        if (!preg_match('/^\d{1,10}$/', $valor)) {
            return null;
        }

        $id = (int) $valor;

        return ($id >= 1 && $id <= self::INT_MAX) ? $id : null;
    }

    // ── Fechas ───────────────────────────────────────────────────────────

    /**
     * Valida una fecha en formato Y-m-d estricto (ida y vuelta).
     *
     * El vacío es opcional (null). Con $permitirFuturo = false rechaza las
     * fechas posteriores a hoy (regla de negocio de nacimientos y vistas).
     *
     * @param  string $campo Etiqueta para el mensaje (ej: 'La fecha de nacimiento').
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public static function fecha(mixed $valor, string $campo, bool $permitirFuturo = false): ?string
    {
        $valor = $valor === null ? '' : (string) $valor;

        if ($valor === '') {
            return null;
        }

        $fecha = \DateTime::createFromFormat('Y-m-d', $valor);

        if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
            throw new \InvalidArgumentException("{$campo} no es una fecha válida.");
        }

        if (!$permitirFuturo && $valor > date('Y-m-d')) {
            throw new \InvalidArgumentException("{$campo} no puede ser una fecha futura.");
        }

        return $valor;
    }

    // ── Decimales ────────────────────────────────────────────────────────

    /**
     * Valida un número decimal dentro de un rango con precisión acotada.
     *
     * Acepta solo formato decimal plano: se rechaza la notación exponencial
     * (ej. "1e10"), los signos y los espacios, para que el valor respete la
     * columna DECIMAL de destino sin conversiones silenciosas.
     *
     * @param  string $campo        Etiqueta para el mensaje (ej: 'La latitud').
     * @param  int    $maxDecimales Decimales admitidos (escala de la columna).
     * @return float|null
     * @throws \InvalidArgumentException
     */
    public static function decimal(mixed $valor, string $campo, float $min, float $max, int $maxDecimales = 2): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = (string) $valor;

        if (!preg_match('/^-?\d+(\.\d+)?$/', $valor)) {
            throw new \InvalidArgumentException(self::mensajeRango($campo, $min, $max, $maxDecimales));
        }

        $parteDecimal = str_contains($valor, '.') ? substr($valor, strpos($valor, '.') + 1) : '';

        if (strlen($parteDecimal) > $maxDecimales) {
            throw new \InvalidArgumentException("{$campo} no puede tener más de {$maxDecimales} decimales.");
        }

        $numero = (float) $valor;

        if ($numero < $min || $numero > $max) {
            throw new \InvalidArgumentException(self::mensajeRango($campo, $min, $max, $maxDecimales));
        }

        return $numero;
    }

    // ── Privado ──────────────────────────────────────────────────────────

    /**
     * Arma el mensaje de rango sin ceros de más (ej: '-90 y 90',
     * '0 y 99999999.99') a partir de la escala de la columna.
     */
    private static function mensajeRango(string $campo, float $min, float $max, int $maxDecimales): string
    {
        return "{$campo} debe ser un número entre "
            . self::formatear($min, $maxDecimales) . ' y '
            . self::formatear($max, $maxDecimales) . '.';
    }

    private static function formatear(float $numero, int $decimales): string
    {
        return rtrim(rtrim(number_format($numero, $decimales, '.', ''), '0'), '.');
    }
}

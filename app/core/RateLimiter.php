<?php

// app/core/RateLimiter.php
// Límite de intentos repetidos para acciones sensibles: login, registro y
// recuperación de contraseña (futura). Persiste en la tabla IntentoAcceso.

namespace App\Core;

class RateLimiter
{
    /**
     * Máximo de intentos por acción dentro de RATE_LIMIT_VENTANA_MINUTOS.
     * 'cuenta' limita por email normalizado; 'origen' por IP del cliente.
     * Una acción sin clave declarada no se limita.
     */
    private const LIMITES = [
        'login'        => ['cuenta' => LOGIN_MAX_INTENTOS,        'origen' => LOGIN_MAX_INTENTOS_ORIGEN],
        'registro'     => [                              'origen' => REGISTRO_MAX_POR_ORIGEN],
        'recuperacion' => ['cuenta' => RECUPERACION_MAX_INTENTOS, 'origen' => RECUPERACION_MAX_INTENTOS_ORIGEN],
    ];

    /**
     * Verifica si la acción sigue bloqueada para esta cuenta y/u origen.
     *
     * Cuenta los intentos registrados dentro de la ventana de tiempo para
     * cada clave aplicable ('cuenta:email' y 'origen:ip'). Si alguna supera
     * su máximo, devuelve los segundos restantes hasta que el intento más
     * viejo salga de la ventana. Devuelve 0 si puede continuar.
     *
     * Uso:
     *   if ($espera = RateLimiter::verificar('login', $email)) { ... }
     *
     * @return int Segundos de espera (0 = permitido).
     */
    public static function verificar(string $accion, ?string $cuenta = null): int
    {
        $limites = self::LIMITES[$accion] ?? [];
        if ($limites === []) {
            return 0;
        }

        $claves = [];
        if (isset($limites['origen'])) {
            $claves[] = ['origen', self::ip()];
        }
        if (isset($limites['cuenta']) && $cuenta !== null && $cuenta !== '') {
            $claves[] = ['cuenta', self::normalizarCuenta($cuenta)];
        }

        $desde = date('Y-m-d H:i:s', time() - RATE_LIMIT_VENTANA_MINUTOS * 60);

        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) AS intentos, MIN(Fecha) AS mas_viejo
             FROM IntentoAcceso
             WHERE Accion = :accion AND Tipo = :tipo
               AND Identificador = :identificador AND Fecha > :desde'
        );

        foreach ($claves as [$tipo, $identificador]) {
            $stmt->execute([
                ':accion'        => $accion,
                ':tipo'          => $tipo,
                ':identificador' => $identificador,
                ':desde'         => $desde,
            ]);

            $resultado = $stmt->fetch();

            if ((int) $resultado['intentos'] >= $limites[$tipo]) {
                $masViejo = $resultado['mas_viejo'];
                $restante = $masViejo
                    ? strtotime((string) $masViejo) + RATE_LIMIT_VENTANA_MINUTOS * 60 - time()
                    : RATE_LIMIT_VENTANA_MINUTOS * 60;

                return max(1, $restante);
            }
        }

        return 0;
    }

    /**
     * Registra un intento (fallido o de solicitud) para cada clave aplicable.
     * Cada fila expira sola al salir de la ventana; de paso se depuran las
     * filas viejas para que la tabla no crezca indefinidamente.
     *
     * Uso en el camino de fallo:
     *   RateLimiter::registrar('login', $email);
     */
    public static function registrar(string $accion, ?string $cuenta = null): void
    {
        if (!isset(self::LIMITES[$accion])) {
            return;
        }

        self::depurar();

        $identificadores = ['origen' => [self::ip()]];
        if ($cuenta !== null && $cuenta !== '') {
            $identificadores['cuenta'] = [self::normalizarCuenta($cuenta)];
        }

        $stmt = Database::getInstance()->prepare(
            'INSERT IGNORE INTO IntentoAcceso (Accion, Tipo, Identificador, Fecha)
             VALUES (:accion, :tipo, :identificador, NOW())'
        );

        foreach ($identificadores as $tipo => $valores) {
            foreach ($valores as $identificador) {
                $stmt->execute([
                    ':accion'        => $accion,
                    ':tipo'          => $tipo,
                    ':identificador' => $identificador,
                ]);
            }
        }
    }

    /**
     * Limpia el conteo tras una acción exitosa (ej.: login correcto).
     * Borra los intentos asociados a esa cuenta y al origen actual.
     *
     * Uso inmediatamente después de autenticar:
     *   RateLimiter::resetear('login', $email);
     */
    public static function resetear(string $accion, string $cuenta): void
    {
        Database::getInstance()->prepare(
            'DELETE FROM IntentoAcceso
             WHERE Accion = :accion
               AND (Identificador = :identificador OR Identificador = :ip)'
        )->execute([
            ':accion'        => $accion,
            ':identificador' => self::normalizarCuenta($cuenta),
            ':ip'            => self::ip(),
        ]);
    }

    /**
     * Formatea una espera para mostrarla al usuario.
     *
     * Uso:
     *   'Volvé a intentarlo en ' . RateLimiter::formatearEspera($segundos)
     */
    public static function formatearEspera(int $segundos): string
    {
        $minutos = (int) ceil($segundos / 60);
        return $minutos === 1
            ? '1 minuto'
            : "{$minutos} minutos";
    }

    // ── Internos ─────────────────────────────────────────────────────────

    /**
     * Elimina los registros salidos de la ventana de conteo.
     */
    private static function depurar(): void
    {
        Database::getInstance()->prepare(
            'DELETE FROM IntentoAcceso
             WHERE Fecha < NOW() - INTERVAL :ventana MINUTE'
        )->execute([':ventana' => RATE_LIMIT_VENTANA_MINUTOS]);
    }

    /**
     * Normaliza el email clave: trim + lowercase, para que
     * 'Usuario@mail.com' y 'usuario@mail.com' cuenten como la misma cuenta.
     */
    private static function normalizarCuenta(string $cuenta): string
    {
        return mb_strtolower(trim($cuenta));
    }

    /**
     * IP del cliente.
     */
    private static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    }
}

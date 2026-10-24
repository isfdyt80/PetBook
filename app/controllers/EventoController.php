<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Especie;
use App\Models\EventoMascota;
use App\Models\Mascota;
use App\Models\Raza;
use App\Models\TipoEvento;
use App\Models\Ubicacion;

class EventoController extends Controller
{
    // ── GET /evento/crear/:tipo ───────────────────────────────────────────

    public function crear(string $tipo): void
    {
        Auth::requireAuth();

        $tipo = $this->tipoValido($tipo);

        $this->renderCrear($tipo, []);
    }

    // ── POST /evento/crear/:tipo ──────────────────────────────────────────

    public function store(string $tipo): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $tipo      = $this->tipoValido($tipo);
        $idUsuario = (int) Session::user()['id'];

        $idExistente = (int) $this->input('id_mascota_existente', 0);
        $idEspecie   = (int) $this->input('especie', 0);
        $idRaza      = (int) $this->input('raza', 0);
        $descripcion = (string) $this->input('descripcion', '');
        $latitud     = (string) $this->input('latitud', '');
        $longitud    = (string) $this->input('longitud', '');
        $direccion   = $this->valorONull('direccion', 255);
        $ciudad      = $this->valorONull('ciudad', 100);

        // ── Validación ────────────────────────────────────────────────────
        $error = null;

        if ($descripcion === '') {
            $error = 'La descripción es obligatoria.';
        } elseif (mb_strlen($descripcion) > 2000) {
            $error = 'La descripción no puede superar los 2000 caracteres.';
        } elseif ($idExistente > 0 && !$this->mascotaEsDelUsuario($idExistente, $idUsuario, $tipo)) {
            $error = 'La mascota seleccionada no es válida.';
        } elseif ($idExistente <= 0 && $idEspecie <= 0) {
            $error = 'La especie es obligatoria.';
        } elseif (!$this->coordenadasValidas($latitud, $longitud)) {
            $error = 'La ubicación no es válida. Intentá de nuevo o completá la dirección.';
        } elseif ($direccion === null && $ciudad === null && $latitud === '') {
            $error = 'Indicá la ubicación: usá tu ubicación actual o completá la dirección o la ciudad.';
        }

        if ($error !== null) {
            Session::flash('error', $error);
            $this->renderCrear($tipo, $_POST);
            return;
        }

        // ── Datos para los modelos ────────────────────────────────────────
        $datosMascota = [
            'Nombre'             => $this->valorONull('nombre', 100),
            'Id_Especie'         => $idEspecie,
            'Id_Raza'            => $idRaza > 0 ? $idRaza : null,
            'Color'              => $this->valorONull('color', 100),
            'Descripcion_Fisica' => $this->valorONull('descripcion_fisica', 1000),
        ];

        $datosUbicacion = [
            'direccion' => $direccion,
            'ciudad'    => $ciudad,
            'provincia' => $this->valorONull('provincia', 100),
            'pais'      => $this->valorONull('pais', 100),
            'latitud'   => $latitud  !== '' ? (float) $latitud  : null,
            'longitud'  => $longitud !== '' ? (float) $longitud : null,
        ];

        // ── Una sola transacción: mascota, ubicación, evento, historial y publicación ──
        try {
            $idEvento = (new EventoMascota())->crear([
                'id_mascota_existente' => $idExistente > 0 ? $idExistente : null,
                'mascota'              => $datosMascota,
                'ubicacion'            => $datosUbicacion,
                'id_usuario'           => $idUsuario,
                'descripcion'          => $descripcion,
            ], $tipo);

        } catch (\InvalidArgumentException $e) {
            // Ej: la raza no corresponde a la especie (mensaje pensado para el usuario).
            Session::flash('error', $e->getMessage());
            $this->renderCrear($tipo, $_POST);
            return;
        } catch (\Throwable $e) {
            // Nunca mostramos el mensaje técnico de PDO al usuario.
            error_log('[EVENTO ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo publicar el reporte. Intentá de nuevo.');
            $this->renderCrear($tipo, $_POST);
            return;
        }

        Session::flash('success', 'Reporte publicado correctamente.');
        $this->redirect('evento/' . $idEvento);
    }

    // ── GET /evento/:id ───────────────────────────────────────────────────

    public function ver(string $id): void
    {
        Auth::requireAuth();

        $evento = (new EventoMascota())->detalle((int) $id);

        if ($evento === false) {
            $this->notFound();
        }

        $this->view('evento.ver', ['evento' => $evento]);
    }

    // ── Privado ───────────────────────────────────────────────────────────

    /**
     * Normaliza el tipo de la URL y lo valida contra el catálogo TipoEvento.
     * Si no existe (ej: /evento/crear/loquesea) responde 404.
     */
    private function tipoValido(string $tipo): string
    {
        $tipo    = strtoupper($tipo);
        $validos = array_column((new TipoEvento())->listar(), 'Nombre');

        if (!in_array($tipo, $validos, true)) {
            $this->notFound();
        }

        return $tipo;
    }

    private function notFound(): never
    {
        http_response_code(404);
        require_once APP_PATH . '/views/errors/404.php';
        exit;
    }

    /**
     * Carga el formulario con los datos que espera la vista evento/crear.php.
     */
    private function renderCrear(string $tipo, array $old): void
    {
        $this->view('evento.crear', [
            'tipo'     => $tipo,
            'mascotas' => (new Mascota())->listarPorUsuario((int) Session::user()['id']),
            'especies' => (new Especie())->listar(),
            'razas'    => (new Raza())->listar(),
            'old'      => $old,
        ]);
    }

    /**
     * Lee un campo del POST: devuelve null si está vacío y lo recorta al máximo
     * que admite la columna.
     */
    private function valorONull(string $campo, int $max): ?string
    {
        $valor = (string) $this->input($campo, '');

        return $valor === '' ? null : mb_substr($valor, 0, $max);
    }

    /**
     * La mascota elegida debe ser del usuario. En ENCONTRADA no se permite
     * elegir una existente: la mascota encontrada no es de quien reporta.
     */
    private function mascotaEsDelUsuario(int $idMascota, int $idUsuario, string $tipo): bool
    {
        if ($tipo === 'ENCONTRADA') {
            return false;
        }

        foreach ((new Mascota())->listarPorUsuario($idUsuario) as $mascota) {
            if ((int) $mascota['Id_Mascota'] === $idMascota) {
                return true;
            }
        }

        return false;
    }

    /**
     * Latitud y longitud van juntas (o ninguna) y dentro de rango.
     */
    private function coordenadasValidas(string $latitud, string $longitud): bool
    {
        if ($latitud === '' && $longitud === '') {
            return true;
        }

        if (!is_numeric($latitud) || !is_numeric($longitud)) {
            return false;
        }

        return abs((float) $latitud) <= 90 && abs((float) $longitud) <= 180;
    }
}
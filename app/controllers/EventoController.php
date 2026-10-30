<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Mascota;
use App\Models\EventoMascota;
use App\Models\TipoEvento;

class EventoController extends Controller
{
    /**
     * Títulos del formulario por tipo, según el catálogo TipoEvento.
     * La vista evento.crear los usa para el encabezado y los textos.
     */
    private const TITULOS = [
        'PERDIDA'    => 'Reportar mascota perdida',
        'ENCONTRADA' => 'Reportar mascota encontrada',
        'ADOPCION'   => 'Dar mascota en adopción',
    ];

    // ── GET /evento/crear/:tipo ───────────────────────────────────────────

    /**
     * Muestra el formulario de alta de evento, parametrizado por tipo.
     *
     * El tipo es un valor del catálogo TipoEvento (ej: 'PERDIDA'), no un
     * campo libre de la URL: se resuelve contra el catálogo y cualquier
     * otra cosa responde 404 antes de llegar a la vista o a la BD.
     * Si alguien pega /evento/crear/loquesea no debe ver un error de SQL.
     */
    public function crear(string $tipo): void
    {
        Auth::requireAuth();

        $tipoNombre = $this->resolverTipo($tipo);

        if ($tipoNombre === null) {
            $this->responder404();
            return;
        }

        $this->renderCrear($tipoNombre);
    }

    // ── POST /evento/crear/:tipo ──────────────────────────────────────────

    /**
     * Procesa el formulario de alta de evento.
     *
     * Valida los datos de entrada (responsabilidad HTTP), revalida el tipo
     * contra el catálogo y delega la persistencia transaccional completa
     * (Ubicación + EventoMascota ACTIVO + Historial + Publicación) al modelo
     * EventoMascota::crear(). Ante un fallo de la transacción muestra un
     * error genérico: nunca el mensaje técnico de PDO en producción.
     */
    public function store(string $tipo): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $tipoNombre = $this->resolverTipo($tipo);

        if ($tipoNombre === null) {
            $this->responder404();
            return;
        }

        $idMascota = (int) $this->input('mascota');

        if ($idMascota <= 0 || !(new Mascota())->perteneceAUsuario($idMascota, (int) Session::user()['id'])) {
            Session::flash('error', 'La mascota indicada no existe o no te pertenece.');
            $this->renderCrear($tipoNombre);
            return;
        }

        $datos = $this->leerDatosFormulario($tipoNombre);

        if ($datos === false) {
            $this->renderCrear($tipoNombre);
            return;
        }

        try {
            $idEvento = (new EventoMascota())->crear($datos, $tipoNombre);
        } catch (\Throwable $e) {
            error_log('[EVENTO ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo crear el evento. Intentá de nuevo.');
            $this->renderCrear($tipoNombre);
            return;
        }

        Session::flash('success', 'Evento creado correctamente.');
        $this->redirect('evento/' . $idEvento);
    }

    // ── Privado ───────────────────────────────────────────────────────────

    /**
     * Normaliza y valida el tipo de evento contra el catálogo.
     *
     * Acepta la forma que venga en la URL (perdida, Perdida, PERDIDA) y
     * devuelve el nombre normalizado del catálogo, o null si no existe
     * (la ruta dejaba pasar cualquier string, acá se corta con 404).
     *
     * @return string|null
     */
    private function resolverTipo(string $tipo): ?string
    {
        $tipoCatalogo = (new TipoEvento())->buscarPorNombre($tipo);

        return $tipoCatalogo === false ? null : $tipoCatalogo['Nombre'];
    }

    /**
     * Lee y valida los campos del formulario de evento (POST).
     *
     * Devuelve los datos normalizados para EventoMascota::crear(), o false
     * si la validación falla (en ese caso ya cargó el flash de error).
     * La mascota se leyo antes (dueniovalidado en store()); acá se completan
     * descripción, ubicación y los campos exclusivos de PERDIDA.
     *
     * @return array|false
     */
    private function leerDatosFormulario(string $tipoNombre): array|false
    {
        $descripcion = $this->input('descripcion');

        if ($descripcion === null || $descripcion === '') {
            Session::flash('error', 'La descripción es obligatoria.');
            return false;
        }

        $datos = [
            'Id_Mascota'  => (int) $this->input('mascota'),
            'Id_Usuario'  => (int) Session::user()['id'],
            'Descripcion' => $descripcion,
        ];

        // Ubicación: disponible en PERDIDA y ENCONTRADA, no aplica a ADOPCION.
        if ($tipoNombre !== 'ADOPCION') {
            $datos['ubicacion'] = $this->leerUbicacion();
        }

        // Recompensa y fecha de última vista: exclusivos de PERDIDA.
        if ($tipoNombre === 'PERDIDA') {
            $recompensa = $this->input('recompensa');

            if ($recompensa !== null && $recompensa !== '') {
                if (!is_numeric($recompensa) || (float) $recompensa < 0) {
                    Session::flash('error', 'La recompensa debe ser un número mayor o igual a cero.');
                    return false;
                }
                $datos['Recompensa'] = (float) $recompensa;
            }

            $fechaUltimaVista = $this->input('fecha_ultima_vista');

            if ($fechaUltimaVista !== null && $fechaUltimaVista !== '') {
                $fecha = \DateTime::createFromFormat('Y-m-d', $fechaUltimaVista);

                if (!$fecha || $fecha->format('Y-m-d') !== $fechaUltimaVista) {
                    Session::flash('error', 'La fecha de última vista no es válida.');
                    return false;
                }
                $datos['Fecha_UltimaVista'] = $fechaUltimaVista . ' 00:00:00';
            }
        }

        return $datos;
    }

    /**
     * Lee los campos de ubicación del formulario.
     * Si todos están vacíos devuelve null (ubicación opcional); si algún
     * campo tiene contenido arma el array para Ubicacion::crear().
     *
     * @return array|null
     */
    private function leerUbicacion(): ?array
    {
        $direccion  = $this->input('direccion');
        $ciudad     = $this->input('ciudad');
        $provincia  = $this->input('provincia');
        $pais       = $this->input('pais');

        if (
            ($direccion === null || $direccion === '')
            && ($ciudad === null || $ciudad === '')
            && ($provincia === null || $provincia === '')
            && ($pais === null || $pais === '')
        ) {
            return null;
        }

        return [
            'direccion' => $direccion  !== null && $direccion  !== '' ? $direccion  : null,
            'ciudad'    => $ciudad     !== null && $ciudad     !== '' ? $ciudad     : null,
            'provincia' => $provincia  !== null && $provincia  !== '' ? $provincia  : null,
            'pais'      => $pais       !== null && $pais       !== '' ? $pais       : null,
        ];
    }

    /**
     * Carga el formulario de alta de evento parametrizado por tipo.
     *
     * La vista recibe:
     *   - tipo:     nombre del tipo ya resuelto del catálogo (PERDIDA, ...).
     *   - mascotas: mascotas del usuario para el <select>.
     *   - old:      $_POST, para repoblar los campos ante un error.
     */
    private function renderCrear(string $tipoNombre): void
    {
        $this->view('evento.crear', [
            'titulo'   => self::TITULOS[$tipoNombre] ?? 'Crear evento',
            'tipo'     => $tipoNombre,
            'mascotas' => (new Mascota())->listarPorUsuario((int) Session::user()['id']),
            'old'      => $_POST,
        ]);
    }

    /**
     * Responde 404 con la vista de error del sitio.
     * Mismo patrón de MascotaController::ver().
     */
    private function responder404(): void
    {
        http_response_code(404);
        $this->view('errors.404');
    }
}

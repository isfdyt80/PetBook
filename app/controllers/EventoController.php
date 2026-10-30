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

    // ── GET /evento/:id ───────────────────────────────────────────────────

    /**
     * Muestra el detalle de un evento con mascota y ubicación.
     *
     * Responde 404 si el evento no existe o está eliminado (soft delete).
     * El detalle es visible para cualquier usuario autenticado: los eventos
     * alimentan el feed público mediante su publicación.
     */
    public function ver(string $id): void
    {
        Auth::requireAuth();

        $idEvento = (int) $id;

        // Resguardo: /evento/crear sin tipo matchearía /evento/:id antes
        // de caer acá con $id="crear"; (int) y ambos casos resuelven a 404.
        if ($idEvento <= 0) {
            $this->responder404();
            return;
        }

        $evento = (new EventoMascota())->buscarDetalle($idEvento);

        if ($evento === false) {
            $this->responder404();
            return;
        }

        $this->view('evento.ver', [
            'titulo'  => $evento['Tipo_Evento'] === 'PERDIDA'   ? 'Mascota perdida'
                       : ($evento['Tipo_Evento'] === 'ENCONTRADA' ? 'Mascota encontrada'
                                                                   : 'Mascota en adopción'),
            'evento'  => $evento,
            'esDueno' => (int) $evento['Id_Usuario'] === (int) Session::user()['id'],
        ]);
    }

    // ── POST /evento/:id/estado ───────────────────────────────────────────

    /**
     * Resuelve o cancela un evento (HU-11).
     *
     * Solo el dueño del evento puede cambiar el estado, y solo hacia
     * RESUELTO o CANCELADO (la vuelta a ACTIVO no se ofrece desde la UI).
     * El modelo valida estado terminal, actualiza el evento, escribe el
     * historial, sella Fecha_Resolucion y archiva la publicación asociada
     * dentro de la misma transacción.
     */
    public function cambiarEstado(string $id): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $idEvento = (int) $id;
        $modelo   = new EventoMascota();

        $evento = $modelo->buscarDetalle($idEvento);

        if (
            $evento === false
            || (int) $evento['Id_Usuario'] !== (int) Session::user()['id']
        ) {
            $this->responder404();
            return;
        }

        $nuevoEstado = strtoupper((string) ($this->input('estado') ?? ''));

        if (!in_array($nuevoEstado, ['RESUELTO', 'CANCELADO'], true)) {
            Session::flash('error', 'El estado indicado no es válido para un evento.');
            $this->redirect('evento/' . $idEvento);
            return;
        }

        try {
            $modelo->cambiarEstado($idEvento, $nuevoEstado, (int) Session::user()['id']);
        } catch (\RuntimeException $e) {
            // Mensajes de negocio del modelo (evento terminal, catálogo),
            // pensados para verse en pantalla.
            Session::flash('error', $e->getMessage());
            $this->redirect('evento/' . $idEvento);
            return;
        } catch (\Throwable $e) {
            error_log('[EVENTO ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo actualizar el evento. Intentá de nuevo.');
            $this->redirect('evento/' . $idEvento);
            return;
        }

        Session::flash(
            'success',
            $nuevoEstado === 'RESUELTO'
                ? 'Marcaste el evento como resuelto. ¡Se buscó a la mascota!'
                : 'El evento fue cancelado.'
        );
        $this->redirect('evento/' . $idEvento);
    }

    // ── POST /evento/:id/eliminar ─────────────────────────────────────────

    /**
     * Da de baja un evento y su publicación (soft delete en cascada).
     *
     * Solo el dueño puede eliminar. Al completar redirige al feed, porque
     * el detalle del evento deja de existir para la UI.
     */
    public function eliminar(string $id): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $idEvento = (int) $id;
        $evento   = (new EventoMascota())->buscarDetalle($idEvento);

        if (
            $evento === false
            || (int) $evento['Id_Usuario'] !== (int) Session::user()['id']
        ) {
            $this->responder404();
            return;
        }

        try {
            (new EventoMascota())->darBaja($idEvento);
        } catch (\Throwable $e) {
            error_log('[EVENTO ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo eliminar el evento. Intentá de nuevo.');
            $this->redirect('evento/' . $idEvento);
            return;
        }

        Session::flash('success', 'El evento fue eliminado, junto con su publicación.');
        $this->redirect('feed');
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
            $ubicacion = $this->leerUbicacion();

            if ($ubicacion === false) {
                return false;
            }

            $datos['ubicacion'] = $ubicacion;
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
     * Lee y valida los campos de ubicación del formulario.
     *
     * Comprende los cuatro campos de texto (dirección/ciudad/provincia/país)
     * y las coordenadas que el navegador aporta con su geolocalización
     * (latitud/longitud, opcionales y por pares).
     *
     * Devuelve:
     *   - null   si el formulario no aportó nada de lo anterior,
     *   - false  si la validación falló (ya cargó el flash de error),
     *   - array  listo para Ubicacion::crear(), con claves null cuando un
     *            campo no se completó (ej. coordenadas GPS sin dirección).
     *
     * @return array|null|false
     */
    private function leerUbicacion(): array|null|false
    {
        $direccion  = $this->input('direccion');
        $ciudad     = $this->input('ciudad');
        $provincia  = $this->input('provincia');
        $pais       = $this->input('pais');

        $latitud  = $this->input('latitud');
        $longitud = $this->input('longitud');

        $latitud  = ($latitud !== null && $latitud !== '')  ? $latitud  : null;
        $longitud = ($longitud !== null && $longitud !== '') ? $longitud : null;

        // Sin nada aportado: no crear registro de ubicación.
        if (
            ($direccion === null || $direccion === '')
            && ($ciudad === null || $ciudad === '')
            && ($provincia === null || $provincia === '')
            && ($pais === null || $pais === '')
            && $latitud === null
            && $longitud === null
        ) {
            return null;
        }

        // Las coordenadas vienen por pares: media posición es un punto roto.
        if (($latitud === null) !== ($longitud === null)) {
            Session::flash('error', 'Si aportás coordenadas, necesitamos latitud y longitud juntas.');
            return false;
        }

        if ($latitud !== null && (!is_numeric($latitud) || $latitud < -90 || $latitud > 90)) {
            Session::flash('error', 'La latitud debe ser un número entre -90 y 90.');
            return false;
        }

        if ($longitud !== null && (!is_numeric($longitud) || $longitud < -180 || $longitud > 180)) {
            Session::flash('error', 'La longitud debe ser un número entre -180 y 180.');
            return false;
        }

        return [
            'direccion' => $direccion  !== null && $direccion  !== '' ? $direccion  : null,
            'ciudad'    => $ciudad     !== null && $ciudad     !== '' ? $ciudad     : null,
            'provincia' => $provincia  !== null && $provincia  !== '' ? $provincia  : null,
            'pais'      => $pais       !== null && $pais       !== '' ? $pais       : null,
            'latitud'   => $latitud  !== null ? (float) $latitud  : null,
            'longitud'  => $longitud !== null ? (float) $longitud : null,
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

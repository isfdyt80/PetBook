<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Especie;
use App\Models\Mascota;
use App\Models\Raza;

class MascotaController extends Controller
{
    // ── GET /mascota ──────────────────────────────────────────────────────

    /**
     * Lista las mascotas activas del usuario autenticado.
     *
     * El escapado de salida (htmlspecialchars) es responsabilidad de la vista.
     * Si el usuario no tiene mascotas, el modelo devuelve un array vacío y la
     * vista se encarga de mostrar el estado sin resultados.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $idUsuario = (int) Session::user()['id'];
        $mascotas  = (new Mascota())->listarPorUsuario($idUsuario);

        $this->view('mascota.index', [
            'titulo'   => 'Mis mascotas',
            'mascotas' => $mascotas,
        ]);
    }

    // ── GET /mascota/crear ────────────────────────────────────────────────

    /**
     * Muestra el formulario de registro de mascota.
     */
    public function crear(): void
    {
        Auth::requireAuth();

        $this->renderCrear();
    }

    // ── POST /mascota/crear ───────────────────────────────────────────────

    /**
     * Procesa el formulario de registro de mascota.
     *
     * Valida los datos de entrada (responsabilidad HTTP) y delega la
     * persistencia y la coherencia especie-raza al modelo Mascota.
     * En caso de error re-renderiza el formulario para no perder los datos
     * ya cargados ($_POST queda disponible para la vista).
     */
    public function guardar(): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $datos = $this->leerDatosFormulario();

        if ($datos === false) {
            $this->renderCrear();
            return;
        }

        $datos['Id_Usuario'] = (int) Session::user()['id'];

        try {
            (new Mascota())->registrar($datos);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            $this->renderCrear();
            return;
        } catch (\Throwable $e) {
            error_log('[MASCOTA ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo registrar la mascota. Intentá de nuevo.');
            $this->renderCrear();
            return;
        }

        Session::flash('success', 'Mascota registrada correctamente.');
        $this->redirect('mascota');
    }

    // ── GET /mascota/:id ──────────────────────────────────────────────────

    /**
     * Muestra el detalle de una mascota.
     *
     * Responde 404 si no existe o está eliminada (soft delete). La vista no
     * recibe el ID a través de un modelo, solo los datos ya resueltos.
     */
    public function ver(string $id): void
    {
        Auth::requireAuth();

        $mascota = (new Mascota())->buscarPorId((int) $id);

        if ($mascota === null) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('mascota.ver', [
            'mascota' => $mascota,
        ]);
    }

    // ── POST /mascota/:id/editar ──────────────────────────────────────────

    /**
     * Procesa el formulario de edición de mascota.
     *
     * Consulta primero que la mascota exista (404 si no), lee y valida los
     * datos con el mismo criterio que guardar() y delega la actualización y
     * la coherencia especie-raza al modelo Mascota.
     */
    public function actualizar(string $id): void
    {
        Auth::requireAuth();
        Session::validateCsrf();

        $idMascota = (int) $id;

        if ((new Mascota())->buscarPorId($idMascota) === null) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $datos = $this->leerDatosFormulario();

        if ($datos === false) {
            $this->back();
            return;
        }

        try {
            (new Mascota())->actualizar($idMascota, $datos);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            $this->back();
            return;
        } catch (\Throwable $e) {
            error_log('[MASCOTA ERROR] ' . $e->getMessage());
            Session::flash('error', 'No se pudo actualizar la mascota. Intentá de nuevo.');
            $this->back();
            return;
        }

        Session::flash('success', 'Mascota actualizada correctamente.');
        $this->redirect('mascota/' . $idMascota);
    }

    // ── Privado ───────────────────────────────────────────────────────────

    /**
     * Lee y valida los campos del formulario de mascota (POST).
     *
     * Compartido por guardar() y actualizar(). Devuelve los datos ya
     * normalizados para el modelo, o false si la validación falla (en ese
     * caso ya cargó el flash de error correspondiente).
     *
     * @return array|false
     */
    private function leerDatosFormulario(): array|false
    {
        $nombre          = $this->input('nombre');
        $idEspecie       = (int) $this->input('especie');
        $idRaza          = (int) $this->input('raza');
        $fechaNacimiento = $this->input('fecha_nacimiento');

        if ($idEspecie <= 0) {
            Session::flash('error', 'La especie es obligatoria.');
            return false;
        }

        if ($nombre === null || $nombre === '') {
            Session::flash('error', 'El nombre de la mascota es obligatorio.');
            return false;
        }

        if ($fechaNacimiento !== null && $fechaNacimiento !== '') {
            $fecha = \DateTime::createFromFormat('Y-m-d', $fechaNacimiento);

            if (!$fecha || $fecha->format('Y-m-d') !== $fechaNacimiento) {
                Session::flash('error', 'La fecha de nacimiento no es válida.');
                return false;
            }
        } else {
            $fechaNacimiento = null;
        }

        return [
            'Nombre'           => $nombre,
            'Id_Especie'       => $idEspecie,
            'Id_Raza'          => $idRaza > 0 ? $idRaza : null,
            'Fecha_Nacimiento' => $fechaNacimiento,
        ];
    }

    /**
     * Carga el formulario de registro con los catálogos necesarios.
     *
     * La vista recibe:
     *   - especies: catálogo para el <select> de especie.
     *   - razas:    catálogo para el <select> de raza (opcional).
     *   - old:      $_POST, para repoblar los campos ante un error.
     */
    private function renderCrear(): void
    {
        $this->view('mascota.crear', [
            'titulo'   => 'Registrar mascota',
            'especies' => (new Especie())->listar(),
            'razas'    => (new Raza())->listar(),
            'old'      => $_POST,
        ]);
    }
}

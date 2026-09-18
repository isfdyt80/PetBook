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

        $idUsuario       = (int) Session::user()['id'];
        $nombre          = $this->input('nombre');
        $idEspecie       = (int) $this->input('especie');
        $idRaza          = (int) $this->input('raza');
        $fechaNacimiento = $this->input('fecha_nacimiento');

        if ($idEspecie <= 0) {
            Session::flash('error', 'La especie es obligatoria.');
            $this->renderCrear();
            return;
        }

        if ($nombre === null || $nombre === '') {
            Session::flash('error', 'El nombre de la mascota es obligatorio.');
            $this->renderCrear();
            return;
        }

        if ($fechaNacimiento !== null && $fechaNacimiento !== '') {
            $fecha = \DateTime::createFromFormat('Y-m-d', $fechaNacimiento);

            if (!$fecha || $fecha->format('Y-m-d') !== $fechaNacimiento) {
                Session::flash('error', 'La fecha de nacimiento no es válida.');
                $this->renderCrear();
                return;
            }
        } else {
            $fechaNacimiento = null;
        }

        try {
            (new Mascota())->registrar([
                'Id_Usuario'       => $idUsuario,
                'Id_Especie'       => $idEspecie,
                'Id_Raza'          => $idRaza > 0 ? $idRaza : null,
                'Nombre'           => $nombre,
                'Fecha_Nacimiento' => $fechaNacimiento,
            ]);
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

    // ── Privado ───────────────────────────────────────────────────────────

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

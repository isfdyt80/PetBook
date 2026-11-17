<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Especie;
use App\Models\Mascota;

class MascotaController extends Controller
{
    /**
     * Valores permitidos para los campos de catálogo del formulario.
     * La vista los ofrece como <select>; acá se revalidan porque el
     * formulario puede enviarse desde fuera del navegador.
     */
    private const TAMAÑOS = ['Pequeño', 'Mediano', 'Grande'];
    private const SEXOS   = ['Macho', 'Hembra'];
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
     * Responde 404 si no existe, está eliminada (soft delete) o no pertenece
     * al usuario autenticado. La vista no recibe el ID a través de un modelo,
     * solo los datos ya resueltos.
     */
    public function ver(string $id): void
    {
        Auth::requireAuth();

        // Solo dígitos dentro del rango de un INT: "12abc" o "99999999999"
        // no deben seleccionar ningún registro (404, no cast silencioso).
        $idMascota = Validator::idDeRuta($id);

        if ($idMascota === null) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $modelo  = new Mascota();
        $mascota = $modelo->buscarPorId($idMascota);

        if (
            $mascota === null
            || !$modelo->perteneceAUsuario($idMascota, (int) Session::user()['id'])
        ) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('mascota.ver', [
            'mascota' => $mascota,
        ]);
    }

    // ── GET /mascota/:id/editar ───────────────────────────────────────────

    /**
     * Muestra el formulario de edición con los datos actuales de la mascota.
     *
     * Responde 404 si no existe o si la mascota no pertenece al usuario
     * autenticado. La vista recibe 'old' mapeado a los nombres de campo del
     * formulario para reutilizar la misma plantilla de crear.
     */
    public function editar(string $id): void
    {
        Auth::requireAuth();

        $idMascota = Validator::idDeRuta($id);

        if ($idMascota === null) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $modelo    = new Mascota();
        $mascota   = $modelo->buscarPorId($idMascota);

        if (
            $mascota === null
            || !$modelo->perteneceAUsuario($idMascota, (int) Session::user()['id'])
        ) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('mascota.editar', [
            'titulo'   => 'Editar mascota',
            'mascota'  => $mascota,
            'especies' => (new Especie())->listar(),
            'old'      => [
                'nombre'           => $mascota['Nombre'] ?? '',
                'especie'          => $mascota['Id_Especie'] ?? '',
                'raza'             => $mascota['Id_Raza'] ?? '',
                'color'            => $mascota['Color'] ?? '',
                'tamaño'           => $mascota['Tamaño'] ?? '',
                'sexo'             => $mascota['Sexo'] ?? '',
                'fecha_nacimiento' => $mascota['Fecha_Nacimiento'] ?? '',
                'edad_aproximada'  => $mascota['Edad_Aproximada'] ?? '',
            ],
        ]);
    }

    // ── POST /mascota/:id/editar ──────────────────────────────────────────

    /**
     * Procesa el formulario de edición de mascota.
     *
     * Consulta primero que la mascota exista y pertenezca al usuario
     * autenticado (404 si no), lee y valida los datos con el mismo criterio
     * que guardar() y delega la actualización y la coherencia especie-raza
     * al modelo Mascota.
     */
    public function actualizar(string $id): void
    {
        Auth::requireAuth();

        $idMascota = Validator::idDeRuta($id);

        if ($idMascota === null) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $modelo = new Mascota();

        if (
            $modelo->buscarPorId($idMascota) === null
            || !$modelo->perteneceAUsuario($idMascota, (int) Session::user()['id'])
        ) {
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
            (new Mascota())->actualizar(
                $idMascota,
                (int) Session::user()['id'],
                $datos
            );
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
        // Obligatoriedad, longitudes del esquema (Mascota.Nombre VARCHAR(100),
        // Color VARCHAR(100), Tamaño VARCHAR(50), Sexo VARCHAR(20),
        // Edad_Aproximada VARCHAR(50)) e IDs como enteros estrictos: todo
        // dato inválido se corta acá, antes del INSERT/UPDATE.
        try {
            $idEspecie = Validator::enteroPositivo($this->input('especie'), 'La especie');
            $idRaza    = Validator::enteroPositivo($this->input('raza'), 'La raza');

            if ($idEspecie === null) {
                Session::flash('error', 'La especie es obligatoria.');
                return false;
            }

            $nombre = Validator::texto($this->input('nombre'), 'El nombre de la mascota', 100, true);
            $color  = Validator::texto($this->input('color'), 'El color', 100);
            $tamaño = Validator::texto($this->input('tamaño'), 'El tamaño', 50);
            $sexo   = Validator::texto($this->input('sexo'), 'El sexo', 20);
            $edad   = Validator::texto($this->input('edad_aproximada'), 'La edad aproximada', 50);

            $fechaNacimiento = Validator::fecha($this->input('fecha_nacimiento'), 'La fecha de nacimiento');
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            return false;
        }

        // Catálogos con valores cerrados en la vista: cualquier otra cosa
        // (el formulario puede enviarse desde fuera del navegador) se rechaza.
        if ($tamaño !== null && !in_array($tamaño, self::TAMAÑOS, true)) {
            Session::flash('error', 'El tamaño indicado no es válido.');
            return false;
        }

        if ($sexo !== null && !in_array($sexo, self::SEXOS, true)) {
            Session::flash('error', 'El sexo indicado no es válido.');
            return false;
        }

        // La especie debe existir: un Id_Especie inexistente fallaría como
        // error de FK en la base, con un mensaje genérico para el usuario.
        if ((new Especie())->buscarPorId($idEspecie) === null) {
            Session::flash('error', 'Elegí una especie válida.');
            return false;
        }

        return [
            'Nombre'           => $nombre,
            'Id_Especie'       => $idEspecie,
            'Id_Raza'          => $idRaza,
            'Color'            => $color,
            'Tamaño'           => $tamaño,
            'Sexo'             => $sexo,
            'Fecha_Nacimiento' => $fechaNacimiento,
            'Edad_Aproximada'  => $edad,
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
            'old'      => $_POST,
        ]);
    }
}

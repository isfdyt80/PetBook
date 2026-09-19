<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Raza;

class RazaController extends Controller
{
    /**
     * GET /raza/por-especie/:id
     *
     * Devuelve las razas de una especie en formato JSON para poblar
     * el select de raza del formulario de mascota vía AJAX.
     */
    public function porEspecie(string $idEspecie): void
    {
        Auth::requireAuth();

        $this->json(
            (new Raza())->listarPorEspecie((int) $idEspecie)
        );
    }
}

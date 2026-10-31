<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Publicacion;

class PublicacionController extends Controller
{
    /**
     * GET /feed
     */
    public function index(): void
    {
        Auth::requireAuth();

        $publicacionModel = new Publicacion();

        $publicaciones = $publicacionModel->obtenerFeed();

        $this->view('publicacion.feed', [
            'titulo'        => 'Feed',
            'publicaciones' => $publicaciones,
        ]);
    }

    /** GET /mis-publicaciones: publicaciones propias activas y archivadas. */
    public function misPublicaciones(): void
    {
        Auth::requireAuth();

        $publicaciones = (new Publicacion())->listarPorUsuario((int) Session::user()['id']);

        $this->view('publicacion.mis_publicaciones', [
            'titulo'        => 'Mis publicaciones',
            'publicaciones' => $publicaciones,
        ]);
    }
}

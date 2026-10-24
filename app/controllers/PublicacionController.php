<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
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
}
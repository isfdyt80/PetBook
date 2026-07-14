<?php

// app/routes.php
// Define todas las rutas de la aplicación.
// El objeto $router es instanciado en public/index.php y disponible aquí.
// Formato: $router->verbo('/ruta/:param', 'Controlador', 'metodo');

// ── Autenticación ─────────────────────────────────────────────────────────
$router->get( '/',          'AuthController', 'index');
$router->get( '/login',     'AuthController', 'index');
$router->post('/login',     'AuthController', 'login');
$router->get( '/registro',  'AuthController', 'showRegistro');
$router->post('/registro',  'AuthController', 'registro');
$router->get( '/logout',    'AuthController', 'logout');

// Recuperación de contraseña
$router->get( '/recuperar',          'AuthController', 'showRecuperar');
$router->post('/recuperar',          'AuthController', 'recuperar');
$router->get( '/reset/:token',       'AuthController', 'showReset');
$router->post('/reset/:token',       'AuthController', 'reset');

// ── Feed ──────────────────────────────────────────────────────────────────
$router->get('/feed',                'PublicacionController', 'index');

// ── Eventos ───────────────────────────────────────────────────────────────
$router->get( '/evento/crear',       'EventoController', 'crear');
$router->post('/evento/crear',       'EventoController', 'store');
$router->get( '/evento/:id',         'EventoController', 'ver');
$router->post('/evento/:id/estado',  'EventoController', 'cambiarEstado');
$router->post('/evento/:id/eliminar','EventoController', 'eliminar');

// ── Mascotas ──────────────────────────────────────────────────────────────
$router->get( '/mascota/crear',      'MascotaController', 'crear');
$router->post('/mascota/crear',      'MascotaController', 'store');
$router->get( '/mascota/:id',        'MascotaController', 'ver');
$router->post('/mascota/:id/editar', 'MascotaController', 'actualizar');

// ── Publicaciones ─────────────────────────────────────────────────────────
$router->get( '/publicacion/:id',          'PublicacionController', 'ver');
$router->post('/publicacion/:id/comentar', 'PublicacionController', 'comentar');
$router->post('/publicacion/:id/reaccion', 'PublicacionController', 'reaccionar');
$router->post('/publicacion/:id/reportar', 'PublicacionController', 'reportar');
$router->post('/publicacion/:id/eliminar', 'PublicacionController', 'eliminar');

// ── Comentarios ───────────────────────────────────────────────────────────
$router->post('/comentario/:id/reportar', 'ComentarioController', 'reportar');
$router->post('/comentario/:id/eliminar', 'ComentarioController', 'eliminar');

// ── Perfil de usuario ─────────────────────────────────────────────────────
$router->get( '/perfil',             'UsuarioController', 'index');
$router->post('/perfil/editar',      'UsuarioController', 'actualizar');

// ── Moderación (requiere rol MODERADOR o ADMIN) ───────────────────────────
$router->get( '/moderacion',                  'ModeracionController', 'index');
$router->post('/moderacion/reporte/:id',      'ModeracionController', 'resolver');

// ── Administración (requiere rol ADMIN) ───────────────────────────────────
$router->get( '/admin',                       'AdminController', 'index');
$router->post('/admin/usuario/:id/rol',       'AdminController', 'asignarRol');
$router->post('/admin/usuario/:id/desactivar','AdminController', 'desactivar');
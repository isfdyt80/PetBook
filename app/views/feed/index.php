<?php
ob_start();
?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <h1 class="mb-4 text-center">
                Feed principal
            </h1>

            <!-- Estado vacío -->
            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <div class="display-1 mb-3">
                        🐾
                    </div>

                    <h3 class="mb-3">
                        Todavía no hay publicaciones
                    </h3>

                    <p class="text-muted mb-0">
                        Cuando los usuarios compartan eventos,
                        actividades o novedades de sus mascotas,
                        aparecerán aquí.
                    </p>

                </div>

            </div>

            <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
            <!-- Ejemplo visual de publicación futura -->
            <div class="card mt-4 border-0 shadow-sm opacity-50">

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-secondary">
                            Evento
                        </span>

                        <small class="text-muted">
                            15/06/2026
                        </small>
                    </div>

                    <h5>
                        Caminata grupal
                    </h5>

                    <p class="mb-2">
                        🐶 Firulais
                    </p>

                    <p class="text-muted mb-3">
                        📍 Plaza San Martín
                    </p>

                    <div class="d-flex gap-3">

                        <span>
                            ❤️ 12 reacciones
                        </span>

                        <span>
                            💬 4 comentarios
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
$titulo  = 'Feed principal';
require_once APP_PATH . '/views/layouts/main.php';
?>
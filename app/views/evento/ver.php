<?php

use App\Core\Session;

ob_start();

$evento = $evento ?? [];
$tipo = $evento['Tipo_Evento'] ?? '';

$iconoPorTipo = ['PERDIDA' => 'bi-search-heart', 'ENCONTRADA' => 'bi-handbag', 'ADOPCION' => 'bi-stars'];

$ubicacion = array_filter([
    $evento['Direccion'] ?? null,
    $evento['Ciudad'] ?? null,
    $evento['Provincia'] ?? null,
    $evento['Pais'] ?? null,
]);
?>

<style>
    .pb-page-title {
        font-family: 'Fraunces', serif;
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--pb-text);
        margin-bottom: 0.2rem;
        letter-spacing: -0.5px;
    }

    .pb-badge {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #F3F1FE;
        color: var(--pb-violet);
    }

    .pb-badge-activo { background: #F0FDF4; color: #166534; }
    .pb-badge-resuelto { background: #EEF0FF; color: #3730A3; }
    .pb-badge-cancelado { background: #FFF1F2; color: #9F1239; }

    .pb-list-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--pb-muted);
        margin-bottom: 0.15rem;
    }

    .pb-list-value {
        font-size: 0.95rem;
        color: var(--pb-text);
        margin-bottom: 0;
        white-space: pre-line;
    }

    .pb-evento-icono {
        width: 90px;
        height: 90px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F3F1FE;
        color: var(--pb-violet);
        font-size: 2.6rem;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm overflow-hidden">

                <div class="text-center pt-4">
                    <div class="pb-evento-icono">
                        <i class="bi <?= htmlspecialchars($iconoPorTipo[$tipo] ?? 'bi-calendar-event', ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>

                    <h1 class="pb-page-title"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>

                    <div class="d-flex justify-content-center gap-2 mb-3 px-3">
                        <span class="pb-badge"><?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?></span>

                        <?php
                        $estado = $evento['Estado_Evento'] ?? 'ACTIVO';
                        $claseEstado = ['ACTIVO' => 'pb-badge pb-badge-activo',
                                        'RESUELTO' => 'pb-badge pb-badge-resuelto',
                                        'CANCELADO' => 'pb-badge pb-badge-cancelado'][$estado] ?? 'pb-badge';
                        ?>
                        <span class="<?= htmlspecialchars($claseEstado, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">

                    <div class="mb-4">
                        <div class="pb-list-label">Descripción</div>
                        <p class="pb-list-value">
                            <?= htmlspecialchars($evento['Descripcion'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div class="mb-4 d-flex flex-wrap">
                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Creado</div>
                            <p class="pb-list-value mb-0">
                                <?= htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_Creacion'])), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Mascota</div>
                            <p class="pb-list-value mb-0">
                                <a href="<?= APP_URL ?>/mascota/<?= (int) ($evento['Id_Mascota'] ?? 0) ?>"
                                   style="color:var(--pb-violet); font-weight:600; text-decoration:none;">
                                    <?= htmlspecialchars($evento['Mascota_Nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    <i class="bi bi-paw" style="font-size:0.8rem;"></i>
                                </a>
                            </p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Especie</div>
                            <p class="pb-list-value mb-0"><?= htmlspecialchars($evento['Especie'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Raza</div>
                            <p class="pb-list-value mb-0"><?= htmlspecialchars($evento['Raza'] ?? 'Sin especificar', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <?php if (!empty($evento['Recompensa'])): ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Recompensa</div>
                                <p class="pb-list-value mb-0">
                                    <i class="bi bi-gift" style="color:var(--pb-violet);"></i>
                                    $<?= htmlspecialchars(number_format((float) $evento['Recompensa'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($evento['Fecha_UltimaVista'])): ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Última vista</div>
                                <p class="pb-list-value mb-0">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_UltimaVista'])), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($evento['Fecha_Resolucion'])): ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Resolución</div>
                                <p class="pb-list-value mb-0">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_Resolucion'])), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($ubicacion)): ?>
                        <div class="pt-3 border-top">
                            <div class="pb-list-label">
                                <i class="bi bi-geo-alt"></i> Ubicación
                            </div>
                            <p class="pb-list-value">
                                <?= htmlspecialchars(implode(', ', $ubicacion), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($esDueno)): ?>
                        <?php if ($estado === 'ACTIVO'): ?>
                            <form method="POST" action="<?= APP_URL ?>/evento/<?= (int) $evento['Id_Evento'] ?>/estado"
                                  class="pt-3 border-top mt-4" onsubmit="return confirm('¿Querés marcar este evento como resuelto o cancelarlo?')">
                                <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

                                <div class="d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label class="pb-list-label d-block" for="estado">Cerrar evento</label>
                                        <select class="pb-input" id="estado" name="estado" style="padding:0.45rem 0.7rem;">
                                            <option value="RESUELTO">Resuelto — se encontró / fue dado</option>
                                            <option value="CANCELADO">Cancelado — ya no sigue activo</option>
                                        </select>
                                    </div>
                                    <button type="submit"
                                            style="background:var(--pb-violet); color:#fff; border:none; border-radius:8px; padding:0.5rem 1rem; font-weight:600; font-size:0.88rem; white-space:nowrap; cursor:pointer;">
                                        Actualizar
                                    </button>
                                </div>
                                <div class="pb-hint">Al cerrarlo se archiva la publicación del feed y queda en tu historial.</div>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/evento/<?= (int) $evento['Id_Evento'] ?>/eliminar"
                              class="<?= $estado === 'ACTIVO' ? 'pt-2' : 'pt-3 border-top mt-4' ?>"
                              onsubmit="return confirm('¿Seguro que querés eliminar este evento? También desaparecerá del feed.')">
                            <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">
                            <button type="submit"
                                    style="background:none; color:#9F1239; border:none; font-size:0.85rem; font-weight:600; padding:0; cursor:pointer;">
                                <i class="bi bi-trash3"></i> Eliminar evento
                            </button>
                        </form>
                    <?php endif; ?>

                </div>

            </div>

            <div class="text-center mt-3">
                <a href="<?= APP_URL ?>/feed"
                   style="font-size:0.87rem; color:var(--pb-violet); font-weight:600; text-decoration:none;">
                    Volver al feed
                </a>
            </div>

        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>

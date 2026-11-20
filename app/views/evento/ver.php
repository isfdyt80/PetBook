<?php

use App\Core\Session;

ob_start();

$evento = $evento ?? [];
$historial = $historial ?? [];
$tipo = $evento['Tipo_Evento'] ?? '';

$iconoPorTipo = ['PERDIDA' => 'bi-search-heart', 'ENCONTRADA' => 'bi-handbag', 'ADOPCION' => 'bi-stars'];

$ubicacionTexto = implode(', ', array_filter(
    [
    $evento['Direccion'] ?? null,
    $evento['Ciudad'] ?? null,
    $evento['Provincia'] ?? null,
    $evento['Pais'] ?? null,
    ]
));

// Coordenadas capturadas con el navegador: pueden existir sin dirección.
$latitud  = ($evento['Latitud']  ?? '') !== '' && ($evento['Latitud']  ?? null) !== null
    ? (string) $evento['Latitud']
    : null;
$longitud = ($evento['Longitud'] ?? '') !== '' && ($evento['Longitud'] ?? null) !== null
    ? (string) $evento['Longitud']
    : null;

$mapaUrl = null;
if ($latitud !== null && $longitud !== null) {
    // Sin texto de dirección, las coordenadas son la única referencia.
    if ($ubicacionTexto === '') {
        $ubicacionTexto = $latitud . ', ' . $longitud;
    }

    $mapaUrl = 'https://www.openstreetmap.org/?mlat=' . rawurlencode($latitud)
        . '&mlon=' . rawurlencode($longitud)
        . '#map=16/' . rawurlencode($latitud) . '/' . rawurlencode($longitud);
}
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

    .pb-history-item { position:relative; padding:0 0 1rem 1.35rem; border-left:2px solid var(--pb-border); }
    .pb-history-item:last-child { padding-bottom:0; border-left-color:transparent; }
    .pb-history-dot { position:absolute; left:-0.42rem; top:.15rem; width:.7rem; height:.7rem; border-radius:50%; background:var(--pb-violet); border:2px solid #fff; }
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm overflow-hidden">

                <div class="text-center pt-4">
                    <div class="pb-evento-icono">
                        <i class="bi <?php echo htmlspecialchars($iconoPorTipo[$tipo] ?? 'bi-calendar-event', ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>

                    <h1 class="pb-page-title"><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>

                    <div class="d-flex justify-content-center gap-2 mb-3 px-3">
                        <span class="pb-badge"><?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?></span>

                        <?php
                        $estado = $evento['Estado_Evento'] ?? 'ACTIVO';
                        $claseEstado = ['ACTIVO' => 'pb-badge pb-badge-activo',
                                        'RESUELTO' => 'pb-badge pb-badge-resuelto',
                                        'CANCELADO' => 'pb-badge pb-badge-cancelado'][$estado] ?? 'pb-badge';
                        ?>
                        <span class="<?php echo htmlspecialchars($claseEstado, ENT_QUOTES, 'UTF-8') ?>">
                            <?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">

                    <div class="mb-4">
                        <div class="pb-list-label">Descripción</div>
                        <p class="pb-list-value">
                            <?php echo htmlspecialchars($evento['Descripcion'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div class="mb-4 d-flex flex-wrap">
                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Creado</div>
                            <p class="pb-list-value mb-0">
                                <?php echo htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_Creacion'])), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Mascota</div>
                            <p class="pb-list-value mb-0">
                                <?php if ($tipo !== 'ENCONTRADA') : ?>
                                <a href="<?php echo APP_URL ?>/mascota/<?php echo (int) ($evento['Id_Mascota'] ?? 0) ?>"
                                   style="color:var(--pb-violet); font-weight:600; text-decoration:none;">
                                    <?php echo htmlspecialchars($evento['Mascota_Nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    <i class="bi bi-paw" style="font-size:0.8rem;"></i>
                                </a>
                                <?php else : ?>
                                    <?php echo htmlspecialchars($evento['Mascota_Nombre'] ?? 'Mascota encontrada', ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Especie</div>
                            <p class="pb-list-value mb-0"><?php echo htmlspecialchars($evento['Especie'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="w-50 mb-3">
                            <div class="pb-list-label">Raza</div>
                            <p class="pb-list-value mb-0"><?php echo htmlspecialchars($evento['Raza'] ?? 'Sin especificar', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <?php if (!empty($evento['Recompensa'])) : ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Recompensa</div>
                                <p class="pb-list-value mb-0">
                                    <i class="bi bi-gift" style="color:var(--pb-violet);"></i>
                                    $<?php echo htmlspecialchars(number_format((float) $evento['Recompensa'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($evento['Fecha_UltimaVista'])) : ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Última vista</div>
                                <p class="pb-list-value mb-0">
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_UltimaVista'])), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($evento['Fecha_Resolucion'])) : ?>
                            <div class="w-50 mb-3">
                                <div class="pb-list-label">Resolución</div>
                                <p class="pb-list-value mb-0">
                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($evento['Fecha_Resolucion'])), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($ubicacionTexto !== '' || $mapaUrl !== null) : ?>
                        <div class="pt-3 border-top">
                            <div class="pb-list-label">
                                <i class="bi bi-geo-alt"></i> Ubicación
                            </div>

                            <?php if ($ubicacionTexto !== '') : ?>
                                <p class="pb-list-value mb-1">
                                    <?php echo htmlspecialchars($ubicacionTexto, ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($mapaUrl !== null) : ?>
                                <a href="<?php echo htmlspecialchars($mapaUrl, ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   style="font-size:0.85rem; color:var(--pb-violet); font-weight:600; text-decoration:none;">
                                    <i class="bi bi-map"></i> Ver en el mapa
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="pt-3 border-top mt-3">
                        <div class="pb-list-label mb-3"><i class="bi bi-clock-history me-1"></i>Historial del evento</div>
                        <?php if (empty($historial)) : ?>
                            <p class="pb-hint mb-0">Todavía no hay movimientos registrados.</p>
                        <?php else : ?>
                            <?php foreach ($historial as $movimiento) : ?>
                                <div class="pb-history-item">
                                    <span class="pb-history-dot"></span>
                                    <div class="d-flex justify-content-between flex-wrap gap-1">
                                        <strong style="font-size:.88rem; color:var(--pb-text);">
                                            <?php echo htmlspecialchars($movimiento['Estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                        <time class="pb-hint" datetime="<?php echo htmlspecialchars($movimiento['Fecha'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($movimiento['Fecha'])), ENT_QUOTES, 'UTF-8') ?>
                                        </time>
                                    </div>
                                    <div class="pb-hint">Por <?php echo htmlspecialchars($movimiento['Nombre_Usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($esDueno)) : ?>
                        <?php if ($tipo !== 'ADOPCION') : ?>
                            <details class="pt-3 border-top mt-4">
                                <summary style="cursor:pointer; font-size:0.87rem; font-weight:600; color:var(--pb-violet);">
                                    <i class="bi bi-geo-alt"></i> Editar ubicación
                                </summary>

                                <form method="POST"
                                      action="<?php echo APP_URL ?>/evento/<?php echo (int) $evento['Id_Evento'] ?>/ubicacion"
                                      class="mt-3">
                                    <input type="hidden" name="_csrf" value="<?php echo Session::csrfToken() ?>">

                                    <label class="pb-label" for="direccion">Dirección</label>
                                    <input class="pb-input mb-2" type="text" id="direccion" name="direccion"
                                        value="<?php echo htmlspecialchars($evento['Direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        maxlength="255" placeholder="Ej: Av. Illia 480" autocomplete="off">

                                    <div class="row">
                                        <div class="col-md-4 mb-1">
                                            <input class="pb-input" type="text" name="ciudad" maxlength="100"
                                                value="<?php echo htmlspecialchars($evento['Ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                placeholder="Ciudad" autocomplete="off">
                                        </div>
                                        <div class="col-md-4 mb-1">
                                            <input class="pb-input" type="text" name="provincia" maxlength="100"
                                                value="<?php echo htmlspecialchars($evento['Provincia'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                placeholder="Provincia" autocomplete="off">
                                        </div>
                                        <div class="col-md-4 mb-1">
                                            <input class="pb-input" type="text" name="pais" maxlength="100"
                                                value="<?php echo htmlspecialchars($evento['Pais'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                placeholder="País" autocomplete="off">
                                        </div>
                                    </div>

                                    <input type="hidden" id="latitud" name="latitud"
                                        value="<?php echo htmlspecialchars($latitud ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                    <input type="hidden" id="longitud" name="longitud"
                                        value="<?php echo htmlspecialchars($longitud ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                    <button type="button" id="btn-geo"
                                            style="background:none; color:var(--pb-violet); border:1.5px solid var(--pb-border); border-radius:8px; padding:0.42rem 0.8rem; font-size:0.82rem; font-weight:600; font-family:'DM Sans', sans-serif; cursor:pointer; margin-top:0.35rem;">
                                        <i class="bi bi-crosshair"></i> Usar mi ubicación actual
                                    </button>

                                    <div class="pb-hint mb-2" id="geo-hint">Si dejás todos los campos vacíos y no capturás coordenadas, se quita la ubicación.</div>

                                    <button type="submit"
                                            style="background:var(--pb-violet); color:#fff; border:none; border-radius:8px; padding:0.5rem 1rem; font-weight:600; font-size:0.88rem; cursor:pointer;">
                                        Guardar ubicación
                                    </button>
                                </form>
                            </details>
                        <?php endif; ?>

                        <?php if ($estado === 'ACTIVO') : ?>
                            <form method="POST" action="<?php echo APP_URL ?>/evento/<?php echo (int) $evento['Id_Evento'] ?>/estado"
                                  class="pt-3 border-top mt-4"
                                  data-confirm-titulo="Cerrar publicación"
                                  data-confirm-texto="¿Querés marcar este evento como resuelto o cancelarlo?"
                                  data-confirm-boton="Sí, cerrar">
                                <input type="hidden" name="_csrf" value="<?php echo Session::csrfToken() ?>">

                                <div class="d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label class="pb-list-label d-block" for="estado">Cerrar publicación</label>
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

                        <form method="POST" action="<?php echo APP_URL ?>/evento/<?php echo (int) $evento['Id_Evento'] ?>/eliminar"
                              class="<?php echo $estado === 'ACTIVO' ? 'pt-2' : 'pt-3 border-top mt-4' ?>"
                              data-confirm-titulo="Eliminar publicación"
                              data-confirm-texto="¿Seguro que querés eliminar este evento? También desaparecerá del feed."
                              data-confirm-boton="Sí, eliminar"
                              data-confirm-color="#9F1239">
                            <input type="hidden" name="_csrf" value="<?php echo Session::csrfToken() ?>">
                            <button type="submit"
                                    style="background:none; color:#9F1239; border:none; font-size:0.85rem; font-weight:600; padding:0; cursor:pointer;">
                                <i class="bi bi-trash3"></i> Eliminar publicación
                            </button>
                        </form>
                    <?php endif; ?>

                </div>

            </div>

            <div class="text-center mt-3">
                <a href="<?php echo APP_URL ?>/feed"
                   style="font-size:0.87rem; color:var(--pb-violet); font-weight:600; text-decoration:none;">
                    Volver al feed
                </a>
            </div>

        </div>

    </div>
</div>

<script src="<?php echo APP_URL ?>/js/evento/geolocalizacion.js"></script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>

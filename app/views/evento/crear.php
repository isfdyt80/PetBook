<?php

use App\Core\Session;

ob_start();

$tipo    = $tipo    ?? 'PERDIDA';
$old     = $old     ?? [];
$mascotas = $mascotas ?? [];

// Campos diferenciales por tipo (catálogo TipoEvento).
$conUbicacion       = $tipo !== 'ADOPCION';
$esPerdida          = $tipo === 'PERDIDA';
$iconoPorTipo       = ['PERDIDA' => 'bi-search-heart', 'ENCONTRADA' => 'bi-handbag', 'ADOPCION' => 'bi-stars'];
$placeholderVista   = [
    'PERDIDA'    => 'Ej: Se vio por última vez en el parque San Martín, tiene placa dorada.',
    'ENCONTRADA' => 'Ej: La encontré cerca del centro, se porta bien y está sana.',
    'ADOPCION'   => 'Ej: Busca hogar responsable, está desparasitada y vacunada.',
]
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

    .pb-page-subtitle {
        font-size: 0.88rem;
        color: var(--pb-muted);
        margin-bottom: 1.75rem;
    }

    .pb-label {
        display: block;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--pb-text);
        margin-bottom: 0.3rem;
    }

    .pb-input {
        border: 1.5px solid var(--pb-border);
        border-radius: 8px;
        padding: 0.6rem 0.85rem;
        font-size: 0.95rem;
        width: 100%;
        background: var(--pb-bg);
        color: var(--pb-text);
        transition: border-color 0.15s;
        outline: none;
        font-family: 'DM Sans', sans-serif;
    }

    .pb-input:focus { border-color: var(--pb-violet); background: #fff; }

    .pb-btn {
        background: var(--pb-violet);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.68rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s;
    }

    .pb-btn:hover { background: var(--pb-violet-d); }

    .pb-hint {
        font-size: 0.78rem;
        color: var(--pb-muted);
        margin-top: 0.25rem;
    }

    .pb-evento-icono {
        width: 70px;
        height: 70px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F3F1FE;
        color: var(--pb-violet);
        font-size: 2rem;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="auth-card"
                 style="background: var(--pb-surface); border: 1px solid var(--pb-border); border-radius: var(--pb-radius); padding: 2.25rem 2rem;">

                <div class="pb-evento-icono text-center">
                    <i class="bi <?= htmlspecialchars($iconoPorTipo[$tipo] ?? 'bi-calendar-event', ENT_QUOTES, 'UTF-8') ?>"></i>
                </div>

                <h1 class="pb-page-title text-center"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="pb-page-subtitle text-center">Completá los datos del evento</div>

                <form action="<?= APP_URL ?>/evento/crear/<?= strtolower($tipo) ?>" method="POST" novalidate>
                    <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

                    <div class="mb-3">
                        <label class="pb-label" for="mascota">Mascota</label>
                        <select class="pb-input" id="mascota" name="mascota" required>
                            <option value="" disabled
                                <?= empty($old['mascota']) ? 'selected' : '' ?>>
                                Elegí una de tus mascotas
                            </option>

                            <?php foreach ($mascotas as $mascota) : ?>
                                <option value="<?= (int) $mascota['Id_Mascota'] ?>"
                                    <?= (string) ($old['mascota'] ?? '') === (string) $mascota['Id_Mascota'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mascota['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pb-hint">Solo se listan las mascotas que registraste.</div>
                    </div>

                    <div class="mb-3">
                        <label class="pb-label" for="descripcion">Descripción</label>
                        <textarea class="pb-input" id="descripcion" name="descripcion" rows="4" required
                            placeholder="<?= htmlspecialchars($placeholderVista[$tipo] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($old['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="pb-hint">Estos detalles se publicarán en tu feed.</div>
                    </div>

                    <?php if ($conUbicacion) : ?>
                        <div class="mb-3">
                            <div class="pb-label mb-2"><i class="bi bi-geo-alt me-1"></i>Última ubicación conocida</div>

                            <label class="pb-label" for="direccion">Dirección</label>
                            <input class="pb-input mb-2" type="text" id="direccion" name="direccion"
                                value="<?= htmlspecialchars($old['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Ej: Av. Illia 480" autocomplete="off">

                            <div class="row">
                                <div class="col-md-4 mb-1">
                                    <input class="pb-input" type="text" name="ciudad"
                                        value="<?= htmlspecialchars($old['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Ciudad" autocomplete="off">
                                </div>
                                <div class="col-md-4 mb-1">
                                    <input class="pb-input" type="text" name="provincia"
                                        value="<?= htmlspecialchars($old['provincia'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Provincia" autocomplete="off">
                                </div>
                                <div class="col-md-4 mb-1">
                                    <input class="pb-input" type="text" name="pais"
                                        value="<?= htmlspecialchars($old['pais'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="País" autocomplete="off">
                                </div>
                            </div>
                            <div class="pb-hint">Opcional, pero ayuda mucho a quien pueda reconocer el lugar.</div>

                            <input type="hidden" id="latitud" name="latitud"
                                value="<?= htmlspecialchars($old['latitud'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <input type="hidden" id="longitud" name="longitud"
                                value="<?= htmlspecialchars($old['longitud'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                            <button type="button" id="btn-geo"
                                    style="background:none; color:var(--pb-violet); border:1.5px solid var(--pb-border); border-radius:8px; padding:0.42rem 0.8rem; font-size:0.82rem; font-weight:600; font-family:'DM Sans', sans-serif; cursor:pointer; margin-top:0.35rem;">
                                <i class="bi bi-crosshair"></i> Usar mi ubicación actual
                            </button>

                            <div class="pb-hint" id="geo-hint">Opcional; si la aceptás, se guarda tu posición exacta del momento.</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($esPerdida) : ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="pb-label" for="fecha_ultima_vista">Fecha de última vista</label>
                                <input class="pb-input" type="date" id="fecha_ultima_vista" name="fecha_ultima_vista"
                                    value="<?= htmlspecialchars($old['fecha_ultima_vista'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <div class="pb-hint">Opcional</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="pb-label" for="recompensa">Recompensa</label>
                                <input class="pb-input" type="number" id="recompensa" name="recompensa" min="0" step="0.01"
                                    value="<?= htmlspecialchars($old['recompensa'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Ej: 5000" autocomplete="off">
                                <div class="pb-hint">Opcional</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <button type="submit" class="pb-btn">
                            <i class="bi <?= htmlspecialchars($iconoPorTipo[$tipo] ?? 'bi-calendar-event', ENT_QUOTES, 'UTF-8') ?> me-1"></i>Crear evento
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>/mascota"
                       style="font-size:0.87rem; color:var(--pb-violet); font-weight:600; text-decoration:none;">
                        Volver a mis mascotas
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
(function ($) {
    const $btn  = $('#btn-geo');
    const $hint = $('#geo-hint');
    const $lat  = $('#latitud');
    const $lon  = $('#longitud');

    // Sin API de geolocalización (navegadores viejos o HTTP no seguro):
    // la ubicación manual sigue siendo válida, así que solo deshabilitamos el botón.
    if (!('geolocation' in navigator)) {
        $btn.prop('disabled', true).html('<i class="bi bi-slash-circle"></i> Geolocalización no disponible');
        return;
    }

    $btn.on('click', function () {
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Obteniendo…');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                // 6 decimales ≈ 11 cm de precisión, sobra para una mascota.
                $lat.val(pos.coords.latitude.toFixed(6));
                $lon.val(pos.coords.longitude.toFixed(6));
                $hint.text('Posición capturada: se envía junto con el formulario.');
                $btn.prop('disabled', false).html('<i class="bi bi-check2-circle"></i> Volver a capturar');
            },
            function (err) {
                $hint.text(
                    err.code === 1
                        ? 'Permiso de ubicación denegado; podés completar los campos de texto igual.'
                        : 'No se pudo obtener tu ubicación; podés completar los campos de texto igual.'
                );
                $btn.prop('disabled', false).html('<i class="bi bi-crosshair"></i> Usar mi ubicación actual');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
})(jQuery);
</script>

<?php
$content = ob_get_clean();
$titulo  = $titulo ?? 'Crear evento';
require_once APP_PATH . '/views/layouts/main.php';
?>

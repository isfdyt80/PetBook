<?php

/** @var string $tipo */
/** @var array  $mascotas */
/** @var array  $especies */
/** @var array  $razas */
/** @var array  $old */

use App\Core\Session;
ob_start();

// Datos que llegan desde EventoController::crear() / store():
//   $tipo      'PERDIDA' | 'ENCONTRADA' | 'ADOPCION' (ya validado por el controlador)
//   $mascotas  Mascota::listarPorUsuario()  → Id_Mascota, Nombre, Especie, Raza
//   $especies  Especie::listar()
//   $razas     Raza::listar()
//   $old       $_POST (para repoblar el form si hubo error)
$old  = $old ?? [];
$slug = strtolower($tipo);

$textos = [
    'PERDIDA' => [
        'titulo'    => 'Reportar mascota perdida',
        'subtitulo' => 'Contanos qué pasó y dónde la viste por última vez.',
        'desc'      => 'Cómo se perdió, señas particulares, si tiene collar...',
        'boton'     => 'Publicar reporte',
        'icono'     => 'bi-search-heart',
    ],
    'ENCONTRADA' => [
        'titulo'    => 'Reportar mascota encontrada',
        'subtitulo' => 'Contanos dónde la encontraste y cómo está.',
        'desc'      => 'Dónde la encontraste, cómo está, si tiene collar o chapita...',
        'boton'     => 'Publicar reporte',
        'icono'     => 'bi-binoculars',
    ],
    'ADOPCION' => [
        'titulo'    => 'Publicar mascota en adopción',
        'subtitulo' => 'Ayudala a encontrar un hogar.',
        'desc'      => 'Carácter, cuidados, si está vacunada o castrada...',
        'boton'     => 'Publicar adopción',
        'icono'     => 'bi-house-heart',
    ],
];
$t = $textos[$tipo];

// ENCONTRADA siempre crea mascota nueva (la mascota encontrada no es del usuario).
$usaExistente = $tipo !== 'ENCONTRADA' && !empty($mascotas);
$idExistente  = (string) ($old['id_mascota_existente'] ?? '');
$mostrarNueva = !$usaExistente || $idExistente === '';
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

    .pb-section {
        font-family: 'Fraunces', serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--pb-text);
        margin: 1.75rem 0 0.9rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--pb-border);
    }

    .pb-btn-outline {
        background: transparent;
        color: var(--pb-violet);
        border: 1.5px solid var(--pb-violet);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
    }

    .pb-btn-outline:hover { background: var(--pb-bg); }
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="auth-card"
                 style="background: var(--pb-surface); border: 1px solid var(--pb-border); border-radius: var(--pb-radius); padding: 2.25rem 2rem;">

                <h1 class="pb-page-title">
                    <i class="bi <?= $t['icono'] ?> me-1" style="color: var(--pb-violet);"></i>
                    <?= htmlspecialchars($t['titulo'], ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <div class="pb-page-subtitle"><?= htmlspecialchars($t['subtitulo'], ENT_QUOTES, 'UTF-8') ?></div>

                <!-- El tipo va en la URL: el usuario no puede editarlo. -->
                <form id="formCrearEvento" action="<?= APP_URL ?>/evento/crear/<?= $slug ?>" method="POST" novalidate>
                    <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

                    <!-- ── Mascota ─────────────────────────────────────── -->
                    <div class="pb-section" style="margin-top:0; padding-top:0; border-top:none;">Mascota</div>

                    <?php if ($usaExistente): ?>
                        <div class="mb-3">
                            <label class="pb-label" for="id_mascota_existente">¿Cuál de tus mascotas?</label>
                            <select class="pb-input" id="id_mascota_existente" name="id_mascota_existente">
                                <option value="">Otra mascota (cargar datos nuevos)</option>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?= (int) $m['Id_Mascota'] ?>"
                                        <?= $idExistente === (string) $m['Id_Mascota'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['Nombre'] ?: 'Sin nombre', ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($m['Especie'], ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div id="bloqueMascotaNueva" <?= $mostrarNueva ? '' : 'style="display:none;"' ?>>

                        <div class="mb-3">
                            <label class="pb-label" for="nombre">Nombre</label>
                            <input class="pb-input" type="text" id="nombre" name="nombre"
                                value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Ej: Firulais" autocomplete="off">
                            <div class="pb-hint">Opcional. Si no lo sabés, dejalo vacío.</div>
                        </div>

                        <div class="mb-3">
                            <label class="pb-label" for="especie">Especie</label>
                            <select class="pb-input" id="especie" name="especie">
                                <option value="" disabled <?= empty($old['especie']) ? 'selected' : '' ?>>
                                    Seleccioná una especie
                                </option>
                                <?php foreach ($especies as $especie): ?>
                                    <option value="<?= (int) $especie['Id_Especie'] ?>"
                                        <?= (string) ($old['especie'] ?? '') === (string) $especie['Id_Especie'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($especie['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="pb-label" for="raza">Raza</label>
                            <select class="pb-input" id="raza" name="raza">
                                <option value="">Raza (opcional)</option>
                                <?php foreach ($razas as $raza): ?>
                                    <option value="<?= (int) $raza['Id_Raza'] ?>"
                                        data-especie="<?= (int) $raza['Id_Especie'] ?>"
                                        <?= (string) ($old['raza'] ?? '') === (string) $raza['Id_Raza'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($raza['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pb-hint">Se muestran las razas de la especie seleccionada.</div>
                        </div>

                        <div class="mb-3">
                            <label class="pb-label" for="color">Color</label>
                            <input class="pb-input" type="text" id="color" name="color"
                                value="<?= htmlspecialchars($old['color'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Ej: Marrón con manchas blancas" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label class="pb-label" for="descripcion_fisica">Descripción física</label>
                            <textarea class="pb-input" id="descripcion_fisica" name="descripcion_fisica" rows="2"
                                placeholder="Tamaño, señas particulares..."><?= htmlspecialchars($old['descripcion_fisica'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            <div class="pb-hint">Opcional</div>
                        </div>

                    </div>

                    <!-- ── Detalle del reporte ─────────────────────────── -->
                    <div class="pb-section">Detalle</div>

                    <div class="mb-3">
                        <label class="pb-label" for="descripcion">Descripción</label>
                        <textarea class="pb-input" id="descripcion" name="descripcion" rows="4" required
                            placeholder="<?= htmlspecialchars($t['desc'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($old['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="pb-hint">Este texto se muestra en la publicación del feed.</div>
                    </div>

                    <!-- ── Ubicación ───────────────────────────────────── -->
                    <div class="pb-section">Ubicación</div>

                    <div class="mb-3">
                        <button type="button" class="pb-btn-outline" id="btnUbicacion">
                            <i class="bi bi-geo-alt me-1"></i>Usar mi ubicación actual
                        </button>
                        <span class="pb-hint ms-2" id="estadoUbicacion"></span>
                        <input type="hidden" id="latitud"  name="latitud"
                               value="<?= htmlspecialchars($old['latitud'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" id="longitud" name="longitud"
                               value="<?= htmlspecialchars($old['longitud'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="pb-label" for="direccion">Dirección o referencia</label>
                        <input class="pb-input" type="text" id="direccion" name="direccion"
                            value="<?= htmlspecialchars($old['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Ej: Plaza San Martín, cerca de la fuente" autocomplete="off">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="pb-label" for="ciudad">Ciudad</label>
                            <input class="pb-input" type="text" id="ciudad" name="ciudad"
                                value="<?= htmlspecialchars($old['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="pb-label" for="provincia">Provincia</label>
                            <input class="pb-input" type="text" id="provincia" name="provincia"
                                value="<?= htmlspecialchars($old['provincia'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="pb-label" for="pais">País</label>
                            <input class="pb-input" type="text" id="pais" name="pais"
                                value="<?= htmlspecialchars($old['pais'] ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="pb-btn">
                        <i class="bi bi-send me-1"></i><?= htmlspecialchars($t['boton'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>/feed"
                       style="font-size:0.87rem; color:var(--pb-violet); font-weight:600; text-decoration:none;">
                        Cancelar y volver al feed
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
(function () {
    // ── Mostrar u ocultar los campos de mascota nueva ────────────────────
    const selectExistente = document.getElementById('id_mascota_existente');
    const bloqueNueva     = document.getElementById('bloqueMascotaNueva');

    if (selectExistente) {
        selectExistente.addEventListener('change', function () {
            bloqueNueva.style.display = selectExistente.value === '' ? '' : 'none';
        });
    }

    // ── Filtrar razas según la especie elegida (igual que mascota/crear) ─
    const especieSelect = document.getElementById('especie');
    const razaSelect    = document.getElementById('raza');

    function filtrarRazas() {
        const especie = especieSelect.value;

        Array.from(razaSelect.options).forEach(function (opt) {
            if (opt.value === '') {
                return;
            }

            const coincide = opt.dataset.especie === especie;
            opt.hidden = !coincide;

            if (!coincide && opt.selected) {
                opt.selected = false;
                razaSelect.value = '';
            }
        });
    }

    especieSelect.addEventListener('change', filtrarRazas);
    filtrarRazas();

    // ── Geolocalización del navegador ────────────────────────────────────
    const btnUbicacion = document.getElementById('btnUbicacion');
    const estado       = document.getElementById('estadoUbicacion');
    const latitud      = document.getElementById('latitud');
    const longitud     = document.getElementById('longitud');

    btnUbicacion.addEventListener('click', function () {
        if (!navigator.geolocation) {
            estado.textContent = 'Tu navegador no permite obtener la ubicación.';
            return;
        }

        estado.textContent = 'Buscando tu ubicación...';

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                latitud.value  = pos.coords.latitude.toFixed(7);
                longitud.value = pos.coords.longitude.toFixed(7);
                estado.textContent = 'Ubicación detectada.';
            },
            function () {
                estado.textContent = 'No pudimos obtenerla. Completá la dirección a mano.';
            }
        );
    });
})();
</script>

<?php
$content = ob_get_clean();
$titulo  = $t['titulo'];
require_once APP_PATH . '/views/layouts/main.php';
?>
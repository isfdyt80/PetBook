<?php
use App\Core\Session;
ob_start();


$old = $old ?? [];
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
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="auth-card"
                 style="background: var(--pb-surface); border: 1px solid var(--pb-border); border-radius: var(--pb-radius); padding: 2.25rem 2rem;">

                <h1 class="pb-page-title">Registrar mascota</h1>
                <div class="pb-page-subtitle">Completá los datos de tu mascota</div>

                <form action="<?= APP_URL ?>/mascota/crear" method="POST" novalidate>
                    <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

                    <div class="mb-3">
                        <label class="pb-label" for="nombre">Nombre</label>
                        <input class="pb-input" type="text" id="nombre" name="nombre"
                            value="<?= htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Ej: Firulais" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label class="pb-label" for="especie">Especie</label>
                        <select class="pb-input" id="especie" name="especie" required>
                            <option value="" disabled
                                <?= empty($old['especie']) ? 'selected' : '' ?>>
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

                    <div class="mb-4">
                        <label class="pb-label" for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input class="pb-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            value="<?= htmlspecialchars($old['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="pb-hint">Opcional</div>
                    </div>

                    <button type="submit" class="pb-btn">
                        <i class="bi bi-paw me-1"></i>Registrar mascota
                    </button>
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
(function () {
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
})();
</script>

<?php
$content = ob_get_clean();
$titulo  = 'Registrar mascota';
require_once APP_PATH . '/views/layouts/main.php';
?>
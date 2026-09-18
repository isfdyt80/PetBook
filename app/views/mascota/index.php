<?php
use App\Core\Session;
ob_start();
?>

<style>
    .pb-page-title {
        font-family: 'Fraunces', serif;
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--pb-text);
        letter-spacing: -0.5px;
    }

    .pb-card-mascota { transition: border-color 0.15s, box-shadow 0.15s; }
    .pb-card-mascota:hover {
        border-color: var(--pb-violet);
        box-shadow: 0 4px 14px rgba(124, 111, 205, 0.18);
    }

    .pb-empty-emoji { font-size: 3rem; line-height: 1; }

    .pb-list-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--pb-muted);
        margin-bottom: 0.15rem;
    }

    .pb-list-value { font-size: 0.95rem; color: var(--pb-text); margin-bottom: 0; }
</style>

<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="pb-page-title mb-0">Mis mascotas</h1>

        <a href="<?= APP_URL ?>/mascota/crear" class="btn"
           style="background: var(--pb-violet); color: #fff; border: none; font-weight: 600;">
            <i class="bi bi-plus-circle me-1"></i>Registrar mascota
        </a>
    </div>

    <?php if (empty($mascotas)): ?>

        <!-- Estado vacío -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">

                <div class="pb-empty-emoji mb-3">
                    <span role="img" aria-label="Huella de mascota">🐾</span>
                </div>

                <h2 class="h3 mb-3">Todavía no registraste mascotas</h2>

                <p class="text-muted mb-4">
                    Registrá tu primera mascota para poder usarla
                    en eventos y publicaciones de Petbook.
                </p>

                <a href="<?= APP_URL ?>/mascota/crear" class="btn"
                   style="background: var(--pb-violet); color: #fff; border: none; font-weight: 600;">
                    <i class="bi bi-paw me-1"></i>Registrar una mascota
                </a>

            </div>
        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($mascotas as $mascota): ?>
                <?php
                    $fechaNacimiento = $mascota['Fecha_Nacimiento'] ?? null;
                    $fechaFormateada = !empty($fechaNacimiento)
                        ? date('d/m/Y', strtotime($fechaNacimiento))
                        : null;
                ?>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm pb-card-mascota">
                        <div class="card-body">

                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <h5 class="card-title mb-0">
                                    <?= htmlspecialchars($mascota['Nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </h5>

                                <span class="badge ms-2"
                                      style="background: var(--pb-violet); color: #fff;">
                                    <?= htmlspecialchars($mascota['Especie'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <div class="mt-3">
                                <div class="pb-list-label">Raza</div>
                                <p class="pb-list-value">
                                    <?= !empty($mascota['Raza'])
                                        ? htmlspecialchars($mascota['Raza'], ENT_QUOTES, 'UTF-8')
                                        : '<span class="text-muted">Sin raza</span>' ?>
                                </p>
                            </div>

                            <div>
                                <div class="pb-list-label">Fecha de nacimiento</div>
                                <p class="pb-list-value">
                                    <?= $fechaFormateada
                                        ? htmlspecialchars($fechaFormateada, ENT_QUOTES, 'UTF-8')
                                        : '<span class="text-muted">—</span>' ?>
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
$titulo  = 'Mis mascotas';
require_once APP_PATH . '/views/layouts/main.php';
?>
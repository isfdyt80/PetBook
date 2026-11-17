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

    .pb-pet-avatar {
        background: #F3F1FE;
        border-radius: var(--pb-radius) var(--pb-radius) 0 0;
    }

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

    .pb-link {
        font-size: 0.87rem;
        color: var(--pb-violet);
        font-weight: 600;
        text-decoration: none;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm overflow-hidden">

                <div class="pb-pet-avatar text-center py-4">
                    <i class="bi bi-paw" style="font-size: 3rem; color: var(--pb-violet);"></i>
                </div>

                <div class="card-body p-4">

                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <h1 class="pb-page-title mb-0">
                            <?= htmlspecialchars($mascota['Nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?>
                        </h1>

                        <span class="badge ms-2" style="background: var(--pb-violet); color: #fff;">
                            <?= htmlspecialchars($mascota['Especie'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="row g-4">

                        <div class="col-6">
                            <div class="pb-list-label">Raza</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Raza'])
                                    ? htmlspecialchars($mascota['Raza'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">Sin raza</span>' ?>
                            </p>
                        </div>

                        <div class="col-6">
                            <div class="pb-list-label">Color</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Color'])
                                    ? htmlspecialchars($mascota['Color'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </p>
                        </div>

                        <div class="col-6">
                            <div class="pb-list-label">Tamaño</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Tamaño'])
                                    ? htmlspecialchars($mascota['Tamaño'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </p>
                        </div>

                        <div class="col-6">
                            <div class="pb-list-label">Sexo</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Sexo'])
                                    ? htmlspecialchars($mascota['Sexo'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </p>
                        </div>

                        <div class="col-6">
                            <div class="pb-list-label">Fecha de nacimiento</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Fecha_Nacimiento'])
                                    ? date('d/m/Y', strtotime($mascota['Fecha_Nacimiento']))
                                    : '<span class="text-muted">—</span>' ?>
                            </p>
                        </div>

                        <div class="col-6">
                            <div class="pb-list-label">Edad aproximada</div>
                            <p class="pb-list-value">
                                <?= !empty($mascota['Edad_Aproximada'])
                                    ? htmlspecialchars($mascota['Edad_Aproximada'], ENT_QUOTES, 'UTF-8')
                                    : '<span class="text-muted">—</span>' ?>
                            </p>
                        </div>

                        <?php if (!empty($mascota['Descripcion_Fisica'])) : ?>
                            <div class="col-12">
                                <div class="pb-list-label">Descripción física</div>
                                <p class="pb-list-value">
                                    <?= htmlspecialchars($mascota['Descripcion_Fisica'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="d-flex align-items-center justify-content-between mt-4 pt-3"
                         style="border-top: 1px solid var(--pb-border);">
                        <a href="<?= APP_URL ?>/mascota/<?= (int) $mascota['Id_Mascota'] ?>/editar" class="pb-link">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>

                        <a href="<?= APP_URL ?>/mascota" class="pb-link">
                            <i class="bi bi-arrow-left me-1"></i>Mis mascotas
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Mascota';
require_once APP_PATH . '/views/layouts/main.php';
?>
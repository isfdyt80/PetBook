<?php
ob_start();
?>

<style>
    .my-posts-wrap { max-width:900px; margin:2rem auto; padding:0 1rem; }
    .my-post-card { background:var(--pb-surface); border:1px solid var(--pb-border); border-radius:var(--pb-radius); padding:1.1rem 1.25rem; margin-bottom:.8rem; }
    .my-post-title { color:var(--pb-text); font-weight:600; margin:0; }
    .my-post-meta { color:var(--pb-muted); font-size:.82rem; }
    .my-post-badge { display:inline-block; border-radius:999px; padding:.22rem .6rem; font-size:.7rem; font-weight:700; text-transform:uppercase; background:#F3F1FE; color:var(--pb-violet-d); }
    .my-post-badge.archived { background:#F1F1F4; color:#625D70; }
    .my-post-link { color:var(--pb-violet-d); font-size:.84rem; font-weight:600; text-decoration:none; white-space:nowrap; }
</style>

<div class="my-posts-wrap">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1" style="font-family:'Fraunces',serif;">Mis publicaciones</h1>
            <p class="mb-0" style="color:var(--pb-muted); font-size:.9rem;">Eventos actuales y anteriores, con su estado más reciente.</p>
        </div>
        <a href="<?= APP_URL ?>/evento/crear/perdida" class="btn btn-sm" style="background:var(--pb-violet);color:#fff;">
            <i class="bi bi-plus-lg me-1"></i>Nueva publicación
        </a>
    </div>

    <?php if (empty($publicaciones)): ?>
        <div class="my-post-card text-center py-5">
            <i class="bi bi-journal-text" style="font-size:2rem;color:var(--pb-violet);"></i>
            <p class="fw-semibold mt-2 mb-1">Todavía no publicaste eventos</p>
            <p class="my-post-meta mb-3">Cuando crees uno, vas a poder seguir su estado desde acá.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a class="my-post-link" href="<?= APP_URL ?>/evento/crear/perdida">Reportar pérdida</a>
                <a class="my-post-link" href="<?= APP_URL ?>/evento/crear/encontrada">Reportar encontrada</a>
                <a class="my-post-link" href="<?= APP_URL ?>/evento/crear/adopcion">Publicar adopción</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($publicaciones as $publicacion): ?>
            <?php $archivada = ($publicacion['Estado_Publicacion'] ?? '') !== 'ACTIVA'; ?>
            <article class="my-post-card">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <span class="my-post-badge <?= $archivada ? 'archived' : '' ?>">
                                <?= htmlspecialchars($publicacion['Estado_Publicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="my-post-meta"><?= htmlspecialchars($publicacion['Tipo_Evento'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <h2 class="my-post-title h6 mb-1">
                            <?= htmlspecialchars($publicacion['Nombre_Mascota'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>
                            <span class="my-post-meta fw-normal">· evento <?= htmlspecialchars($publicacion['Estado_Evento'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </h2>
                        <p class="mb-2" style="font-size:.9rem;white-space:pre-line;">
                            <?= htmlspecialchars($publicacion['Contenido'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <time class="my-post-meta" datetime="<?= htmlspecialchars($publicacion['Fecha_Publicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            Publicada el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($publicacion['Fecha_Publicacion'])), ENT_QUOTES, 'UTF-8') ?>
                        </time>
                    </div>
                    <a class="my-post-link" href="<?= APP_URL ?>/evento/<?= (int) $publicacion['Id_Evento'] ?>">
                        Ver evento e historial <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$titulo = 'Mis publicaciones';
require_once APP_PATH . '/views/layouts/main.php';
?>

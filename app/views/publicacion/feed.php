<?php
ob_start();
?>

<style>
    .feed-wrap {
        max-width: 760px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .feed-title {
        font-family: 'Fraunces', serif;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--pb-text);
    }

    .feed-empty {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        padding: 4rem 2rem;
        text-align: center;
    }

    .feed-empty-icon {
        font-size: 4rem;
        color: var(--pb-violet);
        margin-bottom: 1rem;
    }

    .feed-empty-title {
        font-size: 1.35rem;
        font-weight: 600;
        color: var(--pb-text);
        margin-bottom: .5rem;
    }

    .feed-empty-subtitle {
        color: var(--pb-muted);
        font-size: .95rem;
    }

    .post-card {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        padding: 1.25rem;
        margin-bottom: 1rem;
    }

    .post-type {
        color: var(--pb-violet);
        font-weight: 600;
        font-size: .9rem;
    }

    .post-pet {
        font-size: 1.1rem;
        font-weight: 600;
        margin: .4rem 0;
    }

    .post-meta {
        color: var(--pb-muted);
        font-size: .9rem;
    }
</style>

<div class="feed-wrap">

    <h1 class="feed-title">
        <?= htmlspecialchars($titulo ?? 'Feed', ENT_QUOTES, 'UTF-8') ?>
    </h1>

    <?php if (empty($publicaciones)): ?>

        <div class="feed-empty">
            <div class="feed-empty-icon">
                <i class="bi bi-chat-heart"></i>
            </div>

            <div class="feed-empty-title">
                Todavía no hay publicaciones
            </div>

            <div class="feed-empty-subtitle">
                Cuando los usuarios comiencen a compartir eventos y mascotas,
                aparecerán aquí.
            </div>
        </div>

    <?php else: ?>

        <?php foreach ($publicaciones as $publicacion): ?>

            <div class="post-card">

                <div class="post-type">
                    <?= htmlspecialchars($publicacion['tipo_evento'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="post-pet">
                    <?= htmlspecialchars($publicacion['nombre_mascota'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="post-meta">
                    📍 <?= htmlspecialchars($publicacion['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="post-meta">
                     <span role="img" aria-label="Fecha">📅</span> <?= htmlspecialchars($publicacion['Fecha_Publicacion'] ?? '', ENT_QUOTES, 'UTF-8') ?>                </div>

                <div class="post-meta mt-2">
                    <span role="img" aria-label="Reacciones">❤️</span> <?= htmlspecialchars($publicacion['reacciones'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                    ·
                    <span role="img" aria-label="Comentarios">💬</span> <?= htmlspecialchars($publicacion['comentarios'] ?? '0', ENT_QUOTES, 'UTF-8') ?>
                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
$titulo  = $titulo ?? 'Feed';
require_once APP_PATH . '/views/layouts/main.php';
?>
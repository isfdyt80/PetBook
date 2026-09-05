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
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--pb-text);
        margin-bottom: 1.5rem;
        letter-spacing: -0.5px;
    }

    .feed-card {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }

    .feed-card-meta {
        font-size: 0.82rem;
        color: var(--pb-muted);
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .feed-card-body {
        font-size: 0.97rem;
        color: var(--pb-text);
        white-space: pre-wrap;
        word-break: break-word;
    }

    .feed-empty {
        text-align: center;
        color: var(--pb-muted);
        padding: 3rem 1rem;
        font-size: 0.95rem;
    }
</style>

<div class="feed-wrap">
    <div class="feed-title">Feed</div>

    <?php if (empty($publicaciones)): ?>
        <div class="feed-empty">
            <i class="bi bi-journal-text" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>
            Todavía no hay publicaciones. ¡Sé el primero!
        </div>
    <?php else: ?>
        <?php foreach ($publicaciones as $pub): ?>
            <div class="feed-card">
                <div class="feed-card-meta">
                    <i class="bi bi-clock"></i>
                    <?= htmlspecialchars(
                            date('d/m/Y H:i', strtotime($pub['Fecha_Publicacion'])),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    <?php if ($pub['Editado']): ?>
                        <span title="Editado"><i class="bi bi-pencil"></i> editado</span>
                    <?php endif; ?>
                </div>
                <div class="feed-card-body">
                    <?= htmlspecialchars($pub['Contenido'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>

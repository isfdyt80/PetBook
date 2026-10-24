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
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-bottom: 0.9rem;
    }

    .feed-badge {
        display: inline-block;
        padding: 0.22rem 0.7rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        background: var(--pb-bg);
        border: 1px solid var(--pb-border);
        color: var(--pb-text);
    }

    .feed-badge-activo {
        background: #F0FDF4;
        border-color: #BBF7D0;
        color: #166534;
    }

    .feed-meta-date {
        font-size: 0.82rem;
        color: var(--pb-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .feed-meta-edited {
        font-size: 0.78rem;
        color: var(--pb-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .feed-card-pet {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        margin-bottom: 0.6rem;
    }

    .feed-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--pb-bg);
        border: 1px solid var(--pb-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .feed-pet-name {
        font-weight: 600;
        font-size: 1.02rem;
        color: var(--pb-text);
        margin-bottom: 0;
    }

    .feed-pet-specie {
        font-size: 0.8rem;
        color: var(--pb-muted);
    }

    .feed-location {
        font-size: 0.86rem;
        color: var(--pb-muted);
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.7rem;
    }

    .feed-content {
        font-size: 0.97rem;
        color: var(--pb-text);
        white-space: pre-wrap;
        word-break: break-word;
        margin-bottom: 1rem;
    }

    .feed-card-footer {
        display: flex;
        gap: 1.25rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--pb-border);
        font-size: 0.86rem;
        color: var(--pb-muted);
    }

    .feed-empty {
        text-align: center;
        color: var(--pb-muted);
        padding: 3rem 1rem;
        border: 1px dashed var(--pb-border);
        border-radius: var(--pb-radius);
        background: var(--pb-surface);
    }

    .feed-empty-title {
        font-family: 'Fraunces', serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--pb-text);
        margin-bottom: 0.4rem;
    }
</style>

<div class="feed-wrap">
    <h1 class="feed-title">Feed</h1>

    <?php if (empty($publicaciones)): ?>

        <!-- Estado vacío -->
        <div class="feed-empty">
            <i class="bi bi-journal-text" style="font-size:2.4rem; display:block; margin-bottom:1rem; color:var(--pb-violet);"></i>

            <p class="feed-empty-title">Todavía no hay publicaciones</p>

            <p class="mb-0">
                Cuando los usuarios compartan eventos, actividades o novedades
                de sus mascotas, aparecerán aquí.
            </p>
        </div>

    <?php else: ?>

        <?php foreach ($publicaciones as $pub): ?>
            <article class="feed-card">

                <div class="feed-card-meta">
                    <span class="feed-badge">
                        <?= htmlspecialchars($pub['Tipo_Evento'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <?php if (!empty($pub['Estado_Evento'])): ?>
                        <span class="feed-badge feed-badge-activo">
                            <?= htmlspecialchars($pub['Estado_Evento'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>

                    <time class="feed-meta-date" datetime="<?= htmlspecialchars($pub['Fecha_Publicacion'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-clock"></i>
                        <?= htmlspecialchars(
                            date('d/m/Y H:i', strtotime($pub['Fecha_Publicacion'])),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </time>

                    <?php if ($pub['Editado']): ?>
                        <span class="feed-meta-edited" title="Editado">
                            <i class="bi bi-pencil"></i> editado
                        </span>
                    <?php endif; ?>
                </div>

                <div class="feed-card-pet">
                    <span class="feed-avatar" role="img" aria-label="Mascota">
                        🐾
                    </span>

                    <div>
                        <p class="feed-pet-name">
                            <?= htmlspecialchars($pub['Nombre_Mascota'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <?php if (!empty($pub['Nombre_Especie'])): ?>
                            <div class="feed-pet-specie">
                                <?= htmlspecialchars($pub['Nombre_Especie'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($pub['Ubicacion_Texto'])): ?>
                    <div class="feed-location">
                        <i class="bi bi-geo-alt"></i>
                        <?= htmlspecialchars($pub['Ubicacion_Texto'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pub['Contenido'])): ?>
                    <p class="feed-content">
                        <?= htmlspecialchars($pub['Contenido'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <div class="feed-card-footer">
                    <span>
                        <i class="bi bi-heart"></i>
                        <?= (int) ($pub['Num_Reacciones'] ?? 0) ?> reacciones
                    </span>

                    <span>
                        <i class="bi bi-chat"></i>
                        <?= (int) ($pub['Num_Comentarios'] ?? 0) ?> comentarios
                    </span>
                </div>

            </article>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Feed';
require_once APP_PATH . '/views/layouts/main.php';
?>
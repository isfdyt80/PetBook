<?php
/** @var array $evento  Resultado de EventoMascota::detalle() */
ob_start();

$tipo   = $evento['Tipo_Evento'];
$estado = $evento['Estado_Evento'];

// El tipo es inmutable: se muestra solo como badge, sin ningún control de edición.
$estilosTipo = [
    'PERDIDA'    => ['clase' => 'ev-badge-perdida',    'icono' => 'bi-search-heart'],
    'ENCONTRADA' => ['clase' => 'ev-badge-encontrada', 'icono' => 'bi-binoculars'],
    'ADOPCION'   => ['clase' => 'ev-badge-adopcion',   'icono' => 'bi-house-heart'],
];
$estiloTipo  = $estilosTipo[$tipo] ?? ['clase' => '', 'icono' => 'bi-tag'];
$claseEstado = $estado === 'ACTIVO' ? 'ev-badge-activo' : '';

$nombreMascota = $evento['Nombre_Mascota'] ?: 'Sin nombre';
$especieRaza   = implode(' · ', array_filter([$evento['Nombre_Especie'], $evento['Nombre_Raza']]));

$ubicacionTexto = implode(', ', array_filter([
    $evento['Direccion'],
    $evento['Ciudad'],
    $evento['Provincia'],
    $evento['Pais'],
]));
$tieneCoordenadas = $evento['Latitud'] !== null && $evento['Longitud'] !== null;
?>

<style>
    .ev-wrap {
        max-width: 760px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .ev-back {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.87rem;
        font-weight: 600;
        color: var(--pb-violet);
        text-decoration: none;
        margin-bottom: 1rem;
    }

    .ev-card {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        padding: 1.75rem 1.75rem;
    }

    .ev-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-bottom: 1.25rem;
    }

    .ev-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
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

    .ev-badge-perdida    { background: #FEF2F2; border-color: #FECACA; color: #991B1B; }
    .ev-badge-encontrada { background: #EFF6FF; border-color: #BFDBFE; color: #1E40AF; }
    .ev-badge-adopcion   { background: #F5F3FF; border-color: #DDD6FE; color: #5B21B6; }
    .ev-badge-activo     { background: #F0FDF4; border-color: #BBF7D0; color: #166534; }

    .ev-date {
        font-size: 0.82rem;
        color: var(--pb-muted);
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .ev-pet {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    .ev-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--pb-bg);
        border: 1px solid var(--pb-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .ev-pet-name {
        font-family: 'Fraunces', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--pb-text);
        letter-spacing: -0.5px;
        margin: 0;
    }

    .ev-pet-sub {
        font-size: 0.85rem;
        color: var(--pb-muted);
    }

    .ev-section {
        font-family: 'Fraunces', serif;
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--pb-text);
        margin: 1.5rem 0 0.6rem;
        padding-top: 1.1rem;
        border-top: 1px solid var(--pb-border);
    }

    .ev-details {
        display: grid;
        grid-template-columns: max-content 1fr;
        gap: 0.35rem 1rem;
        font-size: 0.92rem;
        margin: 0;
    }

    .ev-details dt { color: var(--pb-muted); font-weight: 600; }
    .ev-details dd { margin: 0; color: var(--pb-text); }

    .ev-content {
        font-size: 0.97rem;
        color: var(--pb-text);
        white-space: pre-wrap;
        word-break: break-word;
        margin: 0;
    }

    .ev-location {
        font-size: 0.92rem;
        color: var(--pb-text);
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .ev-location a {
        color: var(--pb-violet);
        font-weight: 600;
        text-decoration: none;
    }

    .ev-muted { color: var(--pb-muted); }

    .ev-footer {
        margin-top: 1.5rem;
        padding-top: 0.9rem;
        border-top: 1px solid var(--pb-border);
        font-size: 0.86rem;
        color: var(--pb-muted);
    }
</style>

<div class="ev-wrap">

    <a href="<?= APP_URL ?>/feed" class="ev-back">
        <i class="bi bi-arrow-left"></i> Volver al feed
    </a>

    <article class="ev-card">

        <!-- Tipo, estado y fecha -->
        <div class="ev-meta">
            <span class="ev-badge <?= $estiloTipo['clase'] ?>">
                <i class="bi <?= $estiloTipo['icono'] ?>"></i>
                <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <span class="ev-badge <?= $claseEstado ?>">
                <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <time class="ev-date" datetime="<?= htmlspecialchars($evento['Fecha_Creacion'], ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-clock"></i>
                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($evento['Fecha_Creacion'])), ENT_QUOTES, 'UTF-8') ?>
            </time>
        </div>

        <!-- Mascota -->
        <div class="ev-pet">
            <span class="ev-avatar" role="img" aria-label="Mascota">🐾</span>
            <div>
                <h1 class="ev-pet-name"><?= htmlspecialchars($nombreMascota, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if ($especieRaza !== ''): ?>
                    <div class="ev-pet-sub"><?= htmlspecialchars($especieRaza, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($evento['Color']) || !empty($evento['Descripcion_Fisica'])): ?>
            <dl class="ev-details">
                <?php if (!empty($evento['Color'])): ?>
                    <dt>Color</dt>
                    <dd><?= htmlspecialchars($evento['Color'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>

                <?php if (!empty($evento['Descripcion_Fisica'])): ?>
                    <dt>Descripción física</dt>
                    <dd><?= htmlspecialchars($evento['Descripcion_Fisica'], ENT_QUOTES, 'UTF-8') ?></dd>
                <?php endif; ?>
            </dl>
        <?php endif; ?>

        <!-- Descripción del evento -->
        <h2 class="ev-section">Detalle</h2>
        <?php if (!empty($evento['Descripcion'])): ?>
            <p class="ev-content"><?= htmlspecialchars($evento['Descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <p class="ev-content ev-muted">Sin descripción.</p>
        <?php endif; ?>

        <!-- Ubicación -->
        <h2 class="ev-section">Ubicación</h2>
        <div class="ev-location">
            <?php if ($ubicacionTexto !== '' || $tieneCoordenadas): ?>
                <?php if ($ubicacionTexto !== ''): ?>
                    <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ubicacionTexto, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <?php if ($tieneCoordenadas): ?>
                    <a href="https://www.google.com/maps?q=<?= htmlspecialchars($evento['Latitud'] . ',' . $evento['Longitud'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer">
                        Ver en el mapa <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <span class="ev-muted">Ubicación no indicada.</span>
            <?php endif; ?>
        </div>

        <div class="ev-footer">
            Publicado por <strong><?= htmlspecialchars($evento['Nombre_Publicador'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>

    </article>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Evento: ' . $nombreMascota;
require_once APP_PATH . '/views/layouts/main.php';
?>
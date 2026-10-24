<?php
ob_start();
?>

<style>
    .pb-error {
        min-height: calc(100vh - 150px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .pb-error-card {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        max-width: 460px;
        width: 100%;
        padding: 3rem 2.5rem;
        text-align: center;
    }

    .pb-error-icon {
        font-size: 2.25rem;
        color: var(--pb-violet);
        margin-bottom: 0.4rem;
    }

    .pb-error-code {
        font-family: 'Fraunces', serif;
        font-size: 4.25rem;
        font-weight: 700;
        line-height: 1;
        color: var(--pb-violet);
        margin-bottom: 0.35rem;
        letter-spacing: -1px;
    }

    .pb-error-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--pb-text);
        margin-bottom: 0.6rem;
    }

    .pb-error-text {
        font-size: 0.95rem;
        color: var(--pb-muted);
        margin-bottom: 1.75rem;
    }

    .pb-btn {
        display: inline-block;
        background: var(--pb-violet);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.68rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s;
    }

    .pb-btn:hover { background: var(--pb-violet-d); color: #fff; }
</style>

<div class="pb-error">
    <div class="pb-error-card">
        <div class="pb-error-icon"><i class="bi bi-emoji-dizzy"></i></div>
        <div class="pb-error-code">500</div>
        <div class="pb-error-title">Ups, algo salió mal</div>
        <p class="pb-error-text">Ocurrió un error inesperado en el servidor. Probá de nuevo en unos minutos y, si el problema continúa, contactá al administrador.</p>
        <a href="<?= APP_URL ?>/feed" class="pb-btn">Ir al inicio</a>
    </div>
</div>

<?php
$content = ob_get_clean();
$titulo  = '500 · Error interno';
require_once APP_PATH . '/views/layouts/main.php';
?>
<?php

use App\Core\Session;

ob_start();
?>

<style>
    .auth-wrap {
        min-height: calc(100vh - 130px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .auth-card {
        background: var(--pb-white);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        box-shadow: var(--pb-shadow);
        width: 100%;
        max-width: 420px;
        padding: 2.5rem 2rem;
    }

    .auth-title {
        font-family: 'Fraunces', serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--pb-dark);
        margin-bottom: 0.25rem;
        letter-spacing: -0.5px;
    }

    .auth-subtitle {
        font-size: 0.9rem;
        color: var(--pb-muted);
        margin-bottom: 2rem;
    }

    .pb-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--pb-dark);
        margin-bottom: 0.35rem;
    }

    .pb-input {
        border: 1.5px solid var(--pb-border);
        border-radius: 8px;
        padding: 0.65rem 0.9rem;
        font-size: 0.95rem;
        width: 100%;
        background: var(--pb-cream);
        color: var(--pb-dark);
        transition: border-color 0.15s;
        outline: none;
    }

    .pb-input:focus {
        border-color: var(--pb-amber);
        background: var(--pb-white);
    }

    .pb-btn-primary {
        background: var(--pb-amber);
        color: var(--pb-white);
        border: none;
        border-radius: 8px;
        padding: 0.7rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        transition: background 0.15s;
    }

    .pb-btn-primary:hover {
        background: var(--pb-amber-lt);
    }

    .auth-link {
        font-size: 0.88rem;
        color: var(--pb-muted);
        text-align: center;
        margin-top: 1.25rem;
    }

    .auth-link a {
        color: var(--pb-amber);
        font-weight: 600;
        text-decoration: none;
    }

    .auth-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-title">Bienvenido</div>
        <div class="auth-subtitle">Ingresá a tu cuenta para continuar</div>

        <form action="<?= APP_URL ?>/login" method="POST" novalidate>
            <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

            <div class="mb-3">
                <label class="pb-label" for="email">Email</label>
                <input
                    class="pb-input"
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="pb-label" for="password">Contraseña</label>
                <input
                    class="pb-input"
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="pb-btn-primary">Iniciar sesión</button>
        </form>

        <div class="auth-link">
            ¿No tenés cuenta? <a href="<?= APP_URL ?>/registro">Registrate</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Iniciar sesión';
require_once APP_PATH . '/views/layouts/main.php';
?>
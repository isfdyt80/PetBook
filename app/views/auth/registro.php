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
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        width: 100%;
        max-width: 460px;
        padding: 2.25rem 2rem;
    }

    .auth-title {
        font-family: 'Fraunces', serif;
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--pb-text);
        margin-bottom: 0.2rem;
        letter-spacing: -0.5px;
    }

    .auth-subtitle {
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

    .auth-link {
        font-size: 0.87rem;
        color: var(--pb-muted);
        text-align: center;
        margin-top: 1.1rem;
    }

    .auth-link a { color: var(--pb-violet); font-weight: 600; text-decoration: none; }
    .auth-link a:hover { text-decoration: underline; }
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-title">Crear cuenta</div>
        <div class="auth-subtitle">Completá tus datos para registrarte</div>

        <form action="<?= APP_URL ?>/registro" method="POST" novalidate>
            <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="pb-label" for="nombre">Nombre</label>
                    <input class="pb-input" type="text" id="nombre" name="nombre"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="given-name" required>
                </div>
                <div class="col-6">
                    <label class="pb-label" for="apellido">Apellido</label>
                    <input class="pb-input" type="text" id="apellido" name="apellido"
                        value="<?= htmlspecialchars($_POST['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="family-name" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="pb-label" for="email">Email</label>
                <input class="pb-input" type="email" id="email" name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="email" required>
            </div>

            <div class="mb-4">
                <label class="pb-label" for="password">Contraseña</label>
                <input class="pb-input" type="password" id="password" name="password"
                    autocomplete="new-password" required>
                <div class="pb-hint">Mínimo 8 caracteres</div>
            </div>

            <button type="submit" class="pb-btn">Crear cuenta</button>
        </form>

        <div class="auth-link">
            ¿Ya tenés cuenta? <a href="<?= APP_URL ?>/login">Iniciá sesión</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$titulo  = 'Registro';
require_once APP_PATH . '/views/layouts/main.php';
?>
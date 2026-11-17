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
        gap: 3rem;
        padding: 2rem 1rem;
    }

    /* ── Cat ── */
    .cat-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        user-select: none;
    }

    .cat-svg { width: 160px; height: 160px; }

    /* ── Card ── */
    .auth-card {
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius);
        width: 100%;
        max-width: 400px;
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

    .auth-link {
        font-size: 0.87rem;
        color: var(--pb-muted);
        text-align: center;
        margin-top: 1.1rem;
    }

    .auth-link a { color: var(--pb-violet); font-weight: 600; text-decoration: none; }
    .auth-link a:hover { text-decoration: underline; }

    @media (max-width: 640px) {
        .auth-wrap { flex-direction: column; gap: 1.5rem; }
        .cat-container { order: -1; }
        .cat-svg { width: 110px; height: 110px; }
    }
</style>

<div class="auth-wrap">

    <!-- Gato que sigue el mouse -->
    <div class="cat-container">
        <svg class="cat-svg" id="catFace" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
            <!-- Cabeza -->
            <ellipse cx="80" cy="88" rx="58" ry="54" fill="#EEEAF8" stroke="#C9C1E8" stroke-width="1.5"/>
            <!-- Orejas -->
            <polygon points="30,52 18,18 55,42" fill="#EEEAF8" stroke="#C9C1E8" stroke-width="1.5"/>
            <polygon points="130,52 142,18 105,42" fill="#EEEAF8" stroke="#C9C1E8" stroke-width="1.5"/>
            <!-- Interior orejas -->
            <polygon points="33,50 24,26 50,44" fill="#D5C8F0"/>
            <polygon points="127,50 136,26 110,44" fill="#D5C8F0"/>

            <!-- Ojo izquierdo — esclerótica -->
            <ellipse id="eyeL" cx="58" cy="82" rx="14" ry="14" fill="white" stroke="#C9C1E8" stroke-width="1"/>
            <!-- Pupila izquierda -->
            <circle id="pupilL" cx="58" cy="82" r="6" fill="#3D2E6B"/>
            <!-- Brillo -->
            <circle id="shineL" cx="61" cy="79" r="2.5" fill="white"/>

            <!-- Ojo derecho -->
            <ellipse id="eyeR" cx="102" cy="82" rx="14" ry="14" fill="white" stroke="#C9C1E8" stroke-width="1"/>
            <circle id="pupilR" cx="102" cy="82" r="6" fill="#3D2E6B"/>
            <circle id="shineR" cx="105" cy="79" r="2.5" fill="white"/>

            <!-- Nariz -->
            <polygon points="80,100 76,107 84,107" fill="#C9A8D0"/>
            <!-- Boca -->
            <path d="M76,107 Q80,113 84,107" fill="none" stroke="#C9A8D0" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Bigotes izq -->
            <line x1="62" y1="103" x2="28" y2="98" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
            <line x1="62" y1="107" x2="28" y2="107" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
            <line x1="62" y1="111" x2="28" y2="116" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
            <!-- Bigotes der -->
            <line x1="98" y1="103" x2="132" y2="98" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
            <line x1="98" y1="107" x2="132" y2="107" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
            <line x1="98" y1="111" x2="132" y2="116" stroke="#B0A8CC" stroke-width="1" stroke-linecap="round"/>
        </svg>
    </div>

    <!-- Formulario -->
    <div class="auth-card">
        <div class="auth-title">Bienvenido</div>
        <div class="auth-subtitle">Ingresá a tu cuenta para continuar</div>

        <form action="<?= APP_URL ?>/login" method="POST" novalidate>
            <input type="hidden" name="_csrf" value="<?= Session::csrfToken() ?>">

            <div class="mb-3">
                <label class="pb-label" for="email">Email</label>
                <input class="pb-input" type="email" id="email" name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="email" required>
            </div>

            <div class="mb-4">
                <label class="pb-label" for="password">Contraseña</label>
                <input class="pb-input" type="password" id="password" name="password"
                    autocomplete="current-password" required>
            </div>

            <button type="submit" class="pb-btn">Iniciar sesión</button>
        </form>

        <div class="auth-link">
            ¿No tenés cuenta? <a href="<?= APP_URL ?>/registro">Registrate</a>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/js/auth/gato.js"></script>

<?php
$content = ob_get_clean();
$titulo  = 'Iniciar sesión';
require_once APP_PATH . '/views/layouts/main.php';
?>
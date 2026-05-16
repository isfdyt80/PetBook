<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Fraunces:ital,opsz,wght@0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --pb-cream:    #F5F0E8;
            --pb-dark:     #1A1A1A;
            --pb-amber:    #D4820A;
            --pb-amber-lt: #F0A830;
            --pb-muted:    #6B6560;
            --pb-border:   #E0D8CC;
            --pb-white:    #FFFFFF;
            --pb-radius:   12px;
            --pb-shadow:   0 2px 16px rgba(26,26,26,0.08);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--pb-cream);
            color: var(--pb-dark);
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .pb-navbar {
            background: var(--pb-white);
            border-bottom: 1px solid var(--pb-border);
            padding: 0.75rem 0;
        }

        .pb-brand {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--pb-dark);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .pb-brand span {
            color: var(--pb-amber);
        }

        /* ── Flash messages ── */
        .pb-flash {
            border-radius: var(--pb-radius);
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
        }

        .pb-flash.success { background: #EAF7EE; color: #1A5C2E; }
        .pb-flash.error   { background: #FEF0EE; color: #8B1A10; }
        .pb-flash.warning { background: #FEF8EE; color: #7A4A0A; }
        .pb-flash.info    { background: #EEF4FE; color: #1A3A6E; }

        /* ── Footer ── */
        .pb-footer {
            border-top: 1px solid var(--pb-border);
            padding: 1.25rem 0;
            font-size: 0.82rem;
            color: var(--pb-muted);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="pb-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="<?= APP_URL ?>/feed" class="pb-brand">
                Pet<span>book</span>
            </a>

            <?php if (\App\Core\Auth::check()): ?>
                <?php $usuario = \App\Core\Session::user(); ?>
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size:0.9rem; color:var(--pb-muted);">
                        <?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <a href="<?= APP_URL ?>/logout"
                       style="font-size:0.88rem; color:var(--pb-amber); text-decoration:none; font-weight:500;">
                        Salir
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Flash messages -->
    <?php $flashes = \App\Core\Session::getFlash(); ?>
    <?php if (!empty($flashes)): ?>
        <div class="container mt-3">
            <?php foreach ($flashes as $tipo => $mensaje): ?>
                <div class="pb-flash <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?> d-flex align-items-center gap-2 mb-2">
                    <?php if ($tipo === 'success'): ?>
                        <i class="bi bi-check-circle-fill"></i>
                    <?php elseif ($tipo === 'error'): ?>
                        <i class="bi bi-exclamation-circle-fill"></i>
                    <?php elseif ($tipo === 'warning'): ?>
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-info-circle-fill"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="pb-footer mt-auto">
        <div class="container text-center">
            Petbook &copy; <?= date('Y') ?> — Encontrá y reuní mascotas perdidas
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
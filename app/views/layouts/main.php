<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Fraunces:ital,opsz,wght@0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --pb-bg:       #F8F7FF;
            --pb-surface:  #FFFFFF;
            --pb-border:   #E4E0F5;
            --pb-violet:   #7C6FCD;
            --pb-violet-d: #5A4FB5;
            --pb-text:     #1E1B2E;
            --pb-muted:    #7B748F;
            --pb-radius:   12px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--pb-bg);
            color: var(--pb-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .pb-navbar {
            background: var(--pb-surface);
            border-bottom: 1px solid var(--pb-border);
            padding: 0.75rem 0;
        }

        .pb-brand {
            font-family: 'Fraunces', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--pb-violet);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .pb-flash {
            border-radius: var(--pb-radius);
            border: 1px solid transparent;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .pb-flash.success { background: #F0FDF4; border-color: #BBF7D0; color: #166534; }
        .pb-flash.error   { background: #FFF1F2; border-color: #FECDD3; color: #9F1239; }
        .pb-flash.warning { background: #FFFBEB; border-color: #FDE68A; color: #92400E; }
        .pb-flash.info    { background: #EEF0FF; border-color: #C7D0FF; color: #3730A3; }

        .pb-footer {
            border-top: 1px solid var(--pb-border);
            padding: 1.25rem 0;
            font-size: 0.82rem;
            color: var(--pb-muted);
            margin-top: auto;
        }

        main { flex: 1; }
    </style>
</head>
<body>

    <nav class="pb-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="<?= APP_URL ?>/feed" class="pb-brand">Petbook</a>

            <?php if (\App\Core\Auth::check()): ?>
                <?php $usuario = \App\Core\Session::user(); ?>
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size:0.9rem; color:var(--pb-muted);">
                        <?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <a href="<?= APP_URL ?>/logout"
                       style="font-size:0.88rem; color:var(--pb-violet); text-decoration:none; font-weight:500;">
                        Salir
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <?php $flashes = \App\Core\Session::getFlash(); ?>
    <?php if (!empty($flashes)): ?>
        <div class="container mt-3">
            <?php foreach ($flashes as $tipo => $mensaje): ?>
                <div class="pb-flash <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($tipo === 'success'): ?><i class="bi bi-check-circle-fill"></i>
                    <?php elseif ($tipo === 'error'):   ?><i class="bi bi-exclamation-circle-fill"></i>
                    <?php elseif ($tipo === 'warning'): ?><i class="bi bi-exclamation-triangle-fill"></i>
                    <?php else:                         ?><i class="bi bi-info-circle-fill"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <main><?= $content ?? '' ?></main>

    <footer class="pb-footer">
        <div class="container text-center">
            Petbook &copy; <?= date('Y') ?> — Encontrá y reuní mascotas perdidas
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
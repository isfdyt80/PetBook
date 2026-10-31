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

        .pb-app-main { display: flex; flex: 1; min-width: 0; }
        .pb-sidebar {
            flex: 0 0 225px;
            padding: 1.25rem 0.85rem;
            background: var(--pb-surface);
            border-right: 1px solid var(--pb-border);
        }
        .pb-sidebar-label {
            margin: 0.7rem 0.45rem 0.4rem;
            color: var(--pb-muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .pb-sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.62rem 0.7rem;
            margin-bottom: 0.2rem;
            border-radius: 8px;
            color: var(--pb-text);
            font-size: 0.88rem;
            text-decoration: none;
        }
        .pb-sidebar-link:hover, .pb-sidebar-link.active {
            background: #F3F1FE;
            color: var(--pb-violet-d);
        }
        .pb-main-content { min-width: 0; flex: 1; }

        @media (max-width: 767.98px) {
            .pb-app-main { flex-direction: column; }
            .pb-sidebar {
                flex: none;
                display: flex;
                gap: 0.25rem;
                overflow-x: auto;
                padding: 0.55rem 0.75rem;
                border-right: 0;
                border-bottom: 1px solid var(--pb-border);
            }
            .pb-sidebar-label { display: none; }
            .pb-sidebar-link { flex: 0 0 auto; margin: 0; }
        }
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

    <?php
    $rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($basePath !== '' && str_starts_with((string) $rutaActual, $basePath)) {
        $rutaActual = substr((string) $rutaActual, strlen($basePath));
    }
    $rutaActual = '/' . trim((string) $rutaActual, '/');
    ?>
    <main class="pb-app-main">
        <?php if (\App\Core\Auth::check()): ?>
            <aside class="pb-sidebar" aria-label="Navegación principal">
                <div class="w-100">
                    <div class="pb-sidebar-label">Comunidad</div>
                    <a class="pb-sidebar-link <?= $rutaActual === '/feed' ? 'active' : '' ?>" href="<?= APP_URL ?>/feed">
                        <i class="bi bi-house"></i> Feed
                    </a>
                    <a class="pb-sidebar-link <?= $rutaActual === '/mis-publicaciones' ? 'active' : '' ?>" href="<?= APP_URL ?>/mis-publicaciones">
                        <i class="bi bi-clock-history"></i> Mis publicaciones
                    </a>
                    <div class="pb-sidebar-label">Mis mascotas</div>
                    <a class="pb-sidebar-link <?= str_starts_with((string) $rutaActual, '/mascota') ? 'active' : '' ?>" href="<?= APP_URL ?>/mascota">
                        <i class="bi bi-paw"></i> Mascotas
                    </a>
                    <div class="pb-sidebar-label">Crear publicación</div>
                    <a class="pb-sidebar-link" href="<?= APP_URL ?>/evento/crear/perdida"><i class="bi bi-search-heart"></i> Mascota perdida</a>
                    <a class="pb-sidebar-link" href="<?= APP_URL ?>/evento/crear/encontrada"><i class="bi bi-handbag"></i> Mascota encontrada</a>
                    <a class="pb-sidebar-link" href="<?= APP_URL ?>/evento/crear/adopcion"><i class="bi bi-stars"></i> En adopción</a>
                </div>
            </aside>
        <?php endif; ?>
        <section class="pb-main-content"><?= $content ?? '' ?></section>
    </main>

    <footer class="pb-footer">
        <div class="container text-center">
              — Petbook &copy; <?= date('Y') ?> —       
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

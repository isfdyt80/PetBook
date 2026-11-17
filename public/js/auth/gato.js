/**
 * Petbook — Animación de mirada del gato del login.
 *
 * Mueve las pupilas y brillos del SVG #catFace hacia el cursor,
 * con un desplazamiento máximo de 5px por ojo.
 *
 * Referenciado desde: app/views/auth/login.php
 */
(function () {
    'use strict';

    const svg    = document.getElementById('catFace');
    const pupilL = document.getElementById('pupilL');
    const shineL = document.getElementById('shineL');
    const pupilR = document.getElementById('pupilR');
    const shineR = document.getElementById('shineR');

    // Vista sin el SVG (o con ids renombrados): no hay nada que animar.
    if (!svg || !pupilL || !shineL || !pupilR || !shineR) {
        return;
    }

    const EYE_L = { cx: 58, cy: 82 };
    const EYE_R = { cx: 102, cy: 82 };
    const MAX_OFFSET = 5;
    const SHINE_OFFSET = { x: 3, y: -3 };

    function movePupil(eye, pupil, shine, mouseX, mouseY, rect) {
        const svgW = rect.width;
        const svgH = rect.height;

        // Posición del ojo en coordenadas de pantalla
        const eyeScreenX = rect.left + (eye.cx / 160) * svgW;
        const eyeScreenY = rect.top  + (eye.cy / 160) * svgH;

        const dx = mouseX - eyeScreenX;
        const dy = mouseY - eyeScreenY;
        const dist = Math.sqrt(dx * dx + dy * dy) || 1;

        const factor = Math.min(dist, 60) / 60;
        const ox = (dx / dist) * MAX_OFFSET * factor;
        const oy = (dy / dist) * MAX_OFFSET * factor;

        pupil.setAttribute('cx', eye.cx + ox);
        pupil.setAttribute('cy', eye.cy + oy);
        shine.setAttribute('cx', eye.cx + ox + SHINE_OFFSET.x);
        shine.setAttribute('cy', eye.cy + oy + SHINE_OFFSET.y);
    }

    document.addEventListener('mousemove', function (e) {
        const rect = svg.getBoundingClientRect();
        movePupil(EYE_L, pupilL, shineL, e.clientX, e.clientY, rect);
        movePupil(EYE_R, pupilR, shineR, e.clientX, e.clientY, rect);
    });
})();

/**
 * Petbook — Captura de geolocalización en el formulario de evento.
 *
 * Llena los inputs hidden #latitud / #longitud con la posición del
 * navegador cuando el usuario lo pide explícitamente con #btn-geo.
 *
 * Referenciado desde: app/views/evento/crear.php
 * El <script> debe venir con data-app-url si algún día necesita la base;
 * hoy solo opera sobre ids del propio formulario.
 */
(function () {
    'use strict';

    const btn  = document.getElementById('btn-geo');
    const hint = document.getElementById('geo-hint');
    const lat  = document.getElementById('latitud');
    const lon  = document.getElementById('longitud');

    // Sin bloque de ubicación en esta vista (ADOPCION): no hay nada que inicializar.
    if (!btn || !hint || !lat || !lon) {
        return;
    }

    // Sin API de geolocalización (navegadores viejos o HTTP no seguro):
    // la ubicación manual sigue siendo válida, así que solo deshabilitamos el botón.
    if (!('geolocation' in navigator)) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-slash-circle"></i> Geolocalización no disponible';
        return;
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Obteniendo…';

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                // 6 decimales ≈ 1 metro de precisión, sobra para una mascota.
                lat.value = pos.coords.latitude.toFixed(6);
                lon.value = pos.coords.longitude.toFixed(6);
                hint.textContent = 'Posición capturada (' + lat.value + ', ' + lon.value + '). Se envía con el formulario.';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle"></i> Volver a capturar';
            },
            function (err) {
                hint.textContent = err.code === 1
                    ? 'Permiso de ubicación denegado; podés completar los campos de texto igual.'
                    : 'No se pudo obtener tu ubicación; podés completar los campos de texto igual.';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
})();

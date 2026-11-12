/**
 * Petbook — Captura de geolocalización en el formulario de evento.
 *
 * Llena los inputs hidden #latitud / #longitud con la posición del
 * navegador cuando el usuario lo pide explícitamente con #btn-geo.
 * Antes de disparar el permiso nativo del navegador muestra un diálogo
 * SweetAlert2 explicando por qué se pide la ubicación; el cartel nativo
 * de permisos aparece recién si el usuario confirma ese diálogo.
 *
 * Referenciado desde: app/views/evento/crear.php
 * El <script> debe venir con data-app-url si algún día necesita la base;
 * hoy solo opera sobre ids del propio formulario.
 * Depende de public/js/pb-alerts.js (cargado por el layout).
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
        // El cartel nativo de permisos del navegador no se puede personalizar,
        // así que primero mostramos un diálogo propio que explica por qué se
        // pide la ubicación; el permiso nativo aparece recién si el usuario
        // confirma acá.
        window.pbConfirm({
            icono: 'info',
            titulo: 'Usar mi ubicación actual',
            texto: 'Tu navegador te va a preguntar si permitís compartir tu ubicación. La usamos solo para mostrar dónde se vio o perdió la mascota, y se guarda junto con la publicación.',
            confirmar: 'Permitir ubicación',
        }).then(function (acepta) {
            if (!acepta) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Obteniendo…';

            Swal.fire({
                title: 'Obteniendo tu ubicación…',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () {
                    Swal.showLoading();
                },
            });

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    Swal.close();
                    // 6 decimales ≈ 1 metro de precisión, sobra para una mascota.
                    lat.value = pos.coords.latitude.toFixed(6);
                    lon.value = pos.coords.longitude.toFixed(6);
                    hint.textContent = 'Posición capturada (' + lat.value + ', ' + lon.value + '). Se envía con el formulario.';
                    window.pbToast('success', 'Ubicación capturada. Se envía con el formulario.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2-circle"></i> Volver a capturar';
                },
                function (err) {
                    Swal.close();
                    hint.textContent = err.code === 1
                        ? 'Permiso de ubicación denegado; podés completar los campos de texto igual.'
                        : 'No se pudo obtener tu ubicación; podés completar los campos de texto igual.';
                    window.pbToast('error', err.code === 1
                        ? 'Permiso de ubicación denegado.'
                        : 'No se pudo obtener tu ubicación.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    });
})();

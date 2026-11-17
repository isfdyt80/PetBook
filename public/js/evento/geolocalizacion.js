/**
 * Petbook — Captura de geolocalización en el formulario de evento.
 *
 * Llena los inputs hidden #latitud / #longitud con la posición del
 * navegador cuando el usuario lo pide explícitamente con #btn-geo.
 * Antes de disparar el permiso nativo del navegador muestra un diálogo
 * SweetAlert2 explicando por qué se pide la ubicación; el cartel nativo
 * de permisos aparece recién si el usuario confirma ese diálogo.
 *
 * Estrategia de captura: primero intenta con alta precisión (GPS); si
 * falla con código 2 o 3 (típico en equipos sin GPS) reintenta en modo
 * aproximado por Wi-Fi antes de rendirse.
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

    const MENSAJES_ERROR = {
        1: {
            hint: 'Permiso de ubicación denegado; podés completar los campos de texto igual.',
            toast: 'Permiso de ubicación denegado.',
        },
        2: {
            hint: 'No se pudo determinar tu posición (señal insuficiente); podés completar los campos de texto igual.',
            toast: 'No se pudo determinar tu posición.',
        },
        3: {
            hint: 'Tardó demasiado en llegar tu posición; podés completar los campos de texto igual.',
            toast: 'Tardó demasiado en llegar tu posición.',
        },
    };

    // Alta precisión: GPS si lo hay. Sin GPS, muchos equipos responden
    // POSITION_UNAVAILABLE rápido en vez de degradar solos, por eso hay retry.
    const OPCIONES_GPS    = { enableHighAccuracy: true,  timeout: 8000,  maximumAge: 0 };
    // Aproximado: triangula por Wi-Fi/IP; ~50-500 m de precisión, sobra para una mascota.
    const OPCIONES_APROX  = { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 };

    function restaurarBoton() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
    }

    function exito(pos) {
        Swal.close();
        // 6 decimales ≈ 1 metro de precisión, sobra para una mascota.
        lat.value = pos.coords.latitude.toFixed(6);
        lon.value = pos.coords.longitude.toFixed(6);
        hint.textContent = 'Posición capturada (' + lat.value + ', ' + lon.value + '). Se envía con el formulario.';
        window.pbToast('success', 'Ubicación capturada. Se envía con el formulario.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Volver a capturar';
    }

    function obtener(altaPrecision) {
        navigator.geolocation.getCurrentPosition(
            exito,
            function (err) {
                // El código real y el mensaje del navegador van a la consola:
                // es lo que necesitamos ver si esto vuelve a fallar.
                console.error('[pb-geo] getCurrentPosition falló (código ' + err.code + '):', err.message);

                // Denegado es definitivo, no reintentar. Código 2/3 con GPS:
                // un solo reintento en modo aproximado antes de rendirse.
                if (altaPrecision && err.code !== 1) {
                    obtener(false);
                    return;
                }

                Swal.close();
                const msj = MENSAJES_ERROR[err.code] || MENSAJES_ERROR[2];
                hint.textContent = msj.hint;
                window.pbToast('error', msj.toast);
                restaurarBoton();
            },
            altaPrecision ? OPCIONES_GPS : OPCIONES_APROX
        );
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

            obtener(true);
        });
    });
})();

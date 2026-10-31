/**
 * Petbook — Selector de razas según la especie elegida en mascota.
 *
 * Carga las razas por especie desde /raza/por-especie/:id al cambiar el
 * <select #especie> y, si la vista vino re-renderizada (old con especie
 * ya elegida), restaura la raza guardada en data-raza-old del #raza.
 *
 * Referenciado desde: app/views/mascota/crear.php, app/views/mascota/editar.php
 * El <script> debe declarar data-app-url="<?= APP_URL ?>" para armar las
 * llamadas fetch sin depender de variables JS globales.
 */
(function () {
    'use strict';

    const scriptTag = document.currentScript;
    const baseUrl   = scriptTag ? String(scriptTag.dataset.appUrl || '').replace(/\/+$/, '') : '';

    const selectEspecie = document.getElementById('especie');
    const selectRaza    = document.getElementById('raza');

    // Vista sin los selects (ALTERACIÓN defensiva): no hay nada que hacer.
    if (!selectEspecie || !selectRaza) {
        return;
    }

    const OPCION_VACIA = 'Raza (opcional)';

    function limpiarRazas() {
        selectRaza.innerHTML = '';
        const opcion = document.createElement('option');
        opcion.value = '';
        opcion.textContent = OPCION_VACIA;
        selectRaza.appendChild(opcion);
    }

    function cargarRazas(idEspecie, razaSeleccionada) {
        limpiarRazas();

        if (!idEspecie) {
            return;
        }

        fetch(baseUrl + '/raza/por-especie/' + idEspecie, {
            headers: { Accept: 'application/json' },
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('respuesta ' + respuesta.status);
                }
                return respuesta.json();
            })
            .then(function (razas) {
                razas.forEach(function (raza) {
                    const opcion = document.createElement('option');
                    opcion.value = raza.Id_Raza;
                    opcion.textContent = raza.Nombre;
                    selectRaza.appendChild(opcion);
                });

                if (razaSeleccionada) {
                    selectRaza.value = String(razaSeleccionada);
                }
            })
            .catch(function () {
                // AJAX falla → se mantiene solo la opción vacía: la mascota
                // igual se puede crear sin raza (columna NULL).
            });
    }

    // Al re-renderizar tras un error (o en editar), la especie ya viene
    // elegida: cargamos sus razas y restauramos la raza guardada.
    if (selectEspecie.value) {
        cargarRazas(selectEspecie.value, selectRaza.dataset.razaOld || '');
    }

    selectEspecie.addEventListener('change', function () {
        cargarRazas(this.value, null);
    });
})();

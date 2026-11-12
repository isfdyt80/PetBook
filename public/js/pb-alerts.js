/**
 * Petbook — Helpers de alertas y confirmaciones sobre SweetAlert2.
 *
 * pbToast(tipo, mensaje): toast breve para feedback post-acción.
 *   Tipos válidos: 'success' | 'error' | 'warning' | 'info' (los mismos
 *   que acepta Session::flash()). Tipos desconocidos caen a 'info'.
 *
 * pbConfirm(opciones): Promise<boolean> para reemplazar el confirm()
 *   nativo antes de acciones destructivas. Opciones:
 *   - titulo       (string) título del diálogo
 *   - texto        (string) descripción opcional
 *   - icono        'success' | 'error' | 'warning' | 'info'
 *   - confirmar    texto del botón de confirmación
 *   - cancelar     texto del botón de cancelación
 *   - colorBoton   color hex del botón de confirmación (paleta del proyecto)
 *
 * Referenciado desde: app/views/layouts/main.php (cargado en el footer,
 * después del bundle de SweetAlert2).
 */
(function () {
    'use strict';

    const PALETA = {
        success: { icono: 'success', color: '#166534' },
        error:   { icono: 'error',   color: '#9F1239' },
        warning: { icono: 'warning', color: '#92400E' },
        info:    { icono: 'info',    color: '#3730A3' },
    };

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
    });

    window.pbToast = function (tipo, mensaje) {
        const cfg = PALETA[tipo] || PALETA.info;
        Toast.fire({ icon: cfg.icono, title: mensaje });
    };

    window.pbConfirm = function (opciones) {
        const cfg = opciones || {};
        return Swal.fire({
            icon: cfg.icono || 'warning',
            title: cfg.titulo || '¿Estás seguro?',
            text: cfg.texto || '',
            showCancelButton: true,
            confirmButtonText: cfg.confirmar || 'Sí, confirmar',
            cancelButtonText: cfg.cancelar || 'Cancelar',
            confirmButtonColor: cfg.colorBoton || '#7C6FCD',
            cancelButtonColor: '#7B748F',
            reverseButtons: true,
        }).then((resultado) => resultado.isConfirmed);
    };
})();

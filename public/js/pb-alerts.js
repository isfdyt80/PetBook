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
 * Confirmación declarativa: cualquier <form data-confirm-titulo="...">
 * intercepta su submit y muestra el diálogo antes de enviarse de verdad.
 * Atributos opcionales: data-confirm-texto, data-confirm-boton,
 * data-confirm-color.
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

    // Confirmación genérica por formulario: los <form data-confirm-titulo="...">
    // esperan la promesa de pbConfirm antes de enviarse de verdad. La
    // delegación en document no necesita ajustar las vistas.
    document.addEventListener('submit', (evento) => {
        const form = evento.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirmTitulo) {
            return;
        }
        if (form.dataset.confirmado === '1') {
            return; // Ya confirmado: dejar pasar el envío.
        }
        evento.preventDefault();
        window.pbConfirm({
            titulo: form.dataset.confirmTitulo,
            texto: form.dataset.confirmTexto || '',
            confirmar: form.dataset.confirmBoton || 'Sí, confirmar',
            colorBoton: form.dataset.confirmColor || undefined,
        }).then((acepta) => {
            if (!acepta) {
                return;
            }
            form.dataset.confirmado = '1';
            form.submit();
        });
    });

    // Flashes de sesión: el layout inyecta window.pbFlashes como objeto
    // { tipo: mensaje } y los mostramos como toasts al cargar la página.
    document.addEventListener('DOMContentLoaded', () => {
        const flashes = window.pbFlashes || {};
        Object.keys(flashes).forEach((tipo) => window.pbToast(tipo, flashes[tipo]));
    });
})();

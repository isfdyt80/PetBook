$(document).ready(function () {
    // Espera a que el documento HTML esté completamente cargado antes de ejecutar el código

    $('#close_sesion').click(function () {
        // Cuando se hace clic en el botón con ID 'close_sesion'

        document.cookie = "jwt=; Expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; domain=petbooklocal";
        // Se borra la cookie 'jwt' estableciendo una fecha de expiración en el pasado
        // - 'jwt=': Se asigna un valor vacío a la cookie
        // - 'Expires=Thu, 01 Jan 1970 00:00:01 GMT': Se define la fecha de expiración en el pasado para eliminarla
        // - 'path=/': Aplica el cambio a todas las rutas del sitio
        // - 'domain=petbooklocal': Limita la cookie al dominio específico 'petbooklocal'

        window.location.href = 'http://petbooklocal/sign_in.html';
        // Redirige al usuario a la página de inicio de sesión después de cerrar la sesión
    });

});

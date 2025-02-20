// Ejecuta el código cuando el documento esté listo
$(document).ready(function () {

    // Configura la validación del formulario de inicio de sesión
    $('#form_login').validate({
        rules: {
            i_login_mail: {
                required: true, // El correo es obligatorio
                email: true // Debe ser un formato de correo válido
            },
            i_login_password: "required" // La contraseña es obligatoria
        },
        messages: {
            i_login_mail: {
                required: "Debes ingresar tu correo", // Mensaje si el correo no está ingresado
                email: "El formato del correo no es correcto" // Mensaje si el formato del correo es incorrecto
            },
            i_login_password: "La contraseña es obligatoria" // Mensaje si la contraseña no está ingresada
        },
        errorElement: "div", // Elemento HTML para mostrar los errores
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback"); // Añade la clase de Bootstrap para los errores
            element.parent().append(error); // Añade el error después del elemento
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid"); // Añade la clase de error y quita la de éxito
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid"); // Añade la clase de éxito y quita la de error
        },
        invalidHandler: function () {
            // Muestra una alerta si hay campos inválidos
            $(Swal.fire({
                title: "Alto!",
                icon: "warning",
                text: "Tienes que completar todos los campos"
            }));
        }
    });

    // Valida el campo cuando pierde el foco
    $('#form_login input').on('blur', function () {
        $(this).valid();
    });

    // Maneja el evento de envío del formulario
    $('#form_login').on("submit", function (prevent) {
        prevent.preventDefault(); // Previene el envío del formulario por defecto

        if ($(this).valid()) { // Si el formulario es válido
            var form_data = new FormData(this); // Crea un objeto FormData con los datos del formulario

            // Envía los datos del formulario mediante AJAX
            $.ajax({
                url: "php/form_login.php", // URL del script del servidor
                type: "post", // Método de envío
                dataType: "json", // Tipo de datos esperados del servidor
                data: form_data, // Datos del formulario
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.status === 'success') {
                        window.location.href = data.redirect; // Redirige si el inicio de sesión es exitoso
                    } else {
                        // Muestra un mensaje de error si el inicio de sesión falla
                        Swal.fire({
                            title: "Error!!",
                            icon: "error",
                            text: data.message
                        });
                        console.warn("Error: " + data.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Maneja los errores de la solicitud AJAX
                    console.error("Error: ", textStatus, errorThrown);
                    console.error("Respuesta del servidor:", jqXHR.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Algo salio mal",
                        text: "Lo siento, no pudimos procesar tus datos"
                    });
                }
            });
        }
    });
});
// Primero validamos los datos del formulario
$(document).ready(function () {
    const $i_password = $('#i_signin_password'); // Campo de contraseña
    const $p_strength_message = $('#password-strength'); // Mensaje de fuerza de la contraseña

    // Configura la validación del formulario de registro
    $('#form-sign-in').validate({
        rules: { // Reglas de validación para los campos del formulario
            i_signin_name: "required", // El nombre es obligatorio
            i_signin_lastname: "required", // El apellido es obligatorio
            i_signin_telephone: "required", // El teléfono es obligatorio
            i_signin_place: "required", // La localidad es obligatoria
            i_signin_mail: {
                required: true, // El correo es obligatorio
                email: true // Debe ser un formato de correo válido
            },
            i_signin_password: {
                required: true // La contraseña es obligatoria
            },
            i_signin_repeat_password: {
                required: true, // Repetir la contraseña es obligatorio
                equalTo: "#i_signin_password" // Debe coincidir con la contraseña
            }
        },
        messages: { // Mensajes de error para los campos del formulario
            i_signin_name: "Debes escribir al menos un nombre",
            i_signin_lastname: "Debes escribir al menos un apellido",
            i_signin_telephone: "Debes escribir un número de teléfono",
            i_signin_place: "Debes escribir tu localidad",
            i_signin_mail: {
                required: "Debes escribir un mail válido",
                email: "El formato no es el correcto"
            },
            i_signin_password: "La contraseña es obligatoria",
            i_signin_repeat_password: {
                required: "Debes repetir la contraseña",
                equalTo: "Las contraseñas deben coincidir"
            }
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
    $('#form-sign-in input').on('blur', function () {
        $(this).valid();
    });

    // Verifica la fuerza de la contraseña mientras se escribe
    $i_password.on('input', function () {
        const result = checkPasswordStrength.passwordStrength($i_password.val());

        if (result.id === 0) {
            $p_strength_message.css({ color: 'red' }); // Contraseña débil
        } else if (result.id === 1) {
            $p_strength_message.css({ color: 'orange' }); // Contraseña media
        } else if (result.id === 2) {
            $p_strength_message.css({ color: 'blue' }); // Contraseña fuerte
        } else {
            $p_strength_message.css({ color: 'green' }); // Contraseña muy fuerte
        }
    });

    // Maneja el evento de envío del formulario
    $('#form-sign-in').on("submit", function (prevent) {
        prevent.preventDefault(); // Previene el envío del formulario por defecto

        if ($(this).valid()) { // Si el formulario es válido
            var form_data = new FormData(this); // Crea un objeto FormData con los datos del formulario

            // Envía los datos del formulario mediante AJAX
            $.ajax({
                url: "php/form_signin.php", // URL del script del servidor
                type: "post", // Método de envío
                dataType: "json", // Tipo de datos esperados del servidor
                data: form_data, // Datos del formulario
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    console.log(data);

                    if (data.status === 'success') {
                        Swal.fire({
                            title: "Perfecto",
                            icon: "success",
                            text: "Ahora verifica tu mail para poder entrar a Petbook"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!!",
                            icon: "error",
                            text: data.message
                        });
                        console.warn("Error!!!!: " + data.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Maneja los errores de la solicitud AJAX
                    console.error("Error: ", textStatus, errorThrown);
                    console.error("Respuesta del servidor:", jqXHR.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Algo salió mal",
                        text: "Lo siento, no pudimos procesar tus datos"
                    });
                }
            });
        }
    });
});
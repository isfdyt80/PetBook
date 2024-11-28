// Primero validamos los datos del formulario
$().ready(function () {
    const $b_submit = $('#b_signin');
    const $i_password = $('#i_signin_password');
    const $p_strength_message = $('#password-strength');


    $('#form-sign-in').validate({

        rules: { //Toman el campo 'name'
            i_signin_name: "required",
            i_signin_lastname: "required",
            i_signin_telephone: "required",
            i_signin_place: "required",
            i_signin_mail: {
                required: true,
                email: true
            },
            i_signin_password: {
                required: true
            },
            i_signin_repeat_password: {
                required: true,
                equalTo: "#i_signin_password"
            }
        },
        messages: {
            i_signin_name: "Debes escribir almenos un nombre",
            i_signin_lastname: "Debes escribir almenos un apellido",
            i_signin_telephone: "Debes escribir un número de telefono",
            i_signin_place: "Debes escribir tu localidad",
            i_signin_mail: {
                required: "Debes escribir un mail valido",
                email: "El formato no es el correcto"
            },
            i_signin_password: "La contraseña es obligatoria",
            i_signin_repeat_password: {
                required: "Debes repetir la contraseña",
                equalTo: "Las contraseñas deben coincidir"
            }
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            element.parent().append(error);
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },

        invalidHandler: function () {
            $('<div class="alert alert-danger mt-3">Por favor, corrija los errores antes de enviar.</div>')
                .insertAfter($b_submit)
                .fadeOut(5000, function () {
                    $(this).remove();
                });
        }
    });

    $('#form-sign-in input').on('blur', function () {
        $(this).valid();
    });


    $i_password.on('input', function () {
        const result = checkPasswordStrength.passwordStrength($i_password.val());

        if (result.id === 0) {
            $p_strength_message.text('Su contraseña es MUY DEBIL');
            $p_strength_message.css({ color: 'red' });
        } else if (result.id === 1) {
            $p_strength_message.text('Su contraseña es DEBIL');
            $p_strength_message.css({ color: 'orange' });
        } else if (result.id === 2) {
            $p_strength_message.text('Su contraseña es FUERTE');
            $p_strength_message.css({ color: 'blue' });
        } else {
            $p_strength_message.text('Su contraseña es MUY FUERTE');
            $p_strength_message.css({ color: 'green' });
        }
    });

});




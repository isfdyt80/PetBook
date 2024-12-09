// Primero validamos los datos del formulario
$(document).ready(function () {
    // const $b_submit = $('#b_signin');
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
            $(Swal.fire({
                title: "Alto!",
                icon : "warning",
                text: "Tienes que completar todos los campos"
            }));
        }
    });

    $('#form-sign-in input').on('blur', function () {
        $(this).valid();
    });


    $i_password.on('input', function () {
        const result = checkPasswordStrength.passwordStrength($i_password.val());

        if (result.id === 0) {
            $p_strength_message.css({ color: 'red' });
        } else if (result.id === 1) {
            
            $p_strength_message.css({ color: 'orange' });
        } else if (result.id === 2) {
            
            $p_strength_message.css({ color: 'blue' });
        } else {
            
            $p_strength_message.css({ color: 'green' });
        }
    });



    $('#form-sign-in').on("submit", function (prevent) {

        prevent.preventDefault();

        if ($(this).valid()) {
            var form_data = new FormData(this);

          
            $.ajax({
                url: "php/form_signin.php",
                type: "post",
                dataType: "json",
                data: form_data,
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
                        })
                        // .then(() => {
                            // window.location.href = data.redirect;
                        // });
                        // console.log(data.message);
                    }else{
                        Swal.fire({
                            title: "Error!!",
                            icon: "error",
                            text: data.message
                        });
                        console.warn("Error!!!!: " + data.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
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





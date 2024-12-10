$(document).ready(function () {
    // const $b_submit = $('#b_login');

    $('#form_login').validate({
        rules: {
            i_login_mail: {
                required: true,
                email: true
            },
            i_login_password: "required"
        },
        messages: {
            i_login_mail: {
                required: "Debes ingresar tu correo",
                email: "El formato del correo no es correcto"
            },
            i_login_password:
                "La contraseña es obligatoria"
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

    $('#form_login input').on('blur', function () {
        $(this).valid();
    });

    $('#form_login').on("submit", function (prevent) {

        prevent.preventDefault();

        if ($(this).valid()) {
            var form_data = new FormData(this);


            $.ajax({
                url: "php/form_login.php",
                type: "post",
                dataType: "json",
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.status === 'success') {
                        // Swal.fire({
                        //     title: "Bienvenido",
                        //     // text : "Bienvenido",
                        //     icon: "success"
                        // }).then(() =>{
                            window.location.href = data.redirect;
                        // });
                        // console.log(data.message);
                    } else {
                        Swal.fire({
                            title: "Error!!",
                            icon: "error",
                            text: data.message
                        });
                        console.warn("Error: " + data.message);
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
$(document).ready(function () {
    const $b_submit = $('#b_login');
    
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
            $('<div class="alert alert-danger mt-3">Por favor, si no ingresa sus datos correctamente, no podra entrar.</div>')
                .insertAfter($b_submit)
                .fadeOut(5000, function () {
                    $(this).remove();
                });
        }
    });

    $('#form_login input').on('blur', function(){
        $(this).valid();
    });

    $('#form_login').on("submit", function(prevent){

        prevent.preventDefault();

        if($(this).valid()){
            var form_data = new FormData(this);


            $.ajax({
                url: "form_login.php",
                type: "post",
                dataType: "json",
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
            success: function(data){
                // Aca manejo lo que pasa una ves que se enviaron con exito los datos y me contesto la DB
                console.log("Se aceptaron los datos con exito");
                //Pendiente de modificación
            },
            error: function(jqXHR, textStatus, errorTrown){
                console.error("No se pudieron enviar los datos a la base de datos" + textStatus, errorTrown)
            }
            });
        }
    });
});
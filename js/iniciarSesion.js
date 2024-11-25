$().ready(function () {
    $('#form_login').validate({
        rules: { //toman
            mail: "required",
            password: "required"
        },
        messages: {
            mail: {
                required: "Este campo es obligatorio",
                email: "El formato del email es incorrecto"
            },
            password:
                "Este campo es obligatorio"
        }
    });


});
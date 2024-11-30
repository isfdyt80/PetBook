$(document).ready(function () {
    // Manejar el envío del formulario
    $("#form_login").on("submit", function (event) {
        event.preventDefault(); // Prevenir el envío normal del formulario

        // Obtener los valores de los campos
        const email = $("#email").val();
        const password = $("#password").val();

        if (!email || !password) {
            alert("Por favor, complete todos los campos.");
            return;
        }

        // Generar el hash de la contraseña
        const salt = bcrypt.genSaltSync(10); // Nivel de salado (10 es estándar)
        const hashedPassword = bcrypt.hashSync(password, salt);

        // Enviar los datos cifrados al servidor usando AJAX
        $.ajax({
            url: "/ruta/backend.php", // Cambia esta URL según tu ruta de backend
            method: "POST",
            data: {
                mail: email,
                password: hashedPassword, // Enviar la contraseña cifrada
            },
            success: function (response) {
                // Manejar la respuesta del servidor
                console.log(response);
                if (response.success) {
                    alert("Inicio de sesión exitoso");
                    // Redirigir al usuario a otra página si es necesario
                    window.location.href = "/ruta/dashboard.php";
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (error) {
                console.error("Error en la solicitud:", error);
                alert("Hubo un error. Intente nuevamente.");
            },
        });
    });
});

//libreria jquery 
$(document).ready(function () {
    // Manejar el envío del formulario
    $("#form-sing-in").on("submit", function (event) {
        event.preventDefault(); // Prevenir el envío normal del formulario

        // Obtener los valores de los campos
        const password = $("#password").val();
        const repeatPassword = $("#repeatPassword").val();

        if (!password || !repeatPassword) {
            alert("Por favor, complete todos los campos.");
            return;
        }
    });
    $('#hash-password').on('click', function () {
        const passwordInput = $('#password').val(); // Captura el valor del input
        const saltRounds = 10; // Define las rondas de salt

        // Validar que el campo no esté vacío
        if ($.trim(passwordInput) === '') {
            $('#password-strength').text('Por favor, ingresa una contraseña.');
            return;
            }
    });
    // Usar bcrypt.js para encriptar la contraseña
    bcrypt.hash(passwordInput, saltRounds, (err, hash) => {
        if (err) {
            console.error("Error al encriptar la contraseña:", err);
            document.getElementById('password-strength').innerText = 'Error al encriptar.';
        } else {
            console.log("Contraseña encriptada:", hash);
            document.getElementById('password-strength').innerText = 'Contraseña encriptada con éxito.';
            document.getElementById('hashed-password').innerText = `Hash: ${hash}`;
        }
    });
});
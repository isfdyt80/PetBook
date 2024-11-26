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

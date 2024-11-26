// Primero validamos los datos que no son contraseña del formulario
$().ready(function () {
    $('#form-sign-in').validate({
        rules: { //toman
            nombre: "required",
            apellido: "required",
            telefono:"requiresd",
            localidad:"requiresd",
            email:"requiresd",
            password:"required",
            repeatPassword:"required"
        },
        messages: {
            nombre: 
                "Este campo es obligatorio",
                // email: "El formato del email es incorrecto"
            apellido:
                "Este campo es obligatorio",
            telefono:
                "Este campo es obligatorio",
            localidad:
                "Este campo es obligatorio",
            email:
                "Este campo es obligatorio",
            password:
                "Este campo es obligatorio",
            repeatPassword:
                "Este campo es obligatorio"
        }
    });


});
$(document).ready(function () {
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



console.log('Librería cargada:', typeof checkPasswordStrength !== 'undefined');
console.log(checkPasswordStrength); // Debería mostrar "function"


document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const passwordStrengthMessage = document.getElementById('password-strength');
    const repeatPasswordInput = document.getElementById('repeatPassword');
    const passwordMatchMessage = document.getElementById('password-match');
    const submitButton = document.getElementById('sign-in');

    passwordInput.addEventListener('input', () => {
        const result = checkPasswordStrength.passwordStrength(passwordInput.value);
        passwordStrengthMessage.textContent = `Fortaleza: ${result.value}`;
        passwordStrengthMessage.className = 'form-text';

        if (result.id === 0) {
            passwordStrengthMessage.textContent = 'La contraseña es MUY DEBIL';
            passwordStrengthMessage.style.color = 'red';
        } else if (result.id === 1) {
            passwordStrengthMessage.style.color = 'orange';
        } else if (result.id === 2) {
            passwordStrengthMessage.style.color = 'blue';
        } else {
            passwordStrengthMessage.style.color = 'green';
        }
        validatePassword();
    });

    repeatPasswordInput.addEventListener('input', validatePassword);

    function validatePassword() {
        const password = passwordInput.value;
        const repeatPassword = repeatPasswordInput.value;

        if (repeatPassword === '') {
            passwordMatchMessage.textContent = '';
            submitButton.disabled = true;
            return;
        }

        if (password === repeatPassword) {
            passwordMatchMessage.textContent = 'Las contraseñas coinciden.';
            passwordMatchMessage.style.color = 'green';
            submitButton.disabled = false;
        } else {
            passwordMatchMessage.textContent = 'Las contraseñas NO coinciden';
            passwordMatchMessage.style.color = 'red';
            submitButton.disabled = true;

        }
    }

});

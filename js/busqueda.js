/*Este código usa jQuery para actualizar dinámicamente el campo de selección de ciudades
según la provincia elegida. Cuando el usuario cambia la provincia,
se vacía el campo de ciudad y se agregan nuevas opciones dependiendo de la provincia seleccionada.
*/

$('#provincia').on('change', function () {  // Detecta cuando el usuario cambia la selección de provincia
    const provinciaSeleccionada = $(this).val();  // Obtiene el valor de la provincia seleccionada
    const $ciudadSelect = $('#ciudad');  // Selecciona el elemento <select> de ciudad
    $ciudadSelect.empty();  // Vacía las opciones actuales de ciudad

    if (ciudadesPorProvincia[provinciaSeleccionada]) {  // Verifica si hay ciudades asociadas a la provincia seleccionada
        ciudadesPorProvincia[provinciaSeleccionada].forEach(function (ciudad) {  // Itera sobre las ciudades de la provincia seleccionada
            const $option = $('<option>').text(ciudad);  // Crea una nueva opción con el nombre de la ciudad
            $ciudadSelect.append($option);  // Agrega la opción al <select> de ciudad
        });
    } else {
        const $option = $('<option>').text('Seleccione una provincia primero');  // Opción por defecto si no hay ciudades
        $ciudadSelect.append($option);  // Agrega la opción al <select> de ciudad
    }
});
$('#provincia').on('change', function () {
    const provinciaSeleccionada = $(this).val();
    const $ciudadSelect = $('#ciudad');
    $ciudadSelect.empty();

    if (ciudadesPorProvincia[provinciaSeleccionada]) {
        ciudadesPorProvincia[provinciaSeleccionada].forEach(function (ciudad) {
            const $option = $('<option>').text(ciudad);
            $ciudadSelect.append($option);
        });
    } else {
        const $option = $('<option>').text('Seleccione una provincia primero');
        $ciudadSelect.append($option);
    }
});

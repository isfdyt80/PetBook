$(document).ready(function () {
    // Función para cargar los países en el select
    function cargarPaises() {
        $.ajax({
            url: 'obtener_paises.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let selectPais = $('#pais');
                selectPais.empty().append('<option value="">Seleccione un país</option>');
                $.each(data, function (index, pais) {
                    selectPais.append(`<option value="${pais.id}">${pais.nombre}</option>`);
                });
            },
            error: function () {
                console.error("Error al cargar los países.");
            }
        });
    }

    // Evento para cargar provincias al seleccionar un país
    $('#pais').change(function () {
        let paisId = $(this).val();
        if (paisId) {
            $.ajax({
                url: 'obtener_provincias.php',
                type: 'GET',
                data: { pais_id: paisId },
                dataType: 'json',
                success: function (data) {
                    let selectProvincia = $('#provincia');
                    selectProvincia.empty().append('<option value="">Seleccione una provincia</option>');
                    $.each(data, function (index, provincia) {
                        selectProvincia.append(`<option value="${provincia.id}">${provincia.nombre}</option>`);
                    });
                },
                error: function () {
                    console.error("Error al cargar las provincias.");
                }
            });
        }
    });

    // Evento para cargar localidades al seleccionar una provincia
    $('#provincia').change(function () {
        let provinciaId = $(this).val();
        if (provinciaId) {
            $.ajax({
                url: 'obtener_localidades.php',
                type: 'GET',
                data: { provincia_id: provinciaId },
                dataType: 'json',
                success: function (data) {
                    let selectCiudad = $('#ciudad');
                    selectCiudad.empty().append('<option value="">Seleccione una ciudad</option>');
                    $.each(data, function (index, ciudad) {
                        selectCiudad.append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
                    });
                },
                error: function () {
                    console.error("Error al cargar las ciudades.");
                }
            });
        }
    });

    // Evento para manejar el envío del formulario con AJAX
    $('#animalPerdidoForm, #animalEncontradoForm, #animalAdopcionForm').submit(function (event) {
        event.preventDefault(); // Evita el envío normal del formulario

        let form = $(this);
        let formData = new FormData(form[0]);

        $.ajax({
            url: 'procesar_publicacion.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        form.trigger("reset"); // Limpiar el formulario
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Intentar de nuevo'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la publicación.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Intentar de nuevo'
                });
            }
        });
    });

    // Cargar los países al inicio
    cargarPaises();
});

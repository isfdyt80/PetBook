$(document).ready(function () {
    // Función para cargar las especies en el select
    function cargarEspecies() {
        $.ajax({
            url: 'php/obtener_especies.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let selectEspeciePerdido = $('#especie_perdido');
                let selectEspecieEncontrado = $('#especie_encontrado');
                let selectEspecieAdopcion = $('#especie_adopcion');
                selectEspeciePerdido.empty().append('<option value="">Seleccione una especie</option>');
                selectEspecieEncontrado.empty().append('<option value="">Seleccione una especie</option>');
                selectEspecieAdopcion.empty().append('<option value="">Seleccione una especie</option>');
                $.each(data, function (index, especie) {
                    selectEspeciePerdido.append(`<option value="${especie.id}">${especie.nombre}</option>`);
                    selectEspecieEncontrado.append(`<option value="${especie.id}">${especie.nombre}</option>`);
                    selectEspecieAdopcion.append(`<option value="${especie.id}">${especie.nombre}</option>`);
                });
            },
            error: function () {
                console.error("Error al cargar las especies.");
            }
        });
    }
    // Función para cargar los países en el select
    function cargarPaises() {
        $.ajax({
            url: 'php/obtener_paises.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let selectPaisPerdido = $('#pais_perdido');
                let selectPaisEncontrado = $('#pais_encontrado');
                let selectPaisAdopcion = $('#pais_adopcion');
                selectPaisPerdido.empty().append('<option value="">Seleccione un país</option>');
                selectPaisEncontrado.empty().append('<option value="">Seleccione un país</option>');
                selectPaisAdopcion.empty().append('<option value="">Seleccione un país</option>');
                $.each(data, function (index, pais) {
                    selectPaisPerdido.append(`<option value="${pais.id}">${pais.nombre}</option>`);
                    selectPaisEncontrado.append(`<option value="${pais.id}">${pais.nombre}</option>`);
                    selectPaisAdopcion.append(`<option value="${pais.id}">${pais.nombre}</option>`);
                });
            },
            error: function () {
                console.error("Error al cargar los países.");
            }
        });
    }

    // Evento para cargar provincias al seleccionar un país
    $(document).on('change', '#pais_perdido, #pais_encontrado, #pais_adopcion', function () {
        let paisId = $(this).val();
        if (paisId) {
            $.ajax({
                url: 'php/obtener_provincias.php',
                type: 'GET',
                data: { pais_id: paisId },
                dataType: 'json',
                success: function (data) {
                    let selectProvinciaPerdido = $('#provincia_perdido');
                    let selectProvinciaEncontrado = $('#provincia_encontrado');
                    let selectProvinciaAdopcion = $('#provincia_adopcion');
                    selectProvinciaPerdido.empty().append('<option value="">Seleccione una provincia</option>');
                    selectProvinciaEncontrado.empty().append('<option value="">Seleccione una provincia</option>');
                    selectProvinciaAdopcion.empty().append('<option value="">Seleccione una provincia</option>');
                    $.each(data, function (index, provincia) {
                        selectProvinciaPerdido.append(`<option value="${provincia.id}">${provincia.nombre}</option>`);
                        selectProvinciaEncontrado.append(`<option value="${provincia.id}">${provincia.nombre}</option>`);
                        selectProvinciaAdopcion.append(`<option value="${provincia.id}">${provincia.nombre}</option>`);
                    });
                },
                error: function () {
                    console.error("Error al cargar las provincias.");
                }
            });
        }
    });

    // Evento para cargar localidades al seleccionar una provincia
    $(document).on('change', '#provincia_perdido, #provincia_encontrado, #provincia_adopcion', function () {
        let provinciaId = $(this).val();
        if (provinciaId) {
            $.ajax({
                url: 'php/obtener_localidades.php',
                type: 'GET',
                data: { provincia_id: provinciaId },
                dataType: 'json',
                success: function (data) {
                    let selectCiudadPerdido = $('#ciudad_perdido');
                    let selectCiudadEncontrado = $('#ciudad_encontrado');
                    let selectCiudadAdopcion = $('#ciudad_adopcion');
                    selectCiudadPerdido.empty().append('<option value="">Seleccione una ciudad</option>');
                    selectCiudadEncontrado.empty().append('<option value="">Seleccione una ciudad</option>');
                    selectCiudadAdopcion.empty().append('<option value="">Seleccione una ciudad</option>');
                    $.each(data, function (index, ciudad) {
                        selectCiudadPerdido.append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
                        selectCiudadEncontrado.append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
                        selectCiudadAdopcion.append(`<option value="${ciudad.id}">${ciudad.nombre}</option>`);
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
            url: 'php/procesar_publicacion.php',
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

    // Cargar los países y especies al inicio
    cargarPaises();
    cargarEspecies();
});

$(document).ready(function () {
    // Mostrar u ocultar el campo de recompensa según el checkbox
    $('#recompensaCheckbox').on('change', function () {
        if ($(this).is(':checked')) {
            $('#valorRecompensaGroup').show();
        } else {
            $('#valorRecompensaGroup').hide();
            $('#valorRecompensa').val(''); // Limpiar el valor si se desmarca
        }
    });

    // Capturar el evento de envío del formulario
    $('#animalPerdidoForm').on('submit', function (e) {
        e.preventDefault(); // Evita el envío tradicional del formulario

        // Crear un objeto FormData para manejar archivos y datos
        let formData = new FormData(this);

        $.ajax({
            url: 'procesar_publicacion.php', // Archivo PHP para manejar la solicitud
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                alert('Publicación cargada exitosamente'); // Notificación de éxito
                $('#animalPerdidoForm')[0].reset(); // Limpiar el formulario
                $('#valorRecompensaGroup').hide(); // Ocultar el campo recompensa
                $('#ModalPerdido').modal('hide'); // Cerrar el modal
                cargarPublicaciones(); // Recargar las publicaciones dinámicamente
            },
            error: function () {
                alert('Ocurrió un error al procesar la publicación. Intenta nuevamente.');
            }
        });
    });
});

function cargarPublicaciones() {
    $.ajax({
        url: 'obtener_publicaciones.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            let contenedor = $('#contenedorPublicaciones');
            contenedor.empty(); // Limpia las publicaciones previas

            data.forEach(function (publicacion) {
                contenedor.append(`
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">${publicacion.descripcion}</h5>
                            <p class="card-text">
                                Fecha última vez visto: ${publicacion.fecha_ult_vez}<br>
                                Ubicación: ${publicacion.ubicacion}<br>
                                Recompensa: ${publicacion.valor_recompensa || 'No ofrecida'}<br>
                                Contacto: ${publicacion.tel_dueño}
                            </p>
                        </div>
                    </div>
                `);
            });
        },
        error: function () {
            alert('Error al cargar publicaciones.');
        }
    });
}

// Llamar a la función al cargar la página
$(document).ready(function () {
    cargarPublicaciones();
});

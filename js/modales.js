/* JavaScript
document.addEventListener('DOMContentLoaded', function () {
    var recompensaCheckbox = document.getElementById('recompensaCheckbox');
    var valorRecompensaGroup = document.getElementById('valorRecompensaGroup');

    recompensaCheckbox.addEventListener('change', function () {
        if (recompensaCheckbox.checked) {
            valorRecompensaGroup.style.display = 'block';
        } else {
            valorRecompensaGroup.style.display = 'none';
            document.getElementById('valorRecompensa').value = ''; // Limpiar el valor cuando se desactiva el checkbox
        }
    });

    var obtenerUbicacionBtn = document.getElementById('obtenerUbicacionBtn');
    var ubicacionInput = document.getElementById('ubicacion');

    obtenerUbicacionBtn.addEventListener('click', function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                ubicacionInput.value = `Lat: ${lat}, Lon: ${lon}`;
            }, function (error) {
                alert('Error al obtener la ubicación: ' + error.message);
            });
        } else {
            alert('Geolocalización no es soportada por este navegador.');
        }
    });
});*/

// Jquery
$(document).ready(function () {
    var $recompensaCheckbox = $('#recompensaCheckbox');
    var $valorRecompensaGroup = $('#valorRecompensaGroup');
    var $valorRecompensa = $('#valorRecompensa');
    var $obtenerUbicacionBtn = $('#obtenerUbicacionBtn');
    var $ubicacionInput = $('#ubicacion');

    $recompensaCheckbox.on('change', function () {
        if ($recompensaCheckbox.is(':checked')) {
            $valorRecompensaGroup.show();
        } else {
            $valorRecompensaGroup.hide();
            $valorRecompensa.val(''); // Limpiar el valor cuando se desactiva el checkbox
        }
    });

    $obtenerUbicacionBtn.on('click', function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                $ubicacionInput.val('Lat: ' + lat + ', Lon: ' + lon);
            }, function (error) {
                alert('Error al obtener la ubicación: ' + error.message);
            });
        } else {
            alert('Geolocalización no es soportada por este navegador.');
        }
    });
});

        
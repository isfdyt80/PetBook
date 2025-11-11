export function loadPublicaciones() {
  const $container = $('#card-container');
  $container.empty().append('<p class="text-center text-muted mt-3">Cargando publicaciones...</p>');

  $.ajax({
    url: 'backend/controladores/PublicacionController.php',
    method: 'GET',
    dataType: 'json',
    success: function (data) {
      $container.empty();

      if (!Array.isArray(data) || data.length === 0) {
        $container.append('<p class="text-center text-muted mt-3">No hay publicaciones disponibles.</p>');
        return;
      }

      data.forEach(pub => {
        const card = crearCardPublicacion(pub);
        $container.append(card);
      });
    },
    error: function (xhr, status, err) {
      console.error('Error cargando publicaciones:', err);
      $container.html('<p class="text-danger text-center mt-3">Error al cargar las publicaciones.</p>');
    }
  });
}

// --- Función auxiliar para crear la card ---
function crearCardPublicacion(pub) {
  // Evita fallos si faltan campos
  // Priorizar: foto de la publicación > foto de la mascota > imagen por defecto
  let foto = 'assets/img/default_pet.jpg';
  if (pub.foto && pub.foto !== 'Sin imagen') foto = pub.foto;
  else if (pub.mascota_foto && pub.mascota_foto !== 'Sin imagen') foto = pub.mascota_foto;
  const nombre = pub.nombre_mascota || 'Mascota sin nombre';
  const descripcion = pub.descripcion || 'Sin descripción';
  const estado = pub.estado || 'Desconocido';
  const usuario = pub.usuario_nombre || 'Usuario anónimo';
  const fecha = pub.fecha_creacion ? new Date(pub.fecha_creacion).toLocaleDateString() : 'Sin fecha';

  return `
    <div class="col-12 col-md-6 col-lg-4 mb-3">
      <div class="card h-100 shadow-sm">
        <img src="${foto}" class="card-img-top" alt="Foto de ${nombre}" style="height:200px;object-fit:cover;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title mb-1">${nombre}</h5>
          <p class="text-muted small mb-2">${estado} • ${fecha}</p>
          <p class="card-text flex-grow-1">${descripcion}</p>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <small class="text-muted">${usuario}</small>
            <button class="btn btn-outline-primary btn-sm ver-mas" data-id="${pub.id}">Ver más</button>
          </div>
        </div>
      </div>
    </div>
  `;
}

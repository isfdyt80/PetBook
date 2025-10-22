$(document).on('click', '.post-card', function (e) {
  e.preventDefault();
  const id = $(this).data('id');
  if (!id) return;

  $('#pubModal').remove();

  const modalHtml = `
    <div class="modal fade" id="pubModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cargando publicación...</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body d-flex justify-content-center align-items-center" style="min-height:120px;">
            <div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div>
          </div>
        </div>
      </div>
    </div>
  `;

  $('body').append(modalHtml);

  const modalEl = document.getElementById('pubModal');
  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // Simulacion  "petición AJAX" 
  setTimeout(() => {
    // 
    const data = {
      id: id,
      nombre: "Max el Husky",
      estado: "En adopción",
      descripcion: "Max es un perro muy cariñoso y juguetón. Le encanta correr en la nieve y dormir en lugares cálidos.",
      imagen_url: "https://sadenir.com.uy/equilibrio/wp-content/uploads/sites/2/2020/08/simon-rae-jY_2XG-6HU0-unsplash-1.jpg"
    };

    //  Estructura HTML que Remplazada
    const content = `
      <div class="modal-header">
        <h5 class="modal-title">${data.nombre}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex mb-3 align-items-center">
          <img src="${data.imagen_url}" 
               alt="${data.nombre}" 
               style="width:100px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
          <div class="ms-3">
            <h6 class="mb-1">${data.nombre}</h6>
            <small class="text-muted">${data.estado}</small>
          </div>
        </div>

        <p>${data.descripcion}</p>
      </div>

      <div class="modal-footer">
        <button id="btnComunicar" type="button" class="btn btn-primary">Comunicar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    `;

    // Reemplazamos todo el contenido del modal por el nuevo HTML
    $('#pubModal .modal-content').html(content);

    // Acción del botón (solo para probar)
    $('#btnComunicar').on('click', function () {
      alert(`Hablando sobre: ${data.nombre}`);
    });
  }, 1000); // simulamos 1 segundo de espera

  // Cuando se cierre, eliminar del DOM
  $('#pubModal').on('hidden.bs.modal', function () {
    $(this).remove();
  });
});

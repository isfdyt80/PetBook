export function initPostModalHandlers() {
  $(document).off('click', '.post-card').on('click', '.post-card', function (e) {
    e.preventDefault();

    const $el = $(this);
    const id = $el.attr('data-id') || '';
    if (!id) return;

    const estado = $el.attr('data-estado') || '';
    const nombre = $el.attr('data-nombre') || 'Sin nombre';
    const edad = $el.attr('data-edad') || '';
    const imagen_url = $el.attr('data-foto') || '';
    const raza = $el.attr('data-raza') || '';
    const descripcion = $el.attr('data-descripcion') || '';
    const usuario = $el.attr('data-usuario') || '';

    $('#pubModal').remove();

    const loadingHtml = `
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

    $('body').append(loadingHtml);
    const modalEl = document.getElementById('pubModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    setTimeout(() => {
      const estadoLabel = (estado === 'perdido') ? 'Perdido' : (estado === 'adopcion' ? 'En adopción' : estado);
      const badgeClass = (estado === 'perdido') ? 'bg-danger' : 'bg-success';

      const content = `
        <div class="modal-header">
          <h5 class="modal-title">${escapeHtml(nombre)}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex mb-3 align-items-center">
            <img src="${escapeHtml(imagen_url)}"
                 alt="${escapeHtml(nombre)}"
                 style="width:120px; height:120px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
            <div class="ms-3">
              <h6 class="mb-1">${escapeHtml(nombre)} <span class="badge ${badgeClass} ms-2">${escapeHtml(estadoLabel)}</span></h6>
              <small class="text-muted">${escapeHtml(raza)}</small><br>
              <small class="text-muted">Publicado por: ${usuario || '—'}</small><br>
              <small class="text-muted">Edad: ${escapeHtml(edad)} año(s)</small>
            </div>
          </div>

          <div class="mb-2">
            <h6>Descripción</h6>
            <p class="mb-0">${escapeHtml(descripcion)}</p>
          </div>

        </div>

        <div class="modal-footer">
          <button id="btnComunicar" type="button" class="btn btn-primary">Comunicar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      `;

      $('#pubModal .modal-content').html(content);

      $('#btnComunicar').on('click', function () {
        alert(`Hablando sobre: ${nombre}`);
      });
    }, 300);

    $('#pubModal').on('hidden.bs.modal', function () {
      $(this).remove();
    });

    function escapeHtml(text) {
      return String(text).replace(/[&<>"'`=\/]/g, function (s) {
        return ({
          '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
        })[s];
      });
    }
  });
};

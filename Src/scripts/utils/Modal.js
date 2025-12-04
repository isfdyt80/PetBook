export function initPostModalHandlers() {
  $(document).off('click', '.ver-mas').on('click', '.ver-mas', function (e) {
    e.preventDefault();
    e.stopPropagation();
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
    const ubicacion = $el.attr('data-ubicacion') || '';
    const recompensa = $el.attr('data-recompensa') || '';

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
          <button id="btnEditarPub" type="button" class="btn btn-secondary">Editar</button>
          <button id="btnEliminarPub" type="button" class="btn btn-danger">Eliminar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      `;

      $('#pubModal .modal-content').html(content);

      // Botón 'Comunicar' removido — comunicación no implementada aquí.

      $('#btnEditarPub').on('click', function () {
        $('#editPubModal').remove();

        const editHtml = `
          <div class="modal fade" id="editPubModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Editar publicación</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-2">
                    <label class="form-label">Descripción</label>
                    <textarea id="editDesc" class="form-control" rows="3">${escapeHtml(descripcion)}</textarea>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Foto</label>
                    <div class="d-flex align-items-center gap-2">
                      <input id="editFotoFile" type="file" accept="image/*" class="form-control form-control-sm" />
                      ${imagen_url ? `<img src="${escapeHtml(imagen_url)}" alt="Prev" style="height:48px;width:48px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">` : ''}
                    </div>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Estado</label>
                    <select id="editEstado" class="form-select">
                      <option value="perdido" ${estado==='perdido'?'selected':''}>Perdido</option>
                      <option value="adopcion" ${estado==='adopcion'?'selected':''}>En adopción</option>
                      <option value="encontrado" ${estado==='encontrado'?'selected':''}>Encontrado</option>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Ubicación</label>
                    <input id="editUbic" class="form-control" value="${escapeHtml(ubicacion)}">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Recompensa</label>
                    <input id="editRecomp" type="number" step="0.01" class="form-control" value="${escapeHtml(recompensa)}">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button id="btnSaveEditModal" type="button" class="btn btn-primary">Guardar</button>
                </div>
              </div>
            </div>
          </div>`;

        $('body').append(editHtml);
        const eEl = document.getElementById('editPubModal');
        const eModal = new bootstrap.Modal(eEl);
        eModal.show();

        $('#btnSaveEditModal').off('click').on('click', function () {
          const fd = new FormData();
          fd.append('_method', 'PUT');
          fd.append('id', id);
          fd.append('descripcion', $('#editDesc').val());
          fd.append('estado', $('#editEstado').val());
          fd.append('ubicacion', $('#editUbic').val());
          fd.append('recompensa', $('#editRecomp').val());
          fd.append('usuario_id', 2);

          const fileInput = $('#editFotoFile')[0];
          if (fileInput && fileInput.files && fileInput.files[0]) {
            fd.append('foto_publicacion', fileInput.files[0]);
          }

          $.ajax({
            url: window.getApiUrl('backend/controladores/PublicacionController.php'),
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
              if (res && res.success) {
                eModal.hide();
                $('#editPubModal').remove();
                modal.hide();
                if (typeof loadPublicaciones === 'function') {
                  loadPublicaciones();
                }
              } else {
                alert(res && res.error ? res.error : 'Error al actualizar la publicación');
              }
            },
            error: function (xhr) {
              let msg = 'Error al actualizar la publicación';
              try { const j = JSON.parse(xhr.responseText || '{}'); if (j && j.error) msg = j.error; } catch(e){}
              alert(msg);
            }
          });
        });
      });

      $('#btnEliminarPub').on('click', function () {
        $('#confirmDeleteModal').remove();

        const modalHtml = `
          <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">¿Eliminar publicación?</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <p>¿Estás seguro de eliminar esta publicación?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                  <button id="confirmDeleteYesModal" type="button" class="btn btn-danger">Sí</button>
                </div>
              </div>
            </div>
          </div>`;
          
        $('body').append(modalHtml);
        const cEl = document.getElementById('confirmDeleteModal');
        const cModal = new bootstrap.Modal(cEl);
        cModal.show();

        $('#confirmDeleteYesModal').off('click').on('click', function () {
          $.ajax({
            url: window.getApiUrl('backend/controladores/PublicacionController.php'),
            method: 'DELETE',
            data: { id: id, usuario_id: 2 },
            dataType: 'json',
            success: function (res) {
              if (res && res.success) {
                cModal.hide();
                $('#confirmDeleteModal').remove();
                modal.hide();
                $('#pubModal').remove();
                // Recargar publicaciones
                $(document).trigger('publicacion:eliminada', [id]);
                if (typeof loadPublicaciones === 'function') {
                  loadPublicaciones();
                }
              } else {
                alert(res.error || 'Error al eliminar');
              }
            },
            error: function () {
              cModal.hide();
              $('#confirmDeleteModal').remove();
              alert('Error al eliminar la publicación');
            }
          });
        });
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

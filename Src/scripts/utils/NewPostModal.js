export function initNewPostHandlers() {
  $(document).off('click', '#newPostLink').on('click', '#newPostLink', function (e) {
    e.preventDefault();
    $('#newPostModal').remove();

    const modalHtml = `
      <div class="modal fade" id="newPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Crear nueva publicación</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
              <!-- Lista / selector de mascotas y botón para añadir -->
              <div id="misMascotasBlock" class="mb-3">
                <label class="form-label">Seleccioná una mascota (usar datos guardados)</label>
                <div class="d-flex gap-2">
                  <select id="postMascotaSelect" name="mascota_id" class="form-select">
                    <option value="">Cargando mascotas...</option>
                  </select>
                  <button type="button" id="abrirAgregarMascotaBtn" class="btn btn-outline-primary">Añadir mascota</button>
                </div>
                <div id="postMascotaPreview" class="mt-2"></div>
              </div>

              <form id="newPostForm" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="postEstado" class="form-label">Estado</label>
                  <select id="postEstado" name="estado" class="form-select" required>
                    <option value="perdido">Perdido</option>
                    <option value="adopcion">En adopción</option>
                  </select>
                  <div class="form-text">Marca si la publicación es por mascota perdida o por adopción.</div>
                </div>

                <div class="mb-3">
                  <label for="postDescripcion" class="form-label">Descripción</label>
                  <textarea id="postDescripcion" name="descripcion" class="form-control" rows="3" placeholder="Describe la publicación..." required></textarea>
                </div>

                <div class="mb-3">
                  <label for="postFotoFile" class="form-label">Foto de la publicación (opcional)</label>
                  <input type="file" accept="image/*" class="form-control" id="postFotoFile" name="foto_publicacion" >
                  <div class="mt-2">
                    <img id="postFotoPreview" src="" alt="Preview" style="display:none; width:120px; height:120px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                  </div>
                </div>

                <div class="mb-3">
                  <label for="postRecompensa" class="form-label">Recompensa (opcional)</label>
                  <input type="text" id="postRecompensa" name="recompensa" class="form-control" placeholder="Ej: 5000">
                  <div class="form-text">Si corresponde, importe o detalles de la recompensa.</div>
                </div>

                <input type="hidden" id="useMascotaFoto" name="use_mascota_foto" value="1">

                <div class="text-end">
                  <button type="submit" class="btn btn-primary">Publicar</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;

    $('body').append(modalHtml);
    const modalEl = document.getElementById('newPostModal');
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    // UTIL: escapeHtml
    function escapeHtml(text) {
      return String(text || '').replace(/[&<>"'`=\/]/g, function (s) {
        return ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;' })[s];
      });
    }

    // preview local de la foto de la publicación
    $('#postFotoFile').on('change', function () {
      const f = this.files && this.files[0];
      if (!f) {
        $('#postFotoPreview').hide().attr('src', '');
        // si no hay archivo volver a permitir uso de foto de mascota por defecto
        $('#useMascotaFoto').val('1');
        return;
      }
      const reader = new FileReader();
      reader.onload = function (ev) {
        $('#postFotoPreview').attr('src', ev.target.result).show();
      };
      reader.readAsDataURL(f);
      // si el usuario eligió archivo, no usar foto de mascota por defecto
      $('#useMascotaFoto').val('0');
    });

    // cargar mascotas del usuario por AJAX
    const misMascotasUrl = window.getApiUrl('backend/controladores/MascotaController.php?action=mis_mascotas');
    function cargarMisMascotas(preselectId) {
      $.ajax({
        url: misMascotasUrl,
        method: 'GET',
        dataType: 'json',
          success: function(list) {
          const $sel = $('#postMascotaSelect').empty();
          if (!Array.isArray(list) || list.length === 0) {
            $sel.append('<option value="">No tenés mascotas</option>');
            $('#postMascotaPreview').html('<small class="text-muted">Ninguna mascota. Podés crear una nueva.</small>');
            $('#postMascotaId').remove();
            return;
          }
          $sel.append('<option value="">Elegí una mascota</option>');
          list.forEach(m => {
            const text = (m.nombre || '—') + (m.raza_nombre ? ' — ' + m.raza_nombre : '');
            $sel.append(`<option value="${escapeHtml(m.id)}" data-foto="${escapeHtml(m.foto||'')}" data-raza="${escapeHtml(m.raza_nombre||'')}">${escapeHtml(text)}</option>`);
          });

          if (preselectId) {
            $sel.val(String(preselectId));
            $sel.trigger('change');
          }
        },
        error: function(xhr) {
          $('#postMascotaSelect').empty().append('<option value="">Error cargando mascotas</option>');
          console.warn('Error al cargar mascotas:', xhr.responseText || xhr.statusText);
        }
      });
    }

    // inicial cargar
    cargarMisMascotas();

    // change: actualizar preview de mascota y hidden mascota_id
    $(document).off('change', '#postMascotaSelect').on('change', '#postMascotaSelect', function() {
      const $sel = $(this);
      const val = $sel.val();
      if (!val) {
        $('#postMascotaPreview').html('');
        $('#postMascotaId').remove();
        return;
      }
      const foto = $sel.find('option:selected').data('foto') || '';
      const raza = $sel.find('option:selected').data('raza') || '';
      const text = $sel.find('option:selected').text();

      let html = `<div class="d-flex align-items-center"><div style="width:64px;height:64px;overflow:hidden;border-radius:6px;margin-right:10px">`;
      if (foto) html += `<img src="${escapeHtml(window.getApiUrl('backend/' + foto))}" style="width:64px;height:64px;object-fit:cover;">`;
      else html += `<div style="width:64px;height:64px;background:#f0f0f0;border-radius:6px"></div>`;
      html += `</div><div><div><strong>${escapeHtml(text)}</strong></div><small class="text-muted">${escapeHtml(raza)}</small></div></div>`;

      $('#postMascotaPreview').html(html);

      if ($('#postMascotaId').length === 0) {
        $('#newPostForm').append('<input type="hidden" id="postMascotaId" name="mascota_id" value="">');
      }
      $('#postMascotaId').val(val);

      // si no hay archivo seleccionado, mantener useMascotaFoto=1 para que la publicación use la foto de la mascota
      const hasFile = $('#postFotoFile')[0].files && $('#postFotoFile')[0].files.length;
      if (!hasFile) $('#useMascotaFoto').val('1');
    });

    // botón para abrir el modal de añadir mascota (reutilizar handler existente)
    $(document).off('click', '#abrirAgregarMascotaBtn').on('click', '#abrirAgregarMascotaBtn', function() {
      $('#añadir_mascotas').trigger('click');
    });

    // cuando se publique una mascota desde cualquier parte del sitio, recargamos y preseleccionamos
    $(document).off('mascota:creada.newpost').on('mascota:creada.newpost', function(ev, nuevaMascota) {
      if (nuevaMascota && nuevaMascota.id) {
        cargarMisMascotas(nuevaMascota.id);
      } else {
        cargarMisMascotas();
      }
    });

    // submit del formulario: enviamos descripcion, estado, foto_publicacion (si hay), mascota_id, recompensa y use_mascota_foto
    $('#newPostForm').on('submit', function (ev) {
      ev.preventDefault();

      const estado = $('#postEstado').val();
      const descripcion = $('#postDescripcion').val().trim();
      const mascota_id = $('#postMascotaId').length ? $('#postMascotaId').val() : '';

      if (!descripcion) {
        alert('Completá la descripción.');
        return;
      }

      const fd = new FormData();
      fd.append('estado', estado);
      fd.append('descripcion', descripcion);
      if (mascota_id) fd.append('mascota_id', mascota_id);

      const file = $('#postFotoFile')[0].files && $('#postFotoFile')[0].files[0];
      if (file) {
        fd.append('foto_publicacion', file);
        fd.append('use_mascota_foto', '0');
      } else {
        fd.append('use_mascota_foto', '1');
      }

      const recompensa = $('#postRecompensa').val();
      if (recompensa !== undefined) fd.append('recompensa', recompensa);

      $.ajax({
        url: window.getApiUrl('backend/controladores/PublicacionController.php'),
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
          if (res && res.success) {
            bsModal.hide();
            $('#newPostModal').on('hidden.bs.modal', function () { $(this).remove(); });
            $(document).trigger('publicacion:creada', [res]);
          } else {
            const msg = res && res.error ? res.error : 'Error al crear la publicación.';
            alert(msg);
          }
        },
        error: function(xhr) {
          let msg = 'Error al crear la publicación.';
          try {
            const json = JSON.parse(xhr.responseText || '{}');
            if (json && json.error) msg = json.error;
          } catch (e) {}
          alert(msg);
        }
      });
    });
  });
}
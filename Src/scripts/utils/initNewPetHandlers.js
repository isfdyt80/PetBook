export function initNewPetHandlers() {
  $(document).off('click', '#añadir_mascotas').on('click', '#añadir_mascotas', function (e) {
    e.preventDefault();
    $('#newPetModal').remove();

    const modalHtml = `
      <div class="modal fade" id="newPetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Agregar mascota</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <form id="newPetForm" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="petNombre" class="form-label">Nombre</label>
                  <input id="petNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="petFecha" class="form-label">Fecha de nacimiento</label>
                  <input id="petFecha" type="date" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label for="petRaza" class="form-label">Raza</label>
                  <select id="petRaza" class="form-select" required>
                    <option value="">Cargando...</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="petFotoFile" class="form-label">Foto (desde tu equipo)</label>
                  <input id="petFotoFile" type="file" accept="image/*" class="form-control">
                  <div class="mt-2">
                    <img id="petFotoPreview" src="" alt="Preview" style="display:none; width:120px; height:120px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                  </div>
                </div>

                <!-- usuario_id se toma del entorno (login). Si no está, se envía vacío -->
                <input type="hidden" id="petUsuarioId" value="${window.APP_CURRENT_USER_ID || ''}">

                <div class="text-end">
                  <button type="submit" class="btn btn-primary">Guardar</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;

    $('body').append(modalHtml);
    const modalEl = document.getElementById('newPetModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // cargar razas desde backend; si falla, usa fallback de prueba
    const razasUrl = window.getApiUrl('backend/controladores/MascotaController.php?action=razas');

    // Petición AJAX (jQuery) para traer razas
    $.ajax({
      url: razasUrl,
      method: 'GET',
      dataType: 'json',
      // Si necesitás enviar cookies en cross-domain, descomenta xhrFields:
      // xhrFields: { withCredentials: true },
      success: function(list) {
        if (!Array.isArray(list)) {
          console.warn('Formato de respuesta inesperado para razas', list);
          populateRazasFallback();
          return;
        }
        populateRazas(list);
      },
      error: function(xhr, status, err) {
        console.warn('No se pudieron cargar las razas desde backend:', status, err, xhr.responseText);
        populateRazasFallback();
      }
    });

    function populateRazas(list) {
      const $sel = $('#petRaza').empty();
      $sel.append('<option value="">Seleccioná una raza</option>');
      list.forEach(r => $sel.append(`<option value="${escapeHtml(r.id)}">${escapeHtml(r.nombre)}</option>`));
    }

    function populateRazasFallback() {
      const fallback = [
        { id: 1, nombre: 'Husky' },
        { id: 2, nombre: 'Labrador' },
        { id: 3, nombre: 'Criollo' }
      ];
      populateRazas(fallback);
    }

    // preview de la imagen local
    $('#petFotoFile').on('change', function () {
      const f = this.files && this.files[0];
      if (!f) {
        $('#petFotoPreview').hide().attr('src', '');
        return;
      }
      const reader = new FileReader();
      reader.onload = function (ev) {
        $('#petFotoPreview').attr('src', ev.target.result).show();
      };
      reader.readAsDataURL(f);
    });

    $('#newPetForm').on('submit', function (ev) {
      ev.preventDefault();

      const nombre = $('#petNombre').val().trim();
      const fecha_nacimiento = $('#petFecha').val();
      const raza_id = $('#petRaza').val();
      const usuario_id = $('#petUsuarioId').val();

      if (!nombre || !fecha_nacimiento || !raza_id) {
        alert('Completá todos los campos.');
        return;
      }

      const fileInput = $('#petFotoFile')[0];
      const file = fileInput.files && fileInput.files[0];

      // === Envío como FormData (multipart) ===
      const fd = new FormData();
      fd.append('nombre', nombre);
      fd.append('fecha_nacimiento', fecha_nacimiento);
      fd.append('raza_id', raza_id);
      // preferible tomar usuario_id en backend desde session; si no, lo enviamos
      if (usuario_id) fd.append('usuario_id', usuario_id);

      if (file) fd.append('foto', file);

      $.ajax({
        url: window.getApiUrl('backend/controladores/MascotaController.php'),
        method: 'POST',
        data: fd,
        processData: false, // importante para FormData
        contentType: false, // importante para FormData
        success: function (res) {
          modal.hide();
          $('#newPetModal').on('hidden.bs.modal', function () { $(this).remove(); });
          $(document).trigger('mascota:creada', [res]);
        },
        error: function (xhr) {
          let msg = 'Error al guardar la mascota.';
          try {
            const json = JSON.parse(xhr.responseText || '{}');
            if (json && json.error) msg = json.error;
          } catch (e) { /* ignore parse errors */ }
          alert(msg);
        }
      });
    });

    // util
    function escapeHtml(text) {
      return String(text || '').replace(/[&<>"'`=\/]/g, function (s) {
        return ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;' })[s];
      });
    }
  });
}
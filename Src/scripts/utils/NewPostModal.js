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
              <form id="newPostForm" enctype="multipart/form-data">
                <div class="mb-3">
                  <label for="postEstado" class="form-label">Estado</label>
                  <select id="postEstado" class="form-select" required>
                    <option value="perdido">Perdido</option>
                    <option value="adopcion">En adopción</option>
                  </select>
                  <div class="form-text">Marca si la mascota está perdida o en adopción.</div>
                </div>

                <div class="mb-3">
                  <label for="postNombre" class="form-label">Nombre de la mascota</label>
                  <input type="text" class="form-control" id="postNombre" placeholder="Ej: Max" required>
                </div>

                <div class="mb-3">
                  <label for="postEdad" class="form-label">Edad (años)</label>
                  <input type="number" min="0" step="1" class="form-control" id="postEdad" placeholder="Ej: 3" required>
                </div>

                <div class="mb-3">
                  <label for="postFotoFile" class="form-label">Foto (desde tu equipo)</label>
                  <input type="file" accept="image/*" class="form-control" id="postFotoFile" required>
                </div>

                <div class="mb-3">
                  <label for="postRaza" class="form-label">Raza</label>
                  <input type="text" class="form-control" id="postRaza" placeholder="Ej: Husky" required>
                </div>

                <div class="mb-3">
                  <label for="postDescripcion" class="form-label">Descripción</label>
                  <textarea id="postDescripcion" class="form-control" rows="3" placeholder="Describe la mascota..." required></textarea>
                </div>

                <div class="mb-3">
                  <label for="postUsuario" class="form-label">Usuario</label>
                  <input type="text" class="form-control" id="postUsuario" placeholder="Nombre del usuario" value="" disabled>
                  <div class="form-text">El usuario se completará cuando el sistema de login esté listo.</div>
                </div>

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
    const modal = new bootstrap.Modal(document.getElementById('newPostModal'));
    modal.show();

    $('#newPostForm').on('submit', function (e) {
      e.preventDefault();

      var formData = new FormData(document.getElementById("newPostForm"));
      $.ajax({
        url: "backend/controladores/PublicacionController.php",
        type: "post",
        dataType: "json",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
          if(data.success){
            console.log("todo ok!", data.message);
          }
          console.log(data.error);
        },
        error: function (error) {
          console.error("Error:", error);
        },
      });
    });
/*
      const estado = $('#postEstado').val();
      const nombre = $('#postNombre').val().trim();
      const edad = $('#postEdad').val();
      const fileInput = $('#postFotoFile')[0];
      const raza = $('#postRaza').val().trim();
      const descripcion = $('#postDescripcion').val().trim();
      const usuario = ''; // se llenará más tarde cuando implementes login

      if (!nombre || !edad || !fileInput.files.length || !raza || !descripcion) {
        alert('Por favor completá todos los campos y subí una foto.');
        return;
      }

      const file = fileInput.files[0];
      const reader = new FileReader();

      reader.onload = function (ev) {
        const fotoDataUrl = ev.target.result;
        const id = Date.now();

        // preview corto para la card
        const preview = descripcion.length > 120 ? descripcion.slice(0, 120) + '...' : descripcion;

        const newCard = `
          <a href="#" class="card p-1 col-md-3 ms-5 text-decoration-none post-card"
             data-id="${id}"
             data-estado="${escapeHtml(estado)}"
             data-nombre="${escapeHtml(nombre)}"
             data-edad="${escapeHtml(edad)}"
             data-foto="${escapeHtml(fotoDataUrl)}"
             data-raza="${escapeHtml(raza)}"
             data-descripcion="${escapeHtml(descripcion)}"
             data-usuario="${escapeHtml(usuario)}">
            <div>
              <img src="${escapeHtml(fotoDataUrl)}" class="card-img-top" alt="${escapeHtml(nombre)}" style="height:160px; object-fit:cover;">
              <div class="card-body">
                <h5 class="card-title">${escapeHtml(nombre)}</h5>
                <p class="card-text text-muted mb-1">${escapeHtml(raza)}</p>
                <p class="card-text small text-secondary mb-1 description-preview">${escapeHtml(preview)}</p>
                <small class="text-muted">Publicado por ${usuario || '—'}</small>
              </div>
            </div>
          </a>
        `;

        const targetRow = $('main .row').first();
        if (targetRow.length) {
          targetRow.append(newCard);
        } else {
          $('main').append(`<div class="row mt-3">${newCard}</div>`);
        }

        modal.hide();
        $('#newPostModal').on('hidden.bs.modal', function () {
          $(this).remove();
        });
      };

      reader.readAsDataURL(file);
    });

    function escapeHtml(text) {
      return String(text).replace(/[&<>"'`=\/]/g, function (s) {
        return ({
          '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
        })[s];
      });
    }
      */
  });
}
$(document).ready(function () {
    // Función para obtener publicaciones desde la base de datos
    function obtenerPublicaciones() {
        $.ajax({
            url: 'php/obtener_publicaciones.php', // Ruta del backend que devuelve las publicaciones
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                //console.log("consulta válida");
                // Verificar si la respuesta contiene publicaciones
                if (response.length > 0) {
                    response.forEach(function (publicacion) {
                        let htmlPublicacion = '';

                        // Construcción de la estructura según el tipo de publicación
                        switch (publicacion.tipo_publicacion) {
                            case 'perdido':
                                htmlPublicacion = `
                                   <div class="cartel mt-3">
    <!-- Contenido del cartel -->
    <div class="contenido-cartel">
      <!-- Información del usuario -->
      <div class="d-flex align-items-center mb-3">
        <img src="img/perfil_usuario.jpg" alt="Perfil de Usuario" class="img-perfil img-perfil-roja rounded-circle me-3">
        <div>
          <span class="nombre-usuario fw-bold fs-5">Usuario</span>
          <div class="fecha-publicacion text-muted fs-6">Publicado el ${publicacion.fecha_publicacion}</div>
        </div>
      </div>


      <!-- Franja Roja: "SE BUSCA" -->
      <div class="franja franja-roja">SE BUSCA</div>

      <!-- Franja Negra: "SE OFRECE RECOMPENSA" -->
      <div class="franja-negra">SE OFRECE RECOMPENSA</div>

      <!-- Contenido (Especificaciones y Carrusel) -->
      <div class="contenido">
        <!-- Información -->
        <div class="info-contenedor">
          <div class="info-seccion">
            <div class="mini-franja mini-franja-roja">DESCRIPCIÓN:</div>
            <p class="info-texto descripcion">${publicacion.descripcion}</p>
          </div>
          <div class="info-seccion">
            <div class="mini-franja mini-franja-roja">SE VIÓ POR ÚLTIMA VEZ EL:</div>
            <p class="info-texto fecha">${publicacion.falta_desde}</p>
          </div>
          <div class="info-seccion">
            <div class="mini-franja mini-franja-roja">UBICACIÓN O RADIO DONDE SE PERDIÓ:</div>
            <p class="info-texto ubicacion">${publicacion.ciudad + ", " + publicacion.provincia + ", " + publicacion.pais}</p>
          </div>
        </div>

        <!-- Carrusel -->
        <div class="carrusel-contenedor">
          <div id="carruselMascota" class="carousel slide carrusel-imagen carrusel-imagen-roja" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="php/${publicacion.foto_perfil}" class="d-block w-100" alt="Imagen 1">
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carruselMascota" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carruselMascota" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Siguiente</span>
            </button>
          </div>
          <div class="mini-franja mini-franja-roja">
            Nombre de la mascota
          </div>
        </div>
      </div>

      <!-- Texto y botón de contacto -->
      <p class="contact-text">Si lo ves, <br> por favor contactate al:</p>
      <a href="#" class="contact-btn contact-btn-roja">
        <img src="img/megafono.png" alt="Megáfono">
        <span class="telefono">${publicacion.telefono}</span>
      </a>
    </div>

    <!-- Botones verticales -->
    <div class="botones-verticales">
      <div class="boton-accion">
        <img src="img/reacciones/corazon.png" alt="Reaccionar">
      </div>
      <div class="boton-accion">
        <img src="img/reacciones/compartir.png" alt="Compartir">
      </div>
      <div class="boton-accion">
        <img src="img/reacciones/mensaje.png" alt="Enviar mensaje">
      </div>
      <div class="boton-accion">
        <img src="img/reacciones/favorito.png" alt="Favorito">
      </div>
    </div>
  </div>
  <hr class="mb-3">
                                `;
                                break;
                            case 'encontrado':
                                htmlPublicacion = `
                                     <div class="cartel mt-3">
        <!-- Contenido del cartel -->
        <div class="contenido-cartel">
            <!-- Información del usuario -->
            <div class="d-flex align-items-center mb-3">
                <img src="img/perfil_usuario.jpg" alt="Perfil de Usuario" class="img-perfil img-perfil-amarilla rounded-circle me-3">
                <div>
                    <span class="nombre-usuario fw-bold fs-5">Usuario</span>
                    <div class="fecha-publicacion text-muted fs-6">Publicado el ${publicacion.fecha_publicacion}</div>
                </div>
            </div>

            <!-- Franja Roja: "SE BUSCA" -->
            <div class="franja franja-amarilla">ANIMAL ENCONTRADO (BUSCAMOS A SUS DUEÑOS)</div>

            <!-- Contenido (Especificaciones y Carrusel) -->
            <div class="contenido">
                <!-- Información -->
                <div class="info-contenedor">
                    <div class="info-seccion">
                        <div class="mini-franja mini-franja-amarilla">DESCRIPCIÓN:</div>
                        <p class="info-texto descripcion">${publicacion.descripcion}</p>
                    </div>
                    <div class="info-seccion">
                        <div class="mini-franja mini-franja-amarilla">ENCONTRADO EL:</div>
                        <p class="info-texto fecha">${publicacion.encontrado_el}</p>
                    </div>
                    <div class="info-seccion">
                        <div class="mini-franja mini-franja-amarilla">UBICACIÓN O RADIO DONDE SE ENCONTRÓ:</div>
                        <p class="info-texto ubicacion">${publicacion.ciudad + ", " + publicacion.provincia + ", " + publicacion.pais}</p>
                    </div>
                </div>

                <!-- Carrusel -->
                <div class="carrusel-contenedor">
                    <div id="carruselMascota" class="carousel slide carrusel-imagen carrusel-imagen-amarilla" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="php/${publicacion.foto_perfil}" class="d-block w-100" alt="Imagen 1">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselMascota"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselMascota"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                    <div class="mini-franja mini-franja-amarilla">
                        Ayudemos a reecontrarse con sus dueños
                    </div>
                </div>
            </div>

            <!-- Texto y botón de contacto -->
            <p class="contact-text">Si sos o conoces al dueño, <br> por favor reclamalo:</p>
            <a href="#" class="contact-btn contact-btn-amarilla">
                <img src="img/megafono.png" alt="Megáfono">
                <span class="telefono">RECLAMAR ANIMAL</span>
            </a>

        </div>
        <!-- Botones verticales -->
        <div class="botones-verticales">
            <div class="boton-accion">
                <img src="img/reacciones/corazon.png" alt="Reaccionar">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/compartir.png" alt="Compartir">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/mensaje.png" alt="Enviar mensaje">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/favorito.png" alt="Favorito">
            </div>
        </div>

    </div>
    <hr class="mb-3">
                                `;
                                break;
                            case 'adopcion':
                                htmlPublicacion = `
                                   <div class="cartel mt-3">
        <!-- Contenido del cartel -->
        <div class="contenido-cartel">
            <!-- Información del usuario -->
            <div class="d-flex align-items-center mb-3">
                <img src="img/perfil_usuario.jpg" alt="Perfil de Usuario" class="img-perfil img-perfil-verde rounded-circle me-3">
                <div>
                    <span class="nombre-usuario fw-bold fs-5">Usuario</span>
                    <div class="fecha-publicacion text-muted fs-6">Publicado el ${publicacion.fecha_publicacion}</div>
                </div>
            </div>


            <!-- Franja Roja: "SE BUSCA" -->
            <div class="franja franja-verde">EN ADOPCIÓN</div>

            <!-- Contenido (Especificaciones y Carrusel) -->
            <div class="contenido">
                <!-- Información -->
                <div class="info-contenedor">
                    <div class="info-seccion">
                        <div class="mini-franja mini-franja-verde">DESCRIPCIÓN Y/O REQUISITOS PARA ADOPTAR:</div>
                        <p class="info-texto descripcion">${publicacion.descripcion}</p>
                    </div>
                    <div class="info-seccion">
                        <div class="mini-franja mini-franja-verde">UBICACIÓN:</div>
                        <p class="info-texto ubicacion">${publicacion.ciudad + ", " + publicacion.provincia + ", " + publicacion.pais}</p>
                    </div>
                </div>

                <!-- Carrusel -->
                <div class="carrusel-contenedor">
                    <div id="carruselMascota" class="carousel slide carrusel-imagen carrusel-imagen-verde" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="php/${publicacion.foto_perfil}" class="d-block w-100" alt="Imagen 1">
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carruselMascota"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carruselMascota"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones verticales -->
        <div class="botones-verticales">
            <div class="boton-accion">
                <img src="img/reacciones/corazon.png" alt="Reaccionar">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/compartir.png" alt="Compartir">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/mensaje.png" alt="Enviar mensaje">
            </div>
            <div class="boton-accion">
                <img src="img/reacciones/favorito.png" alt="Favorito">
            </div>
        </div>

    </div>
    <hr class="mb-3">
                                `;
                                break;
                        }

                        // Agregar la publicación al contenedor correspondiente
                        $('#contenedorPublicaciones').append(htmlPublicacion);
                    });
                }
            }
        });
    }

    // Llamar a la función para obtener las publicaciones al cargar la página
    obtenerPublicaciones();
});

<!DOCTYPE html>
<html lang="es">

</html>
<head>
    <!-- Define la codificación de caracteres para admitir caracteres especiales -->
    <meta charset="UTF-8">
    <!-- Configuración para que la página sea responsive en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petbook</title>

    <!-- Enlace a las hojas de estilo necesarias -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> <!-- Bootstrap para estilos y diseño responsivo -->
    <link rel="stylesheet" href="css/index.css"> <!-- Estilos personalizados -->
    <link rel="stylesheet" href="css/carteles.css"> <!-- Estilos generales de carteles -->
    <link rel="stylesheet" href="css/cartel_rojo.css"> <!-- Estilos para carteles de alerta roja -->
    <link rel="stylesheet" href="css/cartel_amarillo.css"> <!-- Estilos para carteles de alerta amarilla -->
    <link rel="stylesheet" href="css/cartel_verde.css"> <!-- Estilos para carteles de alerta verde -->

    <!-- Fuente de iconos Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Fuente personalizada desde Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Logo o nombre de la aplicación -->
            <a class="navbar-brand" href="#">PetBook</a>

            <!-- Botón de menú para pantallas pequeñas -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú de navegación -->
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#animales-perdidos">Animales Perdidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#animales-encontrados">Animales Encontrados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#animales-en-adopcion">Animales en Adopción</a>
                    </li>
                    <li class="nav-item">
                        <!-- Ícono de búsqueda que abre un modal -->
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#ModalBusqueda">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#informacion-util">Información Útil</a>
                    </li>
                </ul>
            </div>
         <!-- Botones de usuario -->
            <div>
                <!-- Botón para ir al perfil del usuario -->
                <button type="button" id="my_user" class="btn btn-secondary" onclick="window.location.href='my_user.php'">
                    <i class="fa-regular fa-user"></i>
                </button>
                <!-- Botón para cerrar sesión -->
                <button type="button" id="close_sesion" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <!-- Contenedor donde se mostrarán las publicaciones dinámicamente -->
        <div id="contenedorPublicaciones"></div>
    </div>

    <!-- Barra lateral derecha con botones de acciones -->
    <div class="right-sidebar">
        <!-- Botón para agregar una nueva publicación -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalNuevaPubli">
            <i class="fa-solid fa-plus"></i>
        </button>
        <!-- Botón para notificaciones -->
        <button class="btn btn-info" title="Notificaciones">
            <i class="fa-solid fa-bell"></i>
        </button>
        <!-- Botón para alertas -->
        <button class="btn btn-warning" title="Alertas">
            <i class="fa-solid fa-exclamation"></i>
        </button>
        <!-- Botón para mensajes -->
        <button class="btn btn-success" title="Mensajes">
            <i class="fa-solid fa-comment"></i>
        </button>
    </div>

    <!-- Inclusión de modales desde archivos externos -->
    <?php
    include 'modales/m-nueva-publicacion.html'; // Modal para agregar una nueva publicación
    include 'modales/m-perdido.html'; // Modal para reportar un animal perdido
    include 'modales/m-encontrado.html'; // Modal para reportar un animal encontrado
    include 'modales/m-adopcion.html'; // Modal para animales en adopción
    include 'modales/m-busqueda.html'; // Modal para búsqueda de animales
    ?>

    <!-- Scripts JS -->
    <script src="js/jquery-3.7.1.min.js"></script> <!-- jQuery para facilitar manipulaciones en la página -->
    <script src="/bootstrap/js/bootstrap.min.js"></script> <!-- Bootstrap JS para la funcionalidad del framework -->
    <script src="js/index.js"></script> <!-- Archivo de JavaScript principal de la página -->
    <script src="js/modales.js"></script> <!-- JavaScript para la gestión de modales -->
    <script src="js/libreria.sweetalert.js"></script> <!-- Librería para mostrar alertas personalizadas -->
    <script src="js/obtener_publicaciones.js"></script> <!-- Script para obtener las publicaciones dinámicamente -->
    <script src="js/procesar_publicacion.js"></script> <!-- Script para procesar publicaciones -->
</body>

</html>

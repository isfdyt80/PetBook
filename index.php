<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetBook</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>

    <!-- Barra de navegación superior -->
    <div class="top-nav">
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#animales-perdidos">Animales Perdidos</a>
            <a href="#animales-encontrados">Animales Encontrados</a>
            <a href="#animales-en-adopcion">Animales en Adopción</a>
            <a href="#informacion-util">Información Útil</a>
        </div>
        <div>
            <img src="/images/usuario.jpg" alt="Perfil" class="profile-img">
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 80px;">
        <h1>PetBook</h1>

        <!-- Boton abrir modal crear publicacion -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalNuevaPubli">
            <i class="fa-solid fa-plus"></i> Crear Publicación
        </button>
    </div>

    <!-- Barra lateral derecha -->
    <div class="right-sidebar">
        <button class="btn btn-primary" title="Nueva Publicación">
            <i class="fa-solid fa-plus"></i>
        </button>
        <button class="btn btn-info" title="Notificaciones">
            <i class="fa-solid fa-bell"></i>
        </button>
        <button class="btn btn-warning" title="Alertas">
            <i class="fa-solid fa-exclamation"></i>
        </button>
        <button class="btn btn-success" title="Mensajes">
            <i class="fa-solid fa-comment"></i>
        </button>
    </div>

    <?php
    include 'modales/m-nueva-publicacion.html';
    include 'modales/m-perdido.html';
    include 'modales/m-encontrado.html';
    include 'modales/m-adopcion.html';
    ?>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/index.js"></script>
    <script src="js/modales.js"></script>
    
</body>
</html>
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">PetBook</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
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
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#ModalBusqueda"><i class="fa-solid fa-magnifying-glass"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#informacion-util">Información Útil</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <img src="/images/usuario.jpg" alt="Perfil" class="me-2">
                </div>
                <button type="button" id="close_sesion" class="btn btn-secondary">Cerrar sesión</button>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <h1>PetBook</h1>

    </div>

    <!-- Barra lateral derecha -->
    <div class="right-sidebar">
        <button type="btn btn-primary" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalNuevaPubli">
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
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="js/index.js"></script>
    <script src="js/modales.js"></script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/my_user.css">
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#animales-perdidos">Animales Perdidos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#animales-encontrados">Animales Encontrados</a></li>
                    <li class="nav-item"><a class="nav-link" href="#animales-en-adopcion">Animales en Adopción</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#ModalBusqueda"><i class="fa-solid fa-magnifying-glass"></i></a></li>
                    <li class="nav-item"><a class="nav-link" href="#informacion-util">Información Útil</a></li>
                </ul>
            </div>
            <div>
                <button type="button" id="my_user" class="btn btn-secondary"><i class="fa-regular fa-user"></i></button>
                <button type="button" id="close_sesion" class="btn btn-secondary"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="user-card">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fa-regular fa-user"></i>
            </div>
            <div class="user-details">
                <b>username</b><br>
                joafossa@gmail.com <i class="fa-regular fa-envelope"></i><br>
                2395-40-7262 <i class="fa-solid fa-phone"></i>
            </div>
        </div>
        <div>
            <button class="btn btn-dark" id="edit_user">Edit User</button>
        </div>
    </div>
</html>

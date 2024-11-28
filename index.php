<!-- conexion -->
<?php
include 'modales/m-nueva-publicacion.html';
include 'modales/m-perdido.html';
include 'modales/m-encontrado.html';
include 'modales/m-adopcion.html';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetBook</title>
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <h1>PetBook</h1>

        <!-- Boton abrir modal crear publicacion -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalNuevaPubli">
            <i class="fa-solid fa-plus"></i>
        </button>

    </div>
    <div id="contenedorPublicaciones"></div>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/index.js"></script>
    <script src="/js/modales.js"></script>
    <script src="/js/form_perdido.js"></script>
</body>

</html>
// Espera a que todo el contenido del DOM esté completamente cargado antes de ejecutar el script
document.addEventListener("DOMContentLoaded", () => {
    console.log("Página cargada correctamente"); // Mensaje en la consola indicando que la página se ha cargado correctamente

    // Selecciona el botón flotante en la página
    const floatingButton = document.querySelector(".floating-button");

    // Agrega un evento de clic al botón flotante
    floatingButton.addEventListener("click", () => {
        alert("¡Vamos a crear una nueva publicación!"); // Muestra una alerta al hacer clic en el botón
    });
});

document.addEventListener("DOMContentLoaded", () => {
    console.log("Página cargada correctamente");

    const floatingButton = document.querySelector(".floating-button");

    floatingButton.addEventListener("click", () => {
        alert("¡Vamos a crear una nueva publicación!");
    });
});

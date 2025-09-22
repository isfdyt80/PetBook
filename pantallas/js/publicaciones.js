/*// Simulación de login con sessionStorage
let isLogged = sessionStorage.getItem("logged");

// Detectar clic en publicaciones
document.querySelectorAll(".publi").forEach(card => {
  card.addEventListener("click", function (e) {
    e.preventDefault();
    let id = this.dataset.publi;

    if (!isLogged) {
      // Guardamos la publicación que quería abrir
      sessionStorage.setItem("goToPubli", id);
      // Redirigimos al login
      window.location.href = "login.html";
    } else {
      // Si ya está logueado → mostrar modal
      let modal = new bootstrap.Modal(document.getElementById("modalPerro" + id));
      modal.show();
    }
  });
});

// Si viene del login y había una publicación pendiente
window.addEventListener("load", () => {
  isLogged = sessionStorage.getItem("logged");
  let goTo = sessionStorage.getItem("goToPubli");
  if (isLogged && goTo) {
    let modal = new bootstrap.Modal(document.getElementById("modalPerro" + goTo));
    modal.show();
    sessionStorage.removeItem("goToPubli"); // limpiamos
  }
});*/
// --- Detectar clic en publicaciones ---
    document.querySelectorAll(".publi").forEach(card => {
      card.addEventListener("click", function() {
        let id = this.dataset.publi;
        let modal = new bootstrap.Modal(document.getElementById("modalPerro" + id));
        modal.show();
      });
    });
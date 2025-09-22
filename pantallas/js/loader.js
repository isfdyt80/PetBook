document.addEventListener("DOMContentLoaded", function() {
  const loginForm = document.getElementById('loginForm');
  const loader = document.getElementById('loader');
  const card = document.querySelector('.card');

  loginForm.addEventListener('submit', function(e){
    e.preventDefault(); // evita enviar el formulario real

    // Oculta el login
    card.style.display = 'none';
    // Muestra loader
    loader.style.display = 'flex';

    // Simula carga y redirige después de 2 segundos
    setTimeout(()=>{
      window.location.href = 'index.html'; // cambia por tu página destino
    }, 2000);
  });
});

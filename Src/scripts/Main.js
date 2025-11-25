import { initPostModalHandlers } from './utils/Modal.js'
import { initNewPostHandlers } from './utils/NewPostModal.js';
import { initNewPetHandlers } from './utils/initNewPetHandlers.js';
import { loadPublicaciones } from './utils/loadPosts.js';

// Función auxiliar global para construir rutas de API
// Detecta si estamos en una carpeta (Public/) o en raíz (Index.html)
window.getApiUrl = function(endpoint) {
  // Si estamos en una subcarpeta (como Public/usuario.html), necesitamos ../
  // Si estamos en raíz (Index.html), necesitamos ./
  const isInSubfolder = window.location.pathname.includes('/Public/');
  const prefix = isInSubfolder ? '../' : './';
  return prefix + endpoint;
};

document.addEventListener('DOMContentLoaded', () => {
  initPostModalHandlers();
  initNewPostHandlers();
  initNewPetHandlers();
  loadPublicaciones();
});
import { initPostModalHandlers } from './utils/Modal.js'
import { initNewPostHandlers } from './utils/NewPostModal.js';
import { initNewPetHandlers } from './utils/initNewPetHandlers.js';

document.addEventListener('DOMContentLoaded', () => {
  initPostModalHandlers();
  initNewPostHandlers();
  initNewPetHandlers();
});
# Petbook — Historias de Usuario

Documento que define las funcionalidades del sistema desde la perspectiva de los distintos roles: Usuario, Moderador y Administrador.

---

# 🐶 Historias de Usuario — Usuario

### HU-01 — Registrarse en el sistema  
**Como** usuario  
**quiero** crear una cuenta  
**para** poder usar Petbook  

**Criterios de aceptación:**
- Se ingresan nombre, email y contraseña
- El email no puede repetirse
- La contraseña se guarda hasheada
- Se crea Persona + Usuario + UsuarioRol
- El sistema confirma el registro

---

### HU-02 — Iniciar sesión  
**Como** usuario  
**quiero** iniciar sesión  
**para** acceder a mi cuenta  

**Criterios de aceptación:**
- Se valida email y contraseña
- Usuario debe estar Activo = true y Eliminado = false
- Se inicia sesión con $_SESSION
- Se muestra error si las credenciales son incorrectas

---

### HU-03 — Cerrar sesión  
**Como** usuario  
**quiero** cerrar sesión  
**para** proteger mi cuenta  

**Criterios de aceptación:**
- Se destruye la sesión
- Se redirige al login

---

### HU-04 — Recuperar contraseña  
**Como** usuario  
**quiero** recuperar mi contraseña  
**para** volver a acceder  

**Criterios de aceptación:**
- Se genera un token único
- Tiene fecha de expiración
- Permite cambiar contraseña si el token es válido

---

### HU-05 — Crear mascota  
**Como** usuario  
**quiero** registrar una mascota  
**para** usarla en eventos  

**Criterios de aceptación:**
- Se requiere al menos especie
- Raza es opcional
- Se valida coherencia especie-raza
- Se guarda en BD

---

### HU-06 — Ver mis mascotas  
**Como** usuario  
**quiero** ver mis mascotas  
**para** gestionarlas  

**Criterios de aceptación:**
- Se listan mascotas asociadas al usuario
- Se muestran datos básicos

---

### HU-07 — Asociarme a una mascota  
**Como** usuario  
**quiero** vincularme a una mascota  
**para** indicar relación con ella  

**Criterios de aceptación:**
- Se crea registro en MascotaUsuario
- Puede ser dueño o no
- Se guarda fecha desde

---

### HU-08 — Crear evento de pérdida  
**Como** usuario  
**quiero** reportar una mascota perdida  
**para** encontrarla  

**Criterios de aceptación:**
- Se crea Mascota si no existe
- Se crea Ubicación
- Se crea EventoMascota (ACTIVO)
- Se crea HistorialEstadoEvento
- Se crea Publicación automáticamente

---

### HU-09 — Crear evento de mascota encontrada  
**Como** usuario  
**quiero** reportar una mascota encontrada  
**para** devolverla  

**Criterios de aceptación:**
- Igual flujo que HU-08
- TipoEvento = ENCONTRADA

---

### HU-10 — Crear evento de adopción  
**Como** usuario  
**quiero** publicar una mascota en adopción  
**para** encontrarle hogar  

---

### HU-11 — Cambiar estado de evento  
**Como** usuario  
**quiero** marcar un evento como resuelto o cancelado  
**para** reflejar su situación  

**Criterios de aceptación:**
- Solo permite ACTIVO → RESUELTO o CANCELADO
- Se guarda en historial
- No puede volver a ACTIVO

---

### HU-12 — Ver feed de publicaciones  
**Como** usuario  
**quiero** ver publicaciones  
**para** enterarme de eventos  

**Criterios de aceptación:**
- Solo publicaciones ACTIVAS
- Ordenadas por fecha
- Muestra datos básicos

---

### HU-13 — Ver detalle de publicación  
**Como** usuario  
**quiero** ver una publicación  
**para** conocer más información  

**Criterios de aceptación:**
- Muestra evento, mascota e imágenes
- Muestra ubicación

---

### HU-14 — Comentar publicación  
**Como** usuario  
**quiero** comentar  
**para** interactuar  

**Criterios de aceptación:**
- Se guarda comentario
- Se lista en la publicación

---

### HU-15 — Reaccionar a publicación  
**Como** usuario  
**quiero** reaccionar  
**para** expresar mi opinión  

**Criterios de aceptación:**
- Una reacción por usuario por publicación
- Puede modificarse

---

### HU-16 — Reportar contenido  
**Como** usuario  
**quiero** reportar contenido  
**para** mantener la comunidad segura  

**Criterios de aceptación:**
- Se crea reporte
- Debe tener publicación o comentario (no ambos)

---

# 🛡️ Historias de Usuario — Moderador

### HU-M01 — Ver reportes pendientes  
Como moderador quiero ver reportes para revisarlos  

---

### HU-M02 — Revisar reporte  
Como moderador quiero evaluar reportes para decidir acciones  

---

### HU-M03 — Suspender publicación  
Como moderador quiero ocultar publicaciones inapropiadas  

---

### HU-M04 — Cancelar evento  
Como moderador quiero invalidar eventos incorrectos  

---

### HU-M05 — Validar coincidencias  
Como moderador quiero confirmar duplicados de mascotas  

---

# 👑 Historias de Usuario — Administrador

### HU-A01 — Gestionar roles  
Como admin quiero asignar roles para controlar permisos  

---

### HU-A02 — Desactivar usuario  
Como admin quiero bloquear cuentas  

---

### HU-A03 — Anonimizar usuario  
Como admin quiero proteger datos personales  

---

### HU-A04 — Ver métricas  
Como admin quiero ver estadísticas del sistema  

---

### HU-A05 — Gestionar catálogos  
Como admin quiero administrar especies, razas y datos base  

---
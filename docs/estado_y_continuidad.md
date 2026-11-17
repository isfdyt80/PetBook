# Estado y continuidad de Petbook

Este documento contrasta las historias de usuario con las rutas declaradas y la implementación disponible en `app/controllers/`, `app/models/` y `app/views/`. Una ruta por sí sola no confirma que una función esté implementada: debe llegar a un método existente y contar con el flujo correspondiente.

## Criterio de estado

- **Implementada:** existe flujo de interfaz/ruta y lógica correspondiente en el código.
- **Parcial:** hay lógica de dominio o una parte del flujo, pero falta una interfaz, ruta o parte de los criterios de aceptación.
- **Pendiente:** no se encontró un flujo funcional en los controladores y vistas actuales.

El estado refleja una revisión estática del repositorio; no implica que se haya probado cada recorrido contra una base de datos en ejecución.

## Historias de usuario

### Usuario

| Historia | Estado | Evidencia y faltantes |
|---|---|---|
| HU-01 Registro | Implementada | `POST /registro` llega a `AuthController::registro()`; `Usuario::registrar()` crea usuario y persona en una transacción. Incluye validación, hash y límite de solicitudes. |
| HU-02 Inicio de sesión | Implementada | `POST /login` valida estado y contraseña, limita intentos, usa un mensaje genérico y crea sesión. |
| HU-03 Cerrar sesión | Implementada | `POST /logout` cierra la sesión. El Router valida CSRF para los POST. |
| HU-04 Recuperar contraseña | Parcial | `TokenRecuperacion` implementa operaciones de token y `app/routes.php` declara `/recuperar` y `/reset/:token`, pero `AuthController` no tiene los métodos enlazados ni existen sus vistas. No hay flujo de solicitud, correo y cambio de contraseña. |
| HU-05 Crear mascota | Implementada | `MascotaController::guardar()` y `Mascota::registrar()` validan especie/raza y registran la mascota. |
| HU-06 Ver mis mascotas | Implementada | `GET /mascota` lista las mascotas vinculadas al usuario; también hay vista de detalle. |
| HU-07 Asociarse a una mascota | Parcial | El registro de una mascota crea su vínculo con el usuario mediante `MascotaUsuario`; no se encontró un flujo independiente para asociarse a una mascota existente o gestionar la relación. |
| HU-08 Evento de pérdida | Parcial | El flujo usa una mascota ya registrada por el usuario y crea evento, ubicación opcional, historial y publicación dentro del modelo transaccional. La historia pide crear una mascota si no existe; actualmente debe seleccionarse una mascota propia. |
| HU-09 Evento de encontrada | Implementada | El formulario permite registrar los datos de una mascota encontrada y el modelo crea el evento y la publicación. |
| HU-10 Evento de adopción | Implementada | El tipo `ADOPCION` está contemplado en el formulario y en la creación del evento/publicación. |
| HU-11 Cambiar estado de evento | Implementada | El dueño puede pasar el evento a `RESUELTO` o `CANCELADO`; el modelo registra historial y archiva la publicación. |
| HU-12 Ver feed | Implementada | `GET /feed` llega a `PublicacionController::index()` y carga el feed. |
| HU-13 Ver detalle de publicación | Parcial | Existe detalle de evento en `GET /evento/:id`, con mascota, ubicación e historial. Sin embargo, la historia pide detalle de publicación e imágenes; `GET /publicacion/:id` apunta a `PublicacionController::ver()`, método que no existe. |
| HU-14 Comentar | Parcial | `Comentario` contiene creación/listado, pero la ruta apunta a `ComentarioController`, clase que no existe. No hay flujo de controlador/vista conectado. |
| HU-15 Reaccionar | Parcial | El modelo `Reaccion` permite crear y eliminar reacciones, pero las rutas apuntan a `PublicacionController::reaccionar()`, método inexistente. |
| HU-16 Reportar contenido | Parcial | `Reporte::crear()` valida que se indique publicación o comentario; las rutas de reporte apuntan a métodos/controladores inexistentes. |

### Moderador

| Historia | Estado | Evidencia y faltantes |
|---|---|---|
| HU-M01 Ver reportes pendientes | Parcial | `Reporte::listarPendientes()` existe; falta `ModeracionController` y su vista. |
| HU-M02 Revisar reporte | Parcial | El modelo permite asignar moderador y resolver reportes; falta el flujo HTTP de revisión. |
| HU-M03 Suspender publicación | Parcial | `Publicacion::cambiarEstado()` ofrece una operación de dominio; falta una interfaz de moderación conectada. |
| HU-M04 Cancelar evento | Parcial | El cambio de estado de evento está implementado para el dueño; no se encontró la acción de moderación ni autorización específica para moderadores. |
| HU-M05 Validar coincidencias | Parcial | `PosibleCoincidencia` ofrece operaciones para crear, listar y actualizar; falta controlador y vista que permitan al moderador gestionarlas. |

### Administrador

| Historia | Estado | Evidencia y faltantes |
|---|---|---|
| HU-A01 Gestionar roles | Parcial | Hay modelos de roles y asignación, y rutas declaradas; falta `AdminController` para atenderlas. |
| HU-A02 Desactivar usuario | Parcial | `Usuario::desactivar()` existe y hay una ruta declarada, pero falta `AdminController` y la interfaz. |
| HU-A03 Anonimizar usuario | Pendiente | No se encontró operación de anonimización ni flujo asociado. |
| HU-A04 Ver métricas | Pendiente | No se encontró implementación de métricas ni pantalla administrativa. |
| HU-A05 Gestionar catálogos | Parcial | Existen modelos para catálogos, pero no se encontró flujo administrativo para crearlos, editarlos o eliminarlos. |

## Rutas que requieren atención

En `app/routes.php` hay definiciones que actualmente no tienen un destino funcional completo:

- Recuperación de contraseña: `AuthController::showRecuperar()`, `recuperar()`, `showReset()` y `reset()` no existen.
- Publicaciones: faltan `PublicacionController::ver()`, `comentar()`, `reaccionar()`, `reportar()` y `eliminar()`.
- Comentarios: no existe `ComentarioController` para `reportar()` y `eliminar()`.
- Perfil de usuario: no existe `UsuarioController`.
- Moderación: no existe `ModeracionController`.
- Administración: no existe `AdminController`.

Estas rutas pueden producir errores de despacho al invocarse. Al completar cada función, actualizar historia, ruta, controlador, modelo, vista y esta tabla en el mismo cambio.

## Próximos pasos sugeridos

1. **Alinear rutas y código:** resolver los destinos ausentes o retirar temporalmente las rutas no disponibles para que no se anuncien como funcionales.
2. **Completar interacción con publicaciones:** implementar detalle de publicación, comentarios, reacciones y reportes; integrar los modelos ya existentes.
3. **Completar recuperación de contraseña:** construir solicitud, envío de correo, validación de token y cambio de clave; configurar SMTP en el entorno local.
4. **Construir los paneles de moderación y administración:** conectar operaciones de reportes, coincidencias, usuarios, roles y catálogos con controles de autorización.
5. **Revisar las historias:** actualizar HU-07 para distinguir vínculo al crear mascota de asociación posterior; precisar HU-08 sobre el requisito de seleccionar una mascota existente; distinguir detalle de evento y detalle de publicación en HU-13.
6. **Mantener este registro:** al terminar un flujo, revisar criterios de aceptación y marcarlo implementado solo cuando estén conectadas la ruta, lógica y presentación.

## Referencias

- [Historias de usuario](historias_usurio.md)
- [Rutas de la aplicación](../app/routes.php)
- [Guía de desarrollo](guia.md)
- [Guía técnica de componentes](guia_desarrollo.md)


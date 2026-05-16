-- =============================================================================
-- PETBOOK — Script de creación de base de datos
-- Motor: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS petbook;
CREATE DATABASE petbook
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE petbook;

-- =============================================================================
-- DOMINIO: IDENTIDAD Y ACCESO
-- =============================================================================

CREATE TABLE Persona (
    Id_Persona       INT          NOT NULL AUTO_INCREMENT,
    Nombre           VARCHAR(100) NULL,
    Apellido         VARCHAR(100) NULL,
    Fecha_Nacimiento DATE         NULL,
    Telefono         VARCHAR(20)  NULL,
    FechaCreacion    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado        BOOLEAN      NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_persona PRIMARY KEY (Id_Persona)
) ENGINE=InnoDB;

CREATE TABLE Usuario (
    Id_Usuario     INT          NOT NULL AUTO_INCREMENT,
    Id_Persona     INT          NOT NULL,
    Email          VARCHAR(191) NOT NULL,
    Password_Hash  VARCHAR(255) NOT NULL,
    Activo         BOOLEAN      NOT NULL DEFAULT TRUE,
    Fecha_Registro DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado      BOOLEAN      NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_usuario    PRIMARY KEY (Id_Usuario),
    CONSTRAINT uq_usuario_email UNIQUE (Email),
    CONSTRAINT fk_usuario_persona
        FOREIGN KEY (Id_Persona) REFERENCES Persona (Id_Persona)
) ENGINE=InnoDB;

CREATE TABLE Rol (
    Id_Rol INT         NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(50) NOT NULL,
    CONSTRAINT pk_rol PRIMARY KEY (Id_Rol),
    CONSTRAINT uq_rol_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE UsuarioRol (
    Id_UsuarioRol INT      NOT NULL AUTO_INCREMENT,
    Id_Usuario    INT      NOT NULL,
    Id_Rol        INT      NOT NULL,
    FechaAsignado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado     BOOLEAN  NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_usuariorol PRIMARY KEY (Id_UsuarioRol),
    CONSTRAINT fk_usuariorol_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_usuariorol_rol
        FOREIGN KEY (Id_Rol) REFERENCES Rol (Id_Rol)
) ENGINE=InnoDB;

CREATE TABLE TokenRecuperacion (
    Id_Token         INT          NOT NULL AUTO_INCREMENT,
    Id_Usuario       INT          NOT NULL,
    Token            VARCHAR(255) NOT NULL,
    Fecha_Expiracion DATETIME     NOT NULL,
    Usado            BOOLEAN      NOT NULL DEFAULT FALSE,
    Eliminado        BOOLEAN      NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_tokenrecuperacion PRIMARY KEY (Id_Token),
    CONSTRAINT uq_token UNIQUE (Token),
    CONSTRAINT fk_token_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: MASCOTAS
-- =============================================================================

CREATE TABLE Especie (
    Id_Especie INT         NOT NULL AUTO_INCREMENT,
    Nombre     VARCHAR(100) NOT NULL,
    CONSTRAINT pk_especie PRIMARY KEY (Id_Especie),
    CONSTRAINT uq_especie_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE Raza (
    Id_Raza    INT          NOT NULL AUTO_INCREMENT,
    Nombre     VARCHAR(100) NOT NULL,
    Id_Especie INT          NOT NULL,
    CONSTRAINT pk_raza PRIMARY KEY (Id_Raza),
    CONSTRAINT fk_raza_especie
        FOREIGN KEY (Id_Especie) REFERENCES Especie (Id_Especie)
) ENGINE=InnoDB;

CREATE TABLE Mascota (
    Id_Mascota          INT          NOT NULL AUTO_INCREMENT,
    Nombre              VARCHAR(100) NULL,
    Id_Especie          INT          NOT NULL,
    Id_Raza             INT          NULL,
    Color               VARCHAR(100) NULL,
    Tamaño              VARCHAR(50)  NULL,
    Sexo                VARCHAR(20)  NULL,
    Fecha_Nacimiento    DATE         NULL,
    Edad_Aproximada     VARCHAR(50)  NULL,
    Descripcion_Fisica  TEXT         NULL,
    Eliminado           BOOLEAN      NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_mascota PRIMARY KEY (Id_Mascota),
    CONSTRAINT fk_mascota_especie
        FOREIGN KEY (Id_Especie) REFERENCES Especie (Id_Especie),
    CONSTRAINT fk_mascota_raza
        FOREIGN KEY (Id_Raza) REFERENCES Raza (Id_Raza)
) ENGINE=InnoDB;

-- Nota: la coherencia entre Id_Especie e Id_Raza debe validarse en backend.
-- MySQL no puede expresar ese constraint sin un trigger.

CREATE TABLE MascotaUsuario (
    Id_MascotaUsuario INT      NOT NULL AUTO_INCREMENT,
    Id_Mascota        INT      NOT NULL,
    Id_Usuario        INT      NOT NULL,
    EsDueno           BOOLEAN  NOT NULL DEFAULT FALSE,
    FechaDesde        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FechaHasta        DATETIME NULL,
    CONSTRAINT pk_mascotausuario PRIMARY KEY (Id_MascotaUsuario),
    CONSTRAINT fk_mascotausuario_mascota
        FOREIGN KEY (Id_Mascota) REFERENCES Mascota (Id_Mascota),
    CONSTRAINT fk_mascotausuario_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

CREATE TABLE PosibleCoincidencia (
    Id              INT            NOT NULL AUTO_INCREMENT,
    Id_MascotaA     INT            NOT NULL,
    Id_MascotaB     INT            NOT NULL,
    Nivel_Confianza DECIMAL(5, 2)  NOT NULL DEFAULT 0.00,
    Revisado        BOOLEAN        NOT NULL DEFAULT FALSE,
    Resultado       VARCHAR(20)    NOT NULL DEFAULT 'PENDIENTE',
    Id_Usuario      INT            NULL,
    CONSTRAINT pk_posiblecoincidencia PRIMARY KEY (Id),
    CONSTRAINT chk_coincidencia_resultado
        CHECK (Resultado IN ('PENDIENTE', 'CONFIRMADO', 'DESCARTADO')),
    CONSTRAINT chk_coincidencia_mascotas_distintas
        CHECK (Id_MascotaA <> Id_MascotaB),
    CONSTRAINT fk_coincidencia_mascotaa
        FOREIGN KEY (Id_MascotaA) REFERENCES Mascota (Id_Mascota),
    CONSTRAINT fk_coincidencia_mascotab
        FOREIGN KEY (Id_MascotaB) REFERENCES Mascota (Id_Mascota),
    CONSTRAINT fk_coincidencia_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: UBICACIÓN
-- =============================================================================

CREATE TABLE Ubicacion (
    Id_Ubicacion INT            NOT NULL AUTO_INCREMENT,
    Direccion    VARCHAR(255)   NULL,
    Ciudad       VARCHAR(100)   NULL,
    Provincia    VARCHAR(100)   NULL,
    Pais         VARCHAR(100)   NULL,
    Latitud      DECIMAL(10, 7) NULL,
    Longitud     DECIMAL(10, 7) NULL,
    CONSTRAINT pk_ubicacion PRIMARY KEY (Id_Ubicacion)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: EVENTOS
-- =============================================================================

CREATE TABLE TipoEvento (
    Id_TipoEvento INT         NOT NULL AUTO_INCREMENT,
    Nombre        VARCHAR(50) NOT NULL,
    CONSTRAINT pk_tipoevento PRIMARY KEY (Id_TipoEvento),
    CONSTRAINT uq_tipoevento_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE EstadoEvento (
    Id_EstadoEvento INT         NOT NULL AUTO_INCREMENT,
    Nombre          VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estadoevento PRIMARY KEY (Id_EstadoEvento),
    CONSTRAINT uq_estadoevento_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE EventoMascota (
    Id_Evento         INT           NOT NULL AUTO_INCREMENT,
    Id_Mascota        INT           NOT NULL,
    Id_Usuario        INT           NOT NULL,
    Id_TipoEvento     INT           NOT NULL,
    Id_EstadoEvento   INT           NOT NULL,
    Descripcion       TEXT          NULL,
    Id_Ubicacion      INT           NULL,
    Fecha_Creacion    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Fecha_Resolucion  DATETIME      NULL,
    Fecha_UltimaVista DATETIME      NULL,
    Recompensa        DECIMAL(10,2) NULL,
    Eliminado         BOOLEAN       NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_eventomascota PRIMARY KEY (Id_Evento),
    CONSTRAINT fk_evento_mascota
        FOREIGN KEY (Id_Mascota) REFERENCES Mascota (Id_Mascota),
    CONSTRAINT fk_evento_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_evento_tipo
        FOREIGN KEY (Id_TipoEvento) REFERENCES TipoEvento (Id_TipoEvento),
    CONSTRAINT fk_evento_estado
        FOREIGN KEY (Id_EstadoEvento) REFERENCES EstadoEvento (Id_EstadoEvento),
    CONSTRAINT fk_evento_ubicacion
        FOREIGN KEY (Id_Ubicacion) REFERENCES Ubicacion (Id_Ubicacion)
) ENGINE=InnoDB;

CREATE TABLE HistorialEstadoEvento (
    Id_Historial    INT      NOT NULL AUTO_INCREMENT,
    Id_Evento       INT      NOT NULL,
    Id_EstadoEvento INT      NOT NULL,
    Id_Usuario      INT      NOT NULL,
    Fecha           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_historialestado PRIMARY KEY (Id_Historial),
    CONSTRAINT fk_historial_evento
        FOREIGN KEY (Id_Evento) REFERENCES EventoMascota (Id_Evento),
    CONSTRAINT fk_historial_estado
        FOREIGN KEY (Id_EstadoEvento) REFERENCES EstadoEvento (Id_EstadoEvento),
    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: PUBLICACIONES Y SOCIAL
-- =============================================================================

CREATE TABLE EstadoPublicacion (
    Id_EstadoPublicacion INT         NOT NULL AUTO_INCREMENT,
    Nombre               VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estadopublicacion PRIMARY KEY (Id_EstadoPublicacion),
    CONSTRAINT uq_estadopublicacion_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE Publicacion (
    Id_Publicacion       INT      NOT NULL AUTO_INCREMENT,
    Id_Evento            INT      NOT NULL,
    Id_Usuario           INT      NOT NULL,
    Id_EstadoPublicacion INT      NOT NULL,
    Contenido            TEXT     NULL,
    Fecha_Publicacion    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Editado              BOOLEAN  NOT NULL DEFAULT FALSE,
    Eliminado            BOOLEAN  NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_publicacion PRIMARY KEY (Id_Publicacion),
    CONSTRAINT fk_publicacion_evento
        FOREIGN KEY (Id_Evento) REFERENCES EventoMascota (Id_Evento),
    CONSTRAINT fk_publicacion_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_publicacion_estado
        FOREIGN KEY (Id_EstadoPublicacion) REFERENCES EstadoPublicacion (Id_EstadoPublicacion)
) ENGINE=InnoDB;

CREATE TABLE Comentario (
    Id_Comentario  INT      NOT NULL AUTO_INCREMENT,
    Id_Publicacion INT      NOT NULL,
    Id_Usuario     INT      NOT NULL,
    Contenido      TEXT     NOT NULL,
    Fecha          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado      BOOLEAN  NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_comentario PRIMARY KEY (Id_Comentario),
    CONSTRAINT fk_comentario_publicacion
        FOREIGN KEY (Id_Publicacion) REFERENCES Publicacion (Id_Publicacion),
    CONSTRAINT fk_comentario_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

CREATE TABLE TipoReaccion (
    Id_TipoReaccion INT         NOT NULL AUTO_INCREMENT,
    Nombre          VARCHAR(50) NOT NULL,
    CONSTRAINT pk_tiporeaccion PRIMARY KEY (Id_TipoReaccion),
    CONSTRAINT uq_tiporeaccion_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE Reaccion (
    Id_Usuario      INT NOT NULL,
    Id_Publicacion  INT NOT NULL,
    Id_TipoReaccion INT NOT NULL,
    CONSTRAINT pk_reaccion PRIMARY KEY (Id_Usuario, Id_Publicacion),
    CONSTRAINT fk_reaccion_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_reaccion_publicacion
        FOREIGN KEY (Id_Publicacion) REFERENCES Publicacion (Id_Publicacion),
    CONSTRAINT fk_reaccion_tipo
        FOREIGN KEY (Id_TipoReaccion) REFERENCES TipoReaccion (Id_TipoReaccion)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: MULTIMEDIA
-- =============================================================================

CREATE TABLE Imagen (
    Id_Imagen      INT          NOT NULL AUTO_INCREMENT,
    Url            VARCHAR(500) NOT NULL,
    Fecha_Creacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Eliminado      BOOLEAN      NOT NULL DEFAULT FALSE,
    CONSTRAINT pk_imagen PRIMARY KEY (Id_Imagen)
) ENGINE=InnoDB;

CREATE TABLE MascotaImagen (
    Id_MascotaImagen INT NOT NULL AUTO_INCREMENT,
    Id_Mascota       INT NOT NULL,
    Id_Imagen        INT NOT NULL,
    CONSTRAINT pk_mascotaimagen PRIMARY KEY (Id_MascotaImagen),
    CONSTRAINT uq_mascotaimagen UNIQUE (Id_Mascota, Id_Imagen),
    CONSTRAINT fk_mascotaimagen_mascota
        FOREIGN KEY (Id_Mascota) REFERENCES Mascota (Id_Mascota),
    CONSTRAINT fk_mascotaimagen_imagen
        FOREIGN KEY (Id_Imagen) REFERENCES Imagen (Id_Imagen)
) ENGINE=InnoDB;

CREATE TABLE PublicacionImagen (
    Id_PublicacionImagen INT NOT NULL AUTO_INCREMENT,
    Id_Publicacion       INT NOT NULL,
    Id_Imagen            INT NOT NULL,
    CONSTRAINT pk_publicacionimagen PRIMARY KEY (Id_PublicacionImagen),
    CONSTRAINT uq_publicacionimagen UNIQUE (Id_Publicacion, Id_Imagen),
    CONSTRAINT fk_publicacionimagen_publicacion
        FOREIGN KEY (Id_Publicacion) REFERENCES Publicacion (Id_Publicacion),
    CONSTRAINT fk_publicacionimagen_imagen
        FOREIGN KEY (Id_Imagen) REFERENCES Imagen (Id_Imagen)
) ENGINE=InnoDB;

-- =============================================================================
-- DOMINIO: MODERACIÓN
-- =============================================================================

CREATE TABLE EstadoReporte (
    Id_EstadoReporte INT         NOT NULL AUTO_INCREMENT,
    Nombre           VARCHAR(50) NOT NULL,
    CONSTRAINT pk_estadoreporte PRIMARY KEY (Id_EstadoReporte),
    CONSTRAINT uq_estadoreporte_nombre UNIQUE (Nombre)
) ENGINE=InnoDB;

CREATE TABLE Reporte (
    Id_Reporte       INT      NOT NULL AUTO_INCREMENT,
    Id_Usuario       INT      NOT NULL,
    Id_Publicacion   INT      NULL,
    Id_Comentario    INT      NULL,
    Id_EstadoReporte INT      NOT NULL,
    Id_Moderador     INT      NULL,
    Motivo           TEXT     NOT NULL,
    Fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Fecha_Resolucion DATETIME NULL,
    CONSTRAINT pk_reporte PRIMARY KEY (Id_Reporte),
    -- Exactamente uno de los dos debe ser no null. Validar en backend.
    CONSTRAINT chk_reporte_target
        CHECK (
            (Id_Publicacion IS NOT NULL AND Id_Comentario IS NULL) OR
            (Id_Publicacion IS NULL     AND Id_Comentario IS NOT NULL)
        ),
    CONSTRAINT fk_reporte_usuario
        FOREIGN KEY (Id_Usuario) REFERENCES Usuario (Id_Usuario),
    CONSTRAINT fk_reporte_publicacion
        FOREIGN KEY (Id_Publicacion) REFERENCES Publicacion (Id_Publicacion),
    CONSTRAINT fk_reporte_comentario
        FOREIGN KEY (Id_Comentario) REFERENCES Comentario (Id_Comentario),
    CONSTRAINT fk_reporte_estado
        FOREIGN KEY (Id_EstadoReporte) REFERENCES EstadoReporte (Id_EstadoReporte),
    CONSTRAINT fk_reporte_moderador
        FOREIGN KEY (Id_Moderador) REFERENCES Usuario (Id_Usuario)
) ENGINE=InnoDB;

-- =============================================================================
-- ÍNDICES
-- =============================================================================

-- Usuario
CREATE INDEX idx_usuario_email        ON Usuario (Email);
CREATE INDEX idx_usuario_activo       ON Usuario (Activo);

-- Mascota
CREATE INDEX idx_mascota_especie      ON Mascota (Id_Especie);
CREATE INDEX idx_mascota_raza         ON Mascota (Id_Raza);

-- MascotaUsuario
CREATE INDEX idx_mascotausuario_mascota ON MascotaUsuario (Id_Mascota);
CREATE INDEX idx_mascotausuario_usuario ON MascotaUsuario (Id_Usuario);

-- EventoMascota
CREATE INDEX idx_evento_mascota       ON EventoMascota (Id_Mascota);
CREATE INDEX idx_evento_usuario       ON EventoMascota (Id_Usuario);
CREATE INDEX idx_evento_tipo          ON EventoMascota (Id_TipoEvento);
CREATE INDEX idx_evento_estado        ON EventoMascota (Id_EstadoEvento);
CREATE INDEX idx_evento_fecha         ON EventoMascota (Fecha_Creacion);

-- Ubicacion (búsquedas geográficas Haversine)
CREATE INDEX idx_ubicacion_latlon     ON Ubicacion (Latitud, Longitud);

-- Publicacion
CREATE INDEX idx_publicacion_evento   ON Publicacion (Id_Evento);
CREATE INDEX idx_publicacion_usuario  ON Publicacion (Id_Usuario);
CREATE INDEX idx_publicacion_estado   ON Publicacion (Id_EstadoPublicacion);
CREATE INDEX idx_publicacion_fecha    ON Publicacion (Fecha_Publicacion);

-- Comentario
CREATE INDEX idx_comentario_publicacion ON Comentario (Id_Publicacion);
CREATE INDEX idx_comentario_usuario     ON Comentario (Id_Usuario);

-- HistorialEstadoEvento
CREATE INDEX idx_historial_evento     ON HistorialEstadoEvento (Id_Evento);

-- Reporte
CREATE INDEX idx_reporte_estado       ON Reporte (Id_EstadoReporte);
CREATE INDEX idx_reporte_moderador    ON Reporte (Id_Moderador);

-- TokenRecuperacion
CREATE INDEX idx_token_usuario        ON TokenRecuperacion (Id_Usuario);
CREATE INDEX idx_token_valor          ON TokenRecuperacion (Token);

-- =============================================================================
-- DATOS DE CATÁLOGO (seed obligatorio para que el sistema funcione)
-- =============================================================================

INSERT INTO Rol (Nombre) VALUES
    ('USUARIO'),
    ('MODERADOR'),
    ('ADMIN');

INSERT INTO TipoEvento (Nombre) VALUES
    ('PERDIDA'),
    ('ENCONTRADA'),
    ('ADOPCION');

INSERT INTO EstadoEvento (Nombre) VALUES
    ('ACTIVO'),
    ('RESUELTO'),
    ('CANCELADO');

INSERT INTO EstadoPublicacion (Nombre) VALUES
    ('ACTIVA'),
    ('ARCHIVADA'),
    ('SUSPENDIDA');

INSERT INTO EstadoReporte (Nombre) VALUES
    ('PENDIENTE'),
    ('REVISADO'),
    ('ACCIONADO'),
    ('DESESTIMADO');

INSERT INTO TipoReaccion (Nombre) VALUES
    ('LIKE'),
    ('LOVE'),
    ('TRISTE'),
    ('ENOJADO'),
    ('APOYO');

INSERT INTO Especie (Nombre) VALUES
    ('Perro'),
    ('Gato'),
    ('Ave'),
    ('Conejo'),
    ('Hurón'),
    ('Otro');

SET FOREIGN_KEY_CHECKS = 1;

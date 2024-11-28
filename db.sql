# crear db
create database petbook_db;

use petbook_db;

alter database petbook_db character set utf8 collate utf8_spanish2_ci;

# crear usuario
CREATE USER 'petbook_user' @'%' IDENTIFIED BY 'P3tB00k_P4ss';

GRANT ALL PRIVILEGES ON petbook_db.* TO 'petbook_user' @'%';

FLUSH PRIVILEGES;

CREATE TABLE mascotas (
    mascotaId INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    raza VARCHAR(100) NOT NULL,
    foto VARCHAR(1000) NOT NULL,
    tipodepicho VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    telefono VARCHAR(20), -- Campo opcional
    ubicacion VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255), -- Aquí se almacena la URL de la imagen del perfil
    verificado BOOLEAN NOT NULL DEFAULT FALSE,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;

CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Clave foránea hacia la tabla usuarios
    estado ENUM(
        'perdido',
        'encontrado',
        'adopcion'
    ) NOT NULL,
    descripcion TEXT NOT NULL,
    ubicacion VARCHAR(255),
    nombre_mascota VARCHAR(255),
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) -- Relación con la tabla usuarios
);

CREATE TABLE animales_perdidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    fecha_ult_vez DATE NOT NULL,
    valor_recompensa DECIMAL(10, 2),
    tel_dueño VARCHAR(20),
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones (id)
);

CREATE TABLE animales_encontrados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    tel_contacto VARCHAR(20),
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones (id)
);

CREATE TABLE animales_adopcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    requisitos_adopcion TEXT NOT NULL,
    tel_contacto VARCHAR(20),
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones (id)
);
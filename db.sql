# crear db
create database petbook_db;
use petbook_db;
alter database petbook_db character set utf8 collate utf8_spanish2_ci;

# crear usuario
CREATE USER 'petbook_user'@'%' IDENTIFIED BY 'P3tB00k_P4ss';
GRANT ALL PRIVILEGES ON petbook_db.* TO 'petbook_user'@'%';
FLUSH PRIVILEGES;

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
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE usuarios (
    usuarioId INT AUTO_INCREMENT PRIMARY KEY, 
    nombre VARCHAR(100) NOT NULL, 
    apellido VARCHAR(100) NOT NULL, 
    email VARCHAR(255) NOT NULL UNIQUE, 
    contraseña VARCHAR(255) NOT NULL, 
    telefono VARCHAR(20) NOT NULL, 
    ubicacion TEXT NOT NULL,  
    verificado BOOLEAN NOT NULL DEFAULT FALSE,
    activo BOOLEAN NOT NULL DEFAULT TRUE, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE publicaciones (
    publicacionId INT AUTO_INCREMENT PRIMARY KEY, 
    estado VARCHAR(100) NOT NULL, 
    usuarioId INT NOT NULL, 
    mascotaId INT,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    recompensa DECIMAL(10, 2),   
    descripcion TEXT,  
    activo BOOLEAN NOT NULL DEFAULT TRUE, 
    ubicacion TEXT,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 

    CONSTRAINT fk_publicaciones_usuario FOREIGN KEY (usuarioId) REFERENCES usuarios(usuarioId),
    CONSTRAINT fk_publicaciones_mascota FOREIGN KEY (mascotaId) REFERENCES mascotas(mascotaId)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

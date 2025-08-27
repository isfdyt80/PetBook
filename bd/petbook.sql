

/*
/*
Este archivo SQL define la estructura de la base de datos "PetBook". 

1. Se crea la base de datos "PetBook" con codificación UTF8MB4 para soportar caracteres especiales.
2. Se definen las tablas principales:
  - Roles: Tipos de usuario (administrador, usuario, etc.).
  - Usuarios: Información personal y credenciales de acceso.
  - Especies: Tipos de animales (perro, gato, etc.).
  - Razas: Razas asociadas a cada especie.
  - Mascotas: Mascotas registradas por los usuarios.
  - Publicaciones: Avisos sobre mascotas (perdidas, encontradas, adoptadas).
  - Comentarios: (comentado) Para interacción entre usuarios en publicaciones.
3. Se incluye un sistema de localización jerárquico:
  - Paises, Provincias, Partidos, Localidades, Calles.
4. Todas las tablas incluyen claves primarias y foráneas para mantener la integridad referencial.
5. Se establecen restricciones de unicidad y relaciones entre entidades.
6. El diseño permite registrar usuarios, asociarles mascotas, publicar avisos y gestionar la ubicación detallada de eventos o domicilios.

Este esquema es la base para una red social orientada a la comunidad de mascotas, facilitando la búsqueda, adopción y recuperación de animales.
*/
*/


-- Crear base de datos
CREATE DATABASE IF NOT EXISTS PetBook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base
USE PetBook;

-- Tabla de roles de usuario
CREATE TABLE Roles (
    rol_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL
);

-- Tabla de usuarios
CREATE TABLE Usuarios (
    usuario_id INT PRIMARY KEY AUTO_INCREMENT,
    rol_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    domicilio VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES Roles(rol_id)
);

-- Tabla de especies (perro, gato, etc.)
CREATE TABLE Especies (
    especie_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL
);

-- Tabla de razas
CREATE TABLE Razas (
    raza_id INT PRIMARY KEY AUTO_INCREMENT,
    especie_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    FOREIGN KEY (especie_id) REFERENCES Especies(especie_id)
);

-- Mascotas registradas por usuarios
CREATE TABLE Mascotas (
    mascota_id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    raza_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL, -- Ej: perdido, encontrado, adoptado
    foto VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_nacimiento DATE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id),
    FOREIGN KEY (raza_id) REFERENCES Razas(raza_id)
);

-- Publicaciones de usuarios sobre sus mascotas
CREATE TABLE Publicaciones (
    publicacion_id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    mascota_id INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    recompensa INT NOT NULL,
    estado VARCHAR(50) NOT NULL, -- Ej: activa, cerrada, resuelta
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id),
    FOREIGN KEY (mascota_id) REFERENCES Mascotas(mascota_id)
);

-- Comentarios de otros usuarios
/*
CREATE TABLE Comentarios (
    comentario_id INT PRIMARY KEY AUTO_INCREMENT,
    publicacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    texto VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publicacion_id) REFERENCES Publicaciones(publicacion_id),
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id)
);
*/

-- Localización
CREATE TABLE Paises (
    pais_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Provincias (
    provincia_id INT PRIMARY KEY AUTO_INCREMENT,
    pais_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (pais_id) REFERENCES Paises(pais_id)
);

CREATE TABLE Partidos (
    partido_id INT PRIMARY KEY AUTO_INCREMENT,
    provincia_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (provincia_id) REFERENCES Provincias(provincia_id)
);

CREATE TABLE Localidades (
    localidad_id INT PRIMARY KEY AUTO_INCREMENT,
    partido_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (partido_id) REFERENCES Partidos(partido_id)
);

CREATE TABLE Calles (
    calle_id INT PRIMARY KEY AUTO_INCREMENT,
    localidad_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (localidad_id) REFERENCES Localidades(localidad_id)
);









-- Esta plantilla fue exportada desde phpMyAdmin. 
-- Contiene la estructura y/o los datos de la base de datos seleccionada, 
-- permitiendo su restauración o migración a otro entorno MySQL. 
-- Incluye instrucciones para crear tablas, insertar datos y definir claves, 
-- facilitando la administración y respaldo de la base de datos.











-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-07-2025 a las 17:27:22
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `petbook`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calles`
--

CREATE TABLE `calles` (
  `calle_id` int(11) NOT NULL,
  `localidad_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especies`
--

CREATE TABLE `especies` (
  `especie_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `localidad_id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `mascota_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `raza_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paises`
--

CREATE TABLE `paises` (
  `pais_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `partido_id` int(11) NOT NULL,
  `provincia_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `provincia_id` int(11) NOT NULL,
  `pais_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mascota_id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `recompensa` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `razas`
--

CREATE TABLE `razas` (
  `raza_id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `domicilio` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calles`
--
ALTER TABLE `calles`
  ADD PRIMARY KEY (`calle_id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `localidad_id` (`localidad_id`);

--
-- Indices de la tabla `especies`
--
ALTER TABLE `especies`
  ADD PRIMARY KEY (`especie_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`localidad_id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `partido_id` (`partido_id`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`mascota_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `raza_id` (`raza_id`);

--
-- Indices de la tabla `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`pais_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`partido_id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `provincia_id` (`provincia_id`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`provincia_id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `pais_id` (`pais_id`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`publicacion_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `mascota_id` (`mascota_id`);

--
-- Indices de la tabla `razas`
--
ALTER TABLE `razas`
  ADD PRIMARY KEY (`raza_id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `especie_id` (`especie_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`rol_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuario_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calles`
--
ALTER TABLE `calles`
  MODIFY `calle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especies`
--
ALTER TABLE `especies`
  MODIFY `especie_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `localidad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `mascota_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paises`
--
ALTER TABLE `paises`
  MODIFY `pais_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `partido_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `provincia_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `publicacion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `razas`
--
ALTER TABLE `razas`
  MODIFY `raza_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calles`
--
ALTER TABLE `calles`
  ADD CONSTRAINT `calles_ibfk_1` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`localidad_id`);

--
-- Filtros para la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD CONSTRAINT `localidades_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`partido_id`);

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`),
  ADD CONSTRAINT `mascotas_ibfk_2` FOREIGN KEY (`raza_id`) REFERENCES `razas` (`raza_id`);

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`provincia_id`);

--
-- Filtros para la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD CONSTRAINT `provincias_ibfk_1` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`pais_id`);

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`),
  ADD CONSTRAINT `publicaciones_ibfk_2` FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`mascota_id`);

--
-- Filtros para la tabla `razas`
--
ALTER TABLE `razas`
  ADD CONSTRAINT `razas_ibfk_1` FOREIGN KEY (`especie_id`) REFERENCES `especies` (`especie_id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

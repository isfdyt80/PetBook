-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-11-2025 a las 00:34:52
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especies`
--

CREATE TABLE `especies` (
  `especie_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especies`
--

INSERT INTO `especies` (`especie_id`, `nombre`, `descripcion`) VALUES
(1, 'Perro', 'Mamífero doméstico comúnmente utilizado como mascota y compañero.'),
(2, 'Gato', 'Felino doméstico, conocido por su independencia y agilidad.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `localidad_id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `mascota_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `raza_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`mascota_id`, `usuario_id`, `raza_id`, `nombre`, `foto`, `activo`, `fecha_nacimiento`, `fecha_creacion`) VALUES
(3, 2, 1, 'Buddy', 'uploads/mascotas/buddy.jpg', 1, '2022-03-15', '2025-11-04 20:19:38'),
(4, 2, 2, 'Luna', 'uploads/mascotas/luna.jpg', 1, '2023-05-10', '2025-11-04 20:19:38'),
(5, 2, 4, 'Paco', 'uploads/mascotas/mascota_690a980f3d2ac_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 1, '2025-11-01', '2025-11-04 21:19:27'),
(6, 2, 2, 'Darma', 'uploads/mascotas/mascota_690a9829398cc_dog_PNG50258.png', 1, '2025-10-31', '2025-11-04 21:19:53'),
(7, 2, 3, 'Marco Aurelio', 'uploads/mascotas/mascota_6925436e6b84e_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 1, '2025-01-10', '2025-11-25 02:49:34'),
(8, 2, 2, 'Joselo', 'uploads/mascotas/mascota_6925dc2c4f58e_descarga.png', 1, '2025-11-01', '2025-11-25 13:41:16'),
(9, 2, 4, 'Jumper', 'uploads/mascotas/mascota_69263745302e6_descarga.png', 1, '2022-06-16', '2025-11-25 20:09:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paises`
--

CREATE TABLE `paises` (
  `pais_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `partido_id` int(11) NOT NULL,
  `provincia_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `provincia_id` int(11) NOT NULL,
  `pais_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `recompensa` int(11) DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`publicacion_id`, `usuario_id`, `mascota_id`, `descripcion`, `foto`, `ubicacion`, `recompensa`, `estado`, `activo`, `fecha_creacion`) VALUES
(1, 2, 6, 'se perdio', 'uploads/mascotas/mascota_690a9829398cc_dog_PNG50258.png', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-04 21:40:39'),
(2, 2, 6, 'Vendo gansos 500$ 2435223233', 'uploads/publicaciones/pub_6926318c9da52.jpg', 'Laguna los pisos', 500, 'adopcion', 1, '2025-11-04 21:41:38'),
(3, 2, 5, 'Una desgracia de animal, por favor llevenlo o se lo doy a los chinos de la castelli', 'uploads/publicaciones/pub_6913b664d0208.png', 'Laguna los pisos', 0, 'adopcion', 1, '2025-11-11 19:19:16'),
(4, 2, 5, 'Gestiona empresas', 'uploads/publicaciones/pub_69263179f3e50.jpg', 'Laguna los pisos', 0, 'perdido', 1, '2025-11-11 19:41:37'),
(5, 2, 5, 'foda', 'uploads/publicaciones/pub_6913c0d87d30d.jpg', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-11 20:03:52'),
(6, 2, 3, 'Ayuda', 'uploads/publicaciones/pub_69253d939d462.jpg', 'laguna', 500, 'perdido', 1, '2025-11-11 20:23:35'),
(7, 2, 7, 'Se perdio ayer ayuda!!', 'uploads/mascotas/mascota_6925436e6b84e_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 'Laguna los pisos', 350, 'perdido', 0, '2025-11-25 02:50:12'),
(8, 2, 7, 'xd', 'uploads/mascotas/mascota_6925436e6b84e_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-25 02:59:11'),
(9, 2, 6, 'dfsd', 'uploads/publicaciones/pub_6925d7d2ae237.jpg', 'Laguna los pisos', 550, 'perdido', 0, '2025-11-25 02:59:39'),
(10, 2, 4, 'fd', 'uploads/mascotas/luna.jpg', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-25 03:00:13'),
(11, 2, 7, 'ddd', 'uploads/mascotas/mascota_6925436e6b84e_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-25 03:00:59'),
(12, 2, 8, 'Requiere actualizar.', 'uploads/publicaciones/pub_6925ddc61a689.png', 'Laguna los pisos', 2520, 'perdido', 1, '2025-11-25 13:41:49'),
(13, 2, 5, 'prueba', 'uploads/publicaciones/pub_6925dd0580859.jpg', 'Laguna los pisos', 5000, 'perdido', 0, '2025-11-25 13:44:53'),
(14, 2, 8, 'prueba2', 'uploads/mascotas/mascota_6925dc2c4f58e_descarga.png', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-25 13:46:32'),
(15, 2, 5, 'prueba23', 'uploads/mascotas/mascota_690a980f3d2ac_- Experiencia de nuestros colaboradores - Servicio al cliente - Proximidad al cliente.jpg', 'Laguna los pisos', NULL, 'perdido', 0, '2025-11-25 13:46:56'),
(16, 2, 7, 'ASA', 'uploads/publicaciones/pub_69262f01988ed.png', 'Laguna los pisos', 0, 'perdido', 0, '2025-11-25 19:34:26'),
(17, 2, 4, 'Hola', 'uploads/publicaciones/pub_692631c589143.jpg', 'Laguna los pisos', NULL, 'perdido', 1, '2025-11-25 19:46:29'),
(18, 2, 9, 'Se llama jumper', 'uploads/publicaciones/pub_692638713a647.jpg', 'Laguna los pisos 2', 500, 'adopcion', 0, '2025-11-25 20:13:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `razas`
--

CREATE TABLE `razas` (
  `raza_id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `razas`
--

INSERT INTO `razas` (`raza_id`, `especie_id`, `nombre`, `descripcion`) VALUES
(1, 1, 'Labrador Retriever', 'Perro de tamaño mediano-grande, muy sociable y activo.'),
(2, 1, 'Bulldog Francés', 'Perro pequeño de orejas grandes, tranquilo y afectuoso.'),
(3, 2, 'Siamés', 'Gato elegante de pelaje corto y ojos azules intensos.'),
(4, 2, 'Persa', 'Gato de pelaje largo y carácter calmado.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `rol_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`rol_id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total del sistema'),
(2, 'Usuario', 'Usuario común de la plataforma');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario_id`, `rol_id`, `nombre`, `apellido`, `email`, `clave`, `domicilio`, `foto`, `activo`, `fecha_creacion`) VALUES
(2, 2, 'Juan', 'González', 'juan@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Calle Falsa 123', 'default.png', 1, '2025-09-01 15:47:47'),
(5, 2, 'Parnup', 'Tipup', 'parnup@gmail.com', '$2y$10$mgMnW.qJqQSq.FNsSERcbeY9HJOR0D7qPSoxysPoFOcc2hiKQJ0Yu', 'Laguna los pisos', '', 1, '2025-09-01 15:52:00'),
(6, 2, 'Paco', 'Jerez', 'jorge@mail.com', '$2y$10$bTrmvKhe6CLhhBJLfHFYH.l9w8GAFW1g2Vg1awBk8rR8bw5/aLSfW', 'ASDASD', '', 1, '2025-09-02 20:24:52'),
(7, 2, 'Marcos', 'Aurelio', 'aura@mail.com', '$2y$10$mCHbFvAhYNHMP/h3ySoPM.KWKatVUaFkr4eftPlkUV/yNCyt0HZCW', 'Gaias 25', '', 1, '2025-10-28 16:10:25');

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
  MODIFY `especie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `localidad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `mascota_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `publicacion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `razas`
--
ALTER TABLE `razas`
  MODIFY `raza_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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

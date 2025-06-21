-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-05-2025 a las 00:22:45
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
-- Base de datos: `camola41_asistencia_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`id`, `name`, `is_deleted`) VALUES
(1, 'Recursos Humanos', 0),
(2, 'Contabilidad', 0),
(3, 'Atención al Cliente', 0),
(4, 'Marketing', 0),
(5, 'Tecnología de la Información', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assistances`
--

CREATE TABLE `assistances` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `state_assistance` tinyint(1) NOT NULL,
  `photo_start` varchar(255) DEFAULT NULL,
  `location_start` varchar(255) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `photo_end` varchar(255) DEFAULT NULL,
  `location_end` varchar(255) DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `notificado` tinyint(1) DEFAULT 0,
  `type_login` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `assistances`
--

INSERT INTO `assistances` (`id`, `user_id`, `state_assistance`, `photo_start`, `location_start`, `date_start`, `photo_end`, `location_end`, `date_end`, `created_at`, `updated_at`, `is_deleted`, `notificado`, `type_login`) VALUES
(416, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-05 06:30:56', '../public/img/user_44_1746479221_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-05 16:07:01', '2025-05-05 06:30:56', '2025-05-05 16:07:01', 0, 0, 1),
(417, 49, 1, '../public/img/user_49_1746451993.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-05 08:33:13', '../public/img/user_49_1746484311_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-05 17:31:51', '2025-05-05 08:33:13', '2025-05-05 17:31:51', 0, 0, 2),
(418, 45, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-05 23:59:00', '2025-05-06 19:36:41', 0, 0, 0),
(419, 44, 1, '../public/img/user_44_1746531273.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-06 06:34:33', '../public/img/user_44_1746565561_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-06 16:06:01', '2025-05-06 06:34:33', '2025-05-06 16:06:01', 0, 0, 1),
(420, 45, 1, '../public/img/user_45_1746536732.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-06 08:05:32', '../public/img/user_45_1746571683_exit.jpg', '-12.035324, -77.054807', '2025-05-06 17:48:03', '2025-05-06 08:05:32', '2025-05-06 20:26:26', 0, 1, 2),
(421, 49, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-06 23:59:00', '2025-05-06 23:59:00', 0, 0, 0),
(425, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-07 06:32:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-07 16:00:21', '2025-05-07 06:32:56', '2025-05-07 22:37:14', 0, 0, 1),
(426, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-07 06:53:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-07 16:11:51', '2025-05-07 06:53:13', '2025-05-07 22:33:39', 0, 0, 1),
(427, 45, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-07 23:59:00', '2025-05-07 23:59:00', 0, 0, 0),
(428, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 16:00:21', '2025-05-08 06:30:56', '2025-05-07 22:39:07', 0, 0, 1),
(429, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 09:59:53', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 17:31:51', '2025-05-08 09:59:53', '2025-05-07 22:33:33', 0, 0, 2),
(430, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 07:50:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-08 16:30:00', '2025-05-08 07:50:00', '2025-05-07 22:33:32', 0, 0, 1),
(437, 44, 3, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-09 23:59:00', '2025-05-09 23:59:00', 0, 0, 0),
(438, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-09 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-09 17:31:51', '2025-05-09 08:13:13', '2025-05-07 22:33:28', 0, 0, 2),
(439, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-09 07:30:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-09 16:30:00', '2025-05-09 07:30:00', '2025-05-07 22:33:25', 0, 0, 1),
(440, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-12 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-12 16:00:21', '2025-05-12 06:30:56', '2025-05-07 22:33:24', 0, 0, 1),
(441, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-12 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-12 17:31:51', '2025-05-12 08:13:13', '2025-05-07 22:33:21', 0, 0, 2),
(442, 45, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-12 23:59:00', '2025-05-12 23:59:00', 0, 0, 0),
(443, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 16:00:21', '2025-05-13 06:30:56', '2025-05-07 22:33:19', 0, 0, 1),
(444, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 08:33:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 17:31:51', '2025-05-13 08:33:13', '2025-05-07 22:33:18', 0, 0, 2),
(445, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 07:10:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-13 16:30:00', '2025-05-13 07:10:00', '2025-05-07 22:33:14', 0, 0, 1),
(446, 44, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-14 23:59:00', '2025-05-14 23:59:00', 0, 0, 0),
(447, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-14 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-14 17:31:51', '2025-05-14 08:13:13', '2025-05-07 22:33:12', 0, 0, 2),
(448, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-14 07:10:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-14 16:30:00', '2025-05-14 07:10:00', '2025-05-07 22:33:11', 0, 0, 1),
(449, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-15 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-15 16:00:21', '2025-05-15 06:30:56', '2025-05-07 22:33:10', 0, 0, 1),
(450, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-15 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-15 17:31:51', '2025-05-15 08:13:13', '2025-05-07 22:36:21', 0, 0, 2),
(451, 45, 3, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-15 23:59:00', '2025-05-15 23:59:00', 0, 0, 0),
(452, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 16:00:21', '2025-05-16 06:30:56', '2025-05-07 22:36:25', 0, 0, 1),
(453, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 17:31:51', '2025-05-16 08:13:13', '2025-05-07 22:36:36', 0, 0, 2),
(454, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 07:10:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-16 16:30:00', '2025-05-16 07:10:00', '2025-05-07 22:36:35', 0, 0, 1),
(455, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-19 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-19 16:00:21', '2025-05-19 06:30:56', '2025-05-07 22:36:33', 0, 0, 1),
(456, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-19 08:33:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-19 17:31:51', '2025-05-19 08:33:13', '2025-05-07 22:36:32', 0, 0, 2),
(457, 45, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-19 23:59:00', '2025-05-19 23:59:00', 0, 0, 0),
(458, 44, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 06:30:56', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 16:00:21', '2025-05-20 06:30:56', '2025-05-07 22:39:56', 0, 0, 1),
(459, 49, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 08:13:13', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 17:31:51', '2025-05-20 08:13:13', '2025-05-07 22:39:51', 0, 0, 2),
(460, 45, 1, '../public/img/user_44_1746444656.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 07:10:00', '../public/img/user_45_1746571683_exit.jpg', '-12.035564422607422, -77.05567169189453', '2025-05-20 16:30:00', '2025-05-20 07:10:00', '2025-05-07 22:39:49', 0, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `automated_tasks`
--

CREATE TABLE `automated_tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `automated_tasks`
--

INSERT INTO `automated_tasks` (`id`, `name`, `user_id`, `date_start`, `date_end`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(22, 'Reposo', 45, '2025-05-05 00:00:00', '2025-05-05 00:00:00', 0, 0, '2025-05-06 17:10:52', '2025-05-06 17:10:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `position`
--

CREATE TABLE `position` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `position`
--

INSERT INTO `position` (`id`, `name`, `is_deleted`) VALUES
(1, 'Gerente General', 0),
(2, 'Gerente de Recursos Humanos', 0),
(5, 'Ejecutivo de Venta', 0),
(6, 'Técnico de Soporte', 0),
(7, 'Analista de Marketing', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `is_activated` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `lastname`, `is_activated`, `is_deleted`, `is_admin`) VALUES
(25, '000', '$2y$10$Yk92wjEk/RFweHfIUvADTO1WHKHLNO9vgmpfrNRNjVNHVbyFNQrw.', 'admin', 'admin', 0, 0, 1),
(44, '222', '$2y$10$5y4cLmbfBQt/cF6PU.DqRerysTe48AtbT54JrSYGBEJSl81Q/OjU2', 'Joaquin', 'Ramos', 0, 0, 0),
(45, '333', '$2y$10$6HSh79KrQE63TkLcAIvOWuk3SNJEbTIZLyF7.j0spXsB3BhxwAt3y', 'Raul', 'Meneses', 0, 0, 0),
(49, '444', '$2y$10$et5bASPEMWSOv7Ni/bOP0.ICkD3TwKzEhMZLoluX4YHdgionz6TXi', 'ana', 'BBB', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workers`
--

CREATE TABLE `workers` (
  `id` int(11) NOT NULL,
  `num_doc` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `id_times` int(11) DEFAULT NULL,
  `id_time_presencial` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `workers`
--

INSERT INTO `workers` (`id`, `num_doc`, `name`, `lastname`, `email`, `user_id`, `is_deleted`, `id_times`, `id_time_presencial`, `area_id`, `position_id`) VALUES
(27, '000', 'admin', 'admin', 'admin@gmial.com', 25, 0, NULL, NULL, NULL, NULL),
(44, '222', 'Joaquin', 'Ramos', '3027joaquin@gmail.com', 44, 0, 1, 3, 5, 6),
(45, '333', 'Raul', 'Meneses', 'nijor82990@benznoi.com', 45, 0, 2, 3, 4, 5),
(49, '444', 'ana', 'BBB', 'afaf@1afafaf', 49, 0, 1, 3, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `work_times`
--

CREATE TABLE `work_times` (
  `id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `hour_time_ini` time DEFAULT NULL,
  `hour_time_end` time DEFAULT NULL,
  `type_login` int(11) NOT NULL,
  `is_activated` tinyint(1) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `work_times`
--

INSERT INTO `work_times` (`id`, `description`, `hour_time_ini`, `hour_time_end`, `type_login`, `is_activated`, `is_deleted`) VALUES
(1, '1 Turno Virtual', '06:30:00', '16:00:00', 1, 1, 0),
(2, '2 Turno Virtual', '07:00:00', '16:30:00', 1, 1, 0),
(3, 'Turno Presencial', '08:00:00', '17:30:00', 2, 1, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `assistances`
--
ALTER TABLE `assistances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `automated_tasks`
--
ALTER TABLE `automated_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_doc` (`num_doc`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_workers_times` (`id_times`),
  ADD KEY `fk_workers_id_time_presencial` (`id_time_presencial`),
  ADD KEY `fk_workers_area` (`area_id`),
  ADD KEY `fk_workers_position` (`position_id`);

--
-- Indices de la tabla `work_times`
--
ALTER TABLE `work_times`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `assistances`
--
ALTER TABLE `assistances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=462;

--
-- AUTO_INCREMENT de la tabla `automated_tasks`
--
ALTER TABLE `automated_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `position`
--
ALTER TABLE `position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `work_times`
--
ALTER TABLE `work_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `assistances`
--
ALTER TABLE `assistances`
  ADD CONSTRAINT `assistances_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `automated_tasks`
--
ALTER TABLE `automated_tasks`
  ADD CONSTRAINT `automated_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `fk_workers_area` FOREIGN KEY (`area_id`) REFERENCES `area` (`id`),
  ADD CONSTRAINT `fk_workers_id_time_presencial` FOREIGN KEY (`id_time_presencial`) REFERENCES `work_times` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_workers_position` FOREIGN KEY (`position_id`) REFERENCES `position` (`id`),
  ADD CONSTRAINT `fk_workers_times` FOREIGN KEY (`id_times`) REFERENCES `work_times` (`id`),
  ADD CONSTRAINT `workers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 17-04-2026 a las 16:59:13
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `req_ti`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inks`
--

CREATE TABLE `inks` (
  `id` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL,
  `colegio` varchar(10) NOT NULL DEFAULT 'DP',
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inks`
--

INSERT INTO `inks` (`id`, `brand`, `model`, `color`, `colegio`, `total_quantity`, `created_at`) VALUES
(1, 'Epson', '504', 'Cyan', 'MC', 3, '2026-04-03 21:19:58'),
(2, 'Epson', '504', 'Magenta', 'MC', 1, '2026-04-03 21:20:16'),
(3, 'Epson', '544', 'Black', 'MC', 11, '2026-04-03 21:20:32'),
(4, 'Epson', '544', 'Cyan', 'MC', 10, '2026-04-03 21:20:48'),
(5, 'Epson', '544', 'Yellow', 'MC', 17, '2026-04-03 21:21:04'),
(6, 'Epson', '544', 'Magenta', 'MC', 17, '2026-04-03 21:21:27'),
(7, 'Epson', '644', 'Black', 'MC', 9, '2026-04-03 21:21:48'),
(8, 'Epson', '644', 'Cyan', 'MC', 18, '2026-04-03 21:22:07'),
(9, 'Epson', '644', 'Yellow', 'MC', 18, '2026-04-03 21:22:23'),
(10, 'Epson', '644', 'Magenta', 'MC', 19, '2026-04-03 21:22:38'),
(11, 'Epson', '504', 'Magenta', 'DP', 2, '2026-04-03 21:32:50'),
(12, 'Epson', '544', 'Cyan', 'DP', 14, '2026-04-03 21:33:08'),
(13, 'Epson', '544', 'Magenta', 'DP', 17, '2026-04-03 21:33:24'),
(14, 'Epson', '544', 'Yellow', 'DP', 20, '2026-04-03 21:33:39'),
(15, 'Epson', '664', 'Cyan', 'DP', 30, '2026-04-03 21:34:03'),
(16, 'Epson', '664', 'Magenta', 'DP', 32, '2026-04-03 21:34:19'),
(17, 'Epson', '664', 'Yellow', 'DP', 32, '2026-04-03 21:34:44'),
(18, 'Epson', '504', 'Black', 'DP', 0, '2026-04-17 01:20:37'),
(19, 'Epson', '504', 'Yellow', 'DP', 0, '2026-04-17 01:24:08'),
(20, 'Epson', '504', 'Cyan', 'DP', 0, '2026-04-17 01:24:25'),
(21, 'Epson', '544', 'Black', 'DP', 0, '2026-04-17 01:24:59'),
(22, 'Epson', '664', 'Black', 'DP', 0, '2026-04-17 01:25:27'),
(23, 'Epson', '504', 'Black', 'MC', 0, '2026-04-17 01:29:37'),
(24, 'Epson', '504', 'Yellow', 'MC', 0, '2026-04-17 01:29:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ink_entries`
--

CREATE TABLE `ink_entries` (
  `id` int(11) NOT NULL,
  `ink_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ink_entries`
--

INSERT INTO `ink_entries` (`id`, `ink_id`, `quantity`, `entry_date`, `user_id`, `created_at`) VALUES
(1, 1, 3, '2026-04-01', 1, '2026-04-03 21:19:58'),
(2, 2, 1, '2026-04-01', 1, '2026-04-03 21:20:16'),
(3, 3, 11, '2026-04-01', 1, '2026-04-03 21:20:32'),
(4, 4, 10, '2026-04-01', 1, '2026-04-03 21:20:48'),
(5, 5, 17, '2026-04-01', 1, '2026-04-03 21:21:04'),
(6, 6, 17, '2026-04-01', 1, '2026-04-03 21:21:27'),
(7, 7, 9, '2026-04-01', 1, '2026-04-03 21:21:48'),
(8, 8, 18, '2026-04-01', 1, '2026-04-03 21:22:07'),
(9, 9, 18, '2026-04-01', 1, '2026-04-03 21:22:23'),
(10, 10, 19, '2026-04-01', 1, '2026-04-03 21:22:38'),
(11, 11, 2, '2026-04-01', 1, '2026-04-03 21:32:50'),
(12, 12, 14, '2026-04-01', 1, '2026-04-03 21:33:08'),
(13, 13, 17, '2026-04-01', 1, '2026-04-03 21:33:24'),
(14, 14, 20, '2026-04-01', 1, '2026-04-03 21:33:39'),
(15, 15, 30, '2026-04-01', 1, '2026-04-03 21:34:03'),
(16, 16, 32, '2026-04-01', 1, '2026-04-03 21:34:19'),
(17, 17, 32, '2026-04-01', 1, '2026-04-03 21:34:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ink_exits`
--

CREATE TABLE `ink_exits` (
  `id` int(11) NOT NULL,
  `ink_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `exit_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observations`
--

CREATE TABLE `observations` (
  `id` int(11) NOT NULL,
  `requirement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `observation_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requirements`
--

CREATE TABLE `requirements` (
  `id` int(11) NOT NULL,
  `school` enum('DP','MC','IQ') NOT NULL,
  `description` text NOT NULL,
  `responsible_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Abierto','Cerrado') NOT NULL DEFAULT 'Abierto',
  `priority` enum('Normal','Alta','Critica') NOT NULL DEFAULT 'Normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `short_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jefe TIC', 'Jefe', 'admin', '2026-02-23 12:19:07'),
(2, 'aangulo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andres Angulo', 'AA', 'user', '2026-02-23 12:19:07'),
(3, 'jdiego', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Diego', 'JD', 'user', '2026-02-23 12:19:07'),
(4, 'grodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gustavo Rodriguez', 'GR', 'user', '2026-02-23 12:19:07'),
(5, 'fojeda', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fabian Ojeda', 'FO', 'user', '2026-02-23 12:19:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `inks`
--
ALTER TABLE `inks`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ink_entries`
--
ALTER TABLE `ink_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ink_id` (`ink_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `ink_exits`
--
ALTER TABLE `ink_exits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ink_id` (`ink_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `observations`
--
ALTER TABLE `observations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requirement_id` (`requirement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `requirements`
--
ALTER TABLE `requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `responsible_id` (`responsible_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `inks`
--
ALTER TABLE `inks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `ink_entries`
--
ALTER TABLE `ink_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `ink_exits`
--
ALTER TABLE `ink_exits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `observations`
--
ALTER TABLE `observations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ink_entries`
--
ALTER TABLE `ink_entries`
  ADD CONSTRAINT `ink_entries_ibfk_1` FOREIGN KEY (`ink_id`) REFERENCES `inks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ink_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ink_exits`
--
ALTER TABLE `ink_exits`
  ADD CONSTRAINT `ink_exits_ibfk_1` FOREIGN KEY (`ink_id`) REFERENCES `inks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ink_exits_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `observations`
--
ALTER TABLE `observations`
  ADD CONSTRAINT `fk_obs_req` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_obs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `requirements`
--
ALTER TABLE `requirements`
  ADD CONSTRAINT `fk_req_user` FOREIGN KEY (`responsible_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

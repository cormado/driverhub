-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-01-2026 a las 15:13:20
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
-- Base de datos: `vintara_db`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE `grant_distance_achievements` (IN `p_user_id` INT)   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_achievement_id INT;
    DECLARE v_points INT;

    DECLARE cur CURSOR FOR
        SELECT a.id, a.points_reward
        FROM achievements a
        WHERE a.active = 1
        AND NOT EXISTS (
            SELECT 1
            FROM user_achievements ua
            WHERE ua.user_id = p_user_id
            AND ua.achievement_id = a.id
        )
        AND (
            (
                a.rule_type = 'distance_job'
                AND (
                    SELECT COUNT(*)
                    FROM trucksbook_jobs j
                    WHERE j.user_id = p_user_id
                    AND j.distance_km >= a.min_distance_km
                ) >= a.required_jobs
            )
            OR
            (
                a.rule_type = 'distance_total'
                AND (
                    SELECT us.total_km
                    FROM user_stats us
                    WHERE us.user_id = p_user_id
                ) >= a.required_total_km
            )
            OR
            (
                a.rule_type = 'total_jobs'
                AND (
                    SELECT us.total_jobs
                    FROM user_stats us
                    WHERE us.user_id = p_user_id
                ) >= a.required_jobs
            )
        );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_achievement_id, v_points;
        IF done THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO user_achievements (user_id, achievement_id)
        VALUES (p_user_id, v_achievement_id);

        UPDATE user_stats
        SET
            total_points = total_points + v_points,
            available_points = available_points + v_points
        WHERE user_id = p_user_id;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `points_reward` int(11) DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `category` varchar(50) DEFAULT 'General',
  `icon` varchar(100) DEFAULT 'fas fa-trophy',
  `rule_type` enum('distance_job','total_jobs','distance_total') NOT NULL,
  `min_distance_km` int(11) DEFAULT NULL,
  `required_jobs` int(11) DEFAULT NULL,
  `required_total_km` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `achievements`
--

INSERT INTO `achievements` (`id`, `code`, `name`, `description`, `points_reward`, `active`, `category`, `icon`, `rule_type`, `min_distance_km`, `required_jobs`, `required_total_km`) VALUES
(3, 'LONG_1K_1', 'Primer viaje largo', 'Completa 1 viaje de al menos 1000 km', 50, 1, 'Distancia', 'fas fa-road', 'distance_job', 1000, 1, NULL),
(4, 'LONG_1K_10', 'Rutero novato', 'Completa 10 viajes de al menos 1000 km', 150, 1, 'Distancia', 'fas fa-route', 'distance_job', 1000, 10, NULL),
(5, 'LONG_1K_100', 'Rutero experto', 'Completa 100 viajes de al menos 1000 km', 500, 1, 'Distancia', 'fas fa-truck-moving', 'distance_job', 1000, 100, NULL),
(6, 'ULTRA_4K_1', 'Maratón', 'Completa 1 viaje de al menos 4000 km', 300, 1, 'Distancia', 'fas fa-stopwatch', 'distance_job', 4000, 1, NULL),
(7, 'ULTRA_4K_10', 'Sal y toca el pasto', 'Completa 10 viajes de al menos 4000 km', 1000, 1, 'Distancia', 'fas fa-medal', 'distance_job', 4000, 10, NULL),
(8, 'TOTAL_10K', 'Primeros pasos', 'Alcanza 10,000 km totales', 100, 1, 'Progreso', 'fas fa-chart-line', 'distance_total', NULL, NULL, 10000),
(9, 'TOTAL_50K', 'Rodando sin parar', 'Alcanza 50,000 km totales', 300, 1, 'Progreso', 'fas fa-chart-line', 'distance_total', NULL, NULL, 50000),
(10, 'TOTAL_100K', 'Veterano de la carretera', 'Alcanza 100,000 km totales', 700, 1, 'Progreso', 'fas fa-chart-line', 'distance_total', NULL, NULL, 100000),
(11, 'TOTAL_500K', 'Leyenda VTC', 'Alcanza 500,000 km totales', 2000, 1, 'Progreso', 'fas fa-crown', 'distance_total', NULL, NULL, 500000),
(18, 'JOBS_10', 'Conductor activo', 'Completa 10 viajes', 50, 1, 'Actividad', 'fas fa-id-badge', 'distance_job', 0, 10, NULL),
(19, 'JOBS_100', 'Conductor constante', 'Completa 100 viajes', 200, 1, 'Actividad', 'fas fa-id-badge', 'distance_job', 0, 100, NULL),
(20, 'JOBS_500', 'Conductor dedicado', 'Completa 500 viajes', 800, 1, 'Actividad', 'fas fa-id-badge', 'distance_job', 0, 500, NULL),
(21, 'TOCA_EL_PASTO', 'Toca el pasto alv', 'TERMINA 20 VIAJES DE AL MENOS 4000 KILOMETROS', 20000, 1, 'Distancia', 'fas fa-truck', 'distance_job', 4000, 20, NULL),
(22, 'primera_chamba', 'Mi primera chamba...', 'Recuerdo el día que de la chamba yo me enamoré, mi viejo llegó sonriendo porque la de chambear me la sé', 100, 1, 'Chamba', 'fas fa-trophy', 'total_jobs', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `event_ids`
--

CREATE TABLE `event_ids` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `cost_points` int(11) NOT NULL,
  `stock` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `infinite_stock` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rewards`
--

INSERT INTO `rewards` (`id`, `code`, `name`, `description`, `cost_points`, `stock`, `active`, `created_at`, `infinite_stock`) VALUES
(1, 'GIFT_CARD_50', 'Tarjeta de Regalo $50 MXN', '50 Peso', 50, 0, 1, '2026-01-16 04:21:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `status` enum('abierto','en_proceso','resuelto','archived') DEFAULT 'abierto',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trucksbook_jobs`
--

CREATE TABLE `trucksbook_jobs` (
  `id` int(11) NOT NULL,
  `job_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game` enum('ETS2','ATS') NOT NULL,
  `job_type` varchar(50) DEFAULT NULL,
  `distance_km` int(11) NOT NULL,
  `profit` int(11) DEFAULT NULL,
  `from_city` varchar(100) NOT NULL,
  `from_country` varchar(50) DEFAULT NULL,
  `to_city` varchar(100) NOT NULL,
  `to_country` varchar(50) DEFAULT NULL,
  `cargo` varchar(150) DEFAULT NULL,
  `truck` varchar(100) DEFAULT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trucksbook_jobs`
--

INSERT INTO `trucksbook_jobs` (`id`, `job_id`, `user_id`, `game`, `job_type`, `distance_km`, `profit`, `from_city`, `from_country`, `to_city`, `to_country`, `cargo`, `truck`, `points_earned`, `created_at`) VALUES
(5, 22682868, 6, '', NULL, 372, 18496, '0', NULL, 'calais', NULL, 'Grapes', 'Scania S', 372, '2026-01-18 06:37:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin','conductor') DEFAULT 'conductor',
  `tmp_id` int(11) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `vtc_rank` varchar(50) DEFAULT NULL,
  `banned_until` datetime DEFAULT NULL,
  `ban_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discord_id` varchar(30) DEFAULT NULL,
  `trucksbook_name` varchar(50) DEFAULT NULL,
  `trucky_driver_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `tmp_id`, `avatar_url`, `vtc_rank`, `banned_until`, `ban_reason`, `created_at`, `discord_id`, `trucksbook_name`, `trucky_driver_id`) VALUES
(1, 'Owner', '$2y$10$rE.MYf7s2waS64ONxAWfs.pipsi77qTj79nJqOBZ6aLL1CoyCjRTu', 'owner', 1, 'assets/img/logo.png', 'Founder', NULL, NULL, '2026-01-15 02:05:32', NULL, NULL, NULL),
(3, 'SKATE300', '$2y$10$1PQ3jd9Sgr2p.dZCUBp97e3Otk6/dMIBuJvj.Qfzs2jXm6QpcjG5e', 'conductor', 5411825, 'https://static.truckersmp.com/avatarsN/5411825.1766021217.png', 'VIN • Reclutador', NULL, NULL, '2026-01-17 02:23:27', NULL, NULL, 251606),
(4, 'Edu_S7', '$2y$10$T3LxRItd7f30FAoVqrCdaOrqt51osUPeq1eh2S.X.5A2TLXjsCxMW', 'conductor', 5381360, 'https://static.truckersmp.com/avatarsN/5381360.1765842486.jpg', 'VIN • Event Team (CC)', NULL, NULL, '2026-01-17 02:30:03', NULL, NULL, 251609),
(5, 'Ynex', '$2y$10$/VFfW/i94/ftkLGeixa5F.rQ4T42tng8.iDIXzp/tN.M7Cq6qI6WS', 'conductor', 3419310, 'https://static.truckersmp.com/avatarsN/3419310.1765835600.png', 'VIN • Founder', NULL, NULL, '2026-01-17 02:31:27', NULL, NULL, 251603),
(6, 'xbyangel_16', '$2y$10$Lr8rj4C86NQa2gzCYZr7F.Rrmbpx7H6eRYvzbTt5suFzUWyzSme22', 'conductor', 6049306, 'https://static.truckersmp.com/avatarsN/6049306.1765836990.png', 'VIN • Recursos H.', NULL, NULL, '2026-01-18 02:30:08', NULL, NULL, 251971);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_achievements`
--

INSERT INTO `user_achievements` (`user_id`, `achievement_id`, `earned_at`) VALUES
(6, 22, '2026-01-18 13:12:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_rewards`
--

CREATE TABLE `user_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_spent` int(11) NOT NULL,
  `status` enum('pendiente','entregado','cancelado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_rewards`
--

INSERT INTO `user_rewards` (`id`, `user_id`, `reward_id`, `points_spent`, `status`, `created_at`) VALUES
(1, 3, 1, 50, 'cancelado', '2026-01-17 04:23:04'),
(2, 6, 1, 50, 'cancelado', '2026-01-18 13:13:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_stats`
--

CREATE TABLE `user_stats` (
  `user_id` int(11) NOT NULL,
  `total_km` int(11) NOT NULL DEFAULT 0,
  `total_jobs` int(11) NOT NULL DEFAULT 0,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `last_job_at` datetime DEFAULT NULL,
  `available_points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_stats`
--

INSERT INTO `user_stats` (`user_id`, `total_km`, `total_jobs`, `total_points`, `last_job_at`, `available_points`) VALUES
(6, 372, 1, 472, '2026-01-18 00:37:24', 472);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `event_ids`
--
ALTER TABLE `event_ids`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`);

--
-- Indices de la tabla `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indices de la tabla `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `trucksbook_jobs`
--
ALTER TABLE `trucksbook_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_jobs_user_distance` (`user_id`,`distance_km`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trucksbook_name` (`trucksbook_name`),
  ADD UNIQUE KEY `trucky_driver_id` (`trucky_driver_id`);

--
-- Indices de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `ua_ibfk_achievement` (`achievement_id`);

--
-- Indices de la tabla `user_rewards`
--
ALTER TABLE `user_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`),
  ADD KEY `status` (`status`);

--
-- Indices de la tabla `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `event_ids`
--
ALTER TABLE `event_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trucksbook_jobs`
--
ALTER TABLE `trucksbook_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `user_rewards`
--
ALTER TABLE `user_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trucksbook_jobs`
--
ALTER TABLE `trucksbook_jobs`
  ADD CONSTRAINT `jobs_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `ua_ibfk_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ua_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_rewards`
--
ALTER TABLE `user_rewards`
  ADD CONSTRAINT `ur_ibfk_reward` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ur_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `stats_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

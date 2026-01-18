SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- CREACIÓN DE TABLAS --

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin','conductor') DEFAULT 'conductor',
  `tmp_id` int(11) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `vtc_rank` varchar(50) DEFAULT NULL,
  `banned_until` datetime DEFAULT NULL,
  `ban_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
ADD COLUMN `discord_id` varchar(30) DEFAULT NULL,
ADD COLUMN `trucksbook_name` varchar(50) DEFAULT NULL,
ADD UNIQUE KEY `trucksbook_name` (`trucksbook_name`);

ALTER TABLE users
ADD COLUMN trucky_driver_id INT DEFAULT NULL,
ADD UNIQUE KEY trucky_driver_id (trucky_driver_id);


CREATE TABLE `event_ids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `status` enum('abierto','en_proceso','resuelto','archived') DEFAULT 'abierto',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USUARIO POR DEFECTO (Owner / 123456) --
INSERT INTO `users` (`username`, `password`, `role`, `tmp_id`, `avatar_url`, `vtc_rank`) VALUES
('Owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 1, 'assets/img/logo.png', 'Founder');

-- COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



CREATE TABLE `trucksbook_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` bigint NOT NULL,
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

  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),
  UNIQUE KEY `job_id` (`job_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `jobs_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_jobs_user_distance ON trucksbook_jobs (user_id, distance_km);



CREATE TABLE `user_stats` (
  `user_id` int(11) NOT NULL,
  `total_km` int(11) NOT NULL DEFAULT 0,
  `total_jobs` int(11) NOT NULL DEFAULT 0,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `last_job_at` datetime DEFAULT NULL,
  `available_points` int(11) NOT NULL DEFAULT 0,

  PRIMARY KEY (`user_id`),
  CONSTRAINT `stats_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




CREATE TABLE `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `points_reward` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `achievements`
ADD COLUMN `active` tinyint(1) NOT NULL DEFAULT 1

ALTER TABLE `achievements`
ADD COLUMN `category` VARCHAR(50) DEFAULT 'General',
ADD COLUMN `icon` VARCHAR(100) DEFAULT 'fas fa-trophy';

ALTER TABLE achievements
ADD COLUMN rule_type ENUM('distance_job', 'distance_total') NOT NULL,
ADD COLUMN min_distance_km INT DEFAULT NULL,
ADD COLUMN required_jobs INT DEFAULT NULL,
ADD COLUMN required_total_km INT DEFAULT NULL;

ALTER TABLE achievements
MODIFY rule_type ENUM('distance_job', 'total_jobs', 'distance_total') NOT NULL;



--Logros por distancia
INSERT INTO achievements
(code, name, description, points_reward, rule_type, min_distance_km, required_jobs, category, icon)
VALUES
('LONG_1K_1', 'Primer viaje largo', 'Completa 1 viaje de al menos 1000 km', 50, 'distance_job', 1000, 1, 'Distancia', 'fas fa-road'),
('LONG_1K_10', 'Rutero novato', 'Completa 10 viajes de al menos 1000 km', 150, 'distance_job', 1000, 10, 'Distancia', 'fas fa-route'),
('LONG_1K_100', 'Rutero experto', 'Completa 100 viajes de al menos 1000 km', 500, 'distance_job', 1000, 100, 'Distancia', 'fas fa-truck-moving');

INSERT INTO achievements
(code, name, description, points_reward, rule_type, min_distance_km, required_jobs, category, icon)
VALUES
('ULTRA_4K_1', 'Maratón', 'Completa 1 viaje de al menos 4000 km', 300, 'distance_job', 4000, 1, 'Distancia', 'fas fa-stopwatch'),
('ULTRA_4K_10', 'Iron Driver', 'Completa 10 viajes de al menos 4000 km', 1000, 'distance_job', 4000, 10, 'Distancia', 'fas fa-medal');


--Logros por km acumulados
INSERT INTO achievements
(code, name, description, points_reward, rule_type, required_total_km, category, icon)
VALUES
('TOTAL_50K', 'Rodando sin parar', 'Alcanza 50,000 km totales', 300, 'distance_total', 50000, 'Progreso', 'fas fa-chart-line'),
('TOTAL_100K', 'Veterano de la carretera', 'Alcanza 100,000 km totales', 700, 'distance_total', 100000, 'Progreso', 'fas fa-chart-line'),
('TOTAL_500K', 'Leyenda VTC', 'Alcanza 500,000 km totales', 2000, 'distance_total', 500000, 'Progreso', 'fas fa-crown');
--Logros por número de viajes
INSERT INTO achievements
(code, name, description, points_reward, rule_type, min_distance_km, required_jobs, category, icon)
VALUES
('JOBS_10',  'Conductor activo',     'Completa 10 viajes',  50,  'distance_job', 0, 10,  'Actividad', 'fas fa-id-badge'),
('JOBS_100', 'Conductor constante',  'Completa 100 viajes', 200, 'distance_job', 0, 100, 'Actividad', 'fas fa-id-badge'),
('JOBS_500', 'Conductor dedicado',   'Completa 500 viajes', 800, 'distance_job', 0, 500, 'Actividad', 'fas fa-id-badge');




CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`achievement_id`),
  CONSTRAINT `ua_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ua_ibfk_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `cost_points` int(11) NOT NULL,
  `stock` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `user_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_spent` int(11) NOT NULL,
  `status` enum('pendiente','entregado','cancelado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reward_id` (`reward_id`),

  CONSTRAINT `ur_ibfk_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ur_ibfk_reward`
    FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `user_rewards`
ADD KEY `status` (`status`);

----------------------------------------------
-- ZONA DE PROCEDIMIENTOS ALMACENADOS
----------------------------------------------

--grant_distance_achievements
-- SE ENCARGA DE ASIGNAR LOGROS Y PUNTOS OBTENIDOS POR LOS LOGROS A LOS USUARIOS
DELIMITER $$

CREATE PROCEDURE grant_distance_achievements(IN p_user_id INT)
BEGIN
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

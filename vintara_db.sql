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


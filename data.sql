DROP DATABASE IF EXISTS `sistem_donasi`;
CREATE DATABASE `sistem_donasi`
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;
USE `sistem_donasi`;

SET FOREIGN_KEY_CHECKS=0;

-- ======================
-- TABLE users
-- ======================
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES
(1,'Administrator','admin@local.test','080000000000','$2y$10$1xpPGDZjldW1WgCUYbJl/O/2W6sPdmF5oKp5.aJNUE1IBicVskuPC','admin',1,'2026-01-03 06:00:42'),
(3,'pebe','pebe@gmail.com','08123456789','$2y$10$99mvYiez94dx3EQOS.VvHehGo5X/SAvhZT/bOgPC5A57vgIEfMzxC','user',1,'2026-01-03 06:46:50'),
(4,'pebe','pebe1@gmail.com','08123456789','$2y$10$8e9fHRuzrU9mjXZ8X5kO.eA5yQM97vfOhk0yPX1PzvMWipzG6DhYC','user',1,'2026-01-03 09:01:27');

-- ======================
-- TABLE categories
-- ======================
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES
(1,'Pendidikan','2026-01-03 05:56:05'),
(2,'Kesehatan','2026-01-03 05:56:05'),
(3,'Bencana','2026-01-03 05:56:05');

-- ======================
-- TABLE campaigns
-- ======================
CREATE TABLE `campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `target_amount` bigint unsigned NOT NULL,
  `collected_amount` bigint unsigned NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `campaigns_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `campaigns` VALUES
(1,3,'Bantuan Banjir aceh','bantuan-banjir-aceh','Bantuan banjir Aceh',50000000,4300000,'2026-01-03','2026-01-10','active','uploads/campaigns/camp.jpg',1,'2026-01-03 07:26:08');

-- ======================
-- TABLE donations
-- ======================
CREATE TABLE `donations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(30) NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `campaign_id` bigint unsigned NOT NULL,
  `amount` bigint unsigned NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `user_id` (`user_id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
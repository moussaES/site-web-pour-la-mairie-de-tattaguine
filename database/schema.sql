-- ====================================================================
-- CAHIER DES CHARGES - SITE WEB COMMUNE DE TATTAGUINE
-- Fichier d'initialisation de la Base de Données (schema.sql)
-- Version : 2.0 (Prise en compte des rôles RBAC, vidéos et statistiques)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `tattaguine_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tattaguine_db`;

-- --------------------------------------------------------------------
-- 1. Table des Rôles Utilisateurs (RBAC)
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `documents`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `visit_stats`;

CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `label` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `label`) VALUES 
(1, 'super_admin', 'Super Administrateur'),
(2, 'redacteur', 'Agent Rédacteur & Modérateur');

-- --------------------------------------------------------------------
-- 2. Table des Utilisateurs (Agents Municipaux)
-- --------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compte Super Admin par défaut (Identifiant: admin | Mot de passe: admin123)
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`) VALUES
(1, 1, 'admin', 'admin@tattaguine.gouv.sn', '$2y$10$fwTe/0r7d8X59mizZ3udIugy2987N84pRbqVnituRmqzKHi.L3imW', 'Administrateur Général PATIP-JF');

-- --------------------------------------------------------------------
-- 3. Table des Catégories de Publications
-- --------------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Actualités Municipales', 'actualites-municipales', 'Toutes les informations et évènements récents de la mairie de Tattaguine.'),
(2, 'Communiqués Officiels', 'communiques-officiels', 'Annonces officielles de Monsieur le Maire et du Conseil Municipal.'),
(3, 'Avis Publics & Arrêtés', 'avis-publics-arretes', 'Avis à la population, délibérations et arrêtés municipaux.'),
(4, 'Projets & Développement', 'projets-developpement', 'Suivi des projets d infrastructure, santé, éducation et environnement.'),
(5, 'État Civil & Demarches', 'etat-civil-demarches', 'Informations et démarches administratives pour les citoyens.');

-- --------------------------------------------------------------------
-- 4. Table des Publications / Articles (avec soutien Images & Vidéos)
-- --------------------------------------------------------------------
CREATE TABLE `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `author_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `image_path` VARCHAR(255) NULL,
    `video_url` VARCHAR(255) NULL, -- Lien externe (YouTube, Vimeo) ou chemin relatif vidéo MP4
    `status` ENUM('draft', 'published') DEFAULT 'published',
    `views_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_posts_categories` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_posts_users` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_posts_status_date` (`status`, `created_at`),
    INDEX `idx_posts_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Publication exemple
INSERT INTO `posts` (`id`, `category_id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `status`, `created_at`) VALUES
(1, 1, 1, 'Lancement officiel du projet de modernisation du site web municipal', 'lancement-projet-site-web-tattaguine', 'La Commune de Tattaguine lance son nouveau site web d information et d interaction citoyenne dans le cadre du programme PATIP-JF.', '<p>C est avec un grand plaisir que la Mairie de Tattaguine annonce la mise en place de son nouveau portal web institutionnel. Ce projet vise a rapprocher l administration municipale des citoyens en offrant un acces direct aux actualites, actes administratifs et espaces d expression.</p>', 'published', NOW());

-- --------------------------------------------------------------------
-- 5. Table des Documents Administratifs Téléchargeables
-- --------------------------------------------------------------------
CREATE TABLE `documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NOT NULL, -- Actes d état civil, Formulaires, Budgets, Conseil municipal
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` VARCHAR(20) NOT NULL,
    `downloads_count` INT DEFAULT 0,
    `uploaded_by` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_documents_users` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. Table des Commentaires Citoyens (Modération a priori)
-- --------------------------------------------------------------------
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `author_name` VARCHAR(100) NOT NULL,
    `author_email` VARCHAR(100) NULL,
    `content` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_response` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comments_posts` FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    INDEX `idx_comments_status` (`post_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. Table des Messages du Formulaire de Contact
-- --------------------------------------------------------------------
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. Table des Statistiques d Audience (Visiteurs Uniques)
-- --------------------------------------------------------------------
CREATE TABLE `visit_stats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `visit_date` DATE NOT NULL,
    `visitor_hash` VARCHAR(64) NOT NULL,
    `page_url` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_visit_per_day` (`visit_date`, `visitor_hash`, `page_url`),
    INDEX `idx_visit_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offer Letter Service Database Dump
-- Compatible with phpMyAdmin, MySQL 8.x, and MariaDB
-- Generated for: Facilities & Technical Services LLC (FTS)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Offer Letter Service Database Tables & Data
--


-- --------------------------------------------------------

--
-- Table structure for table `admins`
--
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
-- Default password: ChangeThisPassword
--
INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@example.com', '$2y$12$eU.5lQf4mFmX6dYcW0jYPeQ7rZ5xH1bHq0Yt.1m5VnZlQ7K2q3bO6', NOW(), NOW());

-- --------------------------------------------------------

--
-- Table structure for table `offer_letters`
--
DROP TABLE IF EXISTS `offer_letters`;
CREATE TABLE `offer_letters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `candidate_name` varchar(255) NOT NULL,
  `passport_number` varchar(50) NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `place_of_posting` varchar(255) NOT NULL,
  `offer_date` date NOT NULL,
  `validity_date` date NOT NULL,
  `joining_date` date DEFAULT NULL,
  `probation_period` varchar(100) NOT NULL DEFAULT '3 months',
  `contract_duration` varchar(100) NOT NULL DEFAULT '2 years',
  `working_hours` varchar(255) NOT NULL DEFAULT '9 hours per day',
  `weekly_day_off` varchar(100) NOT NULL DEFAULT 'Friday',
  `overtime_info` text DEFAULT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `basic_salary_words` varchar(500) NOT NULL,
  `other_allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_allowances_words` varchar(500) NOT NULL,
  `total_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_salary_words` varchar(500) NOT NULL,
  `salary_currency` varchar(10) NOT NULL DEFAULT 'AED',
  `annual_leave` varchar(255) NOT NULL DEFAULT '30 days per year',
  `air_ticket_allowance` varchar(255) NOT NULL DEFAULT 'Economy class, once per year',
  `medical_requirements` text DEFAULT NULL,
  `notice_period` varchar(100) NOT NULL DEFAULT '30 days',
  `additional_terms_title` varchar(255) DEFAULT NULL,
  `additional_terms` text DEFAULT NULL,
  `status` enum('draft','published','viewed','pending_signature','accepted','signed','expired','revoked') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `candidate_ip` varchar(45) DEFAULT NULL,
  `candidate_user_agent` text DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offer_letters_token_unique` (`token`),
  KEY `offer_letters_admin_id_foreign` (`admin_id`),
  CONSTRAINT `offer_letters_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signatures`
--
DROP TABLE IF EXISTS `signatures`;
CREATE TABLE `signatures` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offer_letter_id` bigint(20) UNSIGNED NOT NULL,
  `signature_data` longtext NOT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `signed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `signatures_offer_letter_id_foreign` (`offer_letter_id`),
  CONSTRAINT `signatures_offer_letter_id_foreign` FOREIGN KEY (`offer_letter_id`) REFERENCES `offer_letters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offer_letter_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('original','signed') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `documents_offer_letter_id_foreign` (`offer_letter_id`),
  CONSTRAINT `documents_offer_letter_id_foreign` FOREIGN KEY (`offer_letter_id`) REFERENCES `offer_letters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offer_events`
--
DROP TABLE IF EXISTS `offer_events`;
CREATE TABLE `offer_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `offer_letter_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `offer_events_offer_letter_id_foreign` (`offer_letter_id`),
  CONSTRAINT `offer_events_offer_letter_id_foreign` FOREIGN KEY (`offer_letter_id`) REFERENCES `offer_letters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

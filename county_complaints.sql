-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 31, 2026 at 04:42 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `county_complaints`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_at`) VALUES
(1, 'system maintenance', 'wait for one hour', '2026-02-13 14:23:40'),
(2, 'shortage of internet', 'hold on until the officials  resolve the issue.', '2026-02-14 08:41:02');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `citizen_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int NOT NULL,
  `priority_level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Normal',
  `complaint_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_county` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward_location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `audio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `citizen_name`, `phone`, `department_id`, `priority_level`, `complaint_text`, `sub_county`, `ward_location`, `image_path`, `status`, `submitted_at`, `audio_url`) VALUES
(30, 15, 'mercy', '0717979124', 14, 'Critical', '', 'North Horr', 'Turbi', 'uploads/1774341631_flood.jpg', 'Pending', '2026-03-24 11:40:31', NULL),
(29, 15, 'mercy', '0717979124', 14, 'Critical', '', 'North Horr', 'Turbi', 'uploads/1774341563_fire.jpg', 'Pending', '2026-03-24 11:39:23', NULL),
(28, 15, 'mercy', '0717979124', 15, 'Normal', '', 'North Horr', 'Turbi', 'uploads/1774341262_fire.jpg', 'Pending', '2026-03-24 11:34:22', NULL),
(27, 15, 'mercy', '0717979124', 15, 'Normal', 'outbreak of fire', 'North Horr', 'Turbi', 'uploads/1774340649_fire.jpg', 'Pending', '2026-03-24 11:24:09', NULL),
(26, 15, 'mercy', '0717979124', 15, 'Normal', '', 'North Horr', 'North Horr', 'uploads/1774340604_fire.jpg', 'Pending', '2026-03-24 11:23:24', NULL),
(25, 15, 'mercy', '0717979124', 15, 'Normal', '', 'North Horr', 'North Horr', 'uploads/1774340544_flood.jpg', 'Pending', '2026-03-24 11:22:24', NULL),
(24, 12, 'mercy', '0717917918', 15, 'Normal', '', 'Saku', 'Sagante/Jaldesa', 'uploads/1774339127_flood.jpg', 'Pending', '2026-03-24 10:58:47', NULL),
(23, 12, 'abdube', '0708609096', 15, 'Normal', 'syphilis outbreak in the area', 'Moyale', 'Moyale Township', 'uploads/1774338684_syphilis.jpg', 'Pending', '2026-03-24 10:51:24', NULL),
(22, 9, 'abdube', '0708609096', 3, 'Medium', 'shortage of medicine in maikona dispensary', 'North Horr', 'Maikona', NULL, 'Pending', '2026-03-07 10:34:33', NULL),
(20, 9, 'abdube', '0708609096', 3, 'Critical', 'outbreak of syphillis in the area', 'North Horr', 'North Horr', 'uploads/1771498566_syphilis.jpg', 'Resolved', '2026-02-19 13:56:06', NULL),
(21, 9, 'abdube dida', '0708609096', 15, 'Normal', 'poor health services at maikona dispensary.', 'North Horr', 'Maikona', 'uploads/1772033533_maikona poor services.jpg', 'Resolved', '2026-02-25 18:32:13', NULL),
(31, 15, 'mercy', '0717979124', 4, 'Critical', '', 'Moyale', 'Golbo', 'uploads/1774341865_deforestation.jpg', 'Pending', '2026-03-24 11:44:25', NULL),
(32, 15, 'mercy', '0717979124', 15, 'High', '', 'Saku', 'Karare', 'uploads/1774341949_dirty place.jpg', 'Pending', '2026-03-24 11:45:49', NULL),
(33, 12, 'abdube', '0708609096', 2, 'High', '', 'North Horr', 'North Horr', 'uploads/1774342233_sport.jpg', 'Pending', '2026-03-24 11:50:33', NULL),
(34, 12, 'abdube', '0708609096', 2, 'High', '', 'North Horr', 'North Horr', 'uploads/1774342596_sport.jpg', 'Pending', '2026-03-24 11:56:36', NULL),
(35, 12, 'abdube', '0708609096', 3, 'High', '', 'North Horr', 'Turbi', 'uploads/1774342661_chicken pox.jpg', 'Pending', '2026-03-24 11:57:41', NULL),
(36, 12, 'abdube', '0708609096', 11, 'High', '', 'North Horr', 'North Horr', 'uploads/1774343055_sport1.jpg', 'Pending', '2026-03-24 12:04:15', NULL),
(37, 12, 'abdube', '0708609096', 15, 'Low', '', 'North Horr', 'North Horr', 'uploads/1774343186_school.jpg', 'Pending', '2026-03-24 12:06:26', NULL),
(38, 12, 'abdube', '0708609096', 15, 'Low', '', 'North Horr', 'North Horr', 'uploads/1774343429_school.jpg', 'Pending', '2026-03-24 12:10:29', NULL),
(39, 12, 'abdube', '0708609096', 9, 'Low', '', 'North Horr', 'North Horr', 'uploads/1774343666_school.jpg', 'Pending', '2026-03-24 12:14:26', NULL),
(40, 16, 'greg mugai', '0701768798', 14, 'Critical', '', 'Moyale', 'Golbo', 'uploads/1774355830_fire.jpg', 'Pending', '2026-03-24 15:37:10', NULL),
(41, 12, 'Abdube Dida', '0708609096', 15, 'Normal', 'how to break off', 'Saku', 'Sagante/Jaldesa', NULL, 'Pending', '2026-03-28 16:18:32', NULL),
(42, 12, 'Abdube Dida', '0708609096', 5, 'High', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:14:36', NULL),
(43, 12, 'Abdube Dida', '0708609096', 5, 'High', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:24:26', NULL),
(44, 12, 'Abdube Dida', '0708609096', 5, 'High', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:26:13', NULL),
(45, 12, 'Abdube Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:30:58', NULL),
(46, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:35:25', NULL),
(47, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:43:49', NULL),
(48, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:52:32', NULL),
(49, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:55:33', NULL),
(50, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 07:57:48', NULL),
(51, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 08:01:19', NULL),
(52, 12, 'Abdube U Dida', '0708609096', 3, 'Low', '', 'Saku', 'Marsabit Central', NULL, 'In Progress', '2026-03-30 08:05:48', NULL),
(53, 12, 'Abdube U Dida', '0708609096', 15, 'Normal', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 08:10:15', NULL),
(54, 12, 'Abdube Dida', '0708609096', 15, 'High', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 08:14:17', NULL),
(55, 12, 'Abdube Dida', '0708609096', 14, 'Critical', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-30 08:24:07', NULL),
(56, 12, 'Abdube Dida', '0708609096', 14, 'Critical', '', 'Saku', 'Marsabit Central', 'uploads/1774848315_fire.jpg', 'Pending', '2026-03-30 08:25:15', NULL),
(57, 12, 'abdube dida ', '0708609096', 15, 'Low', '', 'Saku', 'Marsabit Central', NULL, 'In Progress', '2026-03-30 10:06:32', NULL),
(58, 12, 'abdube dida', '0708609096', 3, 'Critical', '', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-31 06:52:26', NULL),
(59, 12, 'abdube dida', '0708609096', 14, 'Critical', 'fire breakout at marsabit town.', 'Saku', 'Marsabit Central', NULL, 'Pending', '2026-03-31 07:33:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_updates`
--

DROP TABLE IF EXISTS `complaint_updates`;
CREATE TABLE IF NOT EXISTS `complaint_updates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `complaint_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `admin_id` int DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `officer_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_name` (`dept_name`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `description`) VALUES
(1, 'Water & Irrigation', NULL),
(2, 'Roads, Transport & Public Works', NULL),
(3, 'Health Services', NULL),
(4, 'Environment & Natural Resources', NULL),
(5, 'Sanitation & Waste Management', NULL),
(6, 'Agriculture, Livestock & Fisheries', NULL),
(7, 'Trade, Tourism & Industrialization', NULL),
(8, 'Land, Housing & Physical Planning', NULL),
(9, 'Education & Public Service', NULL),
(10, 'Finance & Economic Planning', NULL),
(11, 'Sports, Culture & Social Services', NULL),
(12, 'Gender, Youth & PWDs', NULL),
(13, 'ICT & E-Government', NULL),
(14, 'Disaster Management & Fire Services', NULL),
(15, 'County Administration & Enforcement', NULL),
(16, 'Energy & Street Lighting', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `constituency` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('citizen','officer','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'citizen',
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `constituency`, `ward`, `password`, `role`, `phone_number`, `department_id`, `created_at`) VALUES
(2, 'System Admin', 'admin@county.com', NULL, NULL, '$2y$10$IyjDwAXccTBX7u2URMNqYOUIIhvTueXLIjT3ZELgt9WE7a7Hqh8Qi', 'admin', NULL, NULL, '2026-02-13 09:56:57'),
(3, 'Water Officer', 'water@county.com', NULL, NULL, '$2y$10$IyjDwAXccTBX7u2URMNqYOUIIhvTueXLIjT3ZELgt9WE7a7Hqh8Qi', 'officer', NULL, 1, '2026-02-13 09:56:57'),
(10, 'adesh bora', 'adesh@county.com', NULL, NULL, '$2y$10$CHAdJwuSKGe9RbeMmzMjM.274wcK2d/JKPxWiixHx9ne06RdwUYpG', 'officer', NULL, 15, '2026-02-25 15:34:53'),
(5, 'john wario', 'john@county.co.ke', NULL, NULL, '$2y$10$Dto2yulgrk/2Ql7p/imnqOez0XRYKQQ4T.M61CFOa7CjFIIlzNWrS', 'officer', NULL, 3, '2026-02-13 14:00:41'),
(6, 'stanely omondi', 'omondi@go.ke', NULL, NULL, '$2y$10$qDWIO.BMcP/yfElrf6R08..KJ89b9wCMTd6YvD4.b92tx59t/igca', 'officer', NULL, 2, '2026-02-14 07:03:44'),
(11, 'guyo ali', 'guyo@county.com', 'Moyale', 'Golbo', '$2y$10$c8.CIoxuCrhFlVCOXdd9PucAhxKueGlLHweeHi6xnpBM8SJfyi/6a', 'citizen', NULL, NULL, '2026-03-07 07:57:09'),
(12, 'abdube dida', 'abdube@county.com', 'North Horr', 'Maikona', '$2y$10$ITOo.oZQlW1dbDji.isJEOpAzRbgCXtFYgggWteC2L195Jbf8Ji56', 'citizen', '0708609096', NULL, '2026-03-17 05:39:01'),
(14, 'guyo ali', 'guyo@county.co.ke', 'Saku', 'Marsabit Central', '$2y$10$Up64lDps663kOmRcwlCf8OxOCVWp.APRPTH1legrxEiSorY.4lBLC', 'citizen', '0706789787', NULL, '2026-03-17 17:28:18'),
(15, 'jane mercy', 'janemercy@gmail.com', 'Moyale', 'Golbo', '$2y$10$a5Hi0kBnT5IaN.JdlFda3OOYepMdCYdpL5WsSAH9rPwbVnSlxA2A.', 'citizen', '0712398767', NULL, '2026-03-24 08:18:53'),
(16, 'greg mugai', 'greg123@county.com', 'Saku', 'Sagante/Jaldesa', '$2y$10$EHVwRCXHPgFRDc3A5V/wEukKu49QPhjUPEGSgbCL9pVtgJM8oj3RG', 'citizen', '0701678798', NULL, '2026-03-24 12:30:03');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

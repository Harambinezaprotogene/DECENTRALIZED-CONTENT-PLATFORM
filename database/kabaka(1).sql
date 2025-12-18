-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2025 at 12:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kabaka`
--

-- --------------------------------------------------------

--
-- Table structure for table `blockchain_reciept`
--

CREATE TABLE `blockchain_reciept` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id_hash` char(66) DEFAULT NULL,
  `chain` varchar(32) NOT NULL,
  `contract_address` varchar(66) NOT NULL,
  `tx_hash` varchar(80) NOT NULL,
  `payer_address` varchar(66) DEFAULT NULL,
  `amount_wei` decimal(65,0) DEFAULT NULL,
  `onchain_status` enum('pending','confirmed','failed') DEFAULT 'pending',
  `onchain_written_at` datetime DEFAULT NULL,
  `onchain_confirmed_at` datetime DEFAULT NULL,
  `block_number` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blockchain_reciept`
--

INSERT INTO `blockchain_reciept` (`id`, `payment_id`, `payment_id_hash`, `chain`, `contract_address`, `tx_hash`, `payer_address`, `amount_wei`, `onchain_status`, `onchain_written_at`, `onchain_confirmed_at`, `block_number`, `created_at`, `updated_at`) VALUES
(1, 1, '0e7c6e12a0be92b84fc171efc4858676eb64b67c35c372499c68bc5332df29f7', 'polygon-amoy', '0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F', '0xcf07534e5e1affd1eb03f50f63dacc1bb2de6e306416a9cb444d6690a0528e73', '0x0000000000000000000000000000000000000000', 4250000000000000, 'confirmed', '2025-09-15 21:34:56', NULL, 26453686, '2025-09-15 21:34:56', '2025-09-15 21:34:56'),
(2, 2, 'd240a6cd0115451026bd40c4d476f0169ae5671367dc583cd9a0a145e546d80d', 'polygon-amoy', '0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F', '0xb4ae07b6effb8a5f9805ac8c03e215e3f8553c211e57e147b5fddebdf1582f45', '0x0000000000000000000000000000000000000000', 4250000000000000, 'confirmed', '2025-09-15 22:58:02', NULL, 26456177, '2025-09-15 22:58:02', '2025-09-15 22:58:02');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `text` text NOT NULL,
  `status` enum('active','deleted','hidden') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content_id`, `user_id`, `parent_id`, `text`, `status`, `created_at`, `updated_at`) VALUES
(19, 59, '1', NULL, 'I really like this song 🙆🏿‍♀️🙆🏿‍♀️🤩🤩😉😉', 'active', '2025-09-08 19:51:45', '2025-09-08 19:51:45'),
(21, 62, '1', NULL, 'Gahosho', 'active', '2025-09-12 13:29:39', '2025-09-12 13:29:39'),
(22, 60, '1', NULL, 'Let me make this sh*** as my wallpaper 😉😉😁😁', 'active', '2025-09-15 07:54:06', '2025-09-15 07:54:06');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `media_url` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `category` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `thumbnail_url` text DEFAULT NULL,
  `status` char(80) NOT NULL,
  `ownership_note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `user_id`, `title`, `media_url`, `file_path`, `file_size`, `file_type`, `original_filename`, `category`, `description`, `tags`, `thumbnail_url`, `status`, `ownership_note`, `created_at`, `updated_at`) VALUES
(59, 7, 'Bee Gees – Stayin\' Alive (Official Music Video)', '/kabaka/public/uploads/68bdd786bb244_1757271942.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68bdd786bb244_1757271942.mp4', 19040986, 'video/mp4', '68b1d61cdfe99_1756485148.mp4', 'Video', 'Bee Gees – Stayin\' Alive (Official Music Video)', '#Bee Gees#Bee Gees 😁😁😁😁✅✅', '', 'approved', 'Bee Gees – Stayin\' Alive (Official Music Video)', '2025-09-07 21:05:42', '2025-09-09 15:42:51'),
(60, 7, 'Avatar Movie Image', '/kabaka/public/uploads/68c1b9c025539_1757526464.jpg', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1b9c025539_1757526464.jpg', 743786, 'image/jpeg', '68b0f54c5a8fe_1756427596.jpg', 'Image', 'An image inspired by James Cameron’s Avatar, featuring the iconic characters and world of Pandora.', 'avatar, Pandora, Na’vi, Avatar movie, James Cameron, sci-fi, fantasy, movie characters, film image', '', 'approved', 'This image is from Avatar (2009), directed by James Cameron and owned by 20th Century Studios. I do not own the rights. Used here for reference/fan purposes only.', '2025-09-10 19:47:44', '2025-09-10 19:47:44'),
(61, 7, 'Adobe Illustrator Tutorial', '/kabaka/public/uploads/68c1ba041cd6a_1757526532.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1ba041cd6a_1757526532.mp4', 13838423, 'video/mp4', '68b1d7af197f7_1756485551.mp4', 'Video', 'Step-by-step tutorial on how to use Adobe Illustrator for creating professional vector graphics, logos, and illustrations. This guide is designed for beginners and intermediate users who want to improve their design skills.', 'Adobe Illustrator, Illustrator tutorial, vector design, logo design, graphic design, digital art, illustration, design tips', '', 'approved', 'This tutorial was created using Adobe Illustrator. Adobe and Illustrator are trademarks of Adobe Inc.', '2025-09-10 19:48:52', '2025-09-10 19:48:52'),
(62, 7, 'Cartoon Stick Man Animation', '/kabaka/public/uploads/68c1ba57a0db6_1757526615.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1ba57a0db6_1757526615.mp4', 365872, 'video/mp4', '68b1d8afee9b7_1756485807.mp4', 'Video', 'An animated cartoon video featuring a stick man character. The video showcases simple, fun, and creative stick man movements, perfect for entertainment, learning, or inspiration in animation.', 'stick man, cartoon, animation, doodle, character animation, funny video, simple art, cartoon video', '', 'approved', 'Original video animation of a cartoon stick man created for entertainment purposes.', '2025-09-10 19:50:15', '2025-09-10 19:50:15'),
(63, 7, 'UI Card Design', '/kabaka/public/uploads/68c1baa1d81f1_1757526689.png', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1baa1d81f1_1757526689.png', 178145, 'image/png', '68b1d9e43974c_1756486116.png', 'Image', 'A modern user interface card design created for use in web and mobile applications. The card layout demonstrates clean design principles with focus on usability and visual appeal.', 'UI design, card design, web design, mobile app, user interface, UX, mockup, layout', '', 'approved', 'Original UI card design image created for design showcase purposes.', '2025-09-10 19:51:29', '2025-09-10 19:51:29'),
(64, 7, 'Important Web: Design Secrets & Development Essentials', '/kabaka/public/uploads/68c1bb7e1ee30_1757526910.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bb7e1ee30_1757526910.mp4', 194367, 'video/mp4', '68b1d73c564c5_1756485436.mp4', 'Video', 'Unlock the core principles that make websites truly impactful—from layout hierarchy to development tools. This video blends design wisdom with practical tech insights to help creators build smarter, faster, and more user-friendly platforms.z', 'web design, web development, UX, UI, HTML, CSS, JavaScript, SEO, hierarchy, layout, responsive design, beginner tutorial, website tips, frontend, backend, dev tools', '', 'approved', 'Original content created and edited by Robinson. All design insights and development commentary are based on publicly available tutorials and personal experience. No copyrighted material reused without permission.', '2025-09-10 19:55:10', '2025-09-10 19:55:10'),
(65, 7, 'Brush Presets: Create, Customize & Master Your Tools', '/kabaka/public/uploads/68c1bbd7834b4_1757526999.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bbd7834b4_1757526999.mp4', 932092, 'video/mp4', '68b1d91daf89b_1756485917.mp4', 'Video', 'Learn how to create and customize brush presets in Photoshop like a pro. This tutorial walks through essential settings, dynamic adjustments, and export techniques to help artists, designers, and editors streamline their workflow.\r\nInspired by expert guides such as:\r\n- Master Brush Tool from Start to Finish - Photoshop for ...: Covers everything from brush shortcuts to advanced properties like shape dynamics and symmetry.\r\n- Every Photoshop Custom Brush Setting Explained: Breaks down each setting—scattering, texture, dual brush, color dynamics—and how they affect your strokes.\r\n- Photoshop Brushes: Brush Settings In Depth Overview ...: Offers a focused walkthrough of brush tip shape, jitter, and smoothing.\r\n- The Quickest Way to Create a Custom Brush in Photoshop CC ...: A fast-track method for defining and adjusting custom brushes.\r\n- How to Make Photoshop Brushes: Demonstrates how to save presets and make brushes dynamic for design versatility.\r\n- Ultimate Guide to Photoshop Brushes, Brush Settings for ...: Explores brush spacing, opacity, and how to download new brushes from Adobe Creative Cloud.\r\nWhether you\'re building brushes from scratch or refining your presets, this tutorial delivers practical insights for digital artists and Photoshop enthusiasts.\r\n\r\n🔖 Tags\r\nphotoshop, brush presets, custom brushes, digital art, brush settings, shape dynamics, scattering, texture, dual brush, color dynamics, tutorial, design workflow, creative tools\r\nComma-separated for easy parsing and search optimization.\r\n\r\n\r\n🛡️ Ownership Note\r\nOriginal tutorial created and narrated by Robinson. All techniques are based on personal experience and publicly available Photoshop documentation. No copyrighted material reused without permission.\r\n\r\nLet me know if you want to batch this into SQL inserts or prep a thumbnail strategy next. I can also help you build a metadata template for future uploads.', 'photoshop, brush presets, custom brushes, digital art, brush settings, shape dynamics, scattering, texture, dual brush, color dynamics, tutorial, design workflow, creative tools Comma-separated for easy parsing and search optimization.', '', 'approved', 'Original tutorial created and narrated by Robinson. All techniques are based on personal experience and publicly available Photoshop documentation. No copyrighted material reused without permission.', '2025-09-10 19:56:39', '2025-09-10 19:56:39'),
(66, 7, 'Photoshop Tutorial: From Basics to Pro Tools in One Session', '/kabaka/public/uploads/68c1bc2bd9efd_1757527083.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bc2bd9efd_1757527083.mp4', 1733741, 'video/mp4', '68b1d8140ad6a_1756485652.mp4', 'Video', 'This tutorial is your all-in-one guide to mastering Adobe Photoshop. Whether you\'re opening the software for the first time or refining your design workflow, you\'ll learn everything from workspace setup to advanced tools and creative effects.\r\nKey highlights include:\r\n- Photoshop Tutorial for Beginners 2025 | Everything You ...: A fresh, beginner-focused walkthrough with clear pacing and step-by-step guidance.\r\n- Photoshop for Complete Beginners | Lesson 1: Covers workspace setup, file management, and essential tools like Move, Brush, and Text.\r\n- Photoshop Full Course Tutorial (6+ Hours): A deep dive into layers, masks, gradients, filters, and even 3D text—perfect for intermediate learners.\r\n- Photoshop Tutorial for Beginners 2022 | Everything You ...: Offers a concise overview of blending modes, basic editing tools, and exporting.\r\n- Photoshop for Beginners | FREE COURSE: Explains layers, tone adjustments, smart objects, and retouching tools in a structured format.\r\n- Basic Selections - Adobe Photoshop for Beginners - Class 1 ...: Focuses on selection tools and interface navigation—ideal for first-time users.\r\nWhether you\'re designing for social media, editing photos, or building digital art, this tutorial equips you with the skills to work confidently and creatively in Photoshop.\r\n\r\n🔖 Tags\r\nphotoshop, tutorial, beginner, workspace, layers, brush tool, text tool, selection tools, image editing, design, retouching, smart objects, gradients, filters, 3D text, creative cloud\r\nComma-separated for easy parsing and search optimization.\r\n\r\n\r\n🛡️ Ownership Note\r\nOriginal tutorial created and narrated by Robinson. All techniques are based on personal experience and publicly available Photoshop training resources. No copyrighted material reused without permission.\r\n\r\nLet me know if you want to batch this into SQL inserts, prep a thumbnail strategy, or build a reusable metadata template for future uploads. I can also help you segment this tutorial into chapters with timestamps.', 'photoshop, tutorial, beginner, workspace, layers, brush tool, text tool, selection tools, image editing, design, retouching, smart objects, gradients, filters, 3D text, creative cloud', '', 'approved', 'Original tutorial created and narrated by Robinson. All techniques are based on personal experience and publicly available Photoshop training resources. No copyrighted material reused without permission.', '2025-09-10 19:58:03', '2025-09-10 19:58:03'),
(67, 7, 'Waste Management System: Models', '/kabaka/public/uploads/68c1bc7f2f5df_1757527167.mp4', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bc7f2f5df_1757527167.mp4', 13497262, 'video/mp4', '68b1d51986de0_1756484889.mp4', 'Video', 'This video explores the principles and practical models behind modern waste management systems. From basic concepts to advanced technologies, it offers a comprehensive guide for students, urban planners, and sustainability advocates.\r\nKey highlights include:', 'waste management, recycling, sustainability, incineration, smart cities, pneumatic collection, integrated systems, urban planning, environmental strategy, waste reduction, composting, waste-to-energy, public health', '', 'approved', 'Original content created and narrated by Robinson. All models and strategies are based on publicly available resources and personal analysis. No copyrighted material reused without permission.', '2025-09-10 19:59:27', '2025-09-10 19:59:27'),
(68, 7, 'Aviator Game crashing', '/kabaka/public/uploads/68c1bd0accbee_1757527306.png', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bd0accbee_1757527306.png', 34213, 'image/png', '68b1da577cb9e_1756486231.png', 'Image', 'This video breaks down the Aviator game—a fast-paced, multiplier-based betting experience where timing is everything. Learn how the game works, what strategies players use to manage risk, and how to interpret flight patterns for smarter decisions.', 'aviator, crash game, betting strategy, multiplier, online casino, timing, risk management, gambling tutorial, bankroll control, game mechanics, flight pattern, betting psychology Comma-separated for easy parsing and discoverability.   🛡️ Ownership Note Or', '', 'approved', 'Original content created and narrated by Robinson. Gameplay footage and commentary are based on personal experience and publicly available resources. No copyrighted material reused without permission.', '2025-09-10 20:01:46', '2025-09-10 20:01:46'),
(69, 7, 'Instrumental Guitar Music: Relaxing Acoustic Melodies for Peace & Focus', '/kabaka/public/uploads/68c1bf9dde5e8_1757527965.mp3', 'C:\\xampp\\htdocs\\kabaka\\public\\api/../uploads/68c1bf9dde5e8_1757527965.mp3', 1872384, 'audio/mpeg', '68b1d4af40d1a_1756484783.mp3', 'Video', 'Immerse yourself in the soothing sounds of instrumental guitar. This video features gentle acoustic melodies designed to calm the mind, ease stress, and create a peaceful atmosphere for work, study, or rest.', 'guitar music, instrumental, acoustic, relaxing, meditation, fingerstyle, ambient, peaceful, sleep music, study music, worship guitar, classical guitar, soothing melodies', '', 'approved', 'Original recording and arrangement by Robinson. All compositions are either original or based on public domain melodies. No copyrighted material reused without permission.', '2025-09-10 20:12:45', '2025-09-10 20:12:45');

-- --------------------------------------------------------

--
-- Table structure for table `content_moderation_log`
--

CREATE TABLE `content_moderation_log` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` enum('flag','unflag','approve','reject') NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_moderation_log`
--

INSERT INTO `content_moderation_log` (`id`, `content_id`, `admin_id`, `action`, `details`, `created_at`) VALUES
(2, 59, NULL, 'flag', 'auto-flagged: 1 reports >= threshold 1', '2025-09-09 12:28:50'),
(3, 59, 5, 'approve', 'approved by admin', '2025-09-09 12:30:59'),
(4, 59, NULL, 'flag', 'auto-flagged: 1 reports >= threshold 1', '2025-09-09 12:38:27'),
(5, 59, 5, 'approve', 'approved by admin', '2025-09-09 13:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `reporter_id` varchar(255) NOT NULL,
  `reason` varchar(64) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `creator_requirements`
--

CREATE TABLE `creator_requirements` (
  `id` int(11) NOT NULL,
  `min_content_posts` int(11) DEFAULT 5,
  `min_account_age_days` int(11) DEFAULT 30,
  `require_verification` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `creator_requirements`
--

INSERT INTO `creator_requirements` (`id`, `min_content_posts`, `min_account_age_days`, `require_verification`, `updated_by`, `updated_at`) VALUES
(1, 1, 1, 0, 5, '2025-09-17 20:16:21');

-- --------------------------------------------------------

--
-- Table structure for table `engagements`
--

CREATE TABLE `engagements` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `type` enum('view','like','follow') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `claimed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `engagements`
--

INSERT INTO `engagements` (`id`, `content_id`, `user_id`, `type`, `created_at`, `claimed`) VALUES
(1, 59, '1', 'view', '2025-09-08 18:35:12', 1),
(5, 59, '1', 'view', '2025-09-08 18:36:48', 1),
(7, 59, '1', 'view', '2025-09-08 18:38:51', 1),
(8, 59, '1', 'view', '2025-09-08 18:43:28', 1),
(9, 59, '1', 'view', '2025-09-08 18:44:32', 1),
(10, 59, '1', 'view', '2025-09-08 18:54:28', 1),
(11, 59, '1', 'view', '2025-09-08 18:54:43', 1),
(12, 59, '1', 'view', '2025-09-08 19:01:17', 1),
(13, 59, '1', 'view', '2025-09-08 19:04:19', 1),
(14, 59, '1', 'view', '2025-09-08 19:34:21', 1),
(16, 59, '1', 'view', '2025-09-08 19:40:17', 1),
(18, 59, '1', 'view', '2025-09-08 19:51:14', 1),
(19, 59, '1', 'view', '2025-09-08 19:52:30', 1),
(20, 59, '1', 'view', '2025-09-08 20:20:11', 1),
(21, 59, '1', 'view', '2025-09-08 20:23:25', 1),
(22, 59, '1', 'view', '2025-09-08 20:38:35', 1),
(23, 59, '1', 'view', '2025-09-08 20:59:08', 1),
(24, 59, '1', 'view', '2025-09-08 21:00:08', 1),
(26, 59, '1', 'view', '2025-09-08 21:04:26', 1),
(27, 59, '1', 'view', '2025-09-09 10:49:13', 1),
(28, 59, '1', 'view', '2025-09-09 10:49:41', 1),
(29, 59, '1', 'view', '2025-09-09 10:57:41', 1),
(30, 59, '1', 'view', '2025-09-09 11:09:33', 1),
(31, 59, '1', 'view', '2025-09-09 11:45:42', 1),
(32, 59, '1', 'view', '2025-09-09 12:04:07', 1),
(33, 59, '1', 'view', '2025-09-09 12:06:57', 1),
(34, 59, '1', 'view', '2025-09-09 12:09:01', 1),
(35, 59, '1', 'view', '2025-09-09 12:24:57', 1),
(36, 59, '1', 'view', '2025-09-09 12:27:51', 1),
(37, 59, '1', 'view', '2025-09-09 12:38:19', 1),
(38, 59, '1', 'view', '2025-09-09 13:44:29', 1),
(39, 59, '1', 'view', '2025-09-09 13:45:47', 1),
(40, 59, '1', 'view', '2025-09-09 13:46:03', 1),
(41, 59, '1', 'view', '2025-09-09 13:46:24', 1),
(42, 59, '1', 'view', '2025-09-09 13:46:38', 1),
(43, 59, '1', 'view', '2025-09-09 14:01:39', 1),
(44, 59, '1', 'view', '2025-09-09 14:02:04', 1),
(45, 59, '1', 'view', '2025-09-09 14:06:23', 1),
(46, 59, '1', 'view', '2025-09-09 14:07:14', 1),
(47, 59, '1', 'view', '2025-09-09 14:07:30', 1),
(48, 59, '1', 'view', '2025-09-09 14:09:39', 1),
(49, 59, '1', 'view', '2025-09-09 14:10:43', 1),
(50, 59, '1', 'view', '2025-09-09 14:11:15', 1),
(51, 59, '1', 'view', '2025-09-09 14:12:07', 1),
(52, 59, '1', 'view', '2025-09-09 14:26:18', 1),
(53, 59, '1', 'view', '2025-09-09 14:26:51', 1),
(54, 59, '1', 'view', '2025-09-09 14:30:11', 1),
(55, 59, '1', 'view', '2025-09-09 14:31:31', 1),
(56, 59, '1', 'view', '2025-09-09 14:35:03', 1),
(57, 59, '1', 'view', '2025-09-09 14:39:34', 1),
(58, 59, '1', 'view', '2025-09-09 14:41:58', 1),
(59, 59, '1', 'view', '2025-09-09 15:13:24', 1),
(60, 59, '1', 'view', '2025-09-09 15:24:32', 1),
(61, 59, '1', 'like', '2025-09-09 15:24:38', 0),
(62, 62, '1', 'view', '2025-09-12 13:29:16', 1),
(63, 62, '1', 'like', '2025-09-12 13:29:25', 0),
(64, 69, '1', 'view', '2025-09-12 13:41:20', 0),
(65, 60, '1', 'view', '2025-09-15 07:53:21', 1),
(66, 60, '1', 'view', '2025-09-15 07:54:26', 1),
(67, 60, '1', 'like', '2025-09-15 07:54:29', 0),
(68, 59, '1', 'view', '2025-09-15 09:57:21', 1),
(69, 60, '1', 'view', '2025-09-15 09:57:31', 1),
(70, 62, '1', 'view', '2025-09-15 09:57:34', 1),
(71, 61, '1', 'view', '2025-09-15 09:57:38', 1),
(72, 63, '1', 'view', '2025-09-15 09:57:42', 1),
(73, 63, '1', 'view', '2025-09-15 09:57:45', 1),
(74, 64, '1', 'view', '2025-09-15 09:57:49', 0),
(75, 65, '1', 'view', '2025-09-15 09:57:52', 0),
(76, 68, '1', 'view', '2025-09-15 09:57:57', 0),
(77, 67, '1', 'view', '2025-09-15 09:58:00', 0),
(78, 69, '1', 'view', '2025-09-15 09:58:03', 0),
(79, 69, '1', 'view', '2025-09-15 09:58:09', 0),
(80, 66, '1', 'view', '2025-09-15 09:58:25', 0),
(81, 69, '1', 'view', '2025-09-15 09:58:42', 0),
(82, 69, '1', 'like', '2025-09-15 09:58:51', 0),
(83, 65, '1', 'view', '2025-09-15 09:58:59', 0),
(84, 65, '1', 'like', '2025-09-15 09:59:04', 0),
(85, 64, '1', 'view', '2025-09-15 09:59:12', 0),
(86, 64, '1', 'like', '2025-09-15 09:59:15', 0),
(87, 63, '1', 'view', '2025-09-15 09:59:23', 1),
(88, 63, '1', 'like', '2025-09-15 09:59:25', 0),
(89, 63, '1', 'view', '2025-09-15 09:59:31', 1),
(90, 63, '1', 'view', '2025-09-15 09:59:34', 1),
(91, 63, '1', 'view', '2025-09-15 09:59:37', 1),
(92, 63, '1', 'view', '2025-09-15 09:59:42', 1),
(93, 63, '1', 'view', '2025-09-15 09:59:45', 1),
(94, 63, '1', 'view', '2025-09-15 09:59:47', 1),
(95, 63, '1', 'view', '2025-09-15 09:59:50', 1),
(96, 63, '1', 'view', '2025-09-15 09:59:52', 1),
(97, 63, '1', 'view', '2025-09-15 09:59:54', 1),
(98, 63, '1', 'view', '2025-09-15 09:59:56', 1),
(99, 63, '1', 'view', '2025-09-15 09:59:57', 1),
(100, 63, '1', 'view', '2025-09-15 09:59:59', 1),
(101, 63, '1', 'view', '2025-09-15 10:00:01', 1),
(102, 63, '1', 'view', '2025-09-15 10:00:03', 1),
(103, 63, '1', 'view', '2025-09-15 10:00:05', 1),
(104, 63, '1', 'view', '2025-09-15 10:00:07', 1),
(105, 63, '1', 'view', '2025-09-15 10:00:09', 1),
(106, 63, '1', 'view', '2025-09-15 10:00:11', 1),
(107, 63, '1', 'view', '2025-09-15 10:00:12', 1),
(108, 63, '1', 'view', '2025-09-15 10:00:14', 1),
(109, 63, '1', 'view', '2025-09-15 10:00:16', 1),
(110, 63, '1', 'view', '2025-09-15 10:00:17', 1),
(111, 63, '1', 'view', '2025-09-15 10:00:19', 1),
(112, 63, '1', 'view', '2025-09-15 10:00:20', 1),
(113, 63, '1', 'view', '2025-09-15 10:00:22', 1),
(114, 63, '1', 'view', '2025-09-15 10:00:23', 1),
(115, 63, '1', 'view', '2025-09-15 10:00:25', 1),
(116, 63, '1', 'view', '2025-09-15 10:00:27', 1),
(117, 63, '1', 'view', '2025-09-15 10:00:28', 1),
(118, 63, '1', 'view', '2025-09-15 10:00:31', 1),
(119, 63, '1', 'view', '2025-09-15 10:00:33', 1),
(120, 63, '1', 'view', '2025-09-15 10:00:35', 1),
(121, 63, '1', 'view', '2025-09-15 10:00:36', 1),
(122, 63, '1', 'view', '2025-09-15 10:00:38', 1),
(123, 63, '1', 'view', '2025-09-15 10:00:39', 1),
(124, 63, '1', 'view', '2025-09-15 10:00:41', 1),
(125, 63, '1', 'view', '2025-09-15 10:00:43', 1),
(126, 63, '1', 'view', '2025-09-15 10:00:44', 1),
(127, 63, '1', 'view', '2025-09-15 10:00:46', 1),
(128, 63, '1', 'view', '2025-09-15 10:00:49', 1),
(129, 63, '1', 'view', '2025-09-15 10:00:51', 1),
(130, 63, '1', 'view', '2025-09-15 10:00:53', 1),
(131, 63, '1', 'view', '2025-09-15 10:00:55', 1),
(132, 63, '1', 'view', '2025-09-15 10:00:57', 1),
(133, 63, '1', 'view', '2025-09-15 10:00:59', 1),
(134, 63, '1', 'view', '2025-09-15 10:01:01', 1),
(135, 63, '1', 'view', '2025-09-15 10:01:02', 1),
(136, 63, '1', 'view', '2025-09-15 10:01:04', 1),
(137, 63, '1', 'view', '2025-09-15 10:01:07', 1),
(138, 63, '1', 'view', '2025-09-15 10:01:09', 1),
(139, 63, '1', 'view', '2025-09-15 10:01:11', 1),
(140, 63, '1', 'view', '2025-09-15 10:01:13', 1),
(141, 63, '1', 'view', '2025-09-15 10:01:15', 1),
(142, 63, '1', 'view', '2025-09-15 10:01:18', 1),
(143, 63, '1', 'view', '2025-09-15 10:01:20', 1),
(144, 60, '1', 'view', '2025-09-15 10:04:03', 1),
(145, 60, '1', 'view', '2025-09-15 10:04:07', 1),
(146, 63, '1', 'view', '2025-09-15 10:04:15', 1),
(147, 63, '1', 'view', '2025-09-15 10:04:17', 1),
(148, 63, '1', 'view', '2025-09-15 10:04:18', 1),
(149, 63, '1', 'view', '2025-09-15 10:04:19', 1),
(150, 63, '1', 'view', '2025-09-15 10:04:37', 1),
(151, 63, '1', 'view', '2025-09-15 10:04:40', 1),
(152, 63, '1', 'view', '2025-09-15 10:04:42', 1),
(153, 63, '1', 'view', '2025-09-15 10:04:43', 1),
(154, 63, '1', 'view', '2025-09-15 10:04:46', 1),
(155, 63, '1', 'view', '2025-09-15 10:04:48', 1),
(156, 63, '1', 'view', '2025-09-15 10:04:50', 1),
(157, 63, '1', 'view', '2025-09-15 10:04:51', 1),
(158, 63, '1', 'view', '2025-09-15 10:05:06', 1),
(159, 63, '1', 'view', '2025-09-15 10:05:17', 1),
(160, 63, '1', 'view', '2025-09-15 10:05:24', 1),
(161, 63, '1', 'view', '2025-09-15 10:05:27', 1),
(162, 63, '1', 'view', '2025-09-15 10:05:28', 1),
(163, 63, '1', 'view', '2025-09-15 10:05:30', 0),
(164, 63, '1', 'view', '2025-09-15 10:05:32', 0),
(165, 63, '1', 'view', '2025-09-15 10:05:33', 0),
(166, 63, '1', 'view', '2025-09-15 10:05:36', 0),
(167, 63, '1', 'view', '2025-09-15 10:05:37', 0),
(168, 63, '1', 'view', '2025-09-15 10:05:39', 0),
(169, 63, '1', 'view', '2025-09-15 10:05:41', 0),
(170, 63, '1', 'view', '2025-09-15 10:05:43', 0),
(171, 63, '1', 'view', '2025-09-15 10:05:45', 0),
(172, 63, '1', 'view', '2025-09-15 10:05:48', 0),
(173, 63, '1', 'view', '2025-09-15 10:05:51', 0),
(174, 63, '1', 'view', '2025-09-15 10:05:52', 0),
(175, 63, '1', 'view', '2025-09-15 10:05:54', 0),
(176, 63, '1', 'view', '2025-09-15 10:05:56', 0),
(177, 63, '1', 'view', '2025-09-15 10:05:58', 0),
(178, 63, '1', 'view', '2025-09-15 10:06:01', 0),
(179, 63, '1', 'view', '2025-09-15 10:06:03', 0),
(180, 63, '1', 'view', '2025-09-15 10:06:05', 0),
(181, 63, '1', 'view', '2025-09-15 10:06:07', 0),
(182, 63, '1', 'view', '2025-09-15 10:06:09', 0),
(183, 60, '1', 'view', '2025-09-14 17:40:49', 1),
(184, 61, '1', 'view', '2025-09-14 19:20:49', 1),
(185, 62, '1', 'view', '2025-09-14 21:00:49', 1),
(186, 63, '1', 'view', '2025-09-14 22:40:49', 0),
(187, 64, '1', 'view', '2025-09-15 00:20:49', 0),
(188, 65, '1', 'view', '2025-09-15 02:00:49', 0),
(189, 66, '1', 'view', '2025-09-15 03:40:49', 0),
(190, 67, '1', 'view', '2025-09-15 05:20:49', 0),
(191, 68, '1', 'view', '2025-09-15 07:00:49', 0),
(192, 69, '1', 'view', '2025-09-15 08:40:49', 0),
(193, 59, '1', 'view', '2025-09-14 17:50:49', 1),
(194, 60, '1', 'view', '2025-09-14 19:30:49', 1),
(195, 61, '1', 'view', '2025-09-14 21:10:49', 1),
(196, 62, '1', 'view', '2025-09-14 22:50:49', 1),
(197, 63, '1', 'view', '2025-09-15 00:30:49', 0),
(198, 64, '1', 'view', '2025-09-15 02:10:49', 0),
(199, 65, '1', 'view', '2025-09-15 03:50:49', 0),
(200, 66, '1', 'view', '2025-09-15 05:30:49', 0),
(201, 67, '1', 'view', '2025-09-15 07:10:49', 0),
(202, 68, '1', 'view', '2025-09-15 08:50:49', 0),
(203, 69, '1', 'view', '2025-09-14 18:00:49', 0),
(204, 59, '1', 'view', '2025-09-14 19:40:49', 1),
(205, 60, '1', 'view', '2025-09-14 21:20:49', 1),
(206, 61, '1', 'view', '2025-09-14 23:00:49', 1),
(207, 62, '1', 'view', '2025-09-15 00:40:49', 1),
(208, 63, '1', 'view', '2025-09-15 02:20:49', 0),
(209, 64, '1', 'view', '2025-09-15 04:00:49', 0),
(210, 65, '1', 'view', '2025-09-15 05:40:49', 0),
(211, 66, '1', 'view', '2025-09-15 07:20:49', 0),
(212, 67, '1', 'view', '2025-09-15 09:00:49', 0),
(213, 68, '1', 'view', '2025-09-14 18:10:49', 0),
(214, 69, '1', 'view', '2025-09-14 19:50:49', 0),
(215, 59, '1', 'view', '2025-09-14 21:30:49', 1),
(216, 60, '1', 'view', '2025-09-14 23:10:49', 1),
(217, 61, '1', 'view', '2025-09-15 00:50:49', 1),
(218, 62, '1', 'view', '2025-09-15 02:30:49', 1),
(219, 63, '1', 'view', '2025-09-15 04:10:49', 0),
(220, 64, '1', 'view', '2025-09-15 05:50:49', 0),
(221, 65, '1', 'view', '2025-09-15 07:30:49', 0),
(222, 66, '1', 'view', '2025-09-15 09:10:49', 0),
(223, 67, '1', 'view', '2025-09-14 18:20:49', 0),
(224, 68, '1', 'view', '2025-09-14 20:00:49', 0),
(225, 69, '1', 'view', '2025-09-14 21:40:49', 0),
(226, 59, '1', 'view', '2025-09-14 23:20:49', 1),
(227, 60, '1', 'view', '2025-09-15 01:00:49', 1),
(228, 61, '1', 'view', '2025-09-15 02:40:49', 1),
(229, 62, '1', 'view', '2025-09-15 04:20:49', 1),
(230, 63, '1', 'view', '2025-09-15 06:00:49', 0),
(231, 64, '1', 'view', '2025-09-15 07:40:49', 0),
(232, 65, '1', 'view', '2025-09-15 09:20:49', 0),
(233, 66, '1', 'view', '2025-09-14 18:30:49', 0),
(234, 67, '1', 'view', '2025-09-14 20:10:49', 0),
(235, 68, '1', 'view', '2025-09-14 21:50:49', 0),
(236, 69, '1', 'view', '2025-09-14 23:30:49', 0),
(237, 59, '1', 'view', '2025-09-15 01:10:49', 1),
(238, 60, '1', 'view', '2025-09-15 02:50:49', 1),
(239, 61, '1', 'view', '2025-09-15 04:30:49', 1),
(240, 62, '1', 'view', '2025-09-15 06:10:49', 1),
(241, 63, '1', 'view', '2025-09-15 07:50:49', 0),
(242, 64, '1', 'view', '2025-09-15 09:30:49', 0),
(243, 65, '1', 'view', '2025-09-14 18:40:49', 0),
(244, 66, '1', 'view', '2025-09-14 20:20:49', 0),
(245, 67, '1', 'view', '2025-09-14 22:00:49', 0),
(246, 68, '1', 'view', '2025-09-14 23:40:49', 0),
(247, 69, '1', 'view', '2025-09-15 01:20:49', 0),
(248, 59, '1', 'view', '2025-09-15 03:00:49', 1),
(249, 60, '1', 'view', '2025-09-15 04:40:49', 1),
(250, 61, '1', 'view', '2025-09-15 06:20:49', 1),
(251, 62, '1', 'view', '2025-09-15 08:00:49', 1),
(252, 63, '1', 'view', '2025-09-15 09:40:49', 0),
(253, 64, '1', 'view', '2025-09-14 18:50:49', 0),
(254, 65, '1', 'view', '2025-09-14 20:30:49', 0),
(255, 66, '1', 'view', '2025-09-14 22:10:49', 0),
(256, 67, '1', 'view', '2025-09-14 23:50:49', 0),
(257, 68, '1', 'view', '2025-09-15 01:30:49', 0),
(258, 69, '1', 'view', '2025-09-15 03:10:49', 0),
(259, 59, '1', 'view', '2025-09-15 04:50:49', 1),
(260, 60, '1', 'view', '2025-09-15 06:30:49', 1),
(261, 61, '1', 'view', '2025-09-15 08:10:49', 1),
(262, 62, '1', 'view', '2025-09-15 09:50:49', 1),
(263, 63, '1', 'view', '2025-09-14 19:00:49', 0),
(264, 64, '1', 'view', '2025-09-14 20:40:49', 0),
(265, 65, '1', 'view', '2025-09-14 22:20:49', 0),
(266, 66, '1', 'view', '2025-09-15 00:00:49', 0),
(267, 67, '1', 'view', '2025-09-15 01:40:49', 0),
(268, 68, '1', 'view', '2025-09-15 03:20:49', 0),
(269, 69, '1', 'view', '2025-09-15 05:00:49', 0),
(270, 59, '1', 'view', '2025-09-15 06:40:49', 1),
(271, 60, '1', 'view', '2025-09-15 08:20:49', 1),
(272, 61, '1', 'view', '2025-09-15 10:00:49', 1),
(273, 62, '1', 'view', '2025-09-14 19:10:49', 1),
(274, 63, '1', 'view', '2025-09-14 20:50:49', 0),
(275, 64, '1', 'view', '2025-09-14 22:30:49', 0),
(276, 65, '1', 'view', '2025-09-15 00:10:49', 0),
(277, 66, '1', 'view', '2025-09-15 01:50:49', 0),
(278, 67, '1', 'view', '2025-09-15 03:30:49', 0),
(279, 68, '1', 'view', '2025-09-15 05:10:49', 0),
(280, 69, '1', 'view', '2025-09-15 06:50:49', 0),
(281, 59, '1', 'view', '2025-09-15 08:30:49', 1),
(282, 60, '1', 'view', '2025-09-15 10:10:49', 1),
(283, 61, '1', 'view', '2025-09-14 17:41:49', 1),
(284, 62, '1', 'view', '2025-09-14 19:21:49', 1),
(285, 63, '1', 'view', '2025-09-14 21:01:49', 0),
(286, 64, '1', 'view', '2025-09-14 22:41:49', 0),
(287, 65, '1', 'view', '2025-09-15 00:21:49', 0),
(288, 66, '1', 'view', '2025-09-15 02:01:49', 0),
(289, 67, '1', 'view', '2025-09-15 03:41:49', 0),
(290, 68, '1', 'view', '2025-09-15 05:21:49', 0),
(291, 69, '1', 'view', '2025-09-15 07:01:49', 0),
(292, 59, '1', 'view', '2025-09-15 08:41:49', 1),
(293, 60, '1', 'view', '2025-09-14 17:51:49', 1),
(294, 61, '1', 'view', '2025-09-14 19:31:49', 1),
(295, 62, '1', 'view', '2025-09-14 21:11:49', 1),
(296, 63, '1', 'view', '2025-09-14 22:51:49', 0),
(297, 64, '1', 'view', '2025-09-15 00:31:49', 0),
(298, 65, '1', 'view', '2025-09-15 02:11:49', 0),
(299, 66, '1', 'view', '2025-09-15 03:51:49', 0),
(300, 67, '1', 'view', '2025-09-15 05:31:49', 0),
(301, 68, '1', 'view', '2025-09-15 07:11:49', 0),
(302, 69, '1', 'view', '2025-09-15 08:51:49', 0),
(303, 59, '1', 'view', '2025-09-14 18:01:49', 1),
(304, 60, '1', 'view', '2025-09-14 19:41:49', 1),
(305, 61, '1', 'view', '2025-09-14 21:21:49', 1),
(306, 62, '1', 'view', '2025-09-14 23:01:49', 1),
(307, 63, '1', 'view', '2025-09-15 00:41:49', 0),
(308, 64, '1', 'view', '2025-09-15 02:21:49', 0),
(309, 65, '1', 'view', '2025-09-15 04:01:49', 0),
(310, 66, '1', 'view', '2025-09-15 05:41:49', 0),
(311, 67, '1', 'view', '2025-09-15 07:21:49', 0),
(312, 68, '1', 'view', '2025-09-15 09:01:49', 0),
(313, 69, '1', 'view', '2025-09-14 18:11:49', 0),
(314, 59, '1', 'view', '2025-09-14 19:51:49', 1),
(315, 60, '1', 'view', '2025-09-14 21:31:49', 1),
(316, 61, '1', 'view', '2025-09-14 23:11:49', 1),
(317, 62, '1', 'view', '2025-09-15 00:51:49', 1),
(318, 63, '1', 'view', '2025-09-15 02:31:49', 0),
(319, 64, '1', 'view', '2025-09-15 04:11:49', 0),
(320, 65, '1', 'view', '2025-09-15 05:51:49', 0),
(321, 66, '1', 'view', '2025-09-15 07:31:49', 0),
(322, 67, '1', 'view', '2025-09-15 09:11:49', 0),
(323, 68, '1', 'view', '2025-09-14 18:21:49', 0),
(324, 69, '1', 'view', '2025-09-14 20:01:49', 0),
(325, 59, '1', 'view', '2025-09-14 21:41:49', 1),
(326, 60, '1', 'view', '2025-09-14 23:21:49', 1),
(327, 61, '1', 'view', '2025-09-15 01:01:49', 1),
(328, 62, '1', 'view', '2025-09-15 02:41:49', 1),
(329, 63, '1', 'view', '2025-09-15 04:21:49', 0),
(330, 64, '1', 'view', '2025-09-15 06:01:49', 0),
(331, 65, '1', 'view', '2025-09-15 07:41:49', 0),
(332, 66, '1', 'view', '2025-09-15 09:21:49', 0),
(333, 67, '1', 'view', '2025-09-14 18:31:49', 0),
(334, 68, '1', 'view', '2025-09-14 20:11:49', 0),
(335, 69, '1', 'view', '2025-09-14 21:51:49', 0),
(336, 59, '1', 'view', '2025-09-14 23:31:49', 1),
(337, 60, '1', 'view', '2025-09-15 01:11:49', 1),
(338, 61, '1', 'view', '2025-09-15 02:51:49', 1),
(339, 62, '1', 'view', '2025-09-15 04:31:49', 1),
(340, 63, '1', 'view', '2025-09-15 06:11:49', 0),
(341, 64, '1', 'view', '2025-09-15 07:51:49', 0),
(342, 65, '1', 'view', '2025-09-15 09:31:49', 0),
(343, 66, '1', 'view', '2025-09-14 18:41:49', 0),
(344, 67, '1', 'view', '2025-09-14 20:21:49', 0),
(345, 68, '1', 'view', '2025-09-14 22:01:49', 0),
(346, 69, '1', 'view', '2025-09-14 23:41:49', 0),
(347, 59, '1', 'view', '2025-09-15 01:21:49', 1),
(348, 60, '1', 'view', '2025-09-15 03:01:49', 1),
(349, 61, '1', 'view', '2025-09-15 04:41:49', 1),
(350, 62, '1', 'view', '2025-09-15 06:21:49', 1),
(351, 63, '1', 'view', '2025-09-15 08:01:49', 0),
(352, 64, '1', 'view', '2025-09-15 09:41:49', 0),
(353, 65, '1', 'view', '2025-09-14 18:51:49', 0),
(354, 66, '1', 'view', '2025-09-14 20:31:49', 0),
(355, 67, '1', 'view', '2025-09-14 22:11:49', 0),
(356, 68, '1', 'view', '2025-09-14 23:51:49', 0),
(357, 69, '1', 'view', '2025-09-15 01:31:49', 0),
(358, 59, '1', 'view', '2025-09-15 03:11:49', 1),
(359, 60, '1', 'view', '2025-09-15 04:51:49', 1),
(360, 61, '1', 'view', '2025-09-15 06:31:49', 1),
(361, 62, '1', 'view', '2025-09-15 08:11:49', 1),
(362, 63, '1', 'view', '2025-09-15 09:51:49', 0),
(363, 64, '1', 'view', '2025-09-14 19:01:49', 0),
(364, 65, '1', 'view', '2025-09-14 20:41:49', 0),
(365, 66, '1', 'view', '2025-09-14 22:21:49', 0),
(366, 67, '1', 'view', '2025-09-15 00:01:49', 0),
(367, 68, '1', 'view', '2025-09-15 01:41:49', 0),
(368, 69, '1', 'view', '2025-09-15 03:21:49', 0),
(369, 59, '1', 'view', '2025-09-15 05:01:49', 1),
(370, 60, '1', 'view', '2025-09-15 06:41:49', 1),
(371, 61, '1', 'view', '2025-09-15 08:21:49', 1),
(372, 62, '1', 'view', '2025-09-15 10:01:49', 1),
(373, 63, '1', 'view', '2025-09-14 19:11:49', 0),
(374, 64, '1', 'view', '2025-09-14 20:51:49', 0),
(375, 65, '1', 'view', '2025-09-14 22:31:49', 0),
(376, 66, '1', 'view', '2025-09-15 00:11:49', 0),
(377, 67, '1', 'view', '2025-09-15 01:51:49', 0),
(378, 68, '1', 'view', '2025-09-15 03:31:49', 0),
(379, 69, '1', 'view', '2025-09-15 05:11:49', 0),
(380, 59, '1', 'view', '2025-09-15 06:51:49', 1),
(381, 60, '1', 'view', '2025-09-15 08:31:49', 1),
(382, 61, '1', 'view', '2025-09-15 10:11:49', 1),
(383, 62, '1', 'view', '2025-09-14 17:42:49', 1),
(384, 63, '1', 'view', '2025-09-14 19:22:49', 0),
(385, 64, '1', 'view', '2025-09-14 21:02:49', 0),
(386, 65, '1', 'view', '2025-09-14 22:42:49', 0),
(387, 66, '1', 'view', '2025-09-15 00:22:49', 0),
(388, 67, '1', 'view', '2025-09-15 02:02:49', 0),
(389, 68, '1', 'view', '2025-09-15 03:42:49', 0),
(390, 69, '1', 'view', '2025-09-15 05:22:49', 0),
(391, 59, '1', 'view', '2025-09-15 07:02:49', 1),
(392, 60, '1', 'view', '2025-09-15 08:42:49', 1),
(393, 61, '1', 'view', '2025-09-14 17:52:49', 1),
(394, 62, '1', 'view', '2025-09-14 19:32:49', 1),
(395, 63, '1', 'view', '2025-09-14 21:12:49', 0),
(396, 64, '1', 'view', '2025-09-14 22:52:49', 0),
(397, 65, '1', 'view', '2025-09-15 00:32:49', 0),
(398, 66, '1', 'view', '2025-09-15 02:12:49', 0),
(399, 67, '1', 'view', '2025-09-15 03:52:49', 0),
(400, 68, '1', 'view', '2025-09-15 05:32:49', 0),
(401, 69, '1', 'view', '2025-09-15 07:12:49', 0),
(402, 59, '1', 'view', '2025-09-15 08:52:49', 1),
(403, 60, '1', 'view', '2025-09-14 18:02:49', 1),
(404, 61, '1', 'view', '2025-09-14 19:42:49', 1),
(405, 62, '1', 'view', '2025-09-14 21:22:49', 1),
(406, 63, '1', 'view', '2025-09-14 23:02:49', 0),
(407, 64, '1', 'view', '2025-09-15 00:42:49', 0),
(408, 65, '1', 'view', '2025-09-15 02:22:49', 0),
(409, 66, '1', 'view', '2025-09-15 04:02:49', 0),
(410, 67, '1', 'view', '2025-09-15 05:42:49', 0),
(411, 68, '1', 'view', '2025-09-15 07:22:49', 0),
(412, 69, '1', 'view', '2025-09-15 09:02:49', 0),
(413, 59, '1', 'view', '2025-09-14 18:12:49', 1),
(414, 60, '1', 'view', '2025-09-14 19:52:49', 1),
(415, 61, '1', 'view', '2025-09-14 21:32:49', 1),
(416, 62, '1', 'view', '2025-09-14 23:12:49', 1),
(417, 63, '1', 'view', '2025-09-15 00:52:49', 0),
(418, 64, '1', 'view', '2025-09-15 02:32:49', 0),
(419, 65, '1', 'view', '2025-09-15 04:12:49', 0),
(420, 66, '1', 'view', '2025-09-15 05:52:49', 0),
(421, 67, '1', 'view', '2025-09-15 07:32:49', 0),
(422, 68, '1', 'view', '2025-09-15 09:12:49', 0),
(423, 69, '1', 'view', '2025-09-14 18:22:49', 0),
(424, 59, '1', 'view', '2025-09-14 20:02:49', 1),
(425, 60, '1', 'view', '2025-09-14 21:42:49', 1),
(426, 61, '1', 'view', '2025-09-14 23:22:49', 1),
(427, 62, '1', 'view', '2025-09-15 01:02:49', 1),
(428, 63, '1', 'view', '2025-09-15 02:42:49', 0),
(429, 64, '1', 'view', '2025-09-15 04:22:49', 0),
(430, 65, '1', 'view', '2025-09-15 06:02:49', 0),
(431, 66, '1', 'view', '2025-09-15 07:42:49', 0),
(432, 67, '1', 'view', '2025-09-15 09:22:49', 0),
(433, 68, '1', 'view', '2025-09-14 18:32:49', 0),
(434, 69, '1', 'view', '2025-09-14 20:12:49', 0),
(435, 59, '1', 'view', '2025-09-14 21:52:49', 1),
(436, 60, '1', 'view', '2025-09-14 23:32:49', 1),
(437, 61, '1', 'view', '2025-09-15 01:12:49', 1),
(438, 62, '1', 'view', '2025-09-15 02:52:49', 1),
(439, 63, '1', 'view', '2025-09-15 04:32:49', 0),
(440, 64, '1', 'view', '2025-09-15 06:12:49', 0),
(441, 65, '1', 'view', '2025-09-15 07:52:49', 0),
(442, 66, '1', 'view', '2025-09-15 09:32:49', 0),
(443, 67, '1', 'view', '2025-09-14 18:42:49', 0),
(444, 68, '1', 'view', '2025-09-14 20:22:49', 0),
(445, 69, '1', 'view', '2025-09-14 22:02:49', 0),
(446, 59, '1', 'view', '2025-09-14 23:42:49', 1),
(447, 60, '1', 'view', '2025-09-15 01:22:49', 1),
(448, 61, '1', 'view', '2025-09-15 03:02:49', 1),
(449, 62, '1', 'view', '2025-09-15 04:42:49', 1),
(450, 63, '1', 'view', '2025-09-15 06:22:49', 0),
(451, 64, '1', 'view', '2025-09-15 08:02:49', 0),
(452, 65, '1', 'view', '2025-09-15 09:42:49', 0),
(453, 66, '1', 'view', '2025-09-14 18:52:49', 0),
(454, 67, '1', 'view', '2025-09-14 20:32:49', 0),
(455, 68, '1', 'view', '2025-09-14 22:12:49', 0),
(456, 69, '1', 'view', '2025-09-14 23:52:49', 0),
(457, 59, '1', 'view', '2025-09-15 01:32:49', 1),
(458, 60, '1', 'view', '2025-09-15 03:12:49', 1),
(459, 61, '1', 'view', '2025-09-15 04:52:49', 1),
(460, 62, '1', 'view', '2025-09-15 06:32:49', 1),
(461, 63, '1', 'view', '2025-09-15 08:12:49', 0),
(462, 64, '1', 'view', '2025-09-15 09:52:49', 0),
(463, 65, '1', 'view', '2025-09-14 19:02:49', 0),
(464, 66, '1', 'view', '2025-09-14 20:42:49', 0),
(465, 67, '1', 'view', '2025-09-14 22:22:49', 0),
(466, 68, '1', 'view', '2025-09-15 00:02:49', 0),
(467, 69, '1', 'view', '2025-09-15 01:42:49', 0),
(468, 59, '1', 'view', '2025-09-15 03:22:49', 1),
(469, 60, '1', 'view', '2025-09-15 05:02:49', 1),
(470, 61, '1', 'view', '2025-09-15 06:42:49', 1),
(471, 62, '1', 'view', '2025-09-15 08:22:49', 1),
(472, 63, '1', 'view', '2025-09-15 10:02:49', 0),
(473, 64, '1', 'view', '2025-09-14 19:12:49', 0),
(474, 65, '1', 'view', '2025-09-14 20:52:49', 0),
(475, 66, '1', 'view', '2025-09-14 22:32:49', 0),
(476, 67, '1', 'view', '2025-09-15 00:12:49', 0),
(477, 68, '1', 'view', '2025-09-15 01:52:49', 0),
(478, 69, '1', 'view', '2025-09-15 03:32:49', 0),
(479, 59, '1', 'view', '2025-09-15 05:12:49', 1),
(480, 60, '1', 'view', '2025-09-15 06:52:49', 1),
(481, 61, '1', 'view', '2025-09-15 08:32:49', 1),
(482, 62, '1', 'view', '2025-09-15 10:12:49', 1),
(483, 63, '1', 'view', '2025-09-14 17:43:49', 0),
(484, 64, '1', 'view', '2025-09-14 19:23:49', 0),
(485, 65, '1', 'view', '2025-09-14 21:03:49', 0),
(486, 66, '1', 'view', '2025-09-14 22:43:49', 0),
(487, 67, '1', 'view', '2025-09-15 00:23:49', 0),
(488, 68, '1', 'view', '2025-09-15 02:03:49', 0),
(489, 69, '1', 'view', '2025-09-15 03:43:49', 0),
(490, 59, '1', 'view', '2025-09-15 05:23:49', 1),
(491, 60, '1', 'view', '2025-09-15 07:03:49', 1),
(492, 61, '1', 'view', '2025-09-15 08:43:49', 1),
(493, 62, '1', 'view', '2025-09-14 17:53:49', 1),
(494, 63, '1', 'view', '2025-09-14 19:33:49', 0),
(495, 64, '1', 'view', '2025-09-14 21:13:49', 0),
(496, 65, '1', 'view', '2025-09-14 22:53:49', 0),
(497, 66, '1', 'view', '2025-09-15 00:33:49', 0),
(498, 67, '1', 'view', '2025-09-15 02:13:49', 0),
(499, 68, '1', 'view', '2025-09-15 03:53:49', 0),
(500, 69, '1', 'view', '2025-09-15 05:33:49', 0),
(501, 59, '1', 'view', '2025-09-15 07:13:49', 1),
(502, 60, '1', 'view', '2025-09-15 08:53:49', 1),
(503, 61, '1', 'view', '2025-09-14 18:03:49', 1),
(504, 62, '1', 'view', '2025-09-14 19:43:49', 1),
(505, 63, '1', 'view', '2025-09-14 21:23:49', 0),
(506, 64, '1', 'view', '2025-09-14 23:03:49', 0),
(507, 65, '1', 'view', '2025-09-15 00:43:49', 0),
(508, 66, '1', 'view', '2025-09-15 02:23:49', 0),
(509, 67, '1', 'view', '2025-09-15 04:03:49', 0),
(510, 68, '1', 'view', '2025-09-15 05:43:49', 0),
(511, 69, '1', 'view', '2025-09-15 07:23:49', 0),
(512, 59, '1', 'view', '2025-09-15 09:03:49', 1),
(513, 60, '1', 'view', '2025-09-14 18:13:49', 1),
(514, 61, '1', 'view', '2025-09-14 19:53:49', 1),
(515, 62, '1', 'view', '2025-09-14 21:33:49', 1),
(516, 63, '1', 'view', '2025-09-14 23:13:49', 0),
(517, 64, '1', 'view', '2025-09-15 00:53:49', 0),
(518, 65, '1', 'view', '2025-09-15 02:33:49', 0),
(519, 66, '1', 'view', '2025-09-15 04:13:49', 0),
(520, 67, '1', 'view', '2025-09-15 05:53:49', 0),
(521, 68, '1', 'view', '2025-09-15 07:33:49', 0),
(522, 69, '1', 'view', '2025-09-15 09:13:49', 0),
(523, 59, '1', 'view', '2025-09-14 18:23:49', 1),
(524, 60, '1', 'view', '2025-09-14 20:03:49', 1),
(525, 61, '1', 'view', '2025-09-14 21:43:49', 1),
(526, 62, '1', 'view', '2025-09-14 23:23:49', 1),
(527, 63, '1', 'view', '2025-09-15 01:03:49', 0),
(528, 64, '1', 'view', '2025-09-15 02:43:49', 0),
(529, 65, '1', 'view', '2025-09-15 04:23:49', 0),
(530, 66, '1', 'view', '2025-09-15 06:03:49', 0),
(531, 67, '1', 'view', '2025-09-15 07:43:49', 0),
(532, 68, '1', 'view', '2025-09-15 09:23:49', 0),
(533, 69, '1', 'view', '2025-09-14 18:33:49', 0),
(534, 59, '1', 'view', '2025-09-14 20:13:49', 1),
(535, 60, '1', 'view', '2025-09-14 21:53:49', 1),
(536, 61, '1', 'view', '2025-09-14 23:33:49', 1),
(537, 62, '1', 'view', '2025-09-15 01:13:49', 1),
(538, 63, '1', 'view', '2025-09-15 02:53:49', 0),
(539, 64, '1', 'view', '2025-09-15 04:33:49', 0),
(540, 65, '1', 'view', '2025-09-15 06:13:49', 0),
(541, 66, '1', 'view', '2025-09-15 07:53:49', 0),
(542, 67, '1', 'view', '2025-09-15 09:33:49', 0),
(543, 68, '1', 'view', '2025-09-14 18:43:49', 0),
(544, 69, '1', 'view', '2025-09-14 20:23:49', 0),
(545, 59, '1', 'view', '2025-09-14 22:03:49', 1),
(546, 60, '1', 'view', '2025-09-14 23:43:49', 1),
(547, 61, '1', 'view', '2025-09-15 01:23:49', 1),
(548, 62, '1', 'view', '2025-09-15 03:03:49', 1),
(549, 63, '1', 'view', '2025-09-15 04:43:49', 0),
(550, 64, '1', 'view', '2025-09-15 06:23:49', 0),
(551, 65, '1', 'view', '2025-09-15 08:03:49', 0),
(552, 66, '1', 'view', '2025-09-15 09:43:49', 0),
(553, 67, '1', 'view', '2025-09-14 18:53:49', 0),
(554, 68, '1', 'view', '2025-09-14 20:33:49', 0),
(555, 69, '1', 'view', '2025-09-14 22:13:49', 0),
(556, 59, '1', 'view', '2025-09-14 23:53:49', 1),
(557, 60, '1', 'view', '2025-09-15 01:33:49', 1),
(558, 61, '1', 'view', '2025-09-15 03:13:49', 1),
(559, 62, '1', 'view', '2025-09-15 04:53:49', 1),
(560, 63, '1', 'view', '2025-09-15 06:33:49', 0),
(561, 64, '1', 'view', '2025-09-15 08:13:49', 0),
(562, 65, '1', 'view', '2025-09-15 09:53:49', 0),
(563, 66, '1', 'view', '2025-09-14 19:03:49', 0),
(564, 67, '1', 'view', '2025-09-14 20:43:49', 0),
(565, 68, '1', 'view', '2025-09-14 22:23:49', 0),
(566, 69, '1', 'view', '2025-09-15 00:03:49', 0),
(567, 59, '1', 'view', '2025-09-15 01:43:49', 1),
(568, 60, '1', 'view', '2025-09-15 03:23:49', 1),
(569, 61, '1', 'view', '2025-09-15 05:03:49', 1),
(570, 62, '1', 'view', '2025-09-15 06:43:49', 1),
(571, 63, '1', 'view', '2025-09-15 08:23:49', 0),
(572, 64, '1', 'view', '2025-09-15 10:03:49', 0),
(573, 65, '1', 'view', '2025-09-14 19:13:49', 0),
(574, 66, '1', 'view', '2025-09-14 20:53:49', 0),
(575, 67, '1', 'view', '2025-09-14 22:33:49', 0),
(576, 68, '1', 'view', '2025-09-15 00:13:49', 0),
(577, 69, '1', 'view', '2025-09-15 01:53:49', 0),
(578, 59, '1', 'view', '2025-09-15 03:33:49', 1),
(579, 60, '1', 'view', '2025-09-15 05:13:49', 1),
(580, 61, '1', 'view', '2025-09-15 06:53:49', 1),
(581, 62, '1', 'view', '2025-09-15 08:33:49', 1),
(582, 63, '1', 'view', '2025-09-15 10:13:49', 0),
(583, 64, '1', 'view', '2025-09-14 17:44:49', 0),
(584, 65, '1', 'view', '2025-09-14 19:24:49', 0),
(585, 66, '1', 'view', '2025-09-14 21:04:49', 0),
(586, 67, '1', 'view', '2025-09-14 22:44:49', 0),
(587, 68, '1', 'view', '2025-09-15 00:24:49', 0),
(588, 69, '1', 'view', '2025-09-15 02:04:49', 0),
(589, 59, '1', 'view', '2025-09-15 03:44:49', 1),
(590, 60, '1', 'view', '2025-09-15 05:24:49', 1),
(591, 61, '1', 'view', '2025-09-15 07:04:49', 1),
(592, 62, '1', 'view', '2025-09-15 08:44:49', 1),
(593, 63, '1', 'view', '2025-09-14 17:54:49', 0),
(594, 64, '1', 'view', '2025-09-14 19:34:49', 0),
(595, 65, '1', 'view', '2025-09-14 21:14:49', 0),
(596, 66, '1', 'view', '2025-09-14 22:54:49', 0),
(597, 67, '1', 'view', '2025-09-15 00:34:49', 0),
(598, 68, '1', 'view', '2025-09-15 02:14:49', 0),
(599, 69, '1', 'view', '2025-09-15 03:54:49', 0),
(600, 59, '1', 'view', '2025-09-15 05:34:49', 1),
(601, 60, '1', 'view', '2025-09-15 07:14:49', 1),
(602, 61, '1', 'view', '2025-09-15 08:54:49', 1),
(603, 62, '1', 'view', '2025-09-14 18:04:49', 1),
(604, 63, '1', 'view', '2025-09-14 19:44:49', 0),
(605, 64, '1', 'view', '2025-09-14 21:24:49', 0),
(606, 65, '1', 'view', '2025-09-14 23:04:49', 0),
(607, 66, '1', 'view', '2025-09-15 00:44:49', 0),
(608, 67, '1', 'view', '2025-09-15 02:24:49', 0),
(609, 68, '1', 'view', '2025-09-15 04:04:49', 0),
(610, 69, '1', 'view', '2025-09-15 05:44:49', 0),
(611, 59, '1', 'view', '2025-09-15 07:24:49', 1),
(612, 60, '1', 'view', '2025-09-15 09:04:49', 1),
(613, 61, '1', 'view', '2025-09-14 18:14:49', 1),
(614, 62, '1', 'view', '2025-09-14 19:54:49', 1),
(615, 63, '1', 'view', '2025-09-14 21:34:49', 0),
(616, 64, '1', 'view', '2025-09-14 23:14:49', 0),
(617, 65, '1', 'view', '2025-09-15 00:54:49', 0),
(618, 66, '1', 'view', '2025-09-15 02:34:49', 0),
(619, 67, '1', 'view', '2025-09-15 04:14:49', 0),
(620, 68, '1', 'view', '2025-09-15 05:54:49', 0),
(621, 69, '1', 'view', '2025-09-15 07:34:49', 0),
(622, 59, '1', 'view', '2025-09-15 09:14:49', 1),
(623, 60, '1', 'view', '2025-09-14 18:24:49', 1),
(624, 61, '1', 'view', '2025-09-14 20:04:49', 1),
(625, 62, '1', 'view', '2025-09-14 21:44:49', 1),
(626, 63, '1', 'view', '2025-09-14 23:24:49', 0),
(627, 64, '1', 'view', '2025-09-15 01:04:49', 0),
(628, 65, '1', 'view', '2025-09-15 02:44:49', 0),
(629, 66, '1', 'view', '2025-09-15 04:24:49', 0),
(630, 67, '1', 'view', '2025-09-15 06:04:49', 0),
(631, 68, '1', 'view', '2025-09-15 07:44:49', 0),
(632, 69, '1', 'view', '2025-09-15 09:24:49', 0),
(633, 59, '1', 'view', '2025-09-14 18:34:49', 1),
(634, 60, '1', 'view', '2025-09-14 20:14:49', 1),
(635, 61, '1', 'view', '2025-09-14 21:54:49', 1),
(636, 62, '1', 'view', '2025-09-14 23:34:49', 1),
(637, 63, '1', 'view', '2025-09-15 01:14:49', 0),
(638, 64, '1', 'view', '2025-09-15 02:54:49', 0),
(639, 65, '1', 'view', '2025-09-15 04:34:49', 0),
(640, 66, '1', 'view', '2025-09-15 06:14:49', 0),
(641, 67, '1', 'view', '2025-09-15 07:54:49', 0),
(642, 68, '1', 'view', '2025-09-15 09:34:49', 0),
(643, 69, '1', 'view', '2025-09-14 18:44:49', 0),
(644, 59, '1', 'view', '2025-09-14 20:24:49', 1),
(645, 60, '1', 'view', '2025-09-14 22:04:49', 1),
(646, 61, '1', 'view', '2025-09-14 23:44:49', 1),
(647, 62, '1', 'view', '2025-09-15 01:24:49', 1),
(648, 63, '1', 'view', '2025-09-15 03:04:49', 0),
(649, 64, '1', 'view', '2025-09-15 04:44:49', 0),
(650, 65, '1', 'view', '2025-09-15 06:24:49', 0),
(651, 66, '1', 'view', '2025-09-15 08:04:49', 0),
(652, 67, '1', 'view', '2025-09-15 09:44:49', 0),
(653, 68, '1', 'view', '2025-09-14 18:54:49', 0),
(654, 69, '1', 'view', '2025-09-14 20:34:49', 0),
(655, 59, '1', 'view', '2025-09-14 22:14:49', 1),
(656, 60, '1', 'view', '2025-09-14 23:54:49', 1),
(657, 61, '1', 'view', '2025-09-15 01:34:49', 1),
(658, 62, '1', 'view', '2025-09-15 03:14:49', 1),
(659, 63, '1', 'view', '2025-09-15 04:54:49', 0),
(660, 64, '1', 'view', '2025-09-15 06:34:49', 0),
(661, 65, '1', 'view', '2025-09-15 08:14:49', 0),
(662, 66, '1', 'view', '2025-09-15 09:54:49', 0),
(663, 67, '1', 'view', '2025-09-14 19:04:49', 0),
(664, 68, '1', 'view', '2025-09-14 20:44:49', 0),
(665, 69, '1', 'view', '2025-09-14 22:24:49', 0),
(666, 59, '1', 'view', '2025-09-15 00:04:49', 1),
(667, 60, '1', 'view', '2025-09-15 01:44:49', 1),
(668, 61, '1', 'view', '2025-09-15 03:24:49', 1),
(669, 62, '1', 'view', '2025-09-15 05:04:49', 1),
(670, 63, '1', 'view', '2025-09-15 06:44:49', 0),
(671, 64, '1', 'view', '2025-09-15 08:24:49', 0),
(672, 65, '1', 'view', '2025-09-15 10:04:49', 0),
(673, 66, '1', 'view', '2025-09-14 19:14:49', 0),
(674, 67, '1', 'view', '2025-09-14 20:54:49', 0),
(675, 68, '1', 'view', '2025-09-14 22:34:49', 0),
(676, 69, '1', 'view', '2025-09-15 00:14:49', 0),
(677, 59, '1', 'view', '2025-09-15 01:54:49', 1),
(678, 60, '1', 'view', '2025-09-15 03:34:49', 1),
(679, 61, '1', 'view', '2025-09-15 05:14:49', 1),
(680, 62, '1', 'view', '2025-09-15 06:54:49', 1),
(681, 63, '1', 'view', '2025-09-15 08:34:49', 0),
(682, 64, '1', 'view', '2025-09-15 10:14:49', 0),
(683, 65, '1', 'view', '2025-09-14 17:45:49', 0),
(684, 66, '1', 'view', '2025-09-14 19:25:49', 0),
(685, 67, '1', 'view', '2025-09-14 21:05:49', 0),
(686, 68, '1', 'view', '2025-09-14 22:45:49', 0),
(687, 69, '1', 'view', '2025-09-15 00:25:49', 0),
(688, 59, '1', 'view', '2025-09-15 02:05:49', 1),
(689, 60, '1', 'view', '2025-09-15 03:45:49', 1),
(690, 61, '1', 'view', '2025-09-15 05:25:49', 1),
(691, 62, '1', 'view', '2025-09-15 07:05:49', 1),
(692, 63, '1', 'view', '2025-09-15 08:45:49', 0),
(693, 64, '1', 'view', '2025-09-14 17:55:49', 0),
(694, 65, '1', 'view', '2025-09-14 19:35:49', 0),
(695, 66, '1', 'view', '2025-09-14 21:15:49', 0),
(696, 67, '1', 'view', '2025-09-14 22:55:49', 0),
(697, 68, '1', 'view', '2025-09-15 00:35:49', 0),
(698, 69, '1', 'view', '2025-09-15 02:15:49', 0),
(699, 59, '1', 'view', '2025-09-15 03:55:49', 1),
(700, 60, '1', 'view', '2025-09-15 05:35:49', 1),
(701, 61, '1', 'view', '2025-09-15 07:15:49', 1),
(702, 62, '1', 'view', '2025-09-15 08:55:49', 1),
(703, 63, '1', 'view', '2025-09-14 18:05:49', 0),
(704, 64, '1', 'view', '2025-09-14 19:45:49', 0),
(705, 65, '1', 'view', '2025-09-14 21:25:49', 0),
(706, 66, '1', 'view', '2025-09-14 23:05:49', 0),
(707, 67, '1', 'view', '2025-09-15 00:45:49', 0),
(708, 68, '1', 'view', '2025-09-15 02:25:49', 0),
(709, 69, '1', 'view', '2025-09-15 04:05:49', 0),
(710, 59, '1', 'view', '2025-09-15 05:45:49', 1),
(711, 60, '1', 'view', '2025-09-15 07:25:49', 1),
(712, 61, '1', 'view', '2025-09-15 09:05:49', 1),
(713, 62, '1', 'view', '2025-09-14 18:15:49', 1),
(714, 63, '1', 'view', '2025-09-14 19:55:49', 0),
(715, 64, '1', 'view', '2025-09-14 21:35:49', 0),
(716, 65, '1', 'view', '2025-09-14 23:15:49', 0),
(717, 66, '1', 'view', '2025-09-15 00:55:49', 0),
(718, 67, '1', 'view', '2025-09-15 02:35:49', 0),
(719, 68, '1', 'view', '2025-09-15 04:15:49', 0),
(720, 69, '1', 'view', '2025-09-15 05:55:49', 0),
(721, 59, '1', 'view', '2025-09-15 07:35:49', 1),
(722, 60, '1', 'view', '2025-09-15 09:15:49', 1),
(723, 61, '1', 'view', '2025-09-14 18:25:49', 1),
(724, 62, '1', 'view', '2025-09-14 20:05:49', 1),
(725, 63, '1', 'view', '2025-09-14 21:45:49', 0),
(726, 64, '1', 'view', '2025-09-14 23:25:49', 0),
(727, 65, '1', 'view', '2025-09-15 01:05:49', 0),
(728, 66, '1', 'view', '2025-09-15 02:45:49', 0),
(729, 67, '1', 'view', '2025-09-15 04:25:49', 0),
(730, 68, '1', 'view', '2025-09-15 06:05:49', 0),
(731, 69, '1', 'view', '2025-09-15 07:45:49', 0),
(732, 59, '1', 'view', '2025-09-15 09:25:49', 1),
(733, 60, '1', 'view', '2025-09-14 18:35:49', 1),
(734, 61, '1', 'view', '2025-09-14 20:15:49', 1),
(735, 62, '1', 'view', '2025-09-14 21:55:49', 1),
(736, 63, '1', 'view', '2025-09-14 23:35:49', 0),
(737, 64, '1', 'view', '2025-09-15 01:15:49', 0),
(738, 65, '1', 'view', '2025-09-15 02:55:49', 0),
(739, 66, '1', 'view', '2025-09-15 04:35:49', 0),
(740, 67, '1', 'view', '2025-09-15 06:15:49', 0),
(741, 68, '1', 'view', '2025-09-15 07:55:49', 0),
(742, 69, '1', 'view', '2025-09-15 09:35:49', 0),
(743, 59, '1', 'view', '2025-09-14 18:45:49', 1),
(744, 60, '1', 'view', '2025-09-14 20:25:49', 1),
(745, 61, '1', 'view', '2025-09-14 22:05:49', 1),
(746, 62, '1', 'view', '2025-09-14 23:45:49', 1),
(747, 63, '1', 'view', '2025-09-15 01:25:49', 0),
(748, 64, '1', 'view', '2025-09-15 03:05:49', 0),
(749, 65, '1', 'view', '2025-09-15 04:45:49', 0),
(750, 66, '1', 'view', '2025-09-15 06:25:49', 0),
(751, 67, '1', 'view', '2025-09-15 08:05:49', 0),
(752, 68, '1', 'view', '2025-09-15 09:45:49', 0),
(753, 69, '1', 'view', '2025-09-14 18:55:49', 0),
(754, 59, '1', 'view', '2025-09-14 20:35:49', 1),
(755, 60, '1', 'view', '2025-09-14 22:15:49', 1),
(756, 61, '1', 'view', '2025-09-14 23:55:49', 1),
(757, 62, '1', 'view', '2025-09-15 01:35:49', 1),
(758, 63, '1', 'view', '2025-09-15 03:15:49', 0),
(759, 64, '1', 'view', '2025-09-15 04:55:49', 0),
(760, 65, '1', 'view', '2025-09-15 06:35:49', 0),
(761, 66, '1', 'view', '2025-09-15 08:15:49', 0),
(762, 67, '1', 'view', '2025-09-15 09:55:49', 0),
(763, 68, '1', 'view', '2025-09-14 19:05:49', 0),
(764, 69, '1', 'view', '2025-09-14 20:45:49', 0),
(765, 59, '1', 'view', '2025-09-14 22:25:49', 1),
(766, 60, '1', 'view', '2025-09-15 00:05:49', 1),
(767, 61, '1', 'view', '2025-09-15 01:45:49', 1),
(768, 62, '1', 'view', '2025-09-15 03:25:49', 1),
(769, 63, '1', 'view', '2025-09-15 05:05:49', 0),
(770, 64, '1', 'view', '2025-09-15 06:45:49', 0),
(771, 65, '1', 'view', '2025-09-15 08:25:49', 0),
(772, 66, '1', 'view', '2025-09-15 10:05:49', 0),
(773, 67, '1', 'view', '2025-09-14 19:15:49', 0),
(774, 68, '1', 'view', '2025-09-14 20:55:49', 0),
(775, 69, '1', 'view', '2025-09-14 22:35:49', 0),
(776, 59, '1', 'view', '2025-09-15 00:15:49', 1),
(777, 60, '1', 'view', '2025-09-15 01:55:49', 1),
(778, 61, '1', 'view', '2025-09-15 03:35:49', 1),
(779, 62, '1', 'view', '2025-09-15 05:15:49', 1),
(780, 63, '1', 'view', '2025-09-15 06:55:49', 0),
(781, 64, '1', 'view', '2025-09-15 08:35:49', 0),
(782, 65, '1', 'view', '2025-09-15 10:15:49', 0),
(783, 66, '1', 'view', '2025-09-14 17:46:49', 0),
(784, 67, '1', 'view', '2025-09-14 19:26:49', 0),
(785, 68, '1', 'view', '2025-09-14 21:06:49', 0),
(786, 69, '1', 'view', '2025-09-14 22:46:49', 0),
(787, 59, '1', 'view', '2025-09-15 00:26:49', 1),
(788, 60, '1', 'view', '2025-09-15 02:06:49', 1),
(789, 61, '1', 'view', '2025-09-15 03:46:49', 1),
(790, 62, '1', 'view', '2025-09-15 05:26:49', 1),
(791, 63, '1', 'view', '2025-09-15 07:06:49', 0),
(792, 64, '1', 'view', '2025-09-15 08:46:49', 0),
(793, 65, '1', 'view', '2025-09-14 17:56:49', 0),
(794, 66, '1', 'view', '2025-09-14 19:36:49', 0),
(795, 67, '1', 'view', '2025-09-14 21:16:49', 0),
(796, 68, '1', 'view', '2025-09-14 22:56:49', 0),
(797, 69, '1', 'view', '2025-09-15 00:36:49', 0),
(798, 59, '1', 'view', '2025-09-15 02:16:49', 1),
(799, 60, '1', 'view', '2025-09-15 03:56:49', 1),
(800, 61, '1', 'view', '2025-09-15 05:36:49', 1),
(801, 62, '1', 'view', '2025-09-15 07:16:49', 1),
(802, 63, '1', 'view', '2025-09-15 08:56:49', 0),
(803, 64, '1', 'view', '2025-09-14 18:06:49', 0),
(804, 65, '1', 'view', '2025-09-14 19:46:49', 0),
(805, 66, '1', 'view', '2025-09-14 21:26:49', 0),
(806, 67, '1', 'view', '2025-09-14 23:06:49', 0),
(807, 68, '1', 'view', '2025-09-15 00:46:49', 0),
(808, 69, '1', 'view', '2025-09-15 02:26:49', 0),
(809, 59, '1', 'view', '2025-09-15 04:06:49', 1),
(810, 60, '1', 'view', '2025-09-15 05:46:49', 1),
(811, 61, '1', 'view', '2025-09-15 07:26:49', 1),
(812, 62, '1', 'view', '2025-09-15 09:06:49', 1),
(813, 63, '1', 'view', '2025-09-14 18:16:49', 0),
(814, 64, '1', 'view', '2025-09-14 19:56:49', 0),
(815, 65, '1', 'view', '2025-09-14 21:36:49', 0),
(816, 66, '1', 'view', '2025-09-14 23:16:49', 0),
(817, 67, '1', 'view', '2025-09-15 00:56:49', 0),
(818, 68, '1', 'view', '2025-09-15 02:36:49', 0),
(819, 69, '1', 'view', '2025-09-15 04:16:49', 0),
(820, 59, '1', 'view', '2025-09-15 05:56:49', 1),
(821, 60, '1', 'view', '2025-09-15 07:36:49', 1),
(822, 61, '1', 'view', '2025-09-15 09:16:49', 1),
(823, 62, '1', 'view', '2025-09-14 18:26:49', 1),
(824, 63, '1', 'view', '2025-09-14 20:06:49', 0),
(825, 64, '1', 'view', '2025-09-14 21:46:49', 0),
(826, 65, '1', 'view', '2025-09-14 23:26:49', 0),
(827, 66, '1', 'view', '2025-09-15 01:06:49', 0),
(828, 67, '1', 'view', '2025-09-15 02:46:49', 0),
(829, 68, '1', 'view', '2025-09-15 04:26:49', 0),
(830, 69, '1', 'view', '2025-09-15 06:06:49', 0),
(831, 59, '1', 'view', '2025-09-15 07:46:49', 1),
(832, 60, '1', 'view', '2025-09-15 09:26:49', 1),
(833, 61, '1', 'view', '2025-09-14 18:36:49', 1),
(834, 62, '1', 'view', '2025-09-14 20:16:49', 1),
(835, 63, '1', 'view', '2025-09-14 21:56:49', 0),
(836, 64, '1', 'view', '2025-09-14 23:36:49', 0),
(837, 65, '1', 'view', '2025-09-15 01:16:49', 0),
(838, 66, '1', 'view', '2025-09-15 02:56:49', 0),
(839, 67, '1', 'view', '2025-09-15 04:36:49', 0),
(840, 68, '1', 'view', '2025-09-15 06:16:49', 0),
(841, 69, '1', 'view', '2025-09-15 07:56:49', 0),
(842, 59, '1', 'view', '2025-09-15 09:36:49', 1),
(843, 60, '1', 'view', '2025-09-14 18:46:49', 1),
(844, 61, '1', 'view', '2025-09-14 20:26:49', 1),
(845, 62, '1', 'view', '2025-09-14 22:06:49', 1),
(846, 63, '1', 'view', '2025-09-14 23:46:49', 0),
(847, 64, '1', 'view', '2025-09-15 01:26:49', 0),
(848, 65, '1', 'view', '2025-09-15 03:06:49', 0),
(849, 66, '1', 'view', '2025-09-15 04:46:49', 0),
(850, 67, '1', 'view', '2025-09-15 06:26:49', 0),
(851, 68, '1', 'view', '2025-09-15 08:06:49', 0),
(852, 69, '1', 'view', '2025-09-15 09:46:49', 0),
(853, 59, '1', 'view', '2025-09-14 18:56:49', 1),
(854, 60, '1', 'view', '2025-09-14 20:36:49', 1),
(855, 61, '1', 'view', '2025-09-14 22:16:49', 1),
(856, 62, '1', 'view', '2025-09-14 23:56:49', 1),
(857, 63, '1', 'view', '2025-09-15 01:36:49', 0),
(858, 64, '1', 'view', '2025-09-15 03:16:49', 0),
(859, 65, '1', 'view', '2025-09-15 04:56:49', 0),
(860, 66, '1', 'view', '2025-09-15 06:36:49', 0),
(861, 67, '1', 'view', '2025-09-15 08:16:49', 0),
(862, 68, '1', 'view', '2025-09-15 09:56:49', 0),
(863, 69, '1', 'view', '2025-09-14 19:06:49', 0),
(864, 59, '1', 'view', '2025-09-14 20:46:49', 1),
(865, 60, '1', 'view', '2025-09-14 22:26:49', 1),
(866, 61, '1', 'view', '2025-09-15 00:06:49', 1),
(867, 62, '1', 'view', '2025-09-15 01:46:49', 1),
(868, 63, '1', 'view', '2025-09-15 03:26:49', 0),
(869, 64, '1', 'view', '2025-09-15 05:06:49', 0),
(870, 65, '1', 'view', '2025-09-15 06:46:49', 0),
(871, 66, '1', 'view', '2025-09-15 08:26:49', 0),
(872, 67, '1', 'view', '2025-09-15 10:06:49', 0),
(873, 68, '1', 'view', '2025-09-14 19:16:49', 0),
(874, 69, '1', 'view', '2025-09-14 20:56:49', 0),
(875, 59, '1', 'view', '2025-09-14 22:36:49', 1),
(876, 60, '1', 'view', '2025-09-15 00:16:49', 1),
(877, 61, '1', 'view', '2025-09-15 01:56:49', 1),
(878, 62, '1', 'view', '2025-09-15 03:36:49', 1),
(879, 63, '1', 'view', '2025-09-15 05:16:49', 0),
(880, 64, '1', 'view', '2025-09-15 06:56:49', 0),
(881, 65, '1', 'view', '2025-09-15 08:36:49', 0),
(882, 66, '1', 'view', '2025-09-15 10:16:49', 0),
(883, 67, '1', 'view', '2025-09-14 17:47:49', 0),
(884, 68, '1', 'view', '2025-09-14 19:27:49', 0),
(885, 69, '1', 'view', '2025-09-14 21:07:49', 0),
(886, 59, '1', 'view', '2025-09-14 22:47:49', 1),
(887, 60, '1', 'view', '2025-09-15 00:27:49', 1),
(888, 61, '1', 'view', '2025-09-15 02:07:49', 1),
(889, 62, '1', 'view', '2025-09-15 03:47:49', 1),
(890, 63, '1', 'view', '2025-09-15 05:27:49', 0),
(891, 64, '1', 'view', '2025-09-15 07:07:49', 0),
(892, 65, '1', 'view', '2025-09-15 08:47:49', 0),
(893, 66, '1', 'view', '2025-09-14 17:57:49', 0),
(894, 67, '1', 'view', '2025-09-14 19:37:49', 0),
(895, 68, '1', 'view', '2025-09-14 21:17:49', 0),
(896, 69, '1', 'view', '2025-09-14 22:57:49', 0),
(897, 59, '1', 'view', '2025-09-15 00:37:49', 1),
(898, 60, '1', 'view', '2025-09-15 02:17:49', 1),
(899, 61, '1', 'view', '2025-09-15 03:57:49', 1),
(900, 62, '1', 'view', '2025-09-15 05:37:49', 1),
(901, 63, '1', 'view', '2025-09-15 07:17:49', 0),
(902, 64, '1', 'view', '2025-09-15 08:57:49', 0),
(903, 65, '1', 'view', '2025-09-14 18:07:49', 0),
(904, 66, '1', 'view', '2025-09-14 19:47:49', 0),
(905, 67, '1', 'view', '2025-09-14 21:27:49', 0),
(906, 68, '1', 'view', '2025-09-14 23:07:49', 0),
(907, 69, '1', 'view', '2025-09-15 00:47:49', 0),
(908, 59, '1', 'view', '2025-09-15 02:27:49', 1),
(909, 60, '1', 'view', '2025-09-15 04:07:49', 1),
(910, 61, '1', 'view', '2025-09-15 05:47:49', 1),
(911, 62, '1', 'view', '2025-09-15 07:27:49', 1),
(912, 63, '1', 'view', '2025-09-15 09:07:49', 0),
(913, 64, '1', 'view', '2025-09-14 18:17:49', 0),
(914, 65, '1', 'view', '2025-09-14 19:57:49', 0),
(915, 66, '1', 'view', '2025-09-14 21:37:49', 0),
(916, 67, '1', 'view', '2025-09-14 23:17:49', 0),
(917, 68, '1', 'view', '2025-09-15 00:57:49', 0),
(918, 69, '1', 'view', '2025-09-15 02:37:49', 0),
(919, 59, '1', 'view', '2025-09-15 04:17:49', 1),
(920, 60, '1', 'view', '2025-09-15 05:57:49', 1),
(921, 61, '1', 'view', '2025-09-15 07:37:49', 1),
(922, 62, '1', 'view', '2025-09-15 09:17:49', 1),
(923, 63, '1', 'view', '2025-09-14 18:27:49', 0),
(924, 64, '1', 'view', '2025-09-14 20:07:49', 0),
(925, 65, '1', 'view', '2025-09-14 21:47:49', 0),
(926, 66, '1', 'view', '2025-09-14 23:27:49', 0),
(927, 67, '1', 'view', '2025-09-15 01:07:49', 0),
(928, 68, '1', 'view', '2025-09-15 02:47:49', 0),
(929, 69, '1', 'view', '2025-09-15 04:27:49', 0),
(930, 59, '1', 'view', '2025-09-15 06:07:49', 1),
(931, 60, '1', 'view', '2025-09-15 07:47:49', 1),
(932, 61, '1', 'view', '2025-09-15 09:27:49', 1),
(933, 62, '1', 'view', '2025-09-14 18:37:49', 1),
(934, 63, '1', 'view', '2025-09-14 20:17:49', 0),
(935, 64, '1', 'view', '2025-09-14 21:57:49', 0),
(936, 65, '1', 'view', '2025-09-14 23:37:49', 0),
(937, 66, '1', 'view', '2025-09-15 01:17:49', 0),
(938, 67, '1', 'view', '2025-09-15 02:57:49', 0),
(939, 68, '1', 'view', '2025-09-15 04:37:49', 0),
(940, 69, '1', 'view', '2025-09-15 06:17:49', 0),
(941, 59, '1', 'view', '2025-09-15 07:57:49', 1),
(942, 60, '1', 'view', '2025-09-15 09:37:49', 1),
(943, 61, '1', 'view', '2025-09-14 18:47:49', 1),
(944, 62, '1', 'view', '2025-09-14 20:27:49', 1),
(945, 63, '1', 'view', '2025-09-14 22:07:49', 0),
(946, 64, '1', 'view', '2025-09-14 23:47:49', 0),
(947, 65, '1', 'view', '2025-09-15 01:27:49', 0),
(948, 66, '1', 'view', '2025-09-15 03:07:49', 0),
(949, 67, '1', 'view', '2025-09-15 04:47:49', 0),
(950, 68, '1', 'view', '2025-09-15 06:27:49', 0),
(951, 69, '1', 'view', '2025-09-15 08:07:49', 0),
(952, 59, '1', 'view', '2025-09-15 09:47:49', 1),
(953, 60, '1', 'view', '2025-09-14 18:57:49', 1),
(954, 61, '1', 'view', '2025-09-14 20:37:49', 1),
(955, 62, '1', 'view', '2025-09-14 22:17:49', 1),
(956, 63, '1', 'view', '2025-09-14 23:57:49', 0),
(957, 64, '1', 'view', '2025-09-15 01:37:49', 0),
(958, 65, '1', 'view', '2025-09-15 03:17:49', 0),
(959, 66, '1', 'view', '2025-09-15 04:57:49', 0),
(960, 67, '1', 'view', '2025-09-15 06:37:49', 0),
(961, 68, '1', 'view', '2025-09-15 08:17:49', 0),
(962, 69, '1', 'view', '2025-09-15 09:57:49', 0),
(963, 59, '1', 'view', '2025-09-14 19:07:49', 1),
(964, 60, '1', 'view', '2025-09-14 20:47:49', 1),
(965, 61, '1', 'view', '2025-09-14 22:27:49', 1),
(966, 62, '1', 'view', '2025-09-15 00:07:49', 1),
(967, 63, '1', 'view', '2025-09-15 01:47:49', 0),
(968, 64, '1', 'view', '2025-09-15 03:27:49', 0),
(969, 65, '1', 'view', '2025-09-15 05:07:49', 0),
(970, 66, '1', 'view', '2025-09-15 06:47:49', 0),
(971, 67, '1', 'view', '2025-09-15 08:27:49', 0),
(972, 68, '1', 'view', '2025-09-15 10:07:49', 0),
(973, 69, '1', 'view', '2025-09-14 19:17:49', 0),
(974, 59, '1', 'view', '2025-09-14 20:57:49', 1),
(975, 60, '1', 'view', '2025-09-14 22:37:49', 1),
(976, 61, '1', 'view', '2025-09-15 00:17:49', 1),
(977, 62, '1', 'view', '2025-09-15 01:57:49', 1),
(978, 63, '1', 'view', '2025-09-15 03:37:49', 0),
(979, 64, '1', 'view', '2025-09-15 05:17:49', 0),
(980, 65, '1', 'view', '2025-09-15 06:57:49', 0),
(981, 66, '1', 'view', '2025-09-15 08:37:49', 0),
(982, 67, '1', 'view', '2025-09-15 10:17:49', 0),
(983, 68, '1', 'view', '2025-09-14 17:48:49', 0),
(984, 69, '1', 'view', '2025-09-14 19:28:49', 0),
(985, 59, '1', 'view', '2025-09-14 21:08:49', 1),
(986, 60, '1', 'view', '2025-09-14 22:48:49', 1),
(987, 61, '1', 'view', '2025-09-15 00:28:49', 1),
(988, 62, '1', 'view', '2025-09-15 02:08:49', 1),
(989, 63, '1', 'view', '2025-09-15 03:48:49', 0),
(990, 64, '1', 'view', '2025-09-15 05:28:49', 0),
(991, 65, '1', 'view', '2025-09-15 07:08:49', 0),
(992, 66, '1', 'view', '2025-09-15 08:48:49', 0),
(993, 67, '1', 'view', '2025-09-14 17:58:49', 0),
(994, 68, '1', 'view', '2025-09-14 19:38:49', 0),
(995, 69, '1', 'view', '2025-09-14 21:18:49', 0),
(996, 59, '1', 'view', '2025-09-14 22:58:49', 1),
(997, 60, '1', 'view', '2025-09-15 00:38:49', 1),
(998, 61, '1', 'view', '2025-09-15 02:18:49', 1),
(999, 62, '1', 'view', '2025-09-15 03:58:49', 1),
(1000, 63, '1', 'view', '2025-09-15 05:38:49', 0),
(1001, 64, '1', 'view', '2025-09-15 07:18:49', 0),
(1002, 65, '1', 'view', '2025-09-15 08:58:49', 0),
(1003, 66, '1', 'view', '2025-09-14 18:08:49', 0),
(1004, 67, '1', 'view', '2025-09-14 19:48:49', 0),
(1005, 68, '1', 'view', '2025-09-14 21:28:49', 0),
(1006, 69, '1', 'view', '2025-09-14 23:08:49', 0),
(1007, 59, '1', 'view', '2025-09-15 00:48:49', 1),
(1008, 60, '1', 'view', '2025-09-15 02:28:49', 1),
(1009, 61, '1', 'view', '2025-09-15 04:08:49', 1),
(1010, 62, '1', 'view', '2025-09-15 05:48:49', 1),
(1011, 63, '1', 'view', '2025-09-15 07:28:49', 0),
(1012, 64, '1', 'view', '2025-09-15 09:08:49', 0),
(1013, 65, '1', 'view', '2025-09-14 18:18:49', 0),
(1014, 66, '1', 'view', '2025-09-14 19:58:49', 0),
(1015, 67, '1', 'view', '2025-09-14 21:38:49', 0),
(1016, 68, '1', 'view', '2025-09-14 23:18:49', 0),
(1017, 69, '1', 'view', '2025-09-15 00:58:49', 0),
(1018, 59, '1', 'view', '2025-09-15 02:38:49', 1),
(1019, 60, '1', 'view', '2025-09-15 04:18:49', 1),
(1020, 61, '1', 'view', '2025-09-15 05:58:49', 1),
(1021, 62, '1', 'view', '2025-09-15 07:38:49', 1),
(1022, 63, '1', 'view', '2025-09-15 09:18:49', 0),
(1023, 64, '1', 'view', '2025-09-14 18:28:49', 0),
(1024, 65, '1', 'view', '2025-09-14 20:08:49', 0),
(1025, 66, '1', 'view', '2025-09-14 21:48:49', 0),
(1026, 67, '1', 'view', '2025-09-14 23:28:49', 0),
(1027, 68, '1', 'view', '2025-09-15 01:08:49', 0),
(1028, 69, '1', 'view', '2025-09-15 02:48:49', 0),
(1029, 59, '1', 'view', '2025-09-15 04:28:49', 1),
(1030, 60, '1', 'view', '2025-09-15 06:08:49', 1),
(1031, 61, '1', 'view', '2025-09-15 07:48:49', 1),
(1032, 62, '1', 'view', '2025-09-15 09:28:49', 1),
(1033, 63, '1', 'view', '2025-09-14 18:38:49', 0),
(1034, 64, '1', 'view', '2025-09-14 20:18:49', 0),
(1035, 65, '1', 'view', '2025-09-14 21:58:49', 0),
(1036, 66, '1', 'view', '2025-09-14 23:38:49', 0),
(1037, 67, '1', 'view', '2025-09-15 01:18:49', 0),
(1038, 68, '1', 'view', '2025-09-15 02:58:49', 0),
(1039, 69, '1', 'view', '2025-09-15 04:38:49', 0),
(1040, 59, '1', 'view', '2025-09-15 06:18:49', 1),
(1041, 60, '1', 'view', '2025-09-15 07:58:49', 1),
(1042, 61, '1', 'view', '2025-09-15 09:38:49', 1),
(1043, 62, '1', 'view', '2025-09-14 18:48:49', 1),
(1044, 63, '1', 'view', '2025-09-14 20:28:49', 0),
(1045, 64, '1', 'view', '2025-09-14 22:08:49', 0),
(1046, 65, '1', 'view', '2025-09-14 23:48:49', 0),
(1047, 66, '1', 'view', '2025-09-15 01:28:49', 0);
INSERT INTO `engagements` (`id`, `content_id`, `user_id`, `type`, `created_at`, `claimed`) VALUES
(1048, 67, '1', 'view', '2025-09-15 03:08:49', 0),
(1049, 68, '1', 'view', '2025-09-15 04:48:49', 0),
(1050, 69, '1', 'view', '2025-09-15 06:28:49', 0),
(1051, 59, '1', 'view', '2025-09-15 08:08:49', 1),
(1052, 60, '1', 'view', '2025-09-15 09:48:49', 1),
(1053, 61, '1', 'view', '2025-09-14 18:58:49', 1),
(1054, 62, '1', 'view', '2025-09-14 20:38:49', 1),
(1055, 63, '1', 'view', '2025-09-14 22:18:49', 0),
(1056, 64, '1', 'view', '2025-09-14 23:58:49', 0),
(1057, 65, '1', 'view', '2025-09-15 01:38:49', 0),
(1058, 66, '1', 'view', '2025-09-15 03:18:49', 0),
(1059, 67, '1', 'view', '2025-09-15 04:58:49', 0),
(1060, 68, '1', 'view', '2025-09-15 06:38:49', 0),
(1061, 69, '1', 'view', '2025-09-15 08:18:49', 0),
(1062, 59, '1', 'view', '2025-09-15 09:58:49', 1),
(1063, 60, '1', 'view', '2025-09-14 19:08:49', 1),
(1064, 61, '1', 'view', '2025-09-14 20:48:49', 1),
(1065, 62, '1', 'view', '2025-09-14 22:28:49', 1),
(1066, 63, '1', 'view', '2025-09-15 00:08:49', 0),
(1067, 64, '1', 'view', '2025-09-15 01:48:49', 0),
(1068, 65, '1', 'view', '2025-09-15 03:28:49', 0),
(1069, 66, '1', 'view', '2025-09-15 05:08:49', 0),
(1070, 67, '1', 'view', '2025-09-15 06:48:49', 0),
(1071, 68, '1', 'view', '2025-09-15 08:28:49', 0),
(1072, 69, '1', 'view', '2025-09-15 10:08:49', 0),
(1073, 59, '1', 'view', '2025-09-14 19:18:49', 1),
(1074, 60, '1', 'view', '2025-09-14 20:58:49', 1),
(1075, 61, '1', 'view', '2025-09-14 22:38:49', 1),
(1076, 62, '1', 'view', '2025-09-15 00:18:49', 1),
(1077, 63, '1', 'view', '2025-09-15 01:58:49', 0),
(1078, 64, '1', 'view', '2025-09-15 03:38:49', 0),
(1079, 65, '1', 'view', '2025-09-15 05:18:49', 0),
(1080, 66, '1', 'view', '2025-09-15 06:58:49', 0),
(1081, 67, '1', 'view', '2025-09-15 08:38:49', 0),
(1082, 68, '1', 'view', '2025-09-15 10:18:49', 0),
(1083, 69, '1', 'view', '2025-09-14 17:49:49', 0),
(1084, 59, '1', 'view', '2025-09-14 19:29:49', 1),
(1085, 60, '1', 'view', '2025-09-14 21:09:49', 1),
(1086, 61, '1', 'view', '2025-09-14 22:49:49', 1),
(1087, 62, '1', 'view', '2025-09-15 00:29:49', 1),
(1088, 63, '1', 'view', '2025-09-15 02:09:49', 0),
(1089, 64, '1', 'view', '2025-09-15 03:49:49', 0),
(1090, 65, '1', 'view', '2025-09-15 05:29:49', 0),
(1091, 66, '1', 'view', '2025-09-15 07:09:49', 0),
(1092, 67, '1', 'view', '2025-09-15 08:49:49', 0),
(1093, 68, '1', 'view', '2025-09-14 17:59:49', 0),
(1094, 69, '1', 'view', '2025-09-14 19:39:49', 0),
(1095, 59, '1', 'view', '2025-09-14 21:19:49', 1),
(1096, 60, '1', 'view', '2025-09-14 22:59:49', 1),
(1097, 61, '1', 'view', '2025-09-15 00:39:49', 1),
(1098, 62, '1', 'view', '2025-09-15 02:19:49', 1),
(1099, 63, '1', 'view', '2025-09-15 03:59:49', 0),
(1100, 64, '1', 'view', '2025-09-15 05:39:49', 0),
(1101, 65, '1', 'view', '2025-09-15 07:19:49', 0),
(1102, 66, '1', 'view', '2025-09-15 08:59:49', 0),
(1103, 67, '1', 'view', '2025-09-14 18:09:49', 0),
(1104, 68, '1', 'view', '2025-09-14 19:49:49', 0),
(1105, 69, '1', 'view', '2025-09-14 21:29:49', 0),
(1106, 59, '1', 'view', '2025-09-14 23:09:49', 1),
(1107, 60, '1', 'view', '2025-09-15 00:49:49', 1),
(1108, 61, '1', 'view', '2025-09-15 02:29:49', 1),
(1109, 62, '1', 'view', '2025-09-15 04:09:49', 1),
(1110, 63, '1', 'view', '2025-09-15 05:49:49', 0),
(1111, 64, '1', 'view', '2025-09-15 07:29:49', 0),
(1112, 65, '1', 'view', '2025-09-15 09:09:49', 0),
(1113, 66, '1', 'view', '2025-09-14 18:19:49', 0),
(1114, 67, '1', 'view', '2025-09-14 19:59:49', 0),
(1115, 68, '1', 'view', '2025-09-14 21:39:49', 0),
(1116, 69, '1', 'view', '2025-09-14 23:19:49', 0),
(1117, 59, '1', 'view', '2025-09-15 00:59:49', 1),
(1118, 60, '1', 'view', '2025-09-15 02:39:49', 1),
(1119, 61, '1', 'view', '2025-09-15 04:19:49', 1),
(1120, 62, '1', 'view', '2025-09-15 05:59:49', 1),
(1121, 63, '1', 'view', '2025-09-15 07:39:49', 0),
(1122, 64, '1', 'view', '2025-09-15 09:19:49', 0),
(1123, 65, '1', 'view', '2025-09-14 18:29:49', 0),
(1124, 66, '1', 'view', '2025-09-14 20:09:49', 0),
(1125, 67, '1', 'view', '2025-09-14 21:49:49', 0),
(1126, 68, '1', 'view', '2025-09-14 23:29:49', 0),
(1127, 69, '1', 'view', '2025-09-15 01:09:49', 0),
(1128, 59, '1', 'view', '2025-09-15 02:49:49', 1),
(1129, 60, '1', 'view', '2025-09-15 04:29:49', 1),
(1130, 61, '1', 'view', '2025-09-15 06:09:49', 1),
(1131, 62, '1', 'view', '2025-09-15 07:49:49', 1),
(1132, 63, '1', 'view', '2025-09-15 09:29:49', 0),
(1133, 64, '1', 'view', '2025-09-14 18:39:49', 0),
(1134, 65, '1', 'view', '2025-09-14 20:19:49', 0),
(1135, 66, '1', 'view', '2025-09-14 21:59:49', 0),
(1136, 67, '1', 'view', '2025-09-14 23:39:49', 0),
(1137, 68, '1', 'view', '2025-09-15 01:19:49', 0),
(1138, 69, '1', 'view', '2025-09-15 02:59:49', 0),
(1139, 59, '1', 'view', '2025-09-15 04:39:49', 1),
(1140, 60, '1', 'view', '2025-09-15 06:19:49', 1),
(1141, 61, '1', 'view', '2025-09-15 07:59:49', 1),
(1142, 62, '1', 'view', '2025-09-15 09:39:49', 1),
(1143, 63, '1', 'view', '2025-09-14 18:49:49', 0),
(1144, 64, '1', 'view', '2025-09-14 20:29:49', 0),
(1145, 65, '1', 'view', '2025-09-14 22:09:49', 0),
(1146, 66, '1', 'view', '2025-09-14 23:49:49', 0),
(1147, 67, '1', 'view', '2025-09-15 01:29:49', 0),
(1148, 68, '1', 'view', '2025-09-15 03:09:49', 0),
(1149, 69, '1', 'view', '2025-09-15 04:49:49', 0),
(1150, 59, '1', 'view', '2025-09-15 06:29:49', 1),
(1151, 60, '1', 'view', '2025-09-15 08:09:49', 1),
(1152, 61, '1', 'view', '2025-09-15 09:49:49', 1),
(1153, 62, '1', 'view', '2025-09-14 18:59:49', 1),
(1154, 63, '1', 'view', '2025-09-14 20:39:49', 0),
(1155, 64, '1', 'view', '2025-09-14 22:19:49', 0),
(1156, 65, '1', 'view', '2025-09-14 23:59:49', 0),
(1157, 66, '1', 'view', '2025-09-15 01:39:49', 0),
(1158, 67, '1', 'view', '2025-09-15 03:19:49', 0),
(1159, 68, '1', 'view', '2025-09-15 04:59:49', 0),
(1160, 69, '1', 'view', '2025-09-15 06:39:49', 0),
(1161, 59, '1', 'view', '2025-09-15 08:19:49', 1),
(1162, 60, '1', 'view', '2025-09-15 09:59:49', 1),
(1163, 61, '1', 'view', '2025-09-14 19:09:49', 1),
(1164, 62, '1', 'view', '2025-09-14 20:49:49', 1),
(1165, 63, '1', 'view', '2025-09-14 22:29:49', 0),
(1166, 64, '1', 'view', '2025-09-15 00:09:49', 0),
(1167, 65, '1', 'view', '2025-09-15 01:49:49', 0),
(1168, 66, '1', 'view', '2025-09-15 03:29:49', 0),
(1169, 67, '1', 'view', '2025-09-15 05:09:49', 0),
(1170, 68, '1', 'view', '2025-09-15 06:49:49', 0),
(1171, 69, '1', 'view', '2025-09-15 08:29:49', 0),
(1172, 59, '1', 'view', '2025-09-15 10:09:49', 1),
(1173, 60, '1', 'view', '2025-09-14 19:19:49', 1),
(1174, 61, '1', 'view', '2025-09-14 20:59:49', 1),
(1175, 62, '1', 'view', '2025-09-14 22:39:49', 1),
(1176, 63, '1', 'view', '2025-09-15 00:19:49', 0),
(1177, 64, '1', 'view', '2025-09-15 01:59:49', 0),
(1178, 65, '1', 'view', '2025-09-15 03:39:49', 0),
(1179, 66, '1', 'view', '2025-09-15 05:19:49', 0),
(1180, 67, '1', 'view', '2025-09-15 06:59:49', 0),
(1181, 68, '1', 'view', '2025-09-15 08:39:49', 0),
(1182, 69, '1', 'view', '2025-09-15 10:19:49', 0);

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`id`, `follower_id`, `creator_id`, `created_at`) VALUES
(80, 1, 7, '2025-09-08 21:00:35');

-- --------------------------------------------------------

--
-- Table structure for table `moderation_actions`
--

CREATE TABLE `moderation_actions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED NOT NULL,
  `action` enum('remove','restore') NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moderation_auto_actions`
--

CREATE TABLE `moderation_auto_actions` (
  `id` int(11) NOT NULL,
  `auto_approve_uploads` tinyint(1) NOT NULL DEFAULT 0,
  `auto_reject_uploads` tinyint(1) NOT NULL DEFAULT 0,
  `auto_moderate_uploads` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moderation_auto_actions`
--

INSERT INTO `moderation_auto_actions` (`id`, `auto_approve_uploads`, `auto_reject_uploads`, `auto_moderate_uploads`, `updated_by`, `updated_at`) VALUES
(1, 1, 0, 0, 5, '2025-09-07 22:55:51');

-- --------------------------------------------------------

--
-- Table structure for table `moderation_settings`
--

CREATE TABLE `moderation_settings` (
  `id` int(11) NOT NULL,
  `auto_flag_threshold` int(11) DEFAULT 5,
  `review_time_limit_hours` int(11) DEFAULT 24,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moderation_settings`
--

INSERT INTO `moderation_settings` (`id`, `auto_flag_threshold`, `review_time_limit_hours`, `updated_by`, `updated_at`) VALUES
(1, 1, 24, 5, '2025-09-09 12:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `monetization_settings`
--

CREATE TABLE `monetization_settings` (
  `id` int(11) NOT NULL,
  `payment_per_1000_views` decimal(10,2) DEFAULT 0.50,
  `min_followers_for_pay` int(11) DEFAULT 1000,
  `min_views_for_payment` int(11) DEFAULT 10000,
  `enable_monetization` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monetization_settings`
--

INSERT INTO `monetization_settings` (`id`, `payment_per_1000_views`, `min_followers_for_pay`, `min_views_for_payment`, `enable_monetization`, `updated_by`, `updated_at`) VALUES
(1, 200.00, 1, 1000, 1, 5, '2025-09-15 10:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `created_at`) VALUES
(1, 'harambineza01@gmail.com', '2025-09-02 16:16:17'),
(2, 'admin@kabaka.com', '2025-09-08 10:54:40'),
(3, 'mucyorobinson14@gmail.com', '2025-09-08 10:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `payload` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED DEFAULT NULL,
  `amount_cents` bigint(20) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USDT',
  `source` enum('tip','adjustment') NOT NULL DEFAULT 'tip',
  `tx_id` varchar(190) DEFAULT NULL,
  `status` enum('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
  `receipt_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `content_id`, `amount_cents`, `currency`, `source`, `tx_id`, `status`, `receipt_hash`, `created_at`) VALUES
(1, 7, NULL, 4250, 'USDT', '', 'TX_1757964887_7', '', NULL, '2025-09-15 21:34:47'),
(2, 7, NULL, 4250, 'USDT', '', 'TX_1757969871_7', '', NULL, '2025-09-15 22:57:51');

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` int(11) NOT NULL,
  `min_withdrawal_amount` decimal(10,2) DEFAULT 50.00,
  `platform_fee_percent` decimal(5,2) DEFAULT 10.00,
  `processing_fee` decimal(10,2) DEFAULT 2.50,
  `auto_payouts` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_settings`
--

INSERT INTO `payment_settings` (`id`, `min_withdrawal_amount`, `platform_fee_percent`, `processing_fee`, `auto_payouts`, `updated_by`, `updated_at`) VALUES
(1, 50.00, 10.00, 2.50, 1, 5, '2025-09-15 12:47:48');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(100) DEFAULT 'Kabaka',
  `max_upload_size_mb` int(11) DEFAULT 100,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `auto_approve` tinyint(1) DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `require_creator_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`id`, `site_name`, `max_upload_size_mb`, `maintenance_mode`, `auto_approve`, `updated_by`, `updated_at`, `require_creator_approval`) VALUES
(1, 'Kabaka', 100, 0, 0, 5, '2025-09-08 08:29:57', 0);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `money_per_view_cents` int(11) NOT NULL DEFAULT 1,
  `money_per_like_cents` int(11) NOT NULL DEFAULT 2,
  `min_followers_for_payout` int(11) NOT NULL DEFAULT 0,
  `dedupe_minutes` int(11) NOT NULL DEFAULT 30,
  `payout_min_cents` int(11) NOT NULL DEFAULT 1000,
  `auto_payout_day` tinyint(4) NOT NULL DEFAULT 30,
  `email_code_login` tinyint(1) NOT NULL DEFAULT 0,
  `chain_anchor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('viewer','creator','admin') NOT NULL DEFAULT 'viewer',
  `display_name` varchar(120) DEFAULT NULL,
  `binance_pay_id` varchar(120) DEFAULT NULL,
  `usdt_address` varchar(120) DEFAULT NULL,
  `followers_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `email_code` varchar(6) DEFAULT NULL,
  `email_code_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','banned','inactive','pending','suspended','rejected') DEFAULT 'active',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `monetization_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `display_name`, `binance_pay_id`, `usdt_address`, `followers_count`, `email_code`, `email_code_expires_at`, `created_at`, `updated_at`, `status`, `is_verified`, `monetization_enabled`) VALUES
(5, 'admin@kabaka.com', '$2y$10$5mPmErQVj7pURbu6sxgnbuRM5Et5G7HNcIGeUNgfDjNaZ7VktQ.Um', 'admin', 'Kabaka Admin', NULL, NULL, 0, NULL, NULL, '2025-09-02 20:21:36', '2025-09-06 21:00:29', 'active', 0, 0),
(7, 'mucyorobinson14@gmail.com', '$2y$10$jdckxl3xmqRFRNl6ekHz3efqJ0P/iX/webrpzJeM7LfEcBTE0tl9a', 'creator', 'Real_baddest', NULL, '111200045699', 0, NULL, NULL, '2025-09-05 13:40:46', '2025-09-15 13:06:56', 'active', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `viewers`
--

CREATE TABLE `viewers` (
  `id` int(11) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `viewers`
--

INSERT INTO `viewers` (`id`, `display_name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Mother****', 'harambineza01@gmail.com', '$2y$10$YB/zsRi84EYpWkq6CXE4yOFRXbinVEhLJ3Y4l3BwE56vV9.y1dFmu', '2025-08-28 22:46:56', '2025-08-28 22:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `balance_cents` bigint(20) NOT NULL DEFAULT 0,
  `pending_cents` bigint(20) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance_cents`, `pending_cents`, `updated_at`, `created_at`) VALUES
(5, 7, 8500, 0, '2025-09-15 22:57:51', '2025-09-06 16:09:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blockchain_reciept`
--
ALTER TABLE `blockchain_reciept`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blockchain_reciept_tx_hash` (`tx_hash`),
  ADD KEY `idx_blockchain_reciept_payment` (`payment_id`),
  ADD KEY `idx_blockchain_reciept_payhash` (`payment_id_hash`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content_id` (`content_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `content_moderation_log`
--
ALTER TABLE `content_moderation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content_id` (`content_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_report` (`content_id`,`reporter_id`),
  ADD KEY `idx_content_id` (`content_id`),
  ADD KEY `idx_reporter_id` (`reporter_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `creator_requirements`
--
ALTER TABLE `creator_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `engagements`
--
ALTER TABLE `engagements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content_id` (`content_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pair` (`follower_id`,`creator_id`),
  ADD KEY `idx_creator` (`creator_id`);

--
-- Indexes for table `moderation_actions`
--
ALTER TABLE `moderation_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content` (`content_id`),
  ADD KEY `fk_moderation_admin` (`admin_user_id`);

--
-- Indexes for table `moderation_auto_actions`
--
ALTER TABLE `moderation_auto_actions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `moderation_settings`
--
ALTER TABLE `moderation_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `monetization_settings`
--
ALTER TABLE `monetization_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `fk_payments_content` (`content_id`),
  ADD KEY `idx_payments_user_status` (`user_id`,`status`),
  ADD KEY `idx_payments_source` (`source`);

--
-- Indexes for table `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_users_verified` (`is_verified`),
  ADD KEY `idx_users_monetization` (`monetization_enabled`);

--
-- Indexes for table `viewers`
--
ALTER TABLE `viewers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blockchain_reciept`
--
ALTER TABLE `blockchain_reciept`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `content_moderation_log`
--
ALTER TABLE `content_moderation_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `creator_requirements`
--
ALTER TABLE `creator_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `engagements`
--
ALTER TABLE `engagements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1183;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `moderation_actions`
--
ALTER TABLE `moderation_actions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `moderation_auto_actions`
--
ALTER TABLE `moderation_auto_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `moderation_settings`
--
ALTER TABLE `moderation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `monetization_settings`
--
ALTER TABLE `monetization_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `platform_settings`
--
ALTER TABLE `platform_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `viewers`
--
ALTER TABLE `viewers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `fk_content_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `moderation_actions`
--
ALTER TABLE `moderation_actions`
  ADD CONSTRAINT `fk_moderation_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_moderation_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

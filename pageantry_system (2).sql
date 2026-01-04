-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 10:27 AM
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
-- Database: `pageantry_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `pageant_id` int(11) NOT NULL,
  `candidate_number` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `pageant_id`, `candidate_number`, `name`, `age`, `gender`, `description`, `photo`, `created_at`) VALUES
(19, 1, '1', 'Jennie Kim', 21, 'Female', '', 'candidate_1_1767518232.jpg', '2026-01-04 09:17:12'),
(20, 1, '1', 'Taehyung Kim', 21, 'Male', '', 'candidate_1_1767518250.jpg', '2026-01-04 09:17:30'),
(21, 1, '2', 'Jisoo Kim', 21, 'Female', '', 'candidate_2_1767518526.jpg', '2026-01-04 09:22:06'),
(22, 1, '2', 'Jungkook  Jeon', 21, 'Male', '', 'candidate_2_1767518594.jpg', '2026-01-04 09:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `criteria`
--

CREATE TABLE `criteria` (
  `id` int(11) NOT NULL,
  `pageant_id` int(11) NOT NULL,
  `round_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `criteria`
--

INSERT INTO `criteria` (`id`, `pageant_id`, `round_id`, `name`, `percentage`, `description`, `created_at`) VALUES
(1, 1, NULL, 'Introduction', 20.00, 'lkmkni', '2026-01-04 08:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `judge_assignments`
--

CREATE TABLE `judge_assignments` (
  `id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `pageant_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `judge_assignments`
--

INSERT INTO `judge_assignments` (`id`, `judge_id`, `pageant_id`, `created_at`) VALUES
(1, 4, 1, '2026-01-03 16:34:32'),
(2, 5, 1, '2026-01-04 08:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `pageants`
--

CREATE TABLE `pageants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `gender_type` enum('female','male','both') NOT NULL DEFAULT 'female',
  `primary_color` varchar(7) DEFAULT '#667eea',
  `secondary_color` varchar(7) DEFAULT '#764ba2',
  `accent_color` varchar(7) DEFAULT '#ffd700',
  `logo_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pageants`
--

INSERT INTO `pageants` (`id`, `name`, `theme`, `gender_type`, `primary_color`, `secondary_color`, `accent_color`, `logo_image`, `created_at`) VALUES
(1, 'Mr. and Ms. Heneral Santos', NULL, 'both', '#53555a', '#71a7fe', '#feef9a', '', '2026-01-03 16:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `segments`
--

CREATE TABLE `segments` (
  `id` int(11) NOT NULL,
  `pageant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'pageant_name', 'Pageantry Competition', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(2, 'primary_color', '#667eea', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(3, 'secondary_color', '#764ba2', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(4, 'accent_color', '#ffd700', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(5, 'logo_text', '👑', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(6, 'logo_image', '', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(7, 'logo_type', 'emoji', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(8, 'theme_style', 'gradient', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(9, 'background_style', 'gradient', '2026-01-03 16:33:41', '2026-01-03 16:33:41'),
(10, 'card_style', 'glassmorphism', '2026-01-03 16:33:41', '2026-01-03 16:33:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','judge') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$t8mT.9VJNiFKirXSqxoFceJJSmIV2ZHHIDQsvYNieJUiQ26rZMy/i', 'admin', 'System Administrator', '2026-01-03 16:32:25'),
(2, 'judge1', '$2y$10$gfaIYtfL9mSae367anJqder6DOtsOnQF4gEOAU/DanX/vTBIJoMNa', 'judge', 'Sample Judge', '2026-01-03 16:32:25'),
(3, 'pageantry_admin', '$2y$10$YI77T.Kfc59JJlf5MeA4h.uYAe5/oF5U2.oRpnRiX3m0FcADVbxtK', 'admin', 'Pageantry Admin', '2026-01-03 16:32:25'),
(4, 'Nics', '$2y$10$Yf.3THD0vTomNdB32//2eebmRybzk8Rt4EuFO38Jv5en.sLVXw3Mq', 'judge', 'Nicole Bayani', '2026-01-03 16:34:29'),
(5, 'jerica', '$2y$10$.KKD00nqUSqRZ/vtY33k9OJ5Y90ZZGcOOg.o8M9AD1FhO3VJzS6/C', 'judge', 'Jerica', '2026-01-04 08:46:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_candidate_per_pageant_gender` (`pageant_id`,`gender`,`candidate_number`);

--
-- Indexes for table `criteria`
--
ALTER TABLE `criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pageant_id` (`pageant_id`),
  ADD KEY `round_id` (`round_id`);

--
-- Indexes for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`judge_id`,`pageant_id`),
  ADD KEY `pageant_id` (`pageant_id`);

--
-- Indexes for table `pageants`
--
ALTER TABLE `pageants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_score` (`judge_id`,`candidate_id`,`criteria_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `segments`
--
ALTER TABLE `segments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pageant_id` (`pageant_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `criteria`
--
ALTER TABLE `criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pageants`
--
ALTER TABLE `pageants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `segments`
--
ALTER TABLE `segments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`pageant_id`) REFERENCES `pageants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `criteria`
--
ALTER TABLE `criteria`
  ADD CONSTRAINT `criteria_ibfk_1` FOREIGN KEY (`pageant_id`) REFERENCES `pageants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `criteria_ibfk_2` FOREIGN KEY (`round_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `judge_assignments`
--
ALTER TABLE `judge_assignments`
  ADD CONSTRAINT `judge_assignments_ibfk_1` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `judge_assignments_ibfk_2` FOREIGN KEY (`pageant_id`) REFERENCES `pageants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`criteria_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `segments`
--
ALTER TABLE `segments`
  ADD CONSTRAINT `segments_ibfk_1` FOREIGN KEY (`pageant_id`) REFERENCES `pageants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

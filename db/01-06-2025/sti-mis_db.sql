-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 02:06 PM
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
-- Database: `sti-mis_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `role`) VALUES
(1, 'superadmin', '$2y$10$fbz4v.DfwqgzLkSGzbFFW.GFI5ahZ597cOoiBuQnYONEOZRRloSyS', 'superadmin'),
(3, 'admin', '$2y$10$nPQz7mD0CJ5LV8jC1TuGleSEF8V1QY2R/0w8wJ/jeewI.RnpTz0ui', 'admin'),
(4, 'staff', '$2y$10$gl1lKOfrEXGtnc3lnK0JFeT/37Ksm6g/GFpEMP.iiBJzIBieeeX72', 'staff'),
(10, 'bobbydomdomjr', '$2y$10$yMr9VxoMGIPORgEs8lkkQuKg4oIKaxQEU.6bhitmURVTL3waeBPfq', 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `filename`, `start_time`, `end_time`, `uploaded_at`) VALUES
(67, 'ads/1744446404__MG_8019.JPG', '2025-04-11 16:26:00', '2025-04-12 16:26:00', '2025-04-12 08:26:44'),
(78, 'ads/1748511309_PN.jpg', '2025-05-29 01:34:00', '2025-05-29 22:35:00', '2025-05-29 09:35:09'),
(79, 'ads/1748606464_R.png', '2025-05-29 20:00:00', '2025-05-31 20:01:00', '2025-05-30 12:01:04'),
(81, 'ads/1748667408_1746174695_Screenshot_23-4-2025_111447_localhost.jpeg', '2025-05-31 00:56:00', '2025-06-04 12:56:00', '2025-05-31 04:56:48'),
(82, 'ads/1748702065_Screenshot_31-5-2025_223353_localhost.jpeg', '2025-05-30 10:34:00', '2025-06-04 22:34:00', '2025-05-31 14:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_role` enum('admin','staff') DEFAULT NULL,
  `receiver_id` int(11) DEFAULT 0,
  `receiver_role` enum('admin','staff') DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `message`, `is_read`, `created_at`) VALUES
(28, NULL, NULL, 0, 'admin', 'hello', 0, '2025-06-01 10:54:27'),
(29, NULL, NULL, 0, 'admin', 'yes po?', 0, '2025-06-01 10:55:04'),
(30, NULL, 'staff', 0, 'admin', 'ay hello po this admin', 0, '2025-06-01 10:55:49'),
(31, NULL, 'staff', 0, 'admin', 'good day!', 0, '2025-06-01 10:57:57'),
(32, NULL, NULL, 0, 'admin', 'hello', 0, '2025-06-01 10:59:31'),
(33, NULL, 'staff', 0, 'admin', 'this is staf', 0, '2025-06-01 11:00:08'),
(34, NULL, NULL, 0, 'admin', 'this is admin side', 0, '2025-06-01 11:00:16'),
(35, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 11:02:36'),
(36, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 11:09:52'),
(37, NULL, NULL, 0, 'admin', 'what\'s your need?', 0, '2025-06-01 11:10:51'),
(38, NULL, NULL, 0, 'admin', 'hello po', 0, '2025-06-01 11:11:39'),
(39, NULL, NULL, 0, 'admin', 'ahmmmm', 0, '2025-06-01 11:11:44'),
(40, NULL, 'staff', 0, 'admin', 'this is test message', 0, '2025-06-01 11:11:54'),
(41, NULL, NULL, 0, 'admin', 'yes po?', 0, '2025-06-01 11:13:00'),
(42, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 11:16:10'),
(43, NULL, 'staff', 0, 'admin', 'hi', 0, '2025-06-01 11:16:45'),
(44, NULL, 'staff', 0, 'admin', 'this is staff', 0, '2025-06-01 11:20:24'),
(45, NULL, NULL, 0, 'admin', 'yes?', 0, '2025-06-01 11:20:35'),
(46, NULL, NULL, 0, 'admin', 'how may i help you?', 0, '2025-06-01 11:20:41'),
(47, NULL, 'staff', 0, 'admin', 'need technical assistance', 0, '2025-06-01 11:20:58'),
(48, NULL, 'staff', 0, 'admin', 'regarding the system', 0, '2025-06-01 11:21:10'),
(49, NULL, NULL, 0, 'admin', 'copy that.', 0, '2025-06-01 11:21:21'),
(50, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 11:31:58'),
(51, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 11:33:45'),
(52, NULL, NULL, 0, 'admin', 'pwwwwede', 0, '2025-06-01 11:33:57'),
(53, NULL, 'staff', 0, 'admin', 'ey', 0, '2025-06-01 11:38:18'),
(54, NULL, NULL, 0, 'admin', 'hello', 0, '2025-06-01 11:40:19'),
(55, NULL, NULL, 0, 'admin', 'asds', 0, '2025-06-01 11:44:25'),
(56, NULL, NULL, 0, 'admin', 'testtttt', 0, '2025-06-01 11:46:03'),
(57, NULL, 'staff', 0, 'admin', 'this is okay', 0, '2025-06-01 11:46:12'),
(58, NULL, 'staff', 0, 'admin', 'hello po di nagana yung sa profile', 0, '2025-06-01 12:26:02'),
(59, NULL, NULL, 0, 'admin', 'wait', 0, '2025-06-01 12:26:11'),
(60, NULL, NULL, 0, 'admin', 'hello', 0, '2025-06-01 12:29:05'),
(61, NULL, 'staff', 0, 'admin', 'hello po', 0, '2025-06-01 12:34:01'),
(62, NULL, NULL, 0, 'admin', 'okay ba yung chat message niy?', 0, '2025-06-01 12:34:26'),
(63, NULL, 'staff', 0, 'admin', 'kjasdsads', 0, '2025-06-01 12:36:43'),
(64, NULL, NULL, 0, 'admin', 'okay naman skin', 0, '2025-06-01 12:37:19'),
(65, NULL, 'staff', 0, 'admin', '1322', 0, '2025-06-01 12:39:38'),
(66, NULL, NULL, 0, 'admin', 'ergrercr', 0, '2025-06-01 12:57:03'),
(67, NULL, 'staff', 0, 'admin', 'fdsfdsf', 0, '2025-06-01 12:57:08'),
(68, NULL, 'staff', 0, 'admin', 'kjbjlnkn;m\'', 0, '2025-06-01 13:11:10'),
(69, NULL, 'staff', 0, 'admin', 'lkm', 0, '2025-06-01 13:11:13'),
(70, NULL, NULL, 0, 'admin', 'jbjnlk', 0, '2025-06-01 13:11:23'),
(71, NULL, 'staff', 0, 'admin', 'hello', 0, '2025-06-01 13:35:14'),
(72, NULL, 'staff', 0, 'admin', 'sfgdvsd', 0, '2025-06-01 13:36:43'),
(73, NULL, 'staff', 0, 'admin', 'sd', 0, '2025-06-01 15:27:07'),
(74, NULL, NULL, 0, 'admin', 'hello', 0, '2025-06-01 16:05:45'),
(75, NULL, 'staff', 0, 'admin', 'asdasd', 0, '2025-06-01 16:06:17'),
(76, NULL, 'staff', 0, 'admin', 'kjbsds', 0, '2025-06-01 16:06:30'),
(77, NULL, 'staff', 0, 'admin', 'good afternoon', 0, '2025-06-01 16:06:55'),
(78, NULL, NULL, 0, 'admin', 'good day!', 0, '2025-06-01 16:07:16'),
(79, NULL, NULL, 0, 'admin', 'sa', 0, '2025-06-01 16:16:54'),
(80, NULL, NULL, 0, 'admin', 'hi', 0, '2025-06-01 16:19:27'),
(81, NULL, 'staff', 0, 'admin', 'staff', 0, '2025-06-01 16:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `queuing_enabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `queuing_enabled`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `id` int(11) NOT NULL,
  `studentno` varchar(20) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `extname` varchar(10) DEFAULT NULL,
  `middleinitial` varchar(3) DEFAULT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `phone` varchar(15) NOT NULL,
  `course` varchar(50) NOT NULL,
  `yearlevel` varchar(20) NOT NULL,
  `block` varchar(10) NOT NULL,
  `address` text NOT NULL,
  `contactname` varchar(50) NOT NULL,
  `contactno` varchar(15) NOT NULL,
  `relationship` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `registration_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','processing','done','no-show') NOT NULL DEFAULT 'pending',
  `now_serving` tinyint(1) NOT NULL DEFAULT 0,
  `notified` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registration`
--

INSERT INTO `student_registration` (`id`, `studentno`, `lastname`, `firstname`, `extname`, `middleinitial`, `dob`, `email`, `picture`, `gender`, `phone`, `course`, `yearlevel`, `block`, `address`, `contactname`, `contactno`, `relationship`, `created_at`, `updated_at`, `registration_date`, `status`, `now_serving`, `notified`) VALUES
(73, '02000433161', 'Magadia', 'Lourd Gavin', NULL, 'B', '2025-04-15', 'magadia.13412312@nagastieduph', '../uploads/student_02000433161_1748511535.png', 'Male', '0992-986-8567', 'ABM', 'Grade 11', '', 'San Felipe', 'Magadia', '0992-986-8565', 'Guardian', '2025-04-23 01:28:41', '2025-06-01 08:47:26', '2025-05-23 09:28:41', 'done', 0, 0),
(75, '02000495467', 'Marcelo', 'Mary Grace', NULL, 'n', '1994-02-24', 'mary.marcelo@nagastiedu', '../uploads/student_02000495467_1748529970.jpeg', 'Female', '0999-999-9889', 'IT MAWD', 'Grade 11', 'A', 'rtrhdfhchfd', 'Sfssddf', '0992-986-8565', 'Guardian', '2025-04-23 03:11:18', '2025-06-01 08:51:38', '2025-05-23 11:11:18', 'done', 0, 0),
(88, '02000342423', 'Asdasd', 'Adad', NULL, '', '2025-04-10', 'domdom.342423@naga.sti.edu.ph', '', 'Male', '0951-103-3187', 'ABM', 'Grade 11', '', 'wdasghj', 'JUAN ANTONIO V DELA CRUZ', '0985-413-2410', 'Sibling', '2025-04-25 05:01:40', '2025-06-01 08:40:18', '2025-05-25 13:01:40', 'pending', 0, 0),
(89, '02000334544', 'Sda', 'Dasdsad', NULL, '', '2025-04-08', 'delacruz.334544@naga.sti.edu.ph', '', 'Female', '0951-103-3187', 'ABM', 'Grade 11', '', 'wad\'sdfg', 'JUAN ANTONIO V DELA CRUZ', '0950-859-0850', 'Sibling', '2025-04-25 06:22:35', '2025-05-31 01:21:22', '2025-05-25 14:22:35', 'done', 0, 0),
(91, '02000987456', 'Mndz', 'Km', NULL, 'A', '2000-12-01', 'mndz.987456@naga.sti.edu.ph', '', 'Male', '0912-345-6789', 'BS Information Technology', '1st Yr.', 'B', '123 ABC  QWERTY', 'EM', '0998-765-4321', 'Relative', '2025-05-28 10:28:54', '2025-05-31 14:19:05', '2025-05-28 18:28:54', 'done', 0, 0),
(92, '02000123444', 'Domdom', 'Bobby', NULL, 'V', '2000-01-01', 'domdom.123444@naga.sti.edu.ph', '../uploads/student_02000123444_1748671916.png', 'Male', '0921-124-5410', 'BS Information Technology', '4th Yr.', 'A', 'asdsd', 'Sadsd', '0953-141-2424', 'Guardian', '2025-05-29 09:16:09', '2025-05-31 04:48:53', '2025-05-29 17:16:09', 'done', 0, 0),
(93, '02000452135', 'Doe', 'John', NULL, '', '2000-02-01', 'doe.452135@naga.sti.edu.ph', '', 'Male', '0923-418-3153', 'IT MAWD', 'Grade 12', 'A', 'asdsaxaxza', 'Saxasx', '0953-435-1511', 'Relative', '2025-05-30 04:31:22', '2025-05-31 04:54:54', '2025-05-30 12:31:22', 'done', 0, 0),
(94, '02000531132', 'Dasd', 'Sads', NULL, '', '2104-02-01', 'dasd.531132@naga.sti.edu.ph', '', 'Male', '0962-315-3121', 'ABM', 'Grade 11', 'A', 'asdasd', 'Sdas', '0953-111-3131', 'Relative', '2025-05-30 04:33:48', '2025-05-31 05:06:32', '2025-05-30 12:33:48', 'done', 0, 1),
(95, '02000468712', 'Arrieta', 'Levy', NULL, 'N', '2006-10-20', 'arrieta.468712@naga.sti.edu.ph', '../uploads/student_02000468712_1748606554.png', 'Male', '0921-354-3521', 'HUMSS', 'Grade 12', 'A', 'Pangasinan, Philippines', 'Bobby Domdom', '0934-541-3513', 'Relative', '2025-05-30 11:58:18', '2025-06-01 08:47:06', '2025-05-30 19:58:18', 'done', 0, 0),
(96, '02000153153', 'Arieta', 'Levy', NULL, 'C', '2001-02-02', '', '../uploads/student_02000153153_1748681684.png', 'Male', '0541-315-351', 'HUMSS', 'Grade 11', 'A', 'fdfchdcb', '', '', '', '2025-05-30 23:08:12', '2025-05-31 14:34:52', '2025-05-31 07:08:12', 'done', 0, 0),
(97, '02000203462', 'Caranza', 'Janelle France', NULL, 'L', '2004-06-03', 'caranza.203462@naga.sti.edu.ph', '../uploads/student_02000203462_1748701197.png', 'Female', '0912-345-7852', 'BS Hospitality Management', '3rd Yr.', 'A', 'Naga City', 'Jhanella', '0987-432-1851', 'Parent', '2025-05-31 14:18:08', '2025-06-01 04:57:33', '2025-05-31 22:18:08', 'done', 0, 0),
(98, '02000748392', 'Domdom', 'Bobby', NULL, 'V', '2025-06-01', 'domdom.748392@naga.sti.edu.ph', '', 'Male', '0973-838-2828', 'BS Information Technology', '3rd Yr.', 'A', 'Albay', 'Bobby', '0973-828-2828', 'Guardian', '2025-06-01 08:50:38', '2025-06-01 08:51:39', '2025-06-01 16:50:38', 'processing', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `action` text NOT NULL,
  `log_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `username`, `role`, `action`, `log_time`) VALUES
(43, 0, 'staf', 'N/A', 'Failed login attempt', '2025-06-01 15:58:16'),
(44, 4, 'staff', 'staff', 'User logged in', '2025-06-01 15:58:21'),
(45, 4, 'staff', 'staff', 'User logged in', '2025-06-01 16:03:48'),
(46, 8, NULL, NULL, 'User reset their password', '2025-06-01 16:09:18'),
(47, 4, 'staff', 'staff', 'User logged in', '2025-06-01 17:11:37'),
(48, 4, 'staff', 'staff', 'User logged in', '2025-06-01 17:14:57'),
(49, 4, 'staff', 'staff', 'User logged in', '2025-06-01 17:15:23'),
(50, 0, 'superadmin', 'N/A', 'Failed login attempt', '2025-06-01 17:17:15'),
(51, 3, 'admin', 'admin', 'User logged in', '2025-06-01 17:17:20'),
(52, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:18:32'),
(53, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:23:17'),
(54, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:24:55'),
(55, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:27:48'),
(56, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:50:10'),
(57, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:51:07'),
(58, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:51:23'),
(59, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:51:34'),
(60, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:51:37'),
(61, 3, NULL, NULL, 'User reset their password', '2025-06-01 17:51:51'),
(62, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:52:11'),
(63, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:52:20'),
(64, 8, NULL, NULL, 'User reset their password', '2025-06-01 17:52:30'),
(65, 10, 'bobbydomdomjr', 'staff', 'User logged in', '2025-06-01 17:54:04'),
(66, 10, 'bobbydomdomjr', 'staff', 'User logged in', '2025-06-01 17:55:12'),
(67, 10, 'bobbydomdomjr', 'staff', 'User logged in', '2025-06-01 17:55:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `studentno` (`studentno`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_registration`
--
ALTER TABLE `student_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

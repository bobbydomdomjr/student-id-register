-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 27, 2025 at 12:02 PM
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
(3, 'admin', '$2y$10$fbz4v.DfwqgzLkSGzbFFW.GFI5ahZ597cOoiBuQnYONEOZRRloSyS', 'admin'),
(4, 'staff', '$2y$10$fbz4v.DfwqgzLkSGzbFFW.GFI5ahZ597cOoiBuQnYONEOZRRloSyS', 'staff');

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
(59, 'ads/1744241393_OIP.jpg', '2025-04-09 07:29:00', '2025-04-10 07:29:00', '2025-04-09 23:29:53'),
(64, 'ads/1744423182_DataImage17.png', '2025-04-11 09:59:00', '2025-04-12 21:59:00', '2025-04-12 01:59:42'),
(67, 'ads/1744446404__MG_8019.JPG', '2025-04-11 16:26:00', '2025-04-12 16:26:00', '2025-04-12 08:26:44'),
(69, 'ads/1745126294_ABCPOS Queuing System.mp4', '2025-04-19 13:18:00', '2025-04-21 13:18:00', '2025-04-20 05:18:14');

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
(1, 0);

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

INSERT INTO `student_registration` (`id`, `studentno`, `lastname`, `firstname`, `extname`, `middleinitial`, `dob`, `email`, `gender`, `phone`, `course`, `yearlevel`, `block`, `address`, `contactname`, `contactno`, `relationship`, `created_at`, `updated_at`, `registration_date`, `status`, `now_serving`, `notified`) VALUES
(72, '02000847271', 'DOMDOM', 'BOBBY', NULL, 'V', '2025-04-23', 'bobby.domdomjr1@gmailcom', 'Male', '0915-324-2268', 'BS Information Technology', '4th Yr.', 'A', 'Albay', 'Sample', '0972-728-3848', 'Parent', '2025-04-23 01:16:29', '2025-05-25 13:36:25', '2025-05-23 09:16:29', 'pending', 0, 0),
(73, '02000433161', 'Magadia', 'Lourd Gavin', NULL, 'B', '2025-04-15', 'magadia.13412312@nagastieduph', 'Male', '0992-986-8567', 'ABM', 'Grade 11', '', 'San Felipe', 'Magadia', '0992-986-8565', 'Guardian', '2025-04-23 01:28:41', '2025-05-25 13:36:25', '2025-05-23 09:28:41', 'pending', 0, 0),
(75, '02000495467', 'Marcelo', 'Mary Grace', NULL, 'n', '1994-02-24', 'mary.marcelo@nagastiedu', 'Female', '0999-999-9889', 'IT MAWD', 'Grade 11', 'A', 'rtrhdfhchfd', 'Sfssddf', '0992-986-8565', 'Guardian', '2025-04-23 03:11:18', '2025-05-25 14:39:09', '2025-05-23 11:11:18', 'processing', 1, 0),
(86, '02000251413', 'Zxc', 'Zc', NULL, '', '2025-04-03', 'bobby.251413@naga.sti.edu.ph', 'Male', '0951-103-3187', 'HUMSS', 'Grade 11', '', 'adsada', 'Dasdsad Sda', '0946-872-7196', 'Sibling', '2025-04-24 08:31:29', '2025-05-25 13:36:25', '2025-05-24 16:31:29', 'processing', 0, 0),
(88, '02000342423', 'Asdasd', 'Adad', NULL, '', '2025-04-10', 'domdom.342423@naga.sti.edu.ph', 'Male', '0951-103-3187', 'ABM', 'Grade 11', '', 'wdasghj', 'JUAN ANTONIO V DELA CRUZ', '0985-413-2410', 'Sibling', '2025-04-25 05:01:40', '2025-05-25 13:36:25', '2025-05-25 13:01:40', 'pending', 0, 0),
(89, '02000334544', 'Sda', 'Dasdsad', NULL, '', '2025-04-08', 'delacruz.334544@naga.sti.edu.ph', 'Female', '0951-103-3187', 'ABM', 'Grade 11', '', 'wad\'sdfg', 'JUAN ANTONIO V DELA CRUZ', '0950-859-0850', 'Sibling', '2025-04-25 06:22:35', '2025-05-25 13:36:25', '2025-05-25 14:22:35', 'done', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `user_type`) VALUES
(1, 'adminuser', '$2y$10$RUioFX6kpnJP1c8jMN0aLuVKDbpHleQnEAQDZ8Z8mcEQQIbPGTV4', 'admin'),
(2, 'staffuser', '$2y$10$RUioFX6kpnJP1c8jMN0aLuVKDbpHleQnEAQDZ8Z8mcEQQIbPGTV4', 'staff');

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

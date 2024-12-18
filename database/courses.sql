-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 09:14 AM
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
-- Database: `attendance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `lecturer_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_sessions` int(11) NOT NULL DEFAULT 10,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `max_students` int(11) DEFAULT 50,
  `course_description` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `invite_code` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `lecturer_id`, `description`, `total_sessions`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `approval_status`, `max_students`, `course_description`, `department`, `invite_code`) VALUES
(11, 'MIS', 'Management Information System', 1, 'Testing', 10, '2024-12-03', '2024-12-31', 'active', '2024-12-05 07:50:48', '2024-12-11 04:10:52', 'pending', 50, NULL, NULL, NULL),
(12, 'ATM', 'Please', 1, '', 10, '2024-12-11', '2024-12-31', 'active', '2024-12-11 04:17:46', '2024-12-11 15:28:53', 'pending', 50, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD UNIQUE KEY `invite_code` (`invite_code`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

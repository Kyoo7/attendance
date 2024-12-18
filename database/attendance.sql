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
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `time_marked` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `qr_code_used` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `course_id`, `attendance_date`, `status`, `marked_by`, `time_marked`, `latitude`, `longitude`, `notes`, `qr_code_used`, `created_at`, `updated_at`, `session_id`) VALUES
(13, 2, 11, NULL, 'present', 1, '2024-12-12 09:25:38', NULL, NULL, NULL, NULL, '2024-12-12 09:25:34', '2024-12-12 09:25:38', 25),
(14, 2, 11, NULL, 'present', 1, '2024-12-12 14:54:33', NULL, NULL, NULL, NULL, '2024-12-12 14:52:28', '2024-12-12 14:54:33', 23),
(15, 3, 11, NULL, 'present', 1, '2024-12-12 14:54:34', NULL, NULL, NULL, NULL, '2024-12-12 14:52:32', '2024-12-12 14:54:34', 23),
(16, 4, 11, NULL, 'late', 1, '2024-12-12 14:54:36', NULL, NULL, NULL, NULL, '2024-12-12 14:52:33', '2024-12-12 14:54:36', 23),
(17, 5, 11, NULL, 'absent', 1, '2024-12-12 15:33:02', NULL, NULL, NULL, NULL, '2024-12-12 14:52:34', '2024-12-12 15:33:02', 23),
(18, 15, 11, NULL, 'present', 1, '2024-12-13 04:15:02', NULL, NULL, NULL, NULL, '2024-12-13 02:06:38', '2024-12-13 04:15:02', 29),
(19, 5, 11, NULL, 'present', 1, '2024-12-13 04:15:02', NULL, NULL, NULL, NULL, '2024-12-13 04:12:24', '2024-12-13 04:15:02', 29),
(20, 4, 11, NULL, 'present', 1, '2024-12-13 04:15:03', NULL, NULL, NULL, NULL, '2024-12-13 04:12:26', '2024-12-13 04:15:03', 29),
(21, 3, 11, NULL, 'present', 1, '2024-12-13 04:15:03', NULL, NULL, NULL, NULL, '2024-12-13 04:12:26', '2024-12-13 04:15:03', 29),
(22, 2, 11, NULL, 'late', 1, '2024-12-13 04:15:09', NULL, NULL, NULL, NULL, '2024-12-13 04:12:26', '2024-12-13 04:15:09', 29);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `fk_attendance_student` (`student_id`),
  ADD KEY `fk_attendance_session` (`session_id`),
  ADD KEY `fk_attendance_course` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_attendance_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_marker` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_attendance_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

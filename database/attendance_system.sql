-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2024 at 06:08 PM
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
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` enum('create','update','delete') NOT NULL,
  `entity_type` enum('user','course','enrollment') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `entity_name`, `description`, `created_at`) VALUES
(1, 1, 'create', 'course', 1, 'Sample Course', 'Created a new course', '2024-12-04 17:02:12'),
(2, 1, 'update', 'user', 2, 'John Doe', 'Updated user profile', '2024-12-04 17:02:12'),
(3, 1, 'update', 'course', 5, 'adada', 'Updated lecturer, description, total sessions', '2024-12-04 17:32:51'),
(4, 1, 'delete', 'user', 13, 'Charlie Brown', 'Deleted user with role: student', '2024-12-04 17:34:08'),
(5, 12, 'update', 'course', 1, 'Introduction to Computer Science', 'Updated lecturer, total sessions', '2024-12-05 06:37:06'),
(6, 12, 'update', 'course', 8, 'Management Information System', 'Updated course code, lecturer, total sessions', '2024-12-05 07:02:48'),
(7, 12, '', 'user', 6, 'Eva Chen', 'Updated user details: Role: student, Status: active', '2024-12-05 07:29:33'),
(8, 12, '', 'user', 1, 'Dr. John Smith', 'Updated user details: Role: lecturer, Status: active', '2024-12-05 07:29:40'),
(9, 12, 'update', 'course', 1, 'Introduction to Computer Science', 'Updated lecturer, total sessions', '2024-12-05 07:29:45'),
(10, 12, 'update', 'course', 2, 'Data Structures and Algorithms', 'Updated lecturer, total sessions', '2024-12-05 07:49:57'),
(11, 12, 'create', '', 6, 'Full-Stack', 'Created new session for course', '2024-12-05 08:22:25'),
(12, 12, 'delete', '', 6, 'Full-Stack', 'Deleted session for course: Data Structures and Algorithms', '2024-12-05 08:37:02'),
(13, 12, 'create', '', 7, 'Full-Stack', 'Created new session for course', '2024-12-05 08:38:00'),
(14, 12, 'update', '', 7, 'Full-Stack', 'Updated session details for course: Management Information System', '2024-12-05 08:38:05'),
(15, 12, 'create', '', 8, 'Testing', 'Created new session for course', '2024-12-05 08:40:03'),
(16, 12, 'delete', '', 8, 'Testing', 'Deleted session for course: Management Information System', '2024-12-05 08:40:37'),
(17, 12, 'update', '', 7, 'Full-Stack', 'Updated session details for course: Management Information System', '2024-12-05 08:42:51'),
(18, 12, 'delete', '', 7, 'Full-Stack', 'Deleted session for course: Management Information System', '2024-12-05 08:42:54'),
(19, 12, 'create', '', 9, 'Testing', 'Created new session for course', '2024-12-05 08:45:09'),
(20, 12, 'delete', '', 9, 'Testing', 'Deleted session for course: Management Information System', '2024-12-05 08:45:11'),
(21, 12, 'create', 'enrollment', 2, 'Student enrolled in course', 'Enrolled student ID 2 in course ID 11', '2024-12-05 08:48:11'),
(22, 12, 'create', 'enrollment', 3, 'Student enrolled in course', 'Enrolled student ID 3 in course ID 11', '2024-12-05 08:48:11'),
(23, 12, 'create', 'enrollment', 4, 'Student enrolled in course', 'Enrolled student ID 4 in course ID 11', '2024-12-05 08:48:11'),
(24, 12, 'create', 'enrollment', 5, 'Student enrolled in course', 'Enrolled student ID 5 in course ID 11', '2024-12-05 08:48:11'),
(25, 12, 'create', '', 10, 'Testing', 'Created new session for course', '2024-12-05 09:04:58'),
(26, 12, 'create', '', 11, 'Testing', 'Created new session for course', '2024-12-06 02:08:56'),
(27, 12, 'update', 'course', 11, 'Management Information System', 'Updated lecturer, total sessions, start date', '2024-12-06 02:10:17'),
(28, 12, 'update', 'course', 11, 'Management Information System', 'Updated lecturer, total sessions', '2024-12-06 02:17:05'),
(29, 12, 'create', '', 12, 'Testing', 'Created new session for course', '2024-12-06 02:32:58'),
(30, 12, 'update', 'course', 11, 'Management Information System', 'Updated lecturer, total sessions, end date', '2024-12-06 02:57:40'),
(31, 12, 'update', 'course', 11, 'Management Information System', 'Updated lecturer, total sessions', '2024-12-08 04:01:53'),
(32, 12, 'create', '', 13, 'Testing2', 'Created new session for course', '2024-12-08 04:04:22'),
(33, 12, 'create', '', 14, 'Testing3', 'Created new session for course', '2024-12-08 04:07:36'),
(34, 12, 'create', '', 15, 'Testing', '{\"course_id\":11,\"date\":\"2024-12-12\"}', '2024-12-08 04:27:10'),
(35, 12, 'update', '', 15, 'Testing', 'Updated session details for course: Management Information System', '2024-12-08 04:28:15'),
(36, 12, 'create', '', 16, 'Testing', '{\"course_id\":11,\"date\":\"2024-12-26\"}', '2024-12-08 04:28:34'),
(37, 12, 'delete', '', 12, 'Testing', 'Deleted session for course: Management Information System', '2024-12-08 04:28:45'),
(38, 12, 'delete', '', 13, 'Testing2', 'Deleted session for course: Management Information System', '2024-12-08 04:28:50'),
(39, 12, 'update', '', 15, 'Testing', 'Updated session details for course: Management Information System', '2024-12-08 04:54:06'),
(40, 12, 'create', '', 17, 'Current session', '{\"course_id\":11,\"date\":\"2024-12-08\"}', '2024-12-08 04:55:28'),
(41, 12, 'create', '', 18, 'IDK', '{\"course_id\":11,\"date\":\"2024-12-11\"}', '2024-12-11 02:28:21'),
(42, 12, 'create', '', 19, 'IDK', '{\"course_id\":11,\"date\":\"2024-12-11\"}', '2024-12-11 03:21:13'),
(43, 12, 'delete', 'user', 13, 'Saron Borak', 'Deleted user with role: student', '2024-12-11 04:06:02'),
(44, 12, '', 'user', 11, 'Nhim Kimhuy', 'Updated user details: Role: student, Status: active', '2024-12-11 04:06:24'),
(45, 12, 'update', 'course', 11, 'Management Information System', 'Updated lecturer, total sessions', '2024-12-11 04:10:52'),
(46, 12, 'update', 'course', 12, 'Please', 'Updated course code, course name, lecturer, total sessions', '2024-12-11 04:18:00'),
(47, 12, 'update', 'course', 12, 'Please', 'Updated lecturer, total sessions', '2024-12-11 15:19:28'),
(48, 12, 'update', 'course', 12, 'Please', 'Updated lecturer, total sessions', '2024-12-11 15:28:53');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `qr_code_used` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_tokens`
--

CREATE TABLE `attendance_tokens` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','dropped') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `course_id`, `student_id`, `enrollment_date`, `status`) VALUES
(1, 11, 2, '2024-12-05 08:48:11', 'active'),
(2, 11, 3, '2024-12-05 08:48:11', 'active'),
(3, 11, 4, '2024-12-05 08:48:11', 'active'),
(4, 11, 5, '2024-12-05 08:48:11', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `valid_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid_until` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_name` varchar(255) DEFAULT '',
  `description` text DEFAULT NULL,
  `room` varchar(100) DEFAULT '',
  `session_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `course_id`, `date`, `start_time`, `end_time`, `status`, `created_at`, `updated_at`, `session_name`, `description`, `room`, `session_date`, `created_by`) VALUES
(10, 11, '2024-12-05', '13:30:00', '16:30:00', 'scheduled', '2024-12-05 09:04:58', '2024-12-05 09:04:58', 'Testing', '', 'W2', '2024-12-05', 12),
(11, 11, '2024-12-06', '08:30:00', '11:30:00', 'scheduled', '2024-12-06 02:08:56', '2024-12-06 02:08:56', 'Testing', '', 'W2', '2024-12-06', 12),
(14, 11, '2024-12-09', '08:30:00', '11:30:00', 'scheduled', '2024-12-08 04:07:36', '2024-12-08 04:07:36', 'Testing3', '', 'W2', '2024-12-09', 12),
(15, 11, '2024-12-12', '11:27:00', '16:27:00', 'scheduled', '2024-12-08 04:27:10', '2024-12-08 04:54:06', 'Testing', '', 'W3', '2024-12-08', NULL),
(16, 11, '2024-12-26', '11:28:00', '12:28:00', 'scheduled', '2024-12-08 04:28:34', '2024-12-08 04:28:34', 'Testing', '', 'W3', NULL, NULL),
(17, 11, '2024-12-08', '11:54:00', '13:55:00', 'scheduled', '2024-12-08 04:55:28', '2024-12-08 04:55:28', 'Current session', '', 'W3', NULL, NULL),
(18, 11, '2024-12-11', '09:23:00', '23:30:00', 'scheduled', '2024-12-11 02:28:21', '2024-12-11 02:28:21', 'IDK', 'PLS WORK', 'W3', NULL, NULL),
(19, 11, '2024-12-11', '10:21:00', '22:23:00', 'scheduled', '2024-12-11 03:21:13', '2024-12-11 03:21:13', 'IDK', 'asdadsad', 'W3', NULL, NULL);

--
-- Triggers `sessions`
--
DELIMITER $$
CREATE TRIGGER `update_session_status` BEFORE UPDATE ON `sessions` FOR EACH ROW BEGIN
    IF NEW.date < CURDATE() THEN
        SET NEW.status = 'completed';
    ELSEIF NEW.date = CURDATE() AND NEW.start_time <= CURTIME() AND NEW.end_time >= CURTIME() THEN
        SET NEW.status = 'ongoing';
    ELSEIF NEW.date = CURDATE() AND NEW.end_time < CURTIME() THEN
        SET NEW.status = 'completed';
    ELSE
        SET NEW.status = 'scheduled';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `student_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`, `student_id`, `department`, `status`) VALUES
(1, 'teacher@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'lecturer', '2024-12-05 03:30:49', '2024-12-05 07:29:40', NULL, NULL, 'active'),
(2, 'student1@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(3, 'student2@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(4, 'student3@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol Martinez', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(5, 'student4@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(6, 'student5@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eva Chen', 'student', '2024-12-05 03:30:49', '2024-12-05 07:29:33', NULL, NULL, 'active'),
(7, 'student6@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frank Rodriguez', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(8, 'student7@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace Kim', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(9, 'student8@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Henry Patel', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(10, 'student9@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Isabella Silva', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active'),
(11, 'student10@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhim Kimhuy', 'student', '2024-12-05 03:30:49', '2024-12-11 04:06:24', NULL, NULL, 'active'),
(12, 'admin1@eamu.edu', '$2y$10$7chJQazBEp4oJAQ3uq120ufGJqezmQOxYqm2kDxY2WqCqA4dvmE3e', 'System Administrator', 'admin', '2024-12-05 03:32:50', '2024-12-05 03:32:50', NULL, NULL, 'active'),
(14, 'hello@eamu.edu', '$2y$10$tRHQNMf/1UCWokBEi2gNpOcCoT95HcbQUeL8opiP8sAmfDSgo8ldi', 'IhatethisASS', 'lecturer', '2024-12-11 15:32:17', '2024-12-11 15:32:17', NULL, 'BIS', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_attendance_session` (`session_id`);

--
-- Indexes for table `attendance_tokens`
--
ALTER TABLE `attendance_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_course_session` (`course_id`,`session_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD UNIQUE KEY `invite_code` (`invite_code`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_session_created_by` (`created_by`),
  ADD KEY `idx_session_date` (`date`),
  ADD KEY `idx_session_status` (`status`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `attendance_tokens`
--
ALTER TABLE `attendance_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_attendance_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_tokens`
--
ALTER TABLE `attendance_tokens`
  ADD CONSTRAINT `attendance_tokens_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_tokens_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `qr_codes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_session_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_session_statuses` ON SCHEDULE EVERY 1 MINUTE STARTS '2024-12-08 11:26:45' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE sessions
    SET status = CASE
        WHEN date < CURDATE() THEN 'completed'
        WHEN date = CURDATE() AND start_time <= CURTIME() AND end_time >= CURTIME() THEN 'ongoing'
        WHEN date = CURDATE() AND end_time < CURTIME() THEN 'completed'
        ELSE 'scheduled'
    END
    WHERE status != 'cancelled'$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

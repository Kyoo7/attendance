-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 04:00 AM
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
(48, 12, 'update', 'course', 12, 'Please', 'Updated lecturer, total sessions', '2024-12-11 15:28:53'),
(49, 12, 'delete', '', 18, 'IDK', 'Deleted session for course: Management Information System', '2024-12-11 17:28:36'),
(50, 12, 'delete', '', 14, 'Testing3', 'Deleted session for course: Management Information System', '2024-12-11 17:28:40'),
(51, 12, 'delete', '', 15, 'Testing', 'Deleted session for course: Management Information System', '2024-12-11 17:28:43'),
(52, 12, 'delete', '', 11, 'Testing', 'Deleted session for course: Management Information System', '2024-12-11 17:28:46'),
(53, 12, 'delete', '', 10, 'Testing', 'Deleted session for course: Management Information System', '2024-12-11 17:28:49'),
(54, 12, 'delete', '', 17, 'Current session', 'Deleted session for course: Management Information System', '2024-12-11 17:28:52'),
(55, 12, 'delete', '', 16, 'Testing', 'Deleted session for course: Management Information System', '2024-12-11 17:28:56'),
(56, 12, 'delete', '', 19, 'IDK', 'Deleted session for course: Management Information System', '2024-12-11 17:28:59'),
(57, 12, 'create', '', 20, 'IDK', '{\"course_id\":11,\"date\":\"2024-12-12\"}', '2024-12-11 17:52:50'),
(58, 12, 'create', '', 21, 'IDK2', '{\"course_id\":11,\"date\":\"2024-12-11\"}', '2024-12-11 18:03:48'),
(59, 12, 'create', '', 22, 'IDK', '{\"course_id\":11,\"date\":\"2024-12-12\"}', '2024-12-11 18:11:05'),
(60, 12, 'create', '', 23, 'Working?', '{\"course_id\":11,\"date\":\"2024-12-14\"}', '2024-12-11 18:26:53'),
(61, 12, 'create', '', 24, 'IDK3', '{\"course_id\":11,\"date\":\"2024-12-12\"}', '2024-12-11 18:27:17'),
(62, 12, 'create', '', 25, 'Current', '{\"course_id\":11,\"date\":\"2024-12-12\"}', '2024-12-12 07:46:43'),
(63, 1, 'create', '', 28, 'Testing', 'Session \'Testing\' created for course ID: 11', '2024-12-12 07:55:17'),
(64, 1, 'delete', '', 21, 'IDK2', 'Deleted session \'IDK2\' from course \'Management Information System\'', '2024-12-12 07:57:40'),
(65, 1, 'delete', '', 28, 'Testing', 'Deleted session \'Testing\' from course \'Management Information System\'', '2024-12-12 07:57:48'),
(66, 1, 'delete', '', 27, 'Testing', 'Deleted session \'Testing\' from course \'Management Information System\'', '2024-12-12 07:57:54'),
(67, 1, 'update', '', 26, 'Testing', 'Updated session \'Testing\' in course \'Management Information System\'', '2024-12-12 08:00:54'),
(68, 1, 'update', '', 20, 'IDK', 'Updated session \'IDK\' in course \'Management Information System\'', '2024-12-12 08:01:04'),
(69, 1, 'update', '', 1, 'Dr. John Smith', 'Updated profile information', '2024-12-12 08:22:02'),
(70, 1, 'update', '', 1, 'Profile Picture', 'Updated profile picture', '2024-12-12 08:41:48'),
(71, 1, 'update', '', 1, 'Dr. John Smith', 'Updated profile information', '2024-12-12 08:41:50'),
(72, 1, 'update', '', 1, 'Dr. John Smith', 'Updated profile information', '2024-12-12 08:41:50'),
(73, 1, 'update', '', 1, 'Ainee', 'Updated profile information', '2024-12-12 08:53:27'),
(74, 1, 'update', '', 23, 'Working?', 'Updated session \'Working?\' in course \'Management Information System\'', '2024-12-12 14:52:23'),
(75, 1, 'create', '', 29, 'Testing', 'Session \'Testing\' created for course ID: 11', '2024-12-13 02:05:30'),
(76, 15, 'update', '', 15, 'Profile Picture', 'Updated profile picture', '2024-12-13 03:08:56'),
(77, 15, 'update', 'user', 15, 'Student', 'Updated profile information', '2024-12-13 03:12:28'),
(78, 12, 'update', 'course', 16, 'Full-Stack Web Development', 'Updated lecturer, description, status', '2024-12-13 04:27:02'),
(79, 12, 'delete', 'user', 16, 'Saron Borak', 'Deleted user with role: student', '2024-12-13 04:28:11'),
(80, 1, 'create', '', 30, 'Testing', 'Session \'Testing\' created for course ID: 12', '2024-12-15 02:57:14');

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

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `previous_status` enum('present','late','absent') DEFAULT NULL,
  `new_status` enum('present','late','absent') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`id`, `attendance_id`, `previous_status`, `new_status`, `changed_by`, `change_reason`, `created_at`) VALUES
(1, 13, 'present', 'late', 1, NULL, '2024-12-12 09:25:36'),
(2, 13, 'late', 'absent', 1, NULL, '2024-12-12 09:25:37'),
(3, 13, 'absent', 'present', 1, NULL, '2024-12-12 09:25:38'),
(4, 17, 'absent', 'late', 1, NULL, '2024-12-12 15:33:01'),
(5, 17, 'late', 'present', 1, NULL, '2024-12-12 15:33:01'),
(6, 17, 'present', 'absent', 1, NULL, '2024-12-12 15:33:02'),
(7, 19, 'present', 'late', 1, NULL, '2024-12-13 04:12:25'),
(8, 22, 'late', 'present', 1, NULL, '2024-12-13 04:12:27'),
(9, 21, 'late', 'present', 1, NULL, '2024-12-13 04:12:27'),
(10, 20, 'late', 'present', 1, NULL, '2024-12-13 04:12:28'),
(11, 19, 'late', 'present', 1, NULL, '2024-12-13 04:12:29'),
(12, 22, 'present', 'late', 1, NULL, '2024-12-13 04:14:52'),
(13, 21, 'present', 'absent', 1, NULL, '2024-12-13 04:14:53'),
(14, 21, 'absent', 'late', 1, NULL, '2024-12-13 04:14:59'),
(15, 20, 'present', 'late', 1, NULL, '2024-12-13 04:15:00'),
(16, 19, 'present', 'late', 1, NULL, '2024-12-13 04:15:00'),
(17, 19, 'late', 'absent', 1, NULL, '2024-12-13 04:15:00'),
(18, 18, 'present', 'late', 1, NULL, '2024-12-13 04:15:01'),
(19, 18, 'late', 'present', 1, NULL, '2024-12-13 04:15:02'),
(20, 19, 'absent', 'present', 1, NULL, '2024-12-13 04:15:02'),
(21, 20, 'late', 'present', 1, NULL, '2024-12-13 04:15:03'),
(22, 21, 'late', 'present', 1, NULL, '2024-12-13 04:15:03'),
(23, 22, 'late', 'present', 1, NULL, '2024-12-13 04:15:04'),
(24, 22, 'present', 'late', 1, NULL, '2024-12-13 04:15:09');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `late_threshold_minutes` int(11) NOT NULL DEFAULT 15,
  `attendance_window_before_minutes` int(11) NOT NULL DEFAULT 15,
  `attendance_window_after_minutes` int(11) NOT NULL DEFAULT 30,
  `geofencing_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `geofencing_radius_meters` int(11) DEFAULT 100,
  `center_latitude` decimal(10,8) DEFAULT NULL,
  `center_longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 30 minute),
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
(4, 11, 5, '2024-12-05 08:48:11', 'active'),
(5, 12, 15, '2024-12-12 15:49:50', 'active'),
(6, 11, 15, '2024-12-13 02:06:00', 'active'),
(7, 12, 2, '2024-12-13 04:31:40', 'active');

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
(20, 11, '2024-12-12', '00:52:00', '12:58:00', 'completed', '2024-12-11 17:52:50', '2024-12-12 08:01:04', 'IDK', 'HI', 'W3', NULL, NULL),
(23, 11, '2024-12-12', '20:26:00', '23:55:00', 'completed', '2024-12-11 18:26:53', '2024-12-13 02:04:40', 'Working?', 'HI', 'W3', NULL, NULL),
(25, 11, '2024-12-12', '13:30:00', '16:30:00', 'completed', '2024-12-12 07:46:43', '2024-12-12 13:34:55', 'Current', '', 'E2', NULL, NULL),
(26, 11, '2024-12-12', '14:53:00', '16:30:00', 'completed', '2024-12-12 07:53:53', '2024-12-12 13:34:55', 'Testing', '', 'W3', NULL, NULL),
(29, 11, '2024-12-13', '08:30:00', '11:30:00', 'completed', '2024-12-13 02:05:30', '2024-12-13 04:30:31', 'Testing', '', 'E2', NULL, NULL),
(30, 12, '2024-12-15', '08:30:00', '11:30:00', 'ongoing', '2024-12-15 02:57:14', '2024-12-15 02:57:16', 'Testing', 'Testing', 'W3', NULL, NULL);

--
-- Triggers `sessions`
--
DELIMITER $$
CREATE TRIGGER `update_session_status` BEFORE UPDATE ON `sessions` FOR EACH ROW IF NEW.date < CURDATE() THEN
        SET NEW.status = 'completed';
    ELSEIF NEW.date = CURDATE() AND NEW.start_time <= CURTIME() AND NEW.end_time >= CURTIME() THEN
        SET NEW.status = 'ongoing';
    ELSEIF NEW.date = CURDATE() AND NEW.end_time < CURTIME() THEN
        SET NEW.status = 'completed';
    ELSE
        SET NEW.status = 'scheduled';
    END IF
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
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`, `student_id`, `department`, `status`, `profile_picture`) VALUES
(1, 'teacher@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ainee', 'lecturer', '2024-12-05 03:30:49', '2024-12-12 08:53:27', NULL, 'BIS', 'active', '1_1733992908.jpg'),
(2, 'student1@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(3, 'student2@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(4, 'student3@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol Martinez', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(5, 'student4@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Brown', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(6, 'student5@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eva Chen', 'student', '2024-12-05 03:30:49', '2024-12-05 07:29:33', NULL, NULL, 'active', NULL),
(7, 'student6@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frank Rodriguez', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(8, 'student7@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace Kim', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(9, 'student8@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Henry Patel', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(10, 'student9@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Isabella Silva', 'student', '2024-12-05 03:30:49', '2024-12-05 03:30:49', NULL, NULL, 'active', NULL),
(11, 'student10@eamu.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhim Kimhuy', 'student', '2024-12-05 03:30:49', '2024-12-11 04:06:24', NULL, NULL, 'active', NULL),
(12, 'admin1@eamu.edu', '$2y$10$7chJQazBEp4oJAQ3uq120ufGJqezmQOxYqm2kDxY2WqCqA4dvmE3e', 'System Administrator', 'admin', '2024-12-05 03:32:50', '2024-12-05 03:32:50', NULL, NULL, 'active', NULL),
(14, 'hello@eamu.edu', '$2y$10$tRHQNMf/1UCWokBEi2gNpOcCoT95HcbQUeL8opiP8sAmfDSgo8ldi', 'IhatethisASS', 'lecturer', '2024-12-11 15:32:17', '2024-12-11 15:32:17', NULL, 'BIS', 'active', NULL),
(15, 'student@gmail.com', '$2y$10$hhJ6c/sNxCSheMsALmvrTOM.n4wG8wJz67Dhz8JTHjSVGBl2rkh/e', 'Student', 'student', '2024-12-12 15:37:03', '2024-12-13 03:08:56', '2303045X', NULL, 'active', '15_1734059336.jpg');

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
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `fk_attendance_student` (`student_id`),
  ADD KEY `fk_attendance_session` (`session_id`),
  ADD KEY `fk_attendance_course` (`course_id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_tokens`
--
ALTER TABLE `attendance_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  ADD CONSTRAINT `fk_attendance_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_marker` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_attendance_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `fk_log_attendance` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD CONSTRAINT `fk_settings_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

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
CREATE DEFINER=`root`@`localhost` EVENT `update_session_statuses` ON SCHEDULE EVERY 1 SECOND STARTS '2024-12-08 11:26:45' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE sessions
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

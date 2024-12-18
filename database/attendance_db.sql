-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 03:36 AM
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
(80, 1, 'create', '', 30, 'Testing', 'Session \'Testing\' created for course ID: 12', '2024-12-15 02:57:14'),
(81, 12, 'delete', 'user', 14, 'IhatethisASS', 'Deleted user with role: lecturer', '2024-12-16 08:18:51'),
(89, 12, 'delete', 'user', 21, 'Cambodia Pimping Center', 'Deleted user with role: admin', '2024-12-16 08:20:17'),
(91, 17, 'update', '', 17, 'Profile Picture', 'Updated profile picture', '2024-12-16 09:31:48'),
(92, 17, 'update', '', 17, 'Ngin Kimlong', 'Updated profile information', '2024-12-16 09:31:56'),
(93, 18, 'update', '', 18, 'Profile Picture', 'Updated profile picture', '2024-12-16 09:35:06'),
(94, 18, 'update', 'user', 18, 'Saron Borak', 'Updated profile information', '2024-12-16 09:35:11');

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
(276, 18, NULL, NULL, 'present', NULL, '2024-10-14 06:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 61),
(277, 19, NULL, NULL, 'present', NULL, '2024-10-14 06:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 61),
(278, 20, NULL, NULL, 'present', NULL, '2024-10-14 06:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 61),
(279, 18, NULL, NULL, 'present', NULL, '2024-10-18 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 62),
(280, 19, NULL, NULL, 'present', NULL, '2024-10-18 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 62),
(281, 20, NULL, NULL, 'present', NULL, '2024-10-18 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 62),
(282, 18, NULL, NULL, 'present', NULL, '2024-10-23 01:10:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 63),
(283, 19, NULL, NULL, 'present', NULL, '2024-10-23 01:10:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 63),
(284, 20, NULL, NULL, 'present', NULL, '2024-10-23 01:10:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 63),
(285, 18, NULL, NULL, 'present', NULL, '2024-10-25 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 64),
(286, 19, NULL, NULL, 'present', NULL, '2024-10-25 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 64),
(287, 20, NULL, NULL, 'present', NULL, '2024-10-25 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 64),
(288, 18, NULL, NULL, 'present', NULL, '2024-10-30 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 65),
(289, 19, NULL, NULL, 'present', NULL, '2024-10-30 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 65),
(290, 20, NULL, NULL, 'present', NULL, '2024-10-30 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 65),
(291, 18, NULL, NULL, 'present', NULL, '2024-11-01 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 66),
(292, 19, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 66),
(293, 20, NULL, NULL, 'present', NULL, '2024-11-01 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 66),
(294, 18, NULL, NULL, 'present', NULL, '2024-11-04 06:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 67),
(295, 19, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 67),
(296, 20, NULL, NULL, 'present', NULL, '2024-11-04 06:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 67),
(297, 18, NULL, NULL, 'present', NULL, '2024-11-06 01:00:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 68),
(298, 19, NULL, NULL, 'present', NULL, '2024-11-06 01:00:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 68),
(299, 20, NULL, NULL, 'present', NULL, '2024-11-06 01:00:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 68),
(300, 18, NULL, NULL, 'late', NULL, '2024-11-08 01:45:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 69),
(301, 19, NULL, NULL, 'present', NULL, '2024-11-08 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 69),
(302, 20, NULL, NULL, 'late', NULL, '2024-11-08 01:45:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 69),
(303, 18, NULL, NULL, 'late', NULL, '2024-11-13 01:45:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 70),
(304, 19, NULL, NULL, 'late', NULL, '2024-11-13 01:45:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 70),
(305, 20, NULL, NULL, 'present', NULL, '2024-11-13 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 70),
(306, 18, NULL, NULL, 'present', NULL, '2024-11-20 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 71),
(307, 19, NULL, NULL, 'present', NULL, '2024-11-20 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 71),
(308, 20, NULL, NULL, 'present', NULL, '2024-11-20 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 71),
(309, 18, NULL, NULL, 'present', NULL, '2024-11-22 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 72),
(310, 19, NULL, NULL, 'present', NULL, '2024-11-22 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 72),
(311, 20, NULL, NULL, 'present', NULL, '2024-11-22 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 72),
(312, 18, NULL, NULL, 'present', NULL, '2024-11-27 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 73),
(313, 19, NULL, NULL, 'present', NULL, '2024-11-27 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 73),
(314, 20, NULL, NULL, 'present', NULL, '2024-11-27 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 73),
(315, 18, NULL, NULL, 'absent', NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 74),
(316, 19, NULL, NULL, 'present', NULL, '2024-11-29 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 74),
(317, 20, NULL, NULL, 'present', NULL, '2024-11-29 01:30:00', NULL, NULL, NULL, NULL, '2024-12-16 08:37:57', '2024-12-16 08:37:57', 74),
(339, 18, NULL, NULL, 'present', NULL, '2024-12-04 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 76),
(340, 19, NULL, NULL, 'present', NULL, '2024-12-04 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 76),
(341, 20, NULL, NULL, 'present', NULL, '2024-12-04 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 76),
(342, 18, NULL, NULL, 'present', NULL, '2024-12-06 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 77),
(343, 19, NULL, NULL, 'present', NULL, '2024-12-06 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 77),
(344, 20, NULL, NULL, 'present', NULL, '2024-12-06 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 77),
(345, 18, NULL, NULL, 'present', NULL, '2024-12-11 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 78),
(346, 19, NULL, NULL, 'present', NULL, '2024-12-11 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 78),
(347, 20, NULL, NULL, 'present', NULL, '2024-12-11 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 78),
(348, 18, NULL, NULL, 'present', NULL, '2024-12-13 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 79),
(349, 19, NULL, NULL, 'present', NULL, '2024-12-13 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 79),
(350, 20, NULL, NULL, 'present', NULL, '2024-12-13 01:20:00', NULL, NULL, NULL, NULL, '2024-12-16 08:44:46', '2024-12-16 08:44:46', 79),
(351, 19, 17, NULL, 'present', 17, '2024-12-18 02:15:00', NULL, NULL, NULL, NULL, '2024-12-18 02:08:32', '2024-12-18 02:15:00', 80),
(352, 20, 17, NULL, 'present', 17, '2024-12-18 02:15:00', NULL, NULL, NULL, NULL, '2024-12-18 02:08:33', '2024-12-18 02:15:00', 80),
(353, 18, 17, NULL, 'present', 17, '2024-12-18 02:15:01', NULL, NULL, NULL, NULL, '2024-12-18 02:08:34', '2024-12-18 02:15:01', 80);

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

--
-- Dumping data for table `attendance_tokens`
--

INSERT INTO `attendance_tokens` (`id`, `course_id`, `session_id`, `token`, `created_at`, `expires_at`, `used_at`) VALUES
(1, 17, 80, 'fd676736bcdd2f2dac847d1a5fb4d1bfa81e4efcf0c31f5242ef1c65838f8c59', '2024-12-18 02:08:27', '2024-12-17 20:38:27', NULL);

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
(17, 'FSWD', 'Full-Stack Web Development', 17, 'The objective of this module is to introduce students to the comprehensive development of web applications. Students will acquire the knowledge and skills necessary to design, build, test, and deploy a complete web application from end to end. students will learn how to prototype and sketch layouts to visualize and plan the user interface effectively, ensuring a user-centered approach to web application design. In addition, the module encompasses various technologies and tools, covering key areas such as front-end web development, back-end web development, and database development.', 14, '2024-10-14', '2024-12-31', 'active', '2024-12-16 08:21:22', '2024-12-16 08:21:22', 'pending', 50, NULL, NULL, NULL);

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
(8, 17, 18, '2024-10-13 17:00:00', 'active'),
(9, 17, 19, '2024-10-13 17:00:00', 'active'),
(10, 17, 20, '2024-10-13 17:00:00', 'active');

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
-- Table structure for table `remember_me`
--

CREATE TABLE `remember_me` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remember_me`
--

INSERT INTO `remember_me` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(2, 18, '049f80d863f4033a8e429e1fafe409af897cf4c77c5d18e922397fbf8de78edf', '2025-01-17 03:28:59', '2024-12-18 02:28:59');

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
(61, 17, '2024-10-14', '13:30:00', '16:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'HTML5', NULL, 'E2', NULL, NULL),
(62, 17, '2024-10-18', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'HTML5', NULL, 'E2', NULL, NULL),
(63, 17, '2024-10-23', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'CSS3', NULL, 'E2', NULL, NULL),
(64, 17, '2024-10-25', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'CSS3', NULL, 'E2', NULL, NULL),
(65, 17, '2024-10-30', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'JavaScript', NULL, 'E2', NULL, NULL),
(66, 17, '2024-11-01', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'JavaScript', NULL, 'E2', NULL, NULL),
(67, 17, '2024-11-04', '13:30:00', '16:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'PHP', NULL, 'E2', NULL, NULL),
(68, 17, '2024-11-06', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'PHP', NULL, 'E2', NULL, NULL),
(69, 17, '2024-11-08', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Operators and Condition Expression', NULL, 'E2', NULL, NULL),
(70, 17, '2024-11-13', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Operators and Condition Expression', NULL, 'E2', NULL, NULL),
(71, 17, '2024-11-20', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Arrays and Loop', NULL, 'E2', NULL, NULL),
(72, 17, '2024-11-22', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Arrays and Loop', NULL, 'E2', NULL, NULL),
(73, 17, '2024-11-27', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Form handling', NULL, 'E2', NULL, NULL),
(74, 17, '2024-11-29', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:37:06', '2024-12-16 08:38:17', 'Form handling', NULL, 'E2', NULL, NULL),
(76, 17, '2024-12-04', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:44:45', '2024-12-16 08:44:45', 'Working with MySQL Database', NULL, 'E2', NULL, NULL),
(77, 17, '2024-12-06', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:44:45', '2024-12-16 08:44:45', 'Working with MySQL Database', NULL, 'E2', NULL, NULL),
(78, 17, '2024-12-11', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:44:45', '2024-12-16 08:44:45', 'Integrating PHP with MySQL Database', NULL, 'E2', NULL, NULL),
(79, 17, '2024-12-13', '08:30:00', '11:30:00', 'completed', '2024-12-16 08:44:45', '2024-12-16 08:44:45', 'Integrating PHP with MySQL Database', NULL, 'E2', NULL, NULL),
(80, 17, '2024-12-18', '08:30:00', '11:30:00', 'ongoing', '2024-12-16 08:44:45', '2024-12-18 02:07:00', 'State information', NULL, 'E2', NULL, NULL),
(81, 17, '2024-12-20', '08:30:00', '11:30:00', 'scheduled', '2024-12-16 08:44:45', '2024-12-16 08:44:45', 'Assignment Presentation', NULL, 'E2', NULL, NULL);

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
(15, 'student@gmail.com', '$2y$10$hhJ6c/sNxCSheMsALmvrTOM.n4wG8wJz67Dhz8JTHjSVGBl2rkh/e', 'Student', 'student', '2024-12-12 15:37:03', '2024-12-13 03:08:56', '2303045X', NULL, 'active', '15_1734059336.jpg'),
(17, 'kimlong@lecturer.eamu', '$2y$10$umwH40q8MpCNAFOJwuyR5u5IFeev6IGeKuAUoKTpfE7khiaaAdxU6', 'Ngin Kimlong', 'lecturer', '2024-12-16 08:18:39', '2024-12-16 09:31:48', NULL, 'BIS', 'active', '17_1734341508.jpg'),
(18, 'borak@student.eamu', '$2y$10$mfEJsD.gEUKyePHwG8dEvezyzuUn6ZvWpGLagetba20EXUHuza7Im', 'Saron Borak', 'student', '2024-12-16 08:18:39', '2024-12-16 09:35:06', '2303041X', NULL, 'active', '18_1734341706.jpg'),
(19, 'dalis@student.eamu', '$2y$10$mfEJsD.gEUKyePHwG8dEvezyzuUn6ZvWpGLagetba20EXUHuza7Im', 'Hour Dalis', 'student', '2024-12-16 08:18:39', '2024-12-16 08:18:39', '2303033P', NULL, 'active', NULL),
(20, 'kimhuy@student.eamu', '$2y$10$mfEJsD.gEUKyePHwG8dEvezyzuUn6ZvWpGLagetba20EXUHuza7Im', 'Nhim Kimhuy', 'student', '2024-12-16 08:18:39', '2024-12-16 08:18:39', '2303047X', NULL, 'active', NULL);

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
-- Indexes for table `remember_me`
--
ALTER TABLE `remember_me`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=354;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_me`
--
ALTER TABLE `remember_me`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `remember_me`
--
ALTER TABLE `remember_me`
  ADD CONSTRAINT `remember_me_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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

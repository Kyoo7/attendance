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
(30, 12, '2024-12-15', '08:30:00', '11:30:00', 'completed', '2024-12-15 02:57:14', '2024-12-16 04:45:29', 'Testing', 'Testing', 'W3', NULL, NULL);

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

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_session_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

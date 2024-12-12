-- Drop existing foreign key constraints
ALTER TABLE `attendance` DROP FOREIGN KEY IF EXISTS `fk_attendance_session`;
ALTER TABLE `attendance` DROP FOREIGN KEY IF EXISTS `fk_attendance_student`;
ALTER TABLE `attendance` DROP FOREIGN KEY IF EXISTS `fk_attendance_course`;

-- Modify the attendance table
ALTER TABLE `attendance`
  ADD COLUMN `marked_by` int(11) DEFAULT NULL AFTER `status`,
  ADD COLUMN `time_marked` timestamp NULL DEFAULT NULL AFTER `marked_by`,
  ADD COLUMN `latitude` decimal(10,8) DEFAULT NULL AFTER `time_marked`,
  ADD COLUMN `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN `notes` text DEFAULT NULL AFTER `longitude`,
  ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`,
  MODIFY COLUMN `student_id` int(11) NOT NULL,
  MODIFY COLUMN `session_id` int(11) NOT NULL,
  ADD KEY `marked_by` (`marked_by`);

-- Add foreign key constraints
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_marker` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`);

-- Create attendance settings table
CREATE TABLE IF NOT EXISTS `attendance_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `late_threshold_minutes` int(11) NOT NULL DEFAULT 15,
  `attendance_window_before_minutes` int(11) NOT NULL DEFAULT 15,
  `attendance_window_after_minutes` int(11) NOT NULL DEFAULT 30,
  `geofencing_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `geofencing_radius_meters` int(11) DEFAULT 100,
  `center_latitude` decimal(10,8) DEFAULT NULL,
  `center_longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `fk_settings_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create attendance logs table
CREATE TABLE IF NOT EXISTS `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attendance_id` int(11) NOT NULL,
  `previous_status` enum('present','late','absent') DEFAULT NULL,
  `new_status` enum('present','late','absent') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `attendance_id` (`attendance_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `fk_log_attendance` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

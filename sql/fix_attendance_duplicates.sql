-- Create a temporary table to store the latest attendance records
CREATE TEMPORARY TABLE temp_attendance AS
SELECT MAX(id) as id
FROM attendance
GROUP BY student_id, session_id;

-- Delete duplicate records, keeping only the latest ones
DELETE FROM attendance 
WHERE id NOT IN (SELECT id FROM temp_attendance);

-- Add unique constraint to prevent duplicate attendance records
ALTER TABLE `attendance`
ADD UNIQUE KEY `unique_student_session` (`student_id`, `session_id`);

-- Drop the temporary table
DROP TEMPORARY TABLE temp_attendance;

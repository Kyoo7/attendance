<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_code = filter_var($_POST['course_code'], FILTER_SANITIZE_STRING);
    $course_name = filter_var($_POST['course_name'], FILTER_SANITIZE_STRING);
    $lecturer_id = $_POST['lecturer_id'];
    $description = !empty($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    try {
        // Check if course code exists for other courses
        $stmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ? AND id != ?");
        $stmt->execute([$course_code, $course_id]);
        if ($stmt->fetch()) {
            $_SESSION['message'] = "Course code already exists";
            $_SESSION['message_type'] = "error";
            header("Location: ../admin/edit-course.php?id=" . $course_id);
            exit();
        }

        // Get current course details
        $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        // Start transaction
        $conn->beginTransaction();

        // Update course
        $stmt = $conn->prepare("
            UPDATE courses 
            SET course_code = ?, 
                course_name = ?, 
                lecturer_id = ?, 
                description = ?, 
                total_sessions = ?, 
                start_date = ?, 
                end_date = ?, 
                status = ?, 
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $course_code,
            $course_name,
            $lecturer_id,
            $description,
            0, // Default total_sessions to 0
            $start_date,
            $end_date,
            $status,
            $course_id
        ]);

        // Log the activity
        $changes = [];
        if ($course['course_code'] !== $course_code) $changes[] = "course code";
        if ($course['course_name'] !== $course_name) $changes[] = "course name";
        if ($course['lecturer_id'] !== $lecturer_id) $changes[] = "lecturer";
        if ($course['description'] !== $description) $changes[] = "description";
        if ($course['start_date'] !== $start_date) $changes[] = "start date";
        if ($course['end_date'] !== $end_date) $changes[] = "end date";
        if ($course['status'] !== $status) $changes[] = "status";

        $changeDescription = empty($changes) 
            ? "No changes made" 
            : "Updated " . implode(", ", $changes);

        logActivity(
            $conn, 
            $_SESSION['user_id'], 
            'update', 
            'course', 
            $course_id, 
            $course_name,
            $changeDescription
        );

        $conn->commit();
        
        $_SESSION['message'] = "Course updated successfully";
        $_SESSION['message_type'] = "success";
        header("Location: ../admin/courses.php");
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error updating course: " . $e->getMessage());
        $_SESSION['message'] = "Error updating course";
        $_SESSION['message_type'] = "error";
        header("Location: ../admin/edit-course.php?id=" . $course_id);
        exit();
    }
} else {
    header("Location: ../admin/courses.php");
    exit();
}
?>

<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/courses.php");
    exit();
}

// Validate course ID and student ID
$course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

if (!$course_id || !$student_id) {
    $_SESSION['message'] = "Invalid course or student ID.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/courses.php");
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // First, check if the student is actually enrolled in this course
    $checkStmt = $conn->prepare("
        SELECT COUNT(*) as enrolled 
        FROM enrollments 
        WHERE course_id = ? AND student_id = ?
    ");
    $checkStmt->execute([$course_id, $student_id]);
    $enrollmentCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($enrollmentCheck['enrolled'] == 0) {
        throw new Exception("Student is not enrolled in this course.");
    }

    // Remove student from enrollments
    $stmt = $conn->prepare("
        DELETE FROM enrollments 
        WHERE course_id = ? AND student_id = ?
    ");
    $result = $stmt->execute([$course_id, $student_id]);

    if (!$result) {
        throw new PDOException("Failed to remove student from course.");
    }

    // Remove student's attendance records for this course
    $attendanceStmt = $conn->prepare("
        DELETE a FROM attendance a
        JOIN sessions s ON a.session_id = s.id
        WHERE s.course_id = ? AND a.student_id = ?
    ");
    $attendanceStmt->execute([$course_id, $student_id]);

    // Log the activity
    logActivity(
        $conn, 
        $_SESSION['user_id'], 
        'delete', 
        'enrollment', 
        $student_id, 
        "Student removed from course", 
        "Removed student ID $student_id from course ID $course_id"
    );

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['message'] = "Student successfully removed from the course.";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log the error
    error_log("Remove student error: " . $e->getMessage());

    // Set error message
    $_SESSION['message'] = "Error removing student: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Redirect back to course details page
header("Location: ../admin/course-details.php?id=" . $course_id);
exit();
?>

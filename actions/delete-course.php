<?php
require_once '../config/database.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    // Remove CSRF token validation
    
    $course_id = $_POST['course_id'];
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // First, delete all related records to break foreign key constraints
        
        // 1. Delete student_courses records
        $stmt = $conn->prepare("DELETE FROM student_courses WHERE course_id = ?");
        $stmt->execute([$course_id]);
        error_log("Deleted student_courses records for course: " . $stmt->rowCount());
        
        // 2. Delete attendance records
        $stmt = $conn->prepare("DELETE FROM attendance WHERE course_id = ?");
        $stmt->execute([$course_id]);
        error_log("Deleted attendance records for course: " . $stmt->rowCount());
        
        // 3. Delete sessions
        $stmt = $conn->prepare("DELETE FROM sessions WHERE course_id = ?");
        $stmt->execute([$course_id]);
        error_log("Deleted sessions for course: " . $stmt->rowCount());
        
        // 4. Delete enrollments
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $stmt->execute([$course_id]);
        error_log("Deleted enrollments for course: " . $stmt->rowCount());
        
        // 5. Finally, delete the course
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $result = $stmt->execute([$course_id]);
        
        if (!$result) {
            throw new PDOException("Failed to delete course. Error: " . implode(", ", $stmt->errorInfo()));
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['message'] = "Course deleted successfully";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deleting course ID $course_id: " . $e->getMessage());
        $_SESSION['message'] = "Error deleting course. Details: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deleting course ID $course_id: " . $e->getMessage());
        $_SESSION['message'] = "Error deleting course. Details: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "Invalid request";
    $_SESSION['message_type'] = "error";
}

header("Location: ../admin/courses.php");
exit();
?>

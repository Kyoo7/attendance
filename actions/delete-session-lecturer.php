<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../lecturer/courses.php");
    exit();
}

// Validate session ID
$session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

if (!$session_id) {
    $_SESSION['message'] = "Invalid session ID.";
    $_SESSION['message_type'] = "error";
    header("Location: ../lecturer/courses.php");
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // First, verify that the session belongs to a course owned by this lecturer
    $stmt = $conn->prepare("
        SELECT s.id, s.session_name, s.course_id, c.course_name 
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->execute([$session_id, $_SESSION['user_id']]);
    $sessionDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sessionDetails) {
        throw new Exception("Session not found or you don't have permission to delete it.");
    }

    // Delete attendance records for this session
    $stmt = $conn->prepare("DELETE FROM attendance WHERE session_id = ?");
    $stmt->execute([$session_id]);

    // Delete the session
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = ?");
    $stmt->execute([$session_id]);

    // Log the activity
    logActivity(
        $conn,
        $_SESSION['user_id'],
        'delete',
        'session',
        $session_id,
        $sessionDetails['session_name'],
        "Deleted session '{$sessionDetails['session_name']}' from course '{$sessionDetails['course_name']}'"
    );

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Session deleted successfully!";
    $_SESSION['message_type'] = "success";

    // Redirect back to the course page if we have course_id
    if (isset($_POST['course_id'])) {
        header("Location: ../lecturer/view-course.php?id=" . $_POST['course_id']);
    } else {
        header("Location: ../lecturer/courses.php");
    }

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    error_log("Error deleting session: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
    
    // Redirect back to the course page if we have course_id
    if (isset($_POST['course_id'])) {
        header("Location: ../lecturer/view-course.php?id=" . $_POST['course_id']);
    } else {
        header("Location: ../lecturer/courses.php");
    }
}
?>

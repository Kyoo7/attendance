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
    header("Location: ../admin/sessions.php");
    exit();
}

// Validate session ID
$session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

if (!$session_id) {
    $_SESSION['message'] = "Invalid session ID.";
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/sessions.php");
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // First, get session details for logging
    $stmt = $conn->prepare("
        SELECT s.session_name, c.course_name 
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$session_id]);
    $sessionDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sessionDetails) {
        throw new Exception("Session not found.");
    }

    // Delete attendance records for this session
    $stmt = $conn->prepare("DELETE FROM attendance WHERE session_id = ?");
    $stmt->execute([$session_id]);

    // Delete the session
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id = ?");
    $result = $stmt->execute([$session_id]);

    if (!$result) {
        throw new PDOException("Failed to delete session.");
    }

    // Log the activity
    logActivity(
        $conn, 
        $_SESSION['user_id'], 
        'delete', 
        'session', 
        $session_id, 
        $sessionDetails['session_name'], 
        "Deleted session for course: " . $sessionDetails['course_name']
    );

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['message'] = "Session deleted successfully!";
    $_SESSION['message_type'] = "success";

} catch (PDOException $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log the error
    error_log("Session deletion error: " . $e->getMessage());

    // Set error message
    $_SESSION['message'] = "Error deleting session. " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Fetch the course_id for the session before redirecting
$stmt = $conn->prepare("SELECT course_id FROM sessions WHERE id = ?");
$stmt->execute([$session_id]);
$courseResult = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect back to course details page
if ($courseResult && isset($courseResult['course_id'])) {
    header("Location: ../admin/course-details.php?id=" . $courseResult['course_id']);
} else {
    header("Location: ../admin/courses.php");
}
exit();

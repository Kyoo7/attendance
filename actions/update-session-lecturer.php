<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
require_once '../includes/session_update.php';
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

// Validate inputs
$session_id = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
$course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
$session_name = trim($_POST['session_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$room = trim($_POST['room'] ?? '');
$session_date = $_POST['session_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

// Validation checks
$errors = [];

if (!$session_id) $errors[] = "Invalid session ID.";
if (!$course_id) $errors[] = "Please select a course.";
if (empty($session_name)) $errors[] = "Session name is required.";
if (empty($room)) $errors[] = "Room is required.";
if (empty($session_date)) $errors[] = "Session date is required.";
if (empty($start_time)) $errors[] = "Start time is required.";
if (empty($end_time)) $errors[] = "End time is required.";

// Time validation
if ($start_time >= $end_time) $errors[] = "End time must be after start time.";

try {
    // Verify that both the session and course belong to this lecturer
    $stmt = $conn->prepare("
        SELECT s.id as session_id, s.session_name, s.course_id, c.course_name
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->execute([$session_id, $_SESSION['user_id']]);
    $sessionDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sessionDetails) {
        throw new Exception("Session not found or you don't have permission to edit it.");
    }

    // Verify that the target course also belongs to this lecturer
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        throw new Exception("You don't have permission to move the session to this course.");
    }

    // If there are validation errors
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = "error";
        header("Location: ../lecturer/edit-session.php?id=" . $session_id);
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    // Update the session
    $stmt = $conn->prepare("
        UPDATE sessions 
        SET course_id = ?, session_name = ?, description = ?, room = ?, 
            date = ?, start_time = ?, end_time = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $course_id,
        $session_name,
        $description,
        $room,
        $session_date,
        $start_time,
        $end_time,
        $session_id
    ]);

    // Log the activity
    logActivity(
        $conn,
        $_SESSION['user_id'],
        'update',
        'session',
        $session_id,
        $session_name,
        "Updated session '{$session_name}' in course '{$sessionDetails['course_name']}'"
    );

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Session updated successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: ../lecturer/view-course.php?id=" . $course_id);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error updating session: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../lecturer/edit-session.php?id=" . $session_id);
}
?>

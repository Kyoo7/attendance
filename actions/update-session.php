<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
require_once '../includes/session_update.php';
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

// Date validation
$today = date('Y-m-d');
if ($session_date < $today) $errors[] = "Session date cannot be in the past.";

// Time validation
if ($start_time >= $end_time) $errors[] = "End time must be after start time.";

// If there are validation errors
if (!empty($errors)) {
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/edit-session.php?id=" . $session_id);
    exit();
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Determine new status based on updated date and time
    $new_status = updateSessionStatus([
        'date' => $session_date,
        'start_time' => $start_time,
        'end_time' => $end_time
    ]);

    // First, get current session details for logging
    $stmt = $conn->prepare("
        SELECT s.session_name, c.course_name as old_course_name, 
               c.course_name as new_course_name
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$session_id]);
    $oldSessionDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Update session
    $stmt = $conn->prepare("
        UPDATE sessions 
        SET course_id = ?, 
            session_name = ?, 
            description = ?, 
            room = ?, 
            date = ?, 
            start_time = ?, 
            end_time = ?,
            status = ?
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
        $new_status,
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
        "Updated session details for course: " . $oldSessionDetails['old_course_name']
    );

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['message'] = "Session updated successfully!";
    $_SESSION['message_type'] = "success";

} catch (PDOException $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log the error
    error_log("Session update error: " . $e->getMessage());

    // Set error message
    $_SESSION['message'] = "Error updating session. " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Redirect back to course details page
header("Location: ../admin/course-details.php?id=" . $course_id);
exit();

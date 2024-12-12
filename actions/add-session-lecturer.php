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
    header("Location: ../lecturer/add-session.php");
    exit();
}

try {
    // Validate and sanitize inputs
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $session_name = trim($_POST['session_name']);
    $description = trim($_POST['description'] ?? '');
    $room = trim($_POST['room']);
    $session_date = $_POST['session_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Validation
    $errors = [];

    if (!$course_id) {
        $errors[] = "Please select a valid course.";
    }

    if (empty($session_name)) {
        $errors[] = "Session name is required.";
    }

    if (empty($room)) {
        $errors[] = "Room is required.";
    }

    if (empty($session_date)) {
        $errors[] = "Session date is required.";
    }

    if (empty($start_time) || empty($end_time)) {
        $errors[] = "Both start and end times are required.";
    }

    // Verify that the course belongs to the current lecturer
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND lecturer_id = ?");
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $errors[] = "You don't have permission to add sessions to this course.";
    }

    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        header("Location: ../lecturer/add-session.php" . ($course_id ? "?course_id=$course_id" : ""));
        exit();
    }

    // Insert the session
    $stmt = $conn->prepare("
        INSERT INTO sessions (course_id, session_name, description, room, date, start_time, end_time, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')
    ");

    $stmt->execute([
        $course_id,
        $session_name,
        $description,
        $room,
        $session_date,
        $start_time,
        $end_time
    ]);

    // Log the activity
    logActivity(
        $conn, 
        $_SESSION['user_id'],
        'create',
        'session',
        $conn->lastInsertId(),
        $session_name,
        "Session '$session_name' created for course ID: $course_id"
    );

    $_SESSION['message'] = "Session created successfully!";
    $_SESSION['message_type'] = "success";

    // Redirect based on whether we came from a course page
    if (isset($_POST['from_course'])) {
        header("Location: ../lecturer/view-course.php?id=$course_id");
    } else {
        header("Location: ../lecturer/courses.php");
    }

} catch (PDOException $e) {
    error_log("Error creating session: " . $e->getMessage());
    $_SESSION['message'] = "Error creating session. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: ../lecturer/add-session.php" . ($course_id ? "?course_id=$course_id" : ""));
}
?>

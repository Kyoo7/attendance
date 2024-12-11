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
    header("Location: ../admin/add-session.php");
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

    // Debug output
    error_log("Received data: " . print_r($_POST, true));

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
        $errors[] = "Start time and end time are required.";
    }

    // Get lecturer ID from course
    $stmt = $conn->prepare("SELECT lecturer_id FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $lecturer_id = $stmt->fetchColumn();

    if (!$lecturer_id) {
        $errors[] = "Invalid course selected.";
    }

    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        header("Location: ../admin/add-session.php");
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    // Debug output
    error_log("Attempting to insert session with data:");
    error_log("Course ID: $course_id");
    error_log("Session Name: $session_name");
    error_log("Date: $session_date");
    error_log("Start Time: $start_time");
    error_log("End Time: $end_time");

    // Insert into sessions table
    $stmt = $conn->prepare("
        INSERT INTO sessions (
            course_id, session_name, description, 
            room, date, start_time, end_time, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

     // Determine initial status based on date and time
     $initial_status = updateSessionStatus([
        'date' => $session_date,
        'start_time' => $start_time,
        'end_time' => $end_time
    ]);
    $stmt->execute([
        $course_id, $session_name, $description,
        $room, $session_date, $start_time, $end_time, $initial_status
    ]);

    $session_id = $conn->lastInsertId();

    // Log the activity
    logActivity($conn, $_SESSION['user_id'], 'create', 'sessions', $session_id, $session_name, 
        json_encode([
            'course_id' => $course_id,
            'date' => $session_date
        ])
    );

    $conn->commit();

    $_SESSION['message'] = "Session created successfully!";
    $_SESSION['message_type'] = "success";
    
    // Redirect back to course details if came from there
    if (isset($_POST['from_course']) && $_POST['from_course']) {
        header("Location: ../admin/course-details.php?id=" . $course_id);
    } else {
        header("Location: ../admin/sessions.php");
    }
    exit();

} catch (PDOException $e) {
    $conn->rollBack();
    // Log the detailed error
    error_log("Error creating session: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // During development, show the actual error
    $_SESSION['message'] = "Error creating session: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/add-session.php");
    exit();
}

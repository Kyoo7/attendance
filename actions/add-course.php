<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/courses.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

try {
    // Validate required fields
    $required_fields = ['course_code', 'course_name', 'lecturer_id', 'total_sessions', 'start_date', 'end_date'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All required fields must be filled out.");
        }
    }

    // Sanitize input
    $course_code = filter_var($_POST['course_code'], FILTER_SANITIZE_STRING);
    $course_name = filter_var($_POST['course_name'], FILTER_SANITIZE_STRING);
    $lecturer_id = filter_var($_POST['lecturer_id'], FILTER_SANITIZE_NUMBER_INT);
    $total_sessions = filter_var($_POST['total_sessions'], FILTER_SANITIZE_NUMBER_INT);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = !empty($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : null;

    // Validate dates
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    if ($start_timestamp === false || $end_timestamp === false) {
        throw new Exception("Invalid date format.");
    }
    
    if ($start_timestamp > $end_timestamp) {
        throw new Exception("End date must be after start date.");
    }

    // Verify lecturer exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'lecturer'");
    $stmt->execute([$lecturer_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Selected lecturer does not exist.");
    }

    // Check if course code already exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
    $stmt->execute([$course_code]);
    if ($stmt->fetch()) {
        throw new Exception("Course code already exists.");
    }

    // Begin transaction
    $conn->beginTransaction();

    // Insert course
    $stmt = $conn->prepare("
        INSERT INTO courses (course_code, course_name, lecturer_id, total_sessions, 
                           start_date, end_date, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $course_code,
        $course_name,
        $lecturer_id,
        $total_sessions,
        $start_date,
        $end_date,
        $description
    ]);

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Course added successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: ../admin/courses.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction if error occurs
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/add-course.php");
    exit();
}

<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/users.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

try {
    // Validate required fields
    $required_fields = ['full_name', 'email', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All required fields must be filled out.");
        }
    }

    // Sanitize input
    $full_name = htmlspecialchars(trim($_POST['full_name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role = in_array($_POST['role'], ['admin', 'lecturer', 'student']) ? $_POST['role'] : '';
    $password = $_POST['password'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception("Email address already exists.");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Additional validation for role-specific fields
    if ($role === 'student' && empty($_POST['student_id'])) {
        throw new Exception("Student ID is required for student accounts.");
    }
    if ($role === 'lecturer' && empty($_POST['department'])) {
        throw new Exception("Department is required for lecturer accounts.");
    }

    // Sanitize role-specific fields
    $student_id = null;
    $department = null;
    if ($role === 'student') {
        $student_id = htmlspecialchars(trim($_POST['student_id']), ENT_QUOTES, 'UTF-8');
    } else if ($role === 'lecturer') {
        $department = htmlspecialchars(trim($_POST['department']), ENT_QUOTES, 'UTF-8');
    }

    // Begin transaction
    $conn->beginTransaction();

    // Insert user with role-specific fields
    $stmt = $conn->prepare("
        INSERT INTO users (email, password, full_name, role, student_id, department) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$email, $hashedPassword, $full_name, $role, $student_id, $department]);
    $userId = $conn->lastInsertId();

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "User added successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: ../admin/users.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction if error occurs
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: ../admin/add-user.php");
    exit();
}

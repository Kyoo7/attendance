<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

// Ensure only student can access
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../student/profile.php");
    exit();
}

try {
    // Validate inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    $errors = [];

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($student_id)) $errors[] = "Student ID is required.";
    if (empty($current_password)) $errors[] = "Current password is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stored_hash = $stmt->fetchColumn();

    if (!password_verify($current_password, $stored_hash)) {
        $errors[] = "Current password is incorrect.";
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "Email address is already in use.";
    }

    // Check if student ID is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ? AND id != ?");
    $stmt->execute([$student_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "Student ID is already in use.";
    }

    // Password change validation
    if (!empty($new_password) || !empty($confirm_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode("<br>", $errors));
    }

    // Begin transaction
    $conn->beginTransaction();

    // Update profile information
    $sql = "UPDATE users SET 
            full_name = ?,
            email = ?,
            student_id = ?";
    $params = [$full_name, $email, $student_id];

    // Add password update if new password is provided
    if (!empty($new_password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $_SESSION['user_id'];

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Log the activity
    logActivity(
        $conn,
        $_SESSION['user_id'],
        'update',
        'user',
        $_SESSION['user_id'],
        $_SESSION['full_name'],
        'Updated profile information'
    );

    $conn->commit();

    $_SESSION['full_name'] = $full_name;
    $_SESSION['message'] = "Profile updated successfully!";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: ../student/profile.php");
exit();

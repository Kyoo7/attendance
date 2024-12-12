<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../lecturer/profile.php");
    exit();
}

try {
    // Validate inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    $errors = [];

    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($department)) $errors[] = "Department is required.";
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

    // Password change validation
    if (!empty($new_password) || !empty($confirm_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
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

    // Update user information
    $sql = "UPDATE users SET full_name = ?, email = ?, department = ?";
    $params = [$full_name, $email, $department];

    // Add password update if provided
    if (!empty($new_password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $_SESSION['user_id'];

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Log activity
    logActivity(
        $conn,
        $_SESSION['user_id'],
        'update',
        'profile',
        $_SESSION['user_id'],
        $full_name,
        'Updated profile information'
    );

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Profile updated successfully!";
    $_SESSION['message_type'] = "success";
    
    // Update session variables
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error updating profile: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: ../lecturer/profile.php");
exit();
?>

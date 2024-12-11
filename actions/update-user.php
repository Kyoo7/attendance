<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $new_password = trim($_POST['new_password']);

    try {
        // Check if email exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['message'] = "Email already exists";
            $_SESSION['message_type'] = "error";
            header("Location: ../admin/edit-user.php?id=" . $user_id);
            exit();
        }

        // Start transaction
        $conn->beginTransaction();

        if ($new_password) {
            // Update user with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, role = ?, status = ?, 
                    password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $role, $status, $hashed_password, $user_id]);
        } else {
            // Update user without changing password
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, role = ?, status = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $role, $status, $user_id]);
        }

        // Log the activity
        $description = "Updated user details: " . 
                      ($new_password ? "Changed password, " : "") . 
                      "Role: $role, Status: $status";
        logActivity(
            $conn,
            $_SESSION['user_id'],
            'edit',
            'user',
            $user_id,
            $full_name,
            $description
        );

        // Commit transaction
        $conn->commit();

        $_SESSION['message'] = "User updated successfully";
        $_SESSION['message_type'] = "success";
        header("Location: ../admin/users.php");
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error updating user: " . $e->getMessage());
        $_SESSION['message'] = "Error updating user. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: ../admin/edit-user.php?id=" . $user_id);
        exit();
    }
} else {
    header("Location: ../admin/users.php");
    exit();
}
?>

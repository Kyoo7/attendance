<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    try {
        // Get user details before deletion
        $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new PDOException("User not found");
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Delete user's enrollments first
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ?");
        $stmt->execute([$user_id]);
        
        // Update courses if user is a lecturer
        $stmt = $conn->prepare("UPDATE courses SET lecturer_id = NULL WHERE lecturer_id = ?");
        $stmt->execute([$user_id]);
        
        // Log the activity before deleting the user
        logActivity(
            $conn,
            $_SESSION['user_id'],
            'delete',
            'user',
            $user_id,
            $user['full_name'],
            "Deleted user with role: " . $user['role']
        );
        
        // Finally, delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['message'] = "User deleted successfully";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deleting user: " . $e->getMessage());
        $_SESSION['message'] = "Error deleting user. Please try again.";
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "Invalid request";
    $_SESSION['message_type'] = "error";
}

header("Location: ../admin/users.php");
exit();
?>

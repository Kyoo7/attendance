<?php
require_once '../config/database.php';
require_once '../includes/activity_logger.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../" . $_SESSION['role'] . "/profile.php");
    exit();
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception("No file uploaded.");
    }

    $file = $_FILES['profile_picture'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG, and GIF files are allowed.");
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception("File is too large. Maximum size is 5MB.");
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = "../uploads/profile_pictures";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $target_path = $upload_dir . '/' . $filename;

    // Delete old profile picture if exists
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $old_picture = $stmt->fetchColumn();
    
    if ($old_picture && file_exists($upload_dir . '/' . $old_picture)) {
        unlink($upload_dir . '/' . $old_picture);
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception("Failed to upload file.");
    }

    // Update database
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->execute([$filename, $_SESSION['user_id']]);

    // Log activity
    logActivity(
        $conn,
        $_SESSION['user_id'],
        'update',
        'profile_picture',
        $_SESSION['user_id'],
        'Profile Picture',
        'Updated profile picture'
    );

    $_SESSION['message'] = "Profile picture updated successfully!";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    error_log("Error updating profile picture: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "error";
}

header("Location: ../" . $_SESSION['role'] . "/profile.php");
exit();
?>

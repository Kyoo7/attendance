<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../index.php");
        exit();
    }
    
    try {
        // Debug information
        error_log("Login attempt - Email: " . $email);
        
        $stmt = $conn->prepare("SELECT id, email, password, role, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug information
        error_log("User found: " . ($user ? 'Yes' : 'No'));
        if ($user) {
            error_log("Password verification result: " . (password_verify($password, $user['password']) ? 'Success' : 'Failed'));
        }
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Debug information
            error_log("Login successful - Role: " . $user['role']);
            
            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                case 'lecturer':
                    header("Location: ../lecturer/dashboard.php");
                    break;
                case 'student':
                    header("Location: ../student/dashboard.php");
                    break;
                default:
                    header("Location: ../index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            error_log("Login failed - Invalid credentials");
            header("Location: ../index.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "System error. Please try again later.";
        error_log("Login error: " . $e->getMessage());
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>

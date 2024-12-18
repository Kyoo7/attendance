<?php
session_start();
require_once '../config/database.php';

if (isset($_COOKIE['remember_token'])) {
    // Delete the remember me token from database
    $stmt = $conn->prepare("DELETE FROM remember_me WHERE token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    
    // Delete the cookie
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

session_destroy();
header("Location: ../index.php");
exit();
?>

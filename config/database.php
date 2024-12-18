<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance_db');

try {
    // Create PDO connection
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Set timezone for database connection
    $conn->exec("SET time_zone = '+07:00'");
    
} catch(PDOException $e) {
    // Log detailed error information
    error_log("Database Connection Error Details:");
    error_log("Message: " . $e->getMessage());
    error_log("Code: " . $e->getCode());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    
    // Display user-friendly error message
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Remove the problematic table creation code
// If you need to create tables, do it manually or through a separate script

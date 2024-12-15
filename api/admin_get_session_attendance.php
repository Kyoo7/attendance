<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Get session ID from request
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if ($session_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid session ID']);
    exit();
}

try {
    // Get session details
    $stmt = $conn->prepare("
        SELECT s.*, c.course_code, c.course_name
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE s.id = ?
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
        throw new PDOException("Failed to prepare statement");
    }

    if (!$stmt->execute([$session_id])) {
        error_log("Failed to execute statement: " . print_r($stmt->errorInfo(), true));
        throw new PDOException("Failed to execute statement");
    }

    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Session not found']);
        exit();
    }

    // Get attendance records for all enrolled students
    $stmt = $conn->prepare("
        SELECT 
            u.id as student_id,
            u.full_name as name,
            u.email,
            COALESCE(a.status, 'absent') as status,
            a.time_marked as attendance_time
        FROM enrollments e
        JOIN users u ON u.id = e.student_id
        LEFT JOIN attendance a ON u.id = a.student_id AND a.session_id = ?
        WHERE e.course_id = ? AND u.role = 'student' AND e.status = 'active'
        ORDER BY u.full_name
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
        throw new PDOException("Failed to prepare statement");
    }

    if (!$stmt->execute([$session_id, $session['course_id']])) {
        error_log("Failed to execute statement: " . print_r($stmt->errorInfo(), true));
        throw new PDOException("Failed to execute statement");
    }

    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response
    $response = [
        'session' => [
            'session_name' => $session['session_name'],
            'date' => date('d M Y', strtotime($session['date'])),
            'start_time' => date('H:i', strtotime($session['start_time'])),
            'end_time' => date('H:i', strtotime($session['end_time'])),
            'room' => $session['room'],
            'status' => $session['status']
        ],
        'attendance' => array_map(function($record) {
            return [
                'name' => $record['name'],
                'email' => $record['email'],
                'status' => ucfirst($record['status']),
                'attendance_time' => $record['attendance_time'] ? date('H:i', strtotime($record['attendance_time'])) : null
            ];
        }, $attendance)
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in admin_get_session_attendance.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}

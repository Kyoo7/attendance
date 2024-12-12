<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
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
    // Get session details with course info
    $stmt = $conn->prepare("
        SELECT s.*, c.course_code, c.course_name
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    
    $stmt->execute([$session_id, $_SESSION['user_id']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Session not found']);
        exit();
    }

    // Get enrolled students and their attendance records
    $stmt = $conn->prepare("
        SELECT 
            u.id as student_id,
            u.full_name as name,
            u.email,
            COALESCE(a.status, 
                CASE 
                    WHEN ? = 'completed' THEN 'absent'
                    ELSE 'pending'
                END
            ) as status,
            a.time_marked as attendance_time
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
        WHERE e.course_id = ? AND e.status = 'active'
        ORDER BY u.full_name ASC
    ");

    $stmt->execute([$session['status'], $session_id, $session['course_id']]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response
    $response = [
        'error' => false,
        'session' => [
            'id' => $session['id'],
            'session_name' => $session['session_name'],
            'date' => date('d M Y', strtotime($session['date'])),
            'start_time' => date('h:i A', strtotime($session['start_time'])),
            'end_time' => date('h:i A', strtotime($session['end_time'])),
            'room' => $session['room'],
            'status' => $session['status'],
            'course_code' => $session['course_code'],
            'course_name' => $session['course_name']
        ],
        'attendance' => array_map(function($record) {
            return [
                'student_id' => $record['student_id'],
                'name' => $record['name'],
                'email' => $record['email'],
                'status' => $record['status'],
                'attendance_time' => $record['attendance_time'] ? date('h:i A', strtotime($record['attendance_time'])) : null
            ];
        }, $attendance)
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_session_attendance.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching attendance data'
    ]);
}

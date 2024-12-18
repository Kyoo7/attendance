<?php
// Prevent any output before JSON response
header('Content-Type: application/json');

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error handling
function handleError($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Server error: ' . $errstr,
        'details' => "$errstr in $errfile on line $errline"
    ]);
    exit;
}
set_error_handler('handleError');

// Start the session first
session_start();

// Include required files
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lecturer') {
    http_response_code(403);
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid JSON data',
        'details' => json_last_error_msg()
    ]);
    exit;
}

if (!isset($data['student_id']) || !isset($data['session_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Missing required parameters',
        'required' => ['student_id', 'session_id', 'status']
    ]);
    exit;
}

$student_id = filter_var($data['student_id'], FILTER_VALIDATE_INT);
$session_id = filter_var($data['session_id'], FILTER_VALIDATE_INT);
$status = $data['status'];
$lecturer_id = $_SESSION['user_id'];

// Validate status
if (!in_array($status, ['present', 'late', 'absent'])) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid status value',
        'allowed' => ['present', 'late', 'absent']
    ]);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if the session belongs to the lecturer's course
    $stmt = $conn->prepare("
        SELECT s.*, c.lecturer_id, c.id as course_id 
        FROM sessions s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->execute([$session_id, $lecturer_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        throw new Exception('Invalid session or unauthorized access');
    }

    // Check if attendance record exists
    $stmt = $conn->prepare("
        SELECT id, status 
        FROM attendance 
        WHERE student_id = ? AND session_id = ?
    ");
    $stmt->execute([$student_id, $session_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE attendance 
            SET status = ?, 
                marked_by = ?, 
                time_marked = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $lecturer_id, $existing['id']]);

        // Log the change if status is different
        if ($existing['status'] !== $status) {
            $stmt = $conn->prepare("
                INSERT INTO attendance_logs 
                (attendance_id, previous_status, new_status, changed_by, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$existing['id'], $existing['status'], $status, $lecturer_id]);
        }
    } else {
        // Create new record
        $stmt = $conn->prepare("
            INSERT INTO attendance 
            (student_id, session_id, course_id, status, marked_by, time_marked, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ");
        $stmt->execute([$student_id, $session_id, $session['course_id'], $status, $lecturer_id]);
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    $current_time = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    echo json_encode([
        'success' => true,
        'message' => 'Attendance marked successfully',
        'data' => [
            'student_id' => $student_id,
            'status' => $status,
            'time_marked' => $current_time->format('H:i A')
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to mark attendance: ' . $e->getMessage()
    ]);
}

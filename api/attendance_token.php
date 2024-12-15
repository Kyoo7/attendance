<?php
require_once '../config/database.php';
header('Content-Type: application/json');

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function determineAttendanceStatus($sessionStartTime) {
    $currentTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $sessionStart = new DateTime($sessionStartTime, new DateTimeZone('Asia/Jakarta'));
    
    // Calculate time boundaries
    $lateThreshold = clone $sessionStart;
    $lateThreshold->modify('+15 minutes');
    $absentThreshold = clone $sessionStart;
    $absentThreshold->modify('+30 minutes');
    
    if ($currentTime <= $lateThreshold) {
        return 'present';
    } elseif ($currentTime <= $absentThreshold) {
        return 'late';
    } else {
        return 'absent';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'generate':
                if (!isset($data['session_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Session ID is required']);
                    exit;
                }

                $sessionId = $data['session_id'];
                
                // Get course_id for the session
                try {
                    $stmt = $conn->prepare("SELECT course_id FROM sessions WHERE id = ?");
                    $stmt->execute([$sessionId]);
                    $session = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$session) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Session not found']);
                        exit;
                    }
                    
                    $courseId = $session['course_id'];
                    $token = generateToken();
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                    // Delete any existing tokens for this session
                    $stmt = $conn->prepare("DELETE FROM attendance_tokens WHERE session_id = ?");
                    $stmt->execute([$sessionId]);

                    // Create new token with course_id
                    $stmt = $conn->prepare("INSERT INTO attendance_tokens (course_id, session_id, token, expires_at) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$courseId, $sessionId, $token, $expiresAt]);

                    echo json_encode([
                        'success' => true,
                        'token' => $token,
                        'expires_at' => $expiresAt
                    ]);
                } catch (PDOException $e) {
                    http_response_code(500);
                    error_log("Database error: " . $e->getMessage());
                    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                }
                break;

            case 'mark_attendance':
                if (!isset($data['token']) || !isset($data['student_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Token and student ID are required']);
                    exit;
                }

                try {
                    // Verify token and get session
                    $stmt = $conn->prepare("
                        SELECT at.session_id, s.start_time 
                        FROM attendance_tokens at
                        JOIN sessions s ON at.session_id = s.id
                        WHERE at.token = ? AND at.expires_at > NOW()
                    ");
                    $stmt->execute([$data['token']]);
                    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$tokenData) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid or expired token']);
                        exit;
                    }

                    // Determine attendance status based on time
                    $status = determineAttendanceStatus($tokenData['start_time']);

                    // Record attendance
                    $stmt = $conn->prepare("
                        INSERT INTO attendance (session_id, student_id, status, time_marked)
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE status = ?, time_marked = NOW()
                    ");
                    $stmt->execute([
                        $tokenData['session_id'],
                        $data['student_id'],
                        $status,
                        $status
                    ]);

                    echo json_encode([
                        'success' => true,
                        'status' => $status
                    ]);
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Database error']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Action is required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

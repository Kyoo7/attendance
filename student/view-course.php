<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only student can access
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify that the student is enrolled in this course
$sql = "SELECT COUNT(*) FROM enrollments 
        WHERE student_id = ? AND course_id = ? AND status = 'active'";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    if ($stmt->fetchColumn() == 0) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error verifying enrollment: " . $e->getMessage());
    header("Location: dashboard.php");
    exit();
}

// Fetch course details
$sql = "SELECT c.*, u.full_name as lecturer_name, u.email as lecturer_email,
        (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions,
        (SELECT COUNT(*) FROM attendance a 
         JOIN sessions s ON a.session_id = s.id 
         WHERE s.course_id = c.id AND a.student_id = ? AND a.status = 'present') as attended_sessions
        FROM courses c
        JOIN users u ON c.lecturer_id = u.id
        WHERE c.id = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching course details: " . $e->getMessage());
    header("Location: dashboard.php");
    exit();
}

// Fetch attendance statistics for this course
$sql = "SELECT 
            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
            COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count
        FROM attendance a
        JOIN sessions s ON a.session_id = s.id
        WHERE s.course_id = ? AND a.student_id = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching attendance stats: " . $e->getMessage());
    $attendance_stats = ['present_count' => 0, 'absent_count' => 0, 'late_count' => 0];
}

// Calculate attendance percentage based on absences
$sql = "SELECT 
    COUNT(*) as total_sessions,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count
FROM sessions s
LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
WHERE s.course_id = ? AND s.date <= CURRENT_DATE";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    $attendance_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_sessions = $attendance_data['total_sessions'];
    $absent_count = $attendance_data['absent_count'];
    
    // Calculate percentage (start at 100% and subtract for each absence)
    $attendance_percentage = $total_sessions > 0 ? 
        max(0, 100 - (($absent_count / $total_sessions) * 100)) : 100;
    
    // Round to 1 decimal place
    $attendance_percentage = round($attendance_percentage, 1);
} catch (PDOException $e) {
    error_log("Error calculating attendance percentage: " . $e->getMessage());
    $attendance_percentage = 0;
    $total_sessions = 0;
    $absent_count = 0;
}

// Fetch all sessions for this course
$sql = "SELECT s.*, 
        COALESCE(a.status, 'Not marked') as attendance_status,
        COALESCE(a.time_marked, NULL) as time_marked
        FROM sessions s
        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
        WHERE s.course_id = ?
        ORDER BY s.date DESC, s.start_time DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $course_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching sessions: " . $e->getMessage());
    $sessions = [];
}

?>

<link rel="stylesheet" href="../css/view-course.css">

<div class="course-container">
    <!-- Back to Dashboard Link -->
    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Course Header -->
    <div class="course-header">
        <div class="course-title">
            <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
        </div>
        <div class="course-meta">
            <p><i class="fas fa-user"></i> Lecturer: <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
            <p><i class="fas fa-envelope"></i> Contact: <?php echo htmlspecialchars($course['lecturer_email']); ?></p>
            <p><i class="fas fa-calendar"></i> Duration: <?php echo date('d M Y', strtotime($course['start_date'])); ?> - <?php echo date('d M Y', strtotime($course['end_date'])); ?></p>
        </div>
    </div>

    <!-- Attendance Overview -->
    <div class="attendance-overview">
        <div class="attendance-card total">
            <h3>Total Sessions</h3>
            <p class="count"><?php echo $course['total_sessions']; ?></p>
        </div>
        <div class="attendance-card present">
            <h3>Present</h3>
            <p class="count"><?php echo $attendance_stats['present_count']; ?></p>
        </div>
        <div class="attendance-card late">
            <h3>Late</h3>
            <p class="count"><?php echo $attendance_stats['late_count']; ?></p>
        </div>
        <div class="attendance-card absent">
            <h3>Absent</h3>
            <p class="count"><?php echo $attendance_stats['absent_count']; ?></p>
        </div>
    </div>

    <!-- Attendance Progress -->
    <div class="attendance-progress">
        <h2>Attendance Progress</h2>
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress <?php echo $attendance_percentage < 90 ? 'warning' : ''; ?>" 
                     style="width: <?php echo $attendance_percentage; ?>%">
                </div>
            </div>
            <div class="progress-details">
                <span class="percentage <?php echo $attendance_percentage < 90 ? 'warning' : ''; ?>">
                    <?php echo $attendance_percentage; ?>%
                </span>
                <?php if ($attendance_percentage < 90): ?>
                    <span class="warning-text">
                        <i class="fas fa-exclamation-triangle"></i>
                        Warning: Your attendance is below 90%
                    </span>
                <?php endif; ?>
                <div class="attendance-summary">
                    <span>Total Sessions: <?php echo $total_sessions; ?></span>
                    <span>Absences: <?php echo $absent_count; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Session History -->
    <div class="session-history">
        <h2>Session History</h2>
        <div class="session-list">
            <?php if (empty($sessions)): ?>
                <p class="no-sessions">No sessions recorded for this course yet.</p>
            <?php else: ?>
                <?php foreach ($sessions as $session): ?>
                    <div class="session-item <?php echo strtolower($session['attendance_status']); ?>">
                        <div class="session-date">
                            <div class="date"><?php echo date('d M Y', strtotime($session['date'])); ?></div>
                            <div class="time"><?php echo date('h:i A', strtotime($session['start_time'])); ?> - <?php echo date('h:i A', strtotime($session['end_time'])); ?></div>
                        </div>
                        <div class="session-details">
                            <h4><?php echo htmlspecialchars($session['session_name']); ?></h4>
                            <p class="room"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($session['room']); ?></p>
                        </div>
                        <div class="attendance-status">
                            <span class="status-badge <?php echo strtolower($session['attendance_status']); ?>">
                                <?php echo $session['attendance_status']; ?>
                            </span>
                            <?php if ($session['time_marked']): ?>
                                <div class="time-marked">
                                    Marked at <?php echo date('h:i A', strtotime($session['time_marked'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

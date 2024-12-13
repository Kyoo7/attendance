<?php
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/session_update.php';

// Set timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Ensure only student can access
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch student details including profile picture
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get current date and time
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

// Force update all session statuses
forceUpdateAllSessionStatuses($conn);

// Fetch current session (if any)
$sql = "SELECT s.*, c.course_name, c.course_code, c.lecturer_id,
        COALESCE(a.status, 'Not marked') as attendance_status,
        a.time_marked,
        u.full_name as lecturer_name
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        JOIN users u ON c.lecturer_id = u.id
        JOIN enrollments e ON c.id = e.course_id
        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ?
        WHERE e.student_id = ?
        AND s.date = CURRENT_DATE
        AND s.status = 'ongoing'
        LIMIT 1";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $currentSession = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching current session: " . $e->getMessage());
    $currentSession = null;
}

// Fetch enrolled courses with their details
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions,
        (SELECT COUNT(*) FROM attendance a 
         JOIN sessions s ON a.session_id = s.id 
         WHERE s.course_id = c.id AND a.student_id = ? AND a.status = 'present') as attended_sessions,
        u.full_name as lecturer_name
        FROM courses c
        JOIN enrollments e ON c.id = e.course_id
        JOIN users u ON c.lecturer_id = u.id
        WHERE e.student_id = ? AND e.status = 'active'
        ORDER BY c.course_name";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $courses = [];
}

// Fetch upcoming sessions across all enrolled courses
$sql = "SELECT s.*, c.course_name, c.course_code 
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ? 
        AND e.status = 'active'
        AND s.status = 'scheduled'
        AND (
            (s.date = CURRENT_DATE AND s.start_time > CURRENT_TIME)
            OR s.date > CURRENT_DATE
        )
        ORDER BY s.date ASC, s.start_time ASC
        LIMIT 5";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching upcoming sessions: " . $e->getMessage());
    $upcoming_sessions = [];
}

// Get attendance statistics for the current student
$sql = "SELECT 
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
    COUNT(*) as total_sessions,
    ROUND((COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) * 100.0) / NULLIF(COUNT(*), 0), 1) as attendance_rate
FROM attendance a
JOIN sessions s ON a.session_id = s.id
WHERE a.student_id = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set default values if null
    $attendance_stats['present_count'] = $attendance_stats['present_count'] ?? 0;
    $attendance_stats['late_count'] = $attendance_stats['late_count'] ?? 0;
    $attendance_stats['absent_count'] = $attendance_stats['absent_count'] ?? 0;
    $attendance_stats['attendance_rate'] = $attendance_stats['attendance_rate'] ?? 0;

    // Debug information
    error_log("Attendance Stats for user " . $_SESSION['user_id'] . ": " . print_r($attendance_stats, true));
} catch (PDOException $e) {
    error_log("Error fetching attendance statistics: " . $e->getMessage());
    $attendance_stats = [
        'present_count' => 0,
        'late_count' => 0,
        'absent_count' => 0,
        'total_sessions' => 0,
        'attendance_rate' => 0
    ];
}

?>

<link rel="stylesheet" href="../css/student-dashboard.css">

<div class="dashboard-container">
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <p class="subtitle">Here's your academic overview</p>
        </div>
        <a href="profile.php" class="profile-card">
            <div class="profile-image">
                <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($student['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                     alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                <p class="student-id">Student ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            </div>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['attendance_rate']; ?>%</h3>
                <p>Attendance Rate</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['present_count']; ?></h3>
                <p>Present</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['late_count']; ?></h3>
                <p>Late</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['absent_count']; ?></h3>
                <p>Absent</p>
            </div>
        </div>
    </div>

    <!-- Current Session Section -->
    <?php if ($currentSession): ?>
    <div class="current-session-section">
        <h2><i class="fas fa-clock"></i> Current Session</h2>
        <div class="current-session-card">
            <div class="session-header">
                <div class="course-info">
                    <h3><?php echo htmlspecialchars($currentSession['course_name']); ?></h3>
                </div>
                <div class="attendance-badge <?php echo strtolower($currentSession['attendance_status']); ?>">
                    <?php echo $currentSession['attendance_status']; ?>
                    <?php if ($currentSession['time_marked']): ?>
                        <span class="time-marked">
                            <?php echo date('h:i A', strtotime($currentSession['time_marked'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="session-details">
                <div class="detail-item">
                    <i class="fas fa-user"></i>
                    <span>Lecturer: <?php echo htmlspecialchars($currentSession['lecturer_name']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>Time: <?php echo date('h:i A', strtotime($currentSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($currentSession['end_time'])); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Room: <?php echo htmlspecialchars($currentSession['room']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upcoming Sessions -->
    <div class="section">
        <h2><i class="fas fa-calendar-alt"></i> Upcoming Sessions</h2>
        <div class="upcoming-sessions">
            <?php if (empty($upcoming_sessions)): ?>
                <p class="no-data">No upcoming sessions scheduled.</p>
            <?php else: ?>
                <?php foreach ($upcoming_sessions as $session): ?>
                    <div class="session-card">
                        <div class="session-time">
                            <div class="date"><?php echo date('d M', strtotime($session['date'])); ?></div>
                            <div class="time"><?php echo date('h:i A', strtotime($session['start_time'])); ?></div>
                        </div>
                        <div class="session-info">
                            <h4><?php echo htmlspecialchars($session['course_code']); ?> - <?php echo htmlspecialchars($session['session_name']); ?></h4>
                            <p><?php echo htmlspecialchars($session['course_name']); ?></p>
                            <p class="room"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($session['room']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enrolled Courses -->
    <div class="section">
        <h2><i class="fas fa-graduation-cap"></i> My Courses</h2>
        <div class="courses-grid">
            <?php if (empty($courses)): ?>
                <p class="no-data">You are not enrolled in any courses yet.</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <a href="view-course.php?id=<?php echo $course['id']; ?>" class="course-card">
                        <div class="course-header">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                        </div>
                        <div class="course-info">
                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                            <p><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($course['start_date'])); ?> - <?php echo date('M d, Y', strtotime($course['end_date'])); ?></p>
                            <div class="attendance-progress">
                                <?php 
                                    $attendance_rate = $course['total_sessions'] > 0 
                                        ? ($course['attended_sessions'] / $course['total_sessions']) * 100 
                                        : 0;
                                ?>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $attendance_rate; ?>%"></div>
                                </div>
                                <span class="attendance-text">
                                    <?php echo $course['attended_sessions']; ?>/<?php echo $course['total_sessions']; ?> sessions attended
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript functionality here
});
</script>
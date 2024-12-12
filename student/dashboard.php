<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only student can access
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
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
        AND s.date >= CURDATE()
        AND (s.date > CURDATE() OR (s.date = CURDATE() AND s.end_time > CURTIME()))
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

// Get attendance statistics
$sql = "SELECT 
            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
            COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count
        FROM attendance a
        JOIN sessions s ON a.session_id = s.id
        JOIN courses c ON s.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ? AND e.status = 'active'";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching attendance stats: " . $e->getMessage());
    $attendance_stats = ['present_count' => 0, 'absent_count' => 0, 'late_count' => 0];
}
?>

<link rel="stylesheet" href="../css/student-dashboard.css">

<div class="dashboard-container">
    <div class="welcome-section">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p class="subtitle">Here's your academic overview</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($courses); ?></h3>
                <p>Enrolled Courses</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['present_count']; ?></h3>
                <p>Classes Attended</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['late_count']; ?></h3>
                <p>Late Arrivals</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $attendance_stats['absent_count']; ?></h3>
                <p>Absences</p>
            </div>
        </div>
    </div>

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
                    <div class="course-card">
                        <div class="course-header">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                        </div>
                        <div class="course-info">
                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                            <p><i class="fas fa-calendar"></i> <?php echo date('M Y', strtotime($course['start_date'])); ?> - <?php echo date('M Y', strtotime($course['end_date'])); ?></p>
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
                        <a href="view-course.php?id=<?php echo $course['id']; ?>" class="btn-view">View Details</a>
                    </div>
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
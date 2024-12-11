<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get admin info
$admin_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total students
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $stmt->execute();
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get active courses
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE status = 'active'");
    $stmt->execute();
    $activeCourses = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get total lecturers
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'");
    $stmt->execute();
    $totalLecturers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get total enrollments
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments");
    $stmt->execute();
    $totalEnrollments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get recent activities (last 5 activities)
    $stmt = $conn->prepare("
        SELECT 
            'course' as type,
            course_name as name,
            created_at,
            'added' as action
        FROM courses 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT 
            'user' as type,
            full_name as name,
            created_at,
            'joined' as action
        FROM users 
        WHERE role != 'admin' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="../css/admin-activities.css">

<div class="dashboard-container">
    <div class="admin-header">
        <div class="admin-title">
            <h1>Welcome, <?php echo htmlspecialchars($admin['full_name']); ?></h1>
            <div class="admin-info">
                <span class="admin-email">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($admin['email']); ?>
                </span>
                <span class="admin-role">
                    <i class="fas fa-user-shield"></i>
                    <?php echo ucfirst($admin['role']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Students</h3>
                <div class="stat-value"><?php echo number_format($totalStudents); ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3>Active Courses</h3>
                <div class="stat-value"><?php echo number_format($activeCourses); ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-content">
                <h3>Total Lecturers</h3>
                <div class="stat-value"><?php echo number_format($totalLecturers); ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Total Enrollments</h3>
                <div class="stat-value"><?php echo number_format($totalEnrollments); ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="section recent-activities">
        <div class="section-header">
            <h3>Recent Activities</h3>
            <a href="all-activities.php" class="btn-primary">
                <i class="fas fa-list"></i>
                View All Activities
            </a>
        </div>
        <div class="activity-list">
            <?php if (empty($recentActivities)): ?>
                <div class="no-activities">
                    <p>No recent activities found</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php echo $activity['type'] === 'course' ? '📚' : '👤'; ?>
                        </div>
                        <div class="activity-details">
                            <p>
                                <?php if ($activity['type'] === 'course'): ?>
                                    New course "<?php echo htmlspecialchars($activity['name']); ?>" was added
                                <?php else: ?>
                                    <?php echo htmlspecialchars($activity['name']); ?> joined as a new user
                                <?php endif; ?>
                            </p>
                            <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <button class="action-btn" onclick="location.href='add-user.php'">
            <span class="btn-icon">➕</span> Add New User
        </button>
        <button class="action-btn" onclick="location.href='add-course.php'">
            <span class="btn-icon">📚</span> Create Course
        </button>
        <button class="action-btn" onclick="location.href='reports.php'">
            <span class="btn-icon">📊</span> Generate Report
        </button>
    </div>
</div>

<?php
// Helper function to format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "Just now";
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($difference / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}

require_once '../includes/footer.php';
?>

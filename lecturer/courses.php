<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Fetch courses for the current lecturer
$sql = "SELECT 
        c.*,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
        (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions
        FROM courses c
        WHERE c.lecturer_id = ?
        ORDER BY c.created_at DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $courses = [];
}

// Get statistics for the lecturer
try {
    $stats = [
        'total_courses' => $conn->query("SELECT COUNT(*) FROM courses WHERE lecturer_id = " . $_SESSION['user_id'])->fetchColumn(),
        'total_students' => $conn->query("SELECT COUNT(DISTINCT e.student_id) FROM enrollments e 
                                        JOIN courses c ON e.course_id = c.id 
                                        WHERE c.lecturer_id = " . $_SESSION['user_id'])->fetchColumn(),
        'total_sessions' => $conn->query("SELECT COUNT(*) FROM sessions s 
                                        JOIN courses c ON s.course_id = c.id 
                                        WHERE c.lecturer_id = " . $_SESSION['user_id'])->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Error fetching stats: " . $e->getMessage());
    $stats = [
        'total_courses' => 0,
        'total_students' => 0,
        'total_sessions' => 0
    ];
}
?>

<link rel="stylesheet" href="../css/admin-courses.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="dashboard-container">
    <div class="page-header">
        <h2>My Courses</h2>
        <button class="btn-primary" onclick="location.href='add-course.php'">
            <i class="fas fa-plus"></i> Add New Course
        </button>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
    <div class="alert <?php echo $_SESSION['message_type']; ?>">
        <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        ?>
    </div>
    <?php endif; ?>

    <!-- Course Stats -->
    <div class="course-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book" style="color: var(--primary-color);"></i>
            </div>
            <div class="stat-info">
                <h3>My Courses</h3>
                <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users" style="color: var(--primary-color);"></i>
            </div>
            <div class="stat-info">
                <h3>Total Students</h3>
                <div class="stat-value"><?php echo $stats['total_students']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check" style="color: var(--primary-color);"></i>
            </div>
            <div class="stat-info">
                <h3>Total Sessions</h3>
                <div class="stat-value"><?php echo $stats['total_sessions']; ?></div>
            </div>
        </div>
    </div>

    <!-- Course List -->
    <div class="course-grid">
        <?php if (empty($courses)): ?>
            <div class="no-courses">
                <i class="fas fa-book"></i>
                <p>You haven't created any courses yet.</p>
                <button class="btn-primary" onclick="location.href='add-course.php'">Create Your First Course</button>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="course-card" onclick="location.href='view-course.php?id=<?php echo $course['id']; ?>'">
                    <div class="course-header">
                        <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                    </div>
                    <div class="course-info">
                        <div class="info-item">
                            <span class="label">
                                <i class="fas fa-users"></i>
                                Students
                            </span>
                            <span class="value"><?php echo $course['enrolled_students']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">
                                <i class="fas fa-book"></i>
                                Sessions
                            </span>
                            <span class="value"><?php echo $course['total_sessions']; ?></span>
                        </div>
                        <?php
                        // Calculate progress based on current date and course dates
                        $start = strtotime($course['start_date']);
                        $end = strtotime($course['end_date']);
                        $now = time();
                        $progress = 0;
                        
                        if ($now >= $end) {
                            $progress = 100;
                        } elseif ($now >= $start) {
                            $total_duration = $end - $start;
                            $elapsed = $now - $start;
                            $progress = min(100, round(($elapsed / $total_duration) * 100));
                        }
                        ?>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    <div class="course-actions" onclick="event.stopPropagation();">
                        <button class="btn-secondary" onclick="showInviteModal(<?php echo $course['id']; ?>)">
                            <i class="fas fa-share"></i> Invite Students
                        </button>
                        <button class="btn-secondary" onclick="location.href='edit-course.php?id=<?php echo $course['id']; ?>'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-danger" onclick="confirmDeleteCourse(<?php echo $course['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Invite Students</h2>
        <div class="invite-options">
            <div class="invite-link-section">
                <h3>Invite Link</h3>
                <div class="invite-link-container">
                    <input type="text" id="inviteLink" readonly>
                    <button onclick="copyInviteLink()" class="btn-secondary">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            <div class="invite-qr-section">
                <h3>QR Code</h3>
                <div id="qrCode"></div>
                <button onclick="downloadQR()" class="btn-secondary">
                    <i class="fas fa-download"></i> Download QR
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let qrCode = null;

function showInviteModal(courseId) {
    const modal = document.getElementById('inviteModal');
    const inviteLink = `${window.location.origin}/student/join-course.php?code=${courseId}`;
    
    document.getElementById('inviteLink').value = inviteLink;
    
    // Generate QR Code
    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = '';
    qrCode = new QRCode(qrContainer, {
        text: inviteLink,
        width: 200,
        height: 200
    });
    
    modal.style.display = "block";
}

function copyInviteLink() {
    const inviteLink = document.getElementById('inviteLink');
    inviteLink.select();
    document.execCommand('copy');
    alert('Invite link copied to clipboard!');
}

function downloadQR() {
    if (!qrCode) return;
    
    const canvas = document.querySelector('#qrCode canvas');
    const image = canvas.toDataURL("image/png");
    const link = document.createElement('a');
    link.download = 'course-qr.png';
    link.href = image;
    link.click();
}

function confirmDeleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course? This action cannot be undone and will remove all associated sessions and enrollments.')) {
        window.location.href = '../actions/delete-course.php?id=' + courseId;
    }
}

// Close modal when clicking the X or outside the modal
document.querySelector('.close').onclick = function() {
    document.getElementById('inviteModal').style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById('inviteModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    cursor: pointer;
}

.invite-options {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.invite-link-section, .invite-qr-section {
    flex: 1;
}

.invite-link-container {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.invite-link-container input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#qrCode {
    margin: 20px 0;
    text-align: center;
}

.course-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 15px;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.course-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s;
    cursor: pointer;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-danger:hover {
    background: #c82333;
}

.course-stats {
    text-align: center;
}

.course-body {
    text-align: center;
}

.info-item {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.info-item .label {
    font-weight: bold;
}

.info-item .value {
    font-size: 18px;
}

.progress-bar {
    width: 100%;
    height: 10px;
    background-color: #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar .progress {
    height: 100%;
    background-color: #007bff;
}
</style>
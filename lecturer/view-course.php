<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = $_GET['id'];

// Debug information
error_log("Received course_id: " . $course_id);

// Fetch course details and verify ownership
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
        (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions
        FROM courses c 
        WHERE c.id = ? AND c.lecturer_id = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_id, $_SESSION['user_id']]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug course information
    error_log("Course found: " . ($course ? 'yes' : 'no'));
    if ($course) {
        error_log("Course details: " . print_r($course, true));
    }
    
    if (!$course) {
        header("Location: courses.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching course: " . $e->getMessage());
    header("Location: courses.php");
    exit();
}

// Fetch enrolled students
$sql = "SELECT DISTINCT u.id, u.full_name, u.email, u.student_id, u.profile_picture,
        DATE_FORMAT(e.enrollment_date, '%Y-%m-%d %H:%i') as enrolled_at 
        FROM users u 
        INNER JOIN enrollments e ON u.id = e.student_id 
        WHERE e.course_id = ? 
        AND u.role = 'student'
        AND e.status = 'active'
        ORDER BY e.enrollment_date DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug information
    error_log("Course ID for students query: " . $course_id);
    error_log("Number of students found: " . count($students));
    error_log("Student data: " . print_r($students, true));
    
    // If no students found, let's check if there are any enrollments at all
    if (empty($students)) {
        $check_sql = "SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$course_id]);
        $enrollment_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
        error_log("Total enrollments found for course: " . $enrollment_count);
    }
} catch (PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    $students = [];
}

// Fetch sessions
$sql = "SELECT * FROM sessions WHERE course_id = ? ORDER BY date DESC";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching sessions: " . $e->getMessage());
    $sessions = [];
}
?>

<link rel="stylesheet" href="../css/admin-courses.css">

<div class="dashboard-container">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
        <div class="header-actions">
            <button class="btn-secondary" onclick="showInviteModal()">
                <i class="fas fa-share"></i> Invite Students
            </button>
            <button class="btn-primary" onclick="location.href='add-session.php?course_id=<?php echo $course_id; ?>'">
                <i class="fas fa-plus"></i> Add Session
            </button>
            <button class="btn-danger" onclick="confirmDeleteCourse(<?php echo $course_id; ?>)">
                <i class="fas fa-trash"></i> Delete Course
            </button>
            <a href="courses.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
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

    <!-- Course Information -->
    <div class="course-info">
        <div class="info-card">
            <h3>Course Details</h3>
            <div class="info-content">
                <p><strong>Course Code:</strong> <?php echo htmlspecialchars($course['course_code']); ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                <p><strong>Duration:</strong> <?php echo date('M d, Y', strtotime($course['start_date'])); ?> - <?php echo date('M d, Y', strtotime($course['end_date'])); ?></p>
                <p><strong>Total Sessions:</strong> <?php echo $course['total_sessions']; ?></p>
                <p><strong>Status:</strong> <span class="status-badge <?php echo $course['status']; ?>"><?php echo ucfirst($course['status']); ?></span></p>
            </div>
        </div>

        <!-- Sessions -->
        <div class="sessions-section">
            <h3>Sessions</h3>
            <?php if (empty($sessions)): ?>
                <p class="no-data">No sessions created yet.</p>
            <?php else: ?>
                <div class="sessions-list">
                    <?php foreach ($sessions as $session): ?>
                        <?php
                            $current_date = date('Y-m-d');
                            $current_time = date('H:i:s');
                            $session_date = $session['date'];
                            $session_time = $session['start_time'];
                            
                            // Determine session status
                            if ($session_date < $current_date || ($session_date == $current_date && $session_time < $current_time)) {
                                $status = 'completed';
                            } elseif ($session_date == $current_date && 
                                    strtotime($session_time) <= strtotime('+1 hour') && 
                                    strtotime($session_time) >= strtotime('-1 hour')) {
                                $status = 'ongoing';
                            } else {
                                $status = 'pending';
                            }
                        ?>
                        <div class="session-card" onclick="showSessionAttendance(<?php echo $session['id']; ?>)">
                            <div class="session-info">
                                <h4><?php echo htmlspecialchars($session['session_name']); ?></h4>
                                <p>Time: <?php echo date('h:i A', strtotime($session['start_time'])); ?> - <?php echo date('h:i A', strtotime($session['end_time'])); ?></p>
                                <p>Date: <?php echo date('M d, Y', strtotime($session['date'])); ?></p>
                                <p>Room: <?php echo htmlspecialchars($session['room']); ?></p>
                            </div>
                            <div class="session-actions">
                                <span class="session-status <?php echo $status; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Enrolled Students -->
        <div class="students-section">
            <h3>Enrolled Students (<?php echo count($students); ?>)</h3>
            <?php if (empty($students)): ?>
                <p class="no-data">No students enrolled yet.</p>
            <?php else: ?>
                <div class="students-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="student-profile">
                                            <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($student['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                                                 alt="Profile" class="profile-image">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
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
                    <input type="text" id="inviteLink" value="<?php echo sprintf('%s/student/join-course.php?code=%s', rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2), '/'), $course_id); ?>" readonly>
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

<!-- Session Attendance Modal -->
<div id="sessionAttendanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Session Attendance</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="sessionInfo"></div>
            <div id="attendanceList">
                <div class="loading">Loading attendance data...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let qrCode = null;

function showInviteModal() {
    const modal = document.getElementById('inviteModal');
    const inviteLink = document.getElementById('inviteLink').value;
    
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

function confirmDeleteSession(sessionId) {
    if (confirm('Are you sure you want to delete this session? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete-session-lecturer.php';
        
        const sessionInput = document.createElement('input');
        sessionInput.type = 'hidden';
        sessionInput.name = 'session_id';
        sessionInput.value = sessionId;

        const courseInput = document.createElement('input');
        courseInput.type = 'hidden';
        courseInput.name = 'course_id';
        courseInput.value = '<?php echo $course_id; ?>';
        
        form.appendChild(sessionInput);
        form.appendChild(courseInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmDeleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course? This action cannot be undone and will remove all associated sessions and enrollments.')) {
        window.location.href = '../actions/delete-course.php?id=' + courseId;
    }
}

// Close modal when clicking the X or outside the modal
document.querySelectorAll('.close').forEach(closeBtn => {
    closeBtn.onclick = function() {
        this.closest('.modal').style.display = "none";
    }
});

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}

async function showSessionAttendance(sessionId) {
    const modal = document.getElementById('sessionAttendanceModal');
    const sessionInfo = document.getElementById('sessionInfo');
    const attendanceList = document.getElementById('attendanceList');
    
    modal.style.display = 'block';
    attendanceList.innerHTML = '<div class="loading">Loading attendance data...</div>';
    
    try {
        const response = await fetch(`../api/get_session_attendance.php?session_id=${sessionId}`);
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.message);
        }
        
        // Update session info
        sessionInfo.innerHTML = `
            <div class="session-header">
                <h4>${data.session.session_name}</h4>
                <span class="status ${data.session.status}">${data.session.status}</span>
            </div>
            <div class="session-details">
                <p>Date: ${data.session.date}</p>
                <p>Time: ${data.session.start_time} - ${data.session.end_time}</p>
                <p>Room: ${data.session.room || 'Not specified'}</p>
            </div>
        `;
        
        // Create attendance table
        let tableHtml = `
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        data.attendance.forEach(student => {
            tableHtml += `
                <tr>
                    <td>${student.name}</td>
                    <td>${student.email}</td>
                    <td><span class="status-badge ${student.status.toLowerCase()}">${student.status}</span></td>
                    <td>${student.attendance_time || '-'}</td>
                </tr>
            `;
        });
        
        tableHtml += '</tbody></table>';
        attendanceList.innerHTML = tableHtml;
        
    } catch (error) {
        attendanceList.innerHTML = `<div class="error">Failed to load attendance data: ${error.message}</div>`;
        console.error('Error:', error);
    }
}

// Close modal when clicking the close button or outside
window.onclick = function(event) {
    const modal = document.getElementById('sessionAttendanceModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

document.querySelector('.close').onclick = function() {
    document.getElementById('sessionAttendanceModal').style.display = 'none';
}
</script>

<style>
.course-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-content {
    margin-top: 15px;
}

.info-content p {
    margin: 10px 0;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active {
    background: #e3fcef;
    color: #0d6832;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.sessions-section, .students-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sessions-list {
    display: grid;
    gap: 15px;
    margin-top: 15px;
}

.session-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: transform 0.2s;
}

.session-card:hover {
    transform: translateY(-2px);
}

.session-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.students-list table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.students-list th, .students-list td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.students-list th {
    background: #f8f9fa;
    font-weight: 500;
}

.header-actions {
    display: flex;
    gap: 10px;
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

.session-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 8px;
}

.session-status.pending {
    background: #fff3cd;
    color: #856404;
}

.session-status.ongoing {
    background: #cce5ff;
    color: #004085;
}

.session-status.completed {
    background: #d4edda;
    color: #155724;
}

.session-status.cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Modal styles from previous file */
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

.modal-lg {
    width: 90%;
    max-width: 1200px;
}

.session-info {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item label {
    font-weight: 600;
    color: #666;
    margin-right: 8px;
}

.attendance-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.attendance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.attendance-table-container {
    overflow-x: auto;
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
}

.attendance-table th,
.attendance-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.attendance-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.stats-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-right: 4px;
}

.session-card {
    cursor: pointer;
    transition: transform 0.2s;
}

.session-card:hover {
    transform: translateY(-2px);
}

.session-status {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9em;
    font-weight: 500;
}

.session-status.completed {
    background: #e9ecef;
    color: #495057;
}

.session-status.ongoing {
    background: #d4edda;
    color: #155724;
}

.session-status.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
}

.status-badge.present {
    background: #d4edda;
    color: #155724;
}

.status-badge.absent {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #666;
}

.error {
    color: #721c24;
    background: #f8d7da;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}

.student-profile {
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}
</style>

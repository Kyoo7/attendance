<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch courses with lecturer information
$sql = "SELECT 
        c.*,
        u.full_name as lecturer_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
        (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions
        FROM courses c
        LEFT JOIN users u ON c.lecturer_id = u.id
        ORDER BY c.created_at DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $courses = [];
}

// Get statistics
try {
    $stats = [
        'total_courses' => $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
        'active_lecturers' => $conn->query("SELECT COUNT(DISTINCT lecturer_id) FROM courses WHERE status = 'active'")->fetchColumn(),
        'total_students' => $conn->query("SELECT COUNT(*) FROM enrollments")->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Error fetching stats: " . $e->getMessage());
    $stats = [
        'total_courses' => 0,
        'active_lecturers' => 0,
        'total_students' => 0
    ];
}
?>

<link rel="stylesheet" href="../css/admin-courses.css">

<div class="dashboard-container">
    <div class="page-header">
        <h2>Course Management</h2>
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
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3>Total Courses</h3>
                <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-info">
                <h3>Active Lecturers</h3>
                <div class="stat-value"><?php echo $stats['active_lecturers']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Enrolled Students</h3>
                <div class="stat-value"><?php echo $stats['total_students']; ?></div>
            </div>
        </div>
    </div>

    <!-- Course List -->
    <div class="course-grid">
        <?php if (empty($courses)): ?>
        <div class="no-courses">
            <i class="fas fa-books"></i>
            <p>No courses available. Click "Add New Course" to create one.</p>
        </div>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
            <div class="course-card" onclick="location.href='course-details.php?id=<?php echo $course['id']; ?>'">
                <div class="course-header">
                    <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                    <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                </div>
                <div class="course-info">
                    <div class="info-item">
                        <span class="label">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Lecturer
                        </span>
                        <span class="value"><?php echo htmlspecialchars($course['lecturer_name'] ?? 'Not Assigned'); ?></span>
                    </div>
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
                    <span class="progress-text"><?php echo $progress; ?>% Completion</span>
                </div>
                <div class="course-actions" onclick="event.stopPropagation();">
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

<!-- Course Details Modal -->
<div id="courseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalCourseName"></h2>
        <div class="course-details-grid">
            <div class="detail-item">
                <label>Course Code:</label>
                <span id="modalCourseCode"></span>
            </div>
            <div class="detail-item">
                <label>Total Sessions:</label>
                <span id="modalTotalSessions"></span>
            </div>
            <div class="detail-item">
                <label>Start Date:</label>
                <span id="modalStartDate"></span>
            </div>
            <div class="detail-item">
                <label>End Date:</label>
                <span id="modalEndDate"></span>
            </div>
            <div class="detail-item">
                <label>Status:</label>
                <span id="modalStatus"></span>
            </div>
        </div>
        <div class="course-description">
            <h3>Description</h3>
            <p id="modalDescription"></p>
        </div>
    </div>
</div>

<script>
function deleteCourse(courseId, courseName) {
    if (confirm(`Are you sure you want to delete the course "${courseName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete-course.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'course_id';
        input.value = courseId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmDeleteCourse(courseId) {
    if (confirm("Are you sure you want to delete this course? This action cannot be undone.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete-course.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'course_id';
        input.value = courseId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Get the modal
const modal = document.getElementById('courseModal');

// Get the <span> element that closes the modal
const span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

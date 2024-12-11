<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Validate course ID
$course_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$course_id) {
    $_SESSION['message'] = "Invalid course ID.";
    $_SESSION['message_type'] = "error";
    header("Location: courses.php");
    exit();
}

try {
    // Fetch course details
    $courseStmt = $conn->prepare("
        SELECT 
            c.id, 
            c.course_name, 
            c.course_code, 
            c.description, 
            c.start_date, 
            c.end_date, 
            c.status,
            u.full_name as lecturer_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrolled_students,
            (SELECT COUNT(*) FROM sessions WHERE course_id = c.id) as total_sessions,
            (SELECT COUNT(*) FROM sessions WHERE course_id = c.id AND session_date <= CURDATE()) as completed_sessions,
            (SELECT COUNT(DISTINCT student_id) FROM attendance a 
             JOIN sessions s ON a.session_id = s.id 
             WHERE s.course_id = c.id AND a.status = 'present') as total_attendance
        FROM courses c
        LEFT JOIN users u ON c.lecturer_id = u.id
        WHERE c.id = ?
    ");
    $courseStmt->execute([$course_id]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        error_log("No course found for ID: " . $course_id);
        $_SESSION['message'] = "Course not found.";
        $_SESSION['message_type'] = "error";
        header("Location: courses.php");
        exit();
    }

    // Fetch students not already enrolled in this course
    $unenrolledStmt = $conn->prepare("
        SELECT u.id, u.full_name, u.email 
        FROM users u 
        WHERE u.role = 'student' 
        AND u.id NOT IN (
            SELECT student_id 
            FROM enrollments 
            WHERE course_id = ?
        )
    ");
    $unenrolledStmt->execute([$course_id]);
    $unenrolledStudents = $unenrolledStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch currently enrolled students
    $enrolledStmt = $conn->prepare("
        SELECT u.id, u.full_name, u.email 
        FROM users u 
        JOIN enrollments e ON u.id = e.student_id 
        WHERE e.course_id = ?
    ");
    $enrolledStmt->execute([$course_id]);
    $enrolledStudents = $enrolledStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch sessions for this course
    $sessionsStmt = $conn->prepare("
        SELECT 
            s.id, 
            COALESCE(s.session_name, CONCAT(c.course_name, ' Session')) as session_name, 
            COALESCE(s.description, 'No description') as description, 
            COALESCE(s.room, 'Unassigned') as room, 
            COALESCE(s.session_date, s.date) as session_date, 
            COALESCE(s.start_time, '09:00:00') as start_time, 
            COALESCE(s.end_time, '10:00:00') as end_time,
            (
                SELECT COUNT(*) 
                FROM attendance a 
                WHERE a.session_id = s.id AND a.status = 'present'
            ) as attendance_count
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE s.course_id = ?
        ORDER BY s.session_date DESC, s.start_time DESC
    ");
    $sessionsStmt->execute([$course_id]);
    $sessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log the full error details
    error_log("Course details error: " . $e->getMessage());
    error_log("Course ID: " . $course_id);
    error_log("Full query details: " . print_r($courseStmt, true));

    $_SESSION['message'] = "Error loading course details: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: courses.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Course Details</h2>
        <div class="header-actions">
            <a href="courses.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert <?php echo $_SESSION['message_type']; ?>">
            <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="course-details-container">
        <div class="course-info-card">
            <div class="course-header">
                <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
            </div>

            <div class="course-details-grid">
                <div class="detail-item">
                    <label>Lecturer</label>
                    <span><?php echo htmlspecialchars($course['lecturer_name'] ?? 'Not Assigned'); ?></span>
                </div>

                <div class="detail-item">
                    <label>Enrolled Students</label>
                    <span><?php echo $course['enrolled_students'] ?? 0; ?></span>
                </div>

                <div class="detail-item">
                    <label>Course Status</label>
                    <span><?php echo htmlspecialchars(ucfirst($course['status'] ?? 'Unknown')); ?></span>
                </div>

                <div class="detail-item full-width">
                    <label>Course Description</label>
                    <p><?php echo htmlspecialchars($course['description'] ?? 'No description available'); ?></p>
                </div>

                <div class="detail-item">
                    <label>Start Date</label>
                    <span><?php echo date('d M Y', strtotime($course['start_date'])); ?></span>
                </div>

                <div class="detail-item">
                    <label>End Date</label>
                    <span><?php echo date('d M Y', strtotime($course['end_date'])); ?></span>
                </div>

                <div class="detail-item">
                    <label>Total Sessions</label>
                    <span><?php echo $course['total_sessions'] ?? 0; ?></span>
                </div>

                <div class="detail-item">
                    <label>Completed Sessions</label>
                    <span><?php echo $course['completed_sessions'] ?? 0; ?></span>
                </div>

                <div class="detail-item">
                    <label>Total Attendance</label>
                    <span><?php echo $course['total_attendance'] ?? 0; ?></span>
                </div>
            </div>
        </div>

        <div class="students-section">
            <div class="section-header">
                <h4>Course Students</h4>
                <button class="btn-primary" data-toggle="modal" data-target="#addStudentModal">
                    <i class="fas fa-plus"></i> Add Student
                </button>
            </div>

            <div class="enrolled-students">
                <h5>Enrolled Students (<?php echo count($enrolledStudents); ?>)</h5>
                <?php if (empty($enrolledStudents)): ?>
                    <p>No students enrolled in this course.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrolledStudents as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <form action="../actions/remove-student.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this student from the course?');">
                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn-delete btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="sessions-section">
            <div class="section-header">
                <h4>Course Sessions</h4>
                <a href="../admin/add-session.php?course_id=<?php echo $course_id; ?>" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Session
                </a>
            </div>

            <?php if (empty($sessions)): ?>
                <div class="no-sessions-message">
                    <p>No sessions have been created for this course yet.</p>
                </div>
            <?php else: ?>
                <div class="sessions-list">
                    <?php foreach ($sessions as $session): ?>
                        <div class="session-card">
                            <div class="session-header">
                                <h5><?php echo htmlspecialchars($session['session_name']); ?></h5>
                                <div class="session-actions">
                                    <a href="../admin/edit-session.php?id=<?php echo $session['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../actions/delete-session.php" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <button type="submit" class="btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="session-details">
                                <div class="detail-row">
                                    <span class="detail-label">Date:</span>
                                    <span><?php echo date('d M Y', strtotime($session['session_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Time:</span>
                                    <span>
                                        <?php 
                                        echo date('H:i', strtotime($session['start_time'])) . 
                                             ' - ' . 
                                             date('H:i', strtotime($session['end_time'])); 
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Room:</span>
                                    <span><?php echo htmlspecialchars($session['room']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Attendance:</span>
                                    <span><?php echo $session['attendance_count']; ?> students</span>
                                </div>
                                <?php if (!empty($session['description'])): ?>
                                    <div class="detail-row full-width">
                                        <span class="detail-label">Description:</span>
                                        <p><?php echo htmlspecialchars($session['description']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add Students to Course</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../actions/add-students-to-course.php" method="POST" id="addStudentsForm">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <div class="form-group">
                        <label for="studentSelect">Select Students</label>
                        <select multiple class="form-control" id="studentSelect" name="student_ids[]" required>
                            <?php foreach ($unenrolledStudents as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Students</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this session? This action cannot be undone.');
}

$(document).ready(function() {
    $('#studentSelect').select2({
        placeholder: "Select students to add",
        allowClear: true
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

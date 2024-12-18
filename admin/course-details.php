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
        SELECT u.id, u.full_name, u.email, u.student_id 
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
        SELECT u.id, u.full_name, u.email, u.student_id, u.profile_picture 
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

<style>
    :root {
        --primary-color: #8c0055;
        --text-color: #ffffff;
        --border-color: #E2E8F0;
        --background-color: #F7FAFC;
        --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .course-details-container {
        background: white;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
        padding: 2rem;
    }

    .course-info-card {
        margin-bottom: 2rem;
    }

    .course-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .course-header h3 {
        color: white !important;
        font-size: 1.8rem;
        margin: 0 0 1rem 0;
    }

    .course-code {
        display: inline-block;
        background: var(--primary-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .course-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .detail-item {
        padding: 1rem;
        background: var(--background-color);
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-item label {
        display: block;
        color: var(--text-color);
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .detail-item span,
    .detail-item p {
        color: var(--text-color);
        font-size: 1rem;
        margin: 0;
    }

    .alert {
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
    }

    .alert.success {
        background-color: #C6F6D5;
        border: 1px solid #9AE6B4;
        color: #22543D;
    }

    .alert.error {
        background-color: #FED7D7;
        border: 1px solid #FEB2B2;
        color: #822727;
    }

    .alert.warning {
        background-color: #FEFCBF;
        border: 1px solid #FAF089;
        color: #744210;
    }

    .alert.info {
        background-color: #BEE3F8;
        border: 1px solid #90CDF4;
        color: #2A4365;
    }

    .sessions-section {
        margin-bottom: 2rem;
    }

    .sessions-section h5 {
        color: var(--text-color);
        font-size: 1.1rem;
        margin-bottom: 1rem;
        padding-left: 0.5rem;
        border-left: 3px solid var(--primary-color);
    }

    .sessions-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
</style>

<link rel="stylesheet" href="../css/admin-course-details.css">


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

            
            <div class="detail-item">
                <label>Total Sessions</label>
                <span><?php echo $course['total_sessions'] ?? 0; ?></span>
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
                <label>Completed Sessions</label>
                <span><?php echo $course['completed_sessions'] ?? 0; ?></span>
            </div>

            <div class="detail-item">
                <label>Total Attendance</label>
                <span><?php echo $course['total_attendance'] ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

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
            <?php
            $upcomingSessions = array_filter($sessions, function($session) {
                return strtotime($session['session_date']) >= strtotime(date('Y-m-d'));
            });
            $pastSessions = array_filter($sessions, function($session) {
                return strtotime($session['session_date']) < strtotime(date('Y-m-d'));
            });
            ?>

            <?php if (!empty($upcomingSessions)): ?>
                <div class="sessions-section">
                    <h5 class="mb-3">Upcoming Sessions</h5>
                    <div class="sessions-list">
                        <?php foreach ($upcomingSessions as $session): ?>
                            <div class="session-card" onclick="showSessionAttendance(<?php echo $session['id']; ?>)">
                                <div class="session-header">
                                    <h2><?php echo htmlspecialchars($session['session_name']); ?></h2>
                                </div>
                                <div class="session-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Date:</span>
                                        <span><?php echo date('d M Y', strtotime($session['session_date'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Time:</span>
                                        <span><?php echo date('H:i', strtotime($session['start_time'])) . ' - ' . date('H:i', strtotime($session['end_time'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Room:</span>
                                        <span><?php echo htmlspecialchars($session['room']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Attendance:</span>
                                        <span><?php echo $session['attendance_count']; ?> students</span>
                                    </div>
                                </div>
                                <div class="session-actions">
                                    <a href="../admin/edit-session.php?id=<?php echo $session['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-edit" onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../actions/delete-session.php" method="POST" class="d-inline" onsubmit="return confirmDelete(event)">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pastSessions)): ?>
                <div class="sessions-section">
                    <h5>Past Sessions</h5>
                    <div class="sessions-list">
                        <?php foreach ($pastSessions as $session): ?>
                            <div class="session-card" onclick="showSessionAttendance(<?php echo $session['id']; ?>)">
                                <div class="session-header">
                                    <h2><?php echo htmlspecialchars($session['session_name']); ?></h2>
                                </div>
                                <div class="session-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Date:</span>
                                        <span><?php echo date('d M Y', strtotime($session['session_date'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Time:</span>
                                        <span><?php echo date('H:i', strtotime($session['start_time'])) . ' - ' . date('H:i', strtotime($session['end_time'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Room:</span>
                                        <span><?php echo htmlspecialchars($session['room']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Attendance:</span>
                                        <span><?php echo $session['attendance_count']; ?> students</span>
                                    </div>
                                </div>
                                <div class="session-actions">
                                    <a href="../admin/edit-session.php?id=<?php echo $session['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-edit" onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="../actions/delete-session.php" method="POST" class="d-inline" onsubmit="return confirmDelete(event)">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>


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
                        <th>Profile</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolledStudents as $student): ?>
                        <tr>
                            <td>
                                <div class="student-profile">
                                    <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($student['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                                         alt="Profile" class="profile-image">
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="z-index: 1070 !important;">
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
                    
                    <!-- Search Filters -->
                    <div class="search-filters mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="studentIdFilter" class="form-control" placeholder="Search by Student ID">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="nameFilter" class="form-control" placeholder="Search by Name">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="emailFilter" class="form-control" placeholder="Search by Email">
                            </div>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover" id="studentsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" title="Select All">
                                    </th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unenrolledStudents as $student): ?>
                                    <tr class="student-row">
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="<?php echo $student['id']; ?>" class="student-checkbox">
                                        </td>
                                        <td class="student-id"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td class="student-email"><?php echo htmlspecialchars($student['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Selected Count -->
                    <div class="mt-3">
                        <span id="selectedCount" class="text-muted">0 students selected</span>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="addSelectedBtn" disabled>Add Selected Students</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Session Attendance Modal -->
<div id="sessionAttendanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Session Attendance</h3>
            <span class="close" onclick="closeAttendanceModal()">&times;</span>
        </div>
        <div id="sessionInfo"></div>
        <div id="attendanceList"></div>
    </div>
</div>

<script>
function confirmDelete(event) {
    event.stopPropagation();
    return confirm('Are you sure you want to delete this session?');
}

function closeAttendanceModal() {
    document.getElementById('sessionAttendanceModal').style.display = 'none';
}

async function showSessionAttendance(sessionId) {
    const modal = document.getElementById('sessionAttendanceModal');
    const sessionInfo = document.getElementById('sessionInfo');
    const attendanceList = document.getElementById('attendanceList');
    
    modal.style.display = 'block';
    sessionInfo.innerHTML = '';
    attendanceList.innerHTML = '<div class="loading">Loading attendance data...</div>';
    
    try {
        const response = await fetch(`../api/admin_get_session_attendance.php?session_id=${sessionId}`);
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.message || 'Failed to load attendance data');
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
        
        data.attendance.forEach(record => {
            const statusClass = record.status.toLowerCase();
            tableHtml += `
                <tr>
                    <td>${record.name}</td>
                    <td>${record.email}</td>
                    <td><span class="status-badge ${statusClass}">${record.status}</span></td>
                    <td>${record.attendance_time || '-'}</td>
                </tr>
            `;
        });
        
        tableHtml += '</tbody></table>';
        attendanceList.innerHTML = tableHtml;
        
    } catch (error) {
        console.error('Error loading attendance:', error);
        attendanceList.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error: ${error.message}</p>
                <button onclick="closeAttendanceModal()" class="btn-secondary">Close</button>
            </div>
        `;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('sessionAttendanceModal');
    if (event.target === modal) {
        closeAttendanceModal();
    }
}

document.querySelector('.close').onclick = function() {
    document.getElementById('sessionAttendanceModal').style.display = 'none';
}

// Add Student Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const studentIdFilter = document.getElementById('studentIdFilter');
    const nameFilter = document.getElementById('nameFilter');
    const emailFilter = document.getElementById('emailFilter');
    const selectAll = document.getElementById('selectAll');
    const studentRows = document.querySelectorAll('.student-row');
    const selectedCount = document.getElementById('selectedCount');
    const addSelectedBtn = document.getElementById('addSelectedBtn');

    // Filter function
    function filterStudents() {
        const idValue = studentIdFilter.value.toLowerCase();
        const nameValue = nameFilter.value.toLowerCase();
        const emailValue = emailFilter.value.toLowerCase();

        studentRows.forEach(row => {
            const studentId = row.querySelector('.student-id').textContent.toLowerCase();
            const name = row.querySelector('.student-name').textContent.toLowerCase();
            const email = row.querySelector('.student-email').textContent.toLowerCase();

            const matchesFilter = 
                studentId.includes(idValue) &&
                name.includes(nameValue) &&
                email.includes(emailValue);

            row.style.display = matchesFilter ? '' : 'none';
        });
    }

    // Update selected count and button state
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCount.textContent = `${count} student${count !== 1 ? 's' : ''} selected`;
        addSelectedBtn.disabled = count === 0;
    }

    // Event listeners for filters
    studentIdFilter.addEventListener('input', filterStudents);
    nameFilter.addEventListener('input', filterStudents);
    emailFilter.addEventListener('input', filterStudents);

    // Select all functionality
    selectAll.addEventListener('change', function() {
        const visibleCheckboxes = document.querySelectorAll('.student-row:not([style*="display: none"]) .student-checkbox');
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Individual checkbox change
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Clear filters when modal is hidden
    $('#addStudentModal').on('hidden.bs.modal', function () {
        studentIdFilter.value = '';
        nameFilter.value = '';
        emailFilter.value = '';
        filterStudents();
        selectAll.checked = false;
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

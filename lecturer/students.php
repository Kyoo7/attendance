<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Get all courses taught by the lecturer
$stmt = $conn->prepare("
    SELECT id, course_name, course_code 
    FROM courses 
    WHERE lecturer_id = ? AND status = 'active'
");
$stmt->execute([$lecturer_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected course (default to first course if none selected)
$selected_course_id = isset($_GET['course_id']) && !empty($_GET['course_id']) ? $_GET['course_id'] : 
    (!empty($courses) ? $courses[0]['id'] : null);

// Get enrolled students and their attendance statistics
$students = [];
if ($selected_course_id) {
    $stmt = $conn->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
            u.profile_picture,
            u.student_id,
            COUNT(DISTINCT s.id) as total_sessions,
            COUNT(DISTINCT CASE WHEN a.status = 'present' THEN s.id END) as present_sessions,
            COUNT(DISTINCT CASE WHEN a.status = 'late' THEN s.id END) as late_sessions,
            COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN s.id END) as absent_sessions
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        LEFT JOIN sessions s ON s.course_id = e.course_id
        LEFT JOIN attendance a ON a.session_id = s.id AND a.student_id = u.id
        WHERE e.course_id = ? AND u.role = 'student'
        GROUP BY u.id, u.full_name, u.email, u.profile_picture, u.student_id
    ");
    $stmt->execute([$selected_course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - Lecturer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/lecturer-students.css">
</head>
<body>
    <div class="content-wrapper p-6">
        <div class="page-header mb-6">
            <h2 class="text-2xl font-semibold">Student List</h2>
            <p class="text-gray-600">View and manage your students' attendance</p>
        </div>

        <!-- Course Selection -->
        <div class="course-selector mb-6">
            <form action="" method="GET" class="flex gap-4 items-center">
                <label for="course_id" class="font-medium">Select Course:</label>
                <select name="course_id" id="course_id" class="form-select rounded-lg border-gray-300" onchange="this.form.submit()">
                    <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['id']; ?>" <?php echo $selected_course_id == $course['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Student Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($students as $student): 
                // Start with 100% and deduct for absences
                $total_sessions = $student['total_sessions'] ?: 0;
                $absent_sessions = $student['absent_sessions'] ?: 0;
                
                // Each absence deducts a percentage based on total sessions
                $deduction_per_absence = $total_sessions > 0 ? (100 / $total_sessions) : 0;
                $attendance_percentage = max(0, 100 - ($absent_sessions * $deduction_per_absence));
                $attendance_percentage = round($attendance_percentage);
            ?>
            <div class="student-card" onclick="showStudentDetails(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                <div class="student-info">
                    <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($student['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                         alt="Profile" class="student-avatar">
                    <div>
                        <h3 class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                        <p class="student-id"><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></p>
                        <p class="student-email"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                <div class="attendance-stats">
                    <div class="stat">
                        <span class="stat-value <?php echo $attendance_percentage >= 75 ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo $attendance_percentage; ?>%
                        </span>
                        <span class="stat-label">Attendance</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $student['late_sessions']; ?></span>
                        <span class="stat-label">Late</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $student['absent_sessions']; ?></span>
                        <span class="stat-label">Absent</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div id="studentModal" class="modal hidden">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div class="modal-header">
                <img id="modalStudentAvatar" src="" alt="Student Profile" class="modal-avatar">
                <div>
                    <h3 id="modalStudentName"></h3>
                    <p id="modalStudentId" class="text-gray-600"></p>
                </div>
            </div>
            <div class="modal-body">
                <div class="attendance-chart">
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="detailed-stats">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let currentChart = null;

        function showStudentDetails(student) {
            const modal = document.getElementById('studentModal');
            const modalAvatar = document.getElementById('modalStudentAvatar');
            const modalName = document.getElementById('modalStudentName');
            const modalId = document.getElementById('modalStudentId');
            
            modalAvatar.src = student.profile_picture ? 
                `../uploads/profile_pictures/${student.profile_picture}` : 
                '../assets/images/default-profile.png';
            modalName.textContent = student.full_name;
            modalId.textContent = student.student_id || 'N/A';
            
            // Destroy previous chart if it exists
            if (currentChart) {
                currentChart.destroy();
            }
            
            // Create attendance chart
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Late', 'Absent'],
                    datasets: [{
                        data: [
                            student.present_sessions,
                            student.late_sessions,
                            student.absent_sessions
                        ],
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            modal.classList.remove('hidden');
            modal.classList.add('show');
        }

        // Close modal when clicking the close button or outside the modal
        document.querySelector('.close-btn').onclick = () => {
            const modal = document.getElementById('studentModal');
            modal.classList.remove('show');
            modal.classList.add('hidden');
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }
        };

        window.onclick = (event) => {
            const modal = document.getElementById('studentModal');
            if (event.target == modal) {
                modal.classList.remove('show');
                modal.classList.add('hidden');
                if (currentChart) {
                    currentChart.destroy();
                    currentChart = null;
                }
            }
        };
    </script>
</body>
</html>
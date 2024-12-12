<?php
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/session_update.php';

// Set timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Get current date and time
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

// Get lecturer ID from session
$lecturer_id = $_SESSION['user_id'];

// Fetch all courses for the lecturer
$stmt = $conn->prepare("SELECT * FROM courses WHERE lecturer_id = ? AND status = 'active'");
$stmt->execute([$lecturer_id]);
$courses = $stmt->fetchAll();

// Force update all session statuses
forceUpdateAllSessionStatuses($conn);

// Fetch current session (if any)
$stmt = $conn->prepare("
    SELECT s.*, c.course_name, c.course_code 
    FROM sessions s
    JOIN courses c ON s.course_id = c.id
    WHERE s.date = ?
    AND s.status = 'ongoing'
    AND c.lecturer_id = ?
    LIMIT 1
");
$stmt->execute([$currentDate, $lecturer_id]);
$currentSession = $stmt->fetch(PDO::FETCH_ASSOC);

// If no current session, fetch next upcoming session
if (!$currentSession) {
    $stmt = $conn->prepare("
        SELECT s.*, c.course_name, c.course_code 
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE c.lecturer_id = ? 
        AND (
            (s.date = ? AND s.start_time > ?) 
            OR s.date > ?
        )
        AND s.status = 'scheduled'
        ORDER BY s.date ASC, s.start_time ASC
        LIMIT 1
    ");
    $stmt->execute([$lecturer_id, $currentDate, $currentTime, $currentDate]);
    $upcomingSession = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch students if there's a current session
$students = [];
if ($currentSession) {
    $stmt = $conn->prepare("
        SELECT 
            u.*,
            a.status as attendance_status,
            a.time_marked
        FROM users u 
        JOIN enrollments e ON u.id = e.student_id 
        LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
        WHERE e.course_id = ? AND u.role = 'student'
        ORDER BY u.full_name ASC
    ");
    $stmt->execute([$currentSession['id'], $currentSession['course_id']]);
    $students = $stmt->fetchAll();
}

// Fetch teacher info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'lecturer'");
$stmt->execute([$lecturer_id]);
$teacher = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/lecturer-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="flex justify-between items-center">
                <h1 class="welcome-message">Welcome back, <?php echo htmlspecialchars($teacher['full_name']); ?>!</h1>
                <div class="profile-section">
                    <img src="<?php echo !empty($teacher['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($teacher['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                         alt="Profile" class="profile-image">
                    <span class="font-medium"><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                </div>
            </div>
        </header>

        <div class="p-6">
            <!-- Session Card -->
            <div class="session-card">
                <?php if ($currentSession): ?>
                    <div class="session-header">
                        <h2 class="session-title">Current Session</h2>
                        <span class="session-status status-ongoing">In Progress</span>
                    </div>
                    <div class="session-grid">
                        <div class="session-info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value"><?php echo htmlspecialchars($currentSession['course_name']); ?> (<?php echo htmlspecialchars($currentSession['course_code']); ?>)</span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Room</span>
                            <span class="info-value"><?php echo htmlspecialchars($currentSession['room']); ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Time</span>
                            <span class="info-value"><?php echo date('h:i A', strtotime($currentSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($currentSession['end_time'])); ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Session Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($currentSession['session_name']); ?></span>
                        </div>
                    </div>
                <?php elseif (isset($upcomingSession)): ?>
                    <div class="session-header">
                        <h2 class="session-title">Upcoming Session</h2>
                        <span class="session-status status-scheduled">Scheduled</span>
                    </div>
                    <div class="session-grid">
                        <div class="session-info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value"><?php echo htmlspecialchars($upcomingSession['course_name']); ?> (<?php echo htmlspecialchars($upcomingSession['course_code']); ?>)</span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Room</span>
                            <span class="info-value"><?php echo htmlspecialchars($upcomingSession['room']); ?></span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Date & Time</span>
                            <span class="info-value">
                                <?php echo date('M d, Y', strtotime($upcomingSession['date'])); ?><br>
                                <?php echo date('h:i A', strtotime($upcomingSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($upcomingSession['end_time'])); ?>
                            </span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Session Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($upcomingSession['session_name']); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <h2 class="text-2xl font-semibold text-gray-600">No Sessions Scheduled</h2>
                        <p class="text-gray-500 mt-2">There are no current or upcoming sessions scheduled.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Student List Section -->
            <?php if ($currentSession && !empty($students)): ?>
            <div class="student-list-section">
                <div class="student-list-header">
                    <h2 class="text-2xl font-semibold">Attendance List</h2>
                    <input type="search" placeholder="Search students..." class="search-input">
                </div>

                <div class="student-list">
                    <?php foreach ($students as $student): ?>
                    <div class="student-card" data-student-id="<?php echo $student['id']; ?>">
                        <div class="student-info">
                            <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($student['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                                 alt="Student" class="student-avatar">
                            <div class="student-details">
                                <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                <div class="text-meta">
                                    <div><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></div>
                                    <div><?php echo htmlspecialchars($student['email']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="attendance-status">
                            <?php if ($student['attendance_status']): ?>
                                <span class="status-badge status-<?php echo $student['attendance_status']; ?>">
                                    <?php echo ucfirst($student['attendance_status']); ?>
                                    <?php if ($student['time_marked']): ?>
                                        <span class="time-marked">
                                            <?php 
                                                $time = new DateTime($student['time_marked'], new DateTimeZone('UTC'));
                                                $time->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                                echo $time->format('h:i A'); 
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php endif; ?>
                        </div>
                        <div class="attendance-buttons">
                            <button class="attendance-btn btn-present <?php echo $student['attendance_status'] === 'present' ? 'active' : ''; ?>" 
                                    onclick="markAttendance(<?php echo $student['id']; ?>, 'present')">Present</button>
                            <button class="attendance-btn btn-late <?php echo $student['attendance_status'] === 'late' ? 'active' : ''; ?>" 
                                    onclick="markAttendance(<?php echo $student['id']; ?>, 'late')">Late</button>
                            <button class="attendance-btn btn-absent <?php echo $student['attendance_status'] === 'absent' ? 'active' : ''; ?>" 
                                    onclick="markAttendance(<?php echo $student['id']; ?>, 'absent')">Absent</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function markAttendance(studentId, status) {
            const sessionId = <?php echo $currentSession ? $currentSession['id'] : 'null'; ?>;
            if (!sessionId) {
                alert('No active session found');
                return;
            }

            // Show loading state
            const studentCard = document.querySelector(`[data-student-id="${studentId}"]`);
            if (studentCard) {
                const buttons = studentCard.querySelectorAll('.attendance-btn');
                buttons.forEach(btn => btn.disabled = true);
            }

            try {
                const response = await fetch('../api/mark_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        session_id: sessionId,
                        status: status
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to mark attendance');
                }

                if (data.success) {
                    // Update UI
                    if (studentCard) {
                        // Update status badge
                        const statusBadge = studentCard.querySelector('.attendance-status');
                        if (statusBadge) {
                            statusBadge.innerHTML = `
                                <span class="status-badge status-${status}">
                                    ${status.charAt(0).toUpperCase() + status.slice(1)}
                                    <span class="time-marked">
                                        ${data.data.time_marked}
                                    </span>
                                </span>
                            `;
                        }

                        // Update button states
                        const buttons = studentCard.querySelectorAll('.attendance-btn');
                        buttons.forEach(btn => {
                            btn.disabled = false;
                            btn.classList.remove('active');
                            if (btn.classList.contains(`btn-${status}`)) {
                                btn.classList.add('active');
                            }
                        });
                    }
                } else {
                    throw new Error(data.message || 'Failed to mark attendance');
                }
            } catch (error) {
                console.error('Error marking attendance:', error);
                alert(error.message || 'Failed to mark attendance. Please try again.');
                
                // Re-enable buttons on error
                if (studentCard) {
                    const buttons = studentCard.querySelectorAll('.attendance-btn');
                    buttons.forEach(btn => btn.disabled = false);
                }
            }
        }

        // Add search functionality
        document.querySelector('.search-input')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.student-card').forEach(card => {
                const studentName = card.querySelector('h3').textContent.toLowerCase();
                const studentId = card.querySelector('.text-meta div').textContent.toLowerCase();
                const studentEmail = card.querySelector('.text-meta div:last-child').textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || 
                    studentId.includes(searchTerm) || 
                    studentEmail.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

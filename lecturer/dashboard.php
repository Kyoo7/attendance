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
    <style>
        /* Time and Calendar Cards Styles */
        .digital-clock {
            font-family: 'Arial', sans-serif;
            text-align: center;
        }

        #digital-clock .time {
            font-size: 3.5rem;
            font-weight: bold;
            color: #4a5568;
        }

        #digital-clock .seconds {
            font-size: 2rem;
            color: #718096;
        }

        #digital-clock .period {
            font-size: 1.5rem;
            color: #4a5568;
            margin-left: 0.5rem;
        }

        .grid {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .bg-white {
            background-color: white;
        }

        .rounded-lg {
            border-radius: 0.5rem;
        }

        .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .p-6 {
            padding: 1.5rem;
        }
    </style>
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

        <!-- Time and Calendar Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <!-- Calendar Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-sm text-gray-500 mb-2"><?php echo date('F', strtotime($currentDate)); ?></div>
                <div class="text-6xl font-bold text-gray-700 mb-2"><?php echo date('d', strtotime($currentDate)); ?></div>
                <div class="text-lg text-gray-600"><?php echo date('l', strtotime($currentDate)); ?></div>
                <div class="text-sm text-gray-500 mt-2"><?php echo date('Y', strtotime($currentDate)); ?></div>
            </div>

            <!-- Time Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-sm text-gray-500 mb-2">Current Time</div>
                <div id="digital-clock" class="text-5xl font-bold text-gray-700"></div>
            </div>
        </div>

        <div class="p-6">
            <!-- Session Card -->
            <div class="session-card">
                <?php if ($currentSession): ?>
                    <div class="session-header">
                        <h2 class="session-title">Current Session</h2>
                        <span class="session-status status-ongoing mx-5">Ongoing</span>
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
                            <span class="info-label">Date & Time</span>
                            <span class="info-value">
                                <?php echo date('M d, Y', strtotime($currentSession['date'])); ?><br>
                                <?php echo date('h:i A', strtotime($currentSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($currentSession['end_time'])); ?>
                            </span>
                        </div>
                        <div class="session-info-item">
                            <span class="info-label">Session Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($currentSession['session_name']); ?></span>
                        </div>
                    </div>
                <?php elseif (!empty($upcomingSession)): ?>
                    <div class="session-header">
                        <h2 class="session-title">Upcoming Session</h2>
                        <span class="session-status status-scheduled mx-5">Scheduled</span>
                    </div>
                    <div class="session-grid mx-4">
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
                    <div class="session-header">
                        <h2 class="session-title">No Upcoming Sessions</h2>
                    </div>
                    <div class="session-grid">
                        <div class="session-info-item mx-5">
                            <p class="text-gray-600">There are no current or upcoming sessions scheduled. You can create a new session from your course pages.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Student List Section -->
            <?php if ($currentSession && !empty($students)): ?>
            <div class="student-list-section">
                <div class="student-list-header">
                    <div class="flex justify-between items-center gap-20">
                        <h2 class="text-2xl font-semibold">Attendance List</h2>
                        <div class="flex gap-5">
                        <input type="search" placeholder="Search students..." class="search-input">
                            <button id="generateQRBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Generate QR Code
                            </button>
                            
                        </div>
                    </div>
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

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Attendance QR Code</h3>
                <button id="closeQRModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="text-center">
                <canvas id="qrcode" width="200" height="200" class="mx-auto mb-4"></canvas>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Attendance Link:</p>
                    <p id="attendanceLink" class="text-sm text-blue-600 break-all"></p>
                </div>
                <div class="flex justify-center gap-2">
                    <button id="copyLink" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                        Copy Link
                    </button>
                    <button id="refreshQR" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Refresh QR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
    
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

        // QR Code Generation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const generateQRBtn = document.getElementById('generateQRBtn');
            const qrModal = document.getElementById('qrModal');
            const closeQRModal = document.getElementById('closeQRModal');
            const copyLinkBtn = document.getElementById('copyLink');
            const refreshQRBtn = document.getElementById('refreshQR');
            const attendanceLinkElem = document.getElementById('attendanceLink');
            
            let currentSessionId = <?php echo $currentSession ? $currentSession['id'] : 'null'; ?>;
            let qr = null;
            
            async function generateQRCode() {
                if (!currentSessionId) {
                    alert('No active session found');
                    return;
                }
                
                try {
                    const response = await fetch('../api/attendance_token.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'generate',
                            session_id: currentSessionId
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        const attendanceUrl = `${window.location.origin}/Attendance2/student/mark_attendance.php?token=${data.token}`;
                        
                        // Generate QR code
                        if (!qr) {
                            qr = new QRious({
                                element: document.getElementById('qrcode'),
                                size: 200,
                                level: 'H'
                            });
                        }
                        qr.value = attendanceUrl;
                        
                        // Display link
                        attendanceLinkElem.textContent = attendanceUrl;
                        
                        // Show modal
                        qrModal.classList.remove('hidden');
                        qrModal.classList.add('flex');
                    } else {
                        alert('Failed to generate QR code: ' + (data.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to generate QR code');
                }
            }
            
            // Event listeners
            generateQRBtn.addEventListener('click', generateQRCode);
            refreshQRBtn.addEventListener('click', generateQRCode);
            
            closeQRModal.addEventListener('click', () => {
                qrModal.classList.add('hidden');
                qrModal.classList.remove('flex');
            });
            
            copyLinkBtn.addEventListener('click', async () => {
                const link = attendanceLinkElem.textContent;
                try {
                    await navigator.clipboard.writeText(link);
                    alert('Link copied to clipboard!');
                } catch (err) {
                    console.error('Failed to copy:', err);
                    alert('Failed to copy link');
                }
            });
        });

        // Digital Clock Function
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const period = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = (hours % 12) || 12;
            
            document.getElementById('digital-clock').innerHTML = `
                <span class="time">${formattedHours}:${minutes}</span>
                <span class="seconds">:${seconds}</span>
                <span class="period">${period}</span>
            `;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
</body>
</html>

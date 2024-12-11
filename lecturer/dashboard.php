<?php
require_once '../includes/header.php';
require_once '../config/database.php';

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

// Fetch current session (if any)
$stmt = $conn->prepare("
    SELECT s.*, c.course_name, c.course_code 
    FROM sessions s
    JOIN courses c ON s.course_id = c.id
    WHERE s.date = ?
    AND s.status = 'ongoing'
    LIMIT 1
");
$stmt->execute([$currentDate]);
$currentSession = $stmt->fetch(PDO::FETCH_ASSOC);

// If no current session, fetch next upcoming session
if (!$currentSession) {
    $stmt = $conn->prepare("
        SELECT s.*, c.course_name, c.course_code 
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE c.lecturer_id = ? 
        AND ((s.date = ? AND s.start_time > ?) 
             OR s.date > ?)
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
        SELECT u.* FROM users u 
        JOIN enrollments e ON u.id = e.student_id 
        WHERE e.course_id = ? AND u.role = 'student'
    ");
    $stmt->execute([$currentSession['course_id']]);
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
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="p-4 flex justify-between items-center">
                <h1 class="text-xl">Hello <?php echo htmlspecialchars($teacher['full_name']); ?>, Welcome To Your Class Today!</h1>
                <div class="flex items-center gap-4">
                    <button class="p-2">🔔</button>
                    <div class="flex items-center gap-2 bg-purple-50 p-2 rounded-lg">
                        <img src="/placeholder.svg?height=40&width=40" alt="Profile" class="w-8 h-8 rounded-full">
                        <span><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                    </div>
                </div>
            </header>

            <!-- Session Information Card -->
            <div class="p-6">
                <div class="bg-white rounded-lg p-6 mb-6 shadow-sm border">
                    <?php if ($currentSession): ?>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-semibold text-purple-600">Current Session</h2>
                            <span class="px-4 py-1 bg-green-100 text-green-800 rounded-full text-sm">In Progress</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Course</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($currentSession['course_name']); ?> (<?php echo htmlspecialchars($currentSession['course_code']); ?>)</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Room</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($currentSession['room']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Time</p>
                                <p class="font-semibold"><?php echo date('h:i A', strtotime($currentSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($currentSession['end_time'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Session Name</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($currentSession['session_name']); ?></p>
                            </div>
                        </div>
                    <?php elseif (isset($upcomingSession)): ?>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-semibold text-purple-600">Upcoming Session</h2>
                            <span class="px-4 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Scheduled</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">Course</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($upcomingSession['course_name']); ?> (<?php echo htmlspecialchars($upcomingSession['course_code']); ?>)</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Room</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($upcomingSession['room']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600">Date & Time</p>
                                <p class="font-semibold">
                                    <?php echo date('M d, Y', strtotime($upcomingSession['date'])); ?><br>
                                    <?php echo date('h:i A', strtotime($upcomingSession['start_time'])); ?> - <?php echo date('h:i A', strtotime($upcomingSession['end_time'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">Session Name</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($upcomingSession['session_name']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <h2 class="text-2xl font-semibold text-gray-600">No Sessions Scheduled</h2>
                            <p class="text-gray-500 mt-2">There are no current or upcoming sessions scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student List Section -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Student List</h2>
                    <input type="search" placeholder="Search..." class="border rounded-lg px-4 py-2">
                </div>

                <!-- Student Cards -->
                <div class="space-y-4">
                    <?php foreach ($students as $student): ?>
                    <div class="bg-white rounded-lg p-4 flex justify-between items-center border">
                        <div class="flex items-center gap-4">
                            <img src="/placeholder.svg?height=80&width=80" alt="" class="w-16 h-16 rounded-lg">
                            <div>
                                <h3 class="font-semibold"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                <div class="text-sm text-gray-600">
                                    <div><?php echo htmlspecialchars($student['student_id']); ?></div>
                                    <div><?php echo htmlspecialchars($student['email']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php
                            $attendanceStatuses = ['present', 'late', 'absent'];
                            foreach ($attendanceStatuses as $status):
                            ?>
                            <span class="px-4 py-1 rounded-full text-sm <?php
                                echo match($status) {
                                    'present' => 'bg-purple-600 text-white',
                                    'late' => 'bg-yellow-400 text-black',
                                    'absent' => 'bg-red-500 text-white',
                                    default => 'bg-gray-200'
                                };
                            ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <span class="px-4 py-1 rounded-full text-sm bg-gray-200">
                                Pending
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="w-80 p-6">
            <div class="space-y-6">
                <div>
                    <div class="text-gray-500"><?php echo date('F'); ?></div>
                    <div class="text-6xl font-light text-gray-400"><?php echo date('d'); ?></div>
                    <div class="text-gray-500"><?php echo date('l'); ?></div>
                </div>

                <div>
                    <div class="text-gray-500">Current Time</div>
                    <div class="text-4xl font-light text-gray-400" id="current-time">
                        <?php echo date('h:i:s A'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>

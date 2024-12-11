<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Fetch courses
$stmt = $conn->query("SELECT * FROM courses WHERE status = 'active' LIMIT 1");
$course = $stmt->fetch();

// Fetch students for the course
$stmt = $conn->prepare("SELECT u.* FROM users u 
                       JOIN enrollments e ON u.id = e.student_id 
                       WHERE e.course_id = ? AND u.role = 'student'");
$stmt->execute([$course['id']]);
$students = $stmt->fetchAll();

// Fetch current session
$stmt = $conn->prepare("SELECT * FROM sessions WHERE course_id = ? AND date = CURDATE() LIMIT 1");
$stmt->execute([$course['id']]);
$currentSession = $stmt->fetch();

// Fetch teacher info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'lecturer'");
$stmt->execute([$course['lecturer_id']]);
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
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white p-4 flex justify-between items-center border-b">
                <h1 class="text-xl">Hello <?php echo htmlspecialchars($teacher['full_name']); ?>, Welcome To Your Class Today!</h1>
                <div class="flex items-center gap-4">
                    <button class="p-2">🔔</button>
                    <div class="flex items-center gap-2 bg-purple-50 p-2 rounded-lg">
                        <img src="/placeholder.svg?height=40&width=40" alt="Profile" class="w-8 h-8 rounded-full">
                        <span><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                    </div>
                </div>
            </header>

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
        <div class="w-80 bg-white border-l p-6">
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Class Info</h2>
                    <div class="bg-white rounded-lg p-4 border">
                        <h3 class="text-purple-600 text-lg font-semibold"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <ul class="mt-4 space-y-2">
                            <li class="text-gray-600"><?php echo htmlspecialchars($course['course_code']); ?></li>
                            <li class="text-gray-600"><?php echo htmlspecialchars($course['description']); ?></li>
                        </ul>
                    </div>
                </div>

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


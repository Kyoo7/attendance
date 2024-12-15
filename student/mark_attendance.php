<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$token = $_GET['token'] ?? null;
$message = '';
$messageType = '';
$messageTitle = '';

if ($token) {
    $data = [
        'action' => 'mark_attendance',
        'token' => $token,
        'student_id' => $_SESSION['user_id']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/Attendance2/api/attendance_token.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode === 200 && isset($result['success'])) {
        $messageTitle = 'Success!';
        $message = 'Your attendance has been marked as ' . ucfirst($result['status']);
        $messageType = 'success';
    } else {
        $error = $result['error'] ?? 'An unknown error occurred';
        
        // Handle specific error cases
        if (strpos($error, 'Invalid or expired token') !== false) {
            $messageTitle = 'Session Expired';
            $message = 'This attendance session has expired. Please ask your lecturer for a new QR code.';
            $messageType = 'warning';
        } else {
            $messageTitle = 'Error';
            $message = 'Failed to mark attendance: ' . $error;
            $messageType = 'error';
        }
    }
} else {
    $messageTitle = 'Invalid Request';
    $message = 'No attendance token provided';
    $messageType = 'error';
}

// Helper function to get the appropriate CSS classes for message types
function getMessageClasses($type) {
    switch ($type) {
        case 'success':
            return 'bg-green-100 text-green-700 border-green-400';
        case 'warning':
            return 'bg-yellow-100 text-yellow-700 border-yellow-400';
        case 'error':
            return 'bg-red-100 text-red-700 border-red-400';
        default:
            return 'bg-gray-100 text-gray-700 border-gray-400';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <h1 class="text-2xl font-semibold mb-6 text-center">Attendance Marking</h1>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-md border <?php echo getMessageClasses($messageType); ?>">
                    <h2 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($messageTitle); ?></h2>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <div class="text-center">
                <a href="dashboard.php" class="inline-block bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>

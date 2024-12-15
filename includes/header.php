<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EAMU - University Attendance System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/courses.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../css/admin-forms.css">
    <link rel="stylesheet" href="../css/admin-courses.css">
    <link rel="stylesheet" href="../css/admin-reports.css">
    <link rel="stylesheet" href="../css/admin-activities.css">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <link rel="stylesheet" href="../css/lecturer-dashboard.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap and jQuery -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../images/EAMU.png" alt="EAMU Logo" class="logo">
            </div>
            
            <nav class="nav-menu">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <div class="nav-item">
                        <a href="../admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../admin/users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../admin/courses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> Courses
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../admin/reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </div>
                <?php elseif ($_SESSION['role'] === 'lecturer'): ?>
                    <div class="nav-item">
                        <a href="../lecturer/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../lecturer/courses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> My Courses
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../lecturer/students.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-check"></i> Students
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../lecturer/profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-check"></i> Profile
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-item">
                        <a href="../student/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../student/profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-check"></i> Profile
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="nav-item">
                    <a href="../actions/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">

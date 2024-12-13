<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only student can access
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch student details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['message'] = "Error loading profile.";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }

    // Fetch student's academic information
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT e.course_id) as enrolled_courses,
            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
            ROUND(AVG(CASE WHEN a.status IN ('present', 'late') THEN 100 ELSE 0 END), 1) as attendance_rate
        FROM enrollments e
        LEFT JOIN sessions s ON e.course_id = s.course_id
        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = e.student_id
        WHERE e.student_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $academic_info = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching student details: " . $e->getMessage());
    $_SESSION['message'] = "Error loading profile. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>My Profile</h2>
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

    <div class="profile-container">
        <!-- Profile Picture Section -->
        <div class="profile-picture-section">
            <div class="profile-picture">
                <img src="<?php echo !empty($student['profile_picture']) ? '../uploads/profile_pictures/' . $student['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                     alt="Profile Picture" id="profilePreview">
            </div>
            <form action="../actions/update-profile-picture.php" method="POST" enctype="multipart/form-data" id="profilePictureForm">
                <div class="upload-btn-wrapper">
                    <button class="btn btn-secondary" type="button" onclick="document.getElementById('profilePicture').click()">
                        <i class="fas fa-camera"></i> Change Picture
                    </button>
                    <input type="file" name="profile_picture" id="profilePicture" accept="image/*" style="display: none;">
                </div>
            </form>
        </div>

        <!-- Profile Information Section -->
        <div class="profile-info-section">
            <form action="../actions/update-student-profile.php" method="POST" class="profile-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" 
                           value="<?php echo htmlspecialchars($student['student_id'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_password">Current Password (required to save changes)</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="password-change-section">
                    <h3>Change Password</h3>
                    <p class="text-muted">Leave blank if you don't want to change your password</p>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profilePicture').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
        // Automatically submit the form when a new picture is selected
        document.getElementById('profilePictureForm').submit();
    }
});

// Password validation
document.querySelector('.profile-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword || confirmPassword) {
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
        }
    }
});
</script>
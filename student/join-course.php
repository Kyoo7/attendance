<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only students can access this page
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$course = null;

// Check if a course code is provided
if (isset($_GET['code'])) {
    $course_id = (int)$_GET['code'];
    
    // Fetch course details
    $sql = "SELECT c.*, u.full_name as lecturer_name 
            FROM courses c 
            JOIN users u ON c.lecturer_id = u.id 
            WHERE c.id = ? AND c.status = 'active'";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$course) {
            $error = 'Invalid course code or the course is no longer active.';
        } else {
            // Check if student is already enrolled
            $check_sql = "SELECT id, status FROM enrollments 
                         WHERE student_id = ? AND course_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$_SESSION['user_id'], $course_id]);
            $enrollment = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($enrollment) {
                if ($enrollment['status'] === 'active') {
                    $error = 'You are already enrolled in this course.';
                } else {
                    $error = 'Your enrollment in this course is no longer active.';
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error checking course: " . $e->getMessage());
        $error = 'An error occurred while checking the course. Please try again later.';
    }
}

// Handle enrollment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_course'])) {
    $course_id = (int)$_POST['course_id'];
    
    // Verify course exists and is active
    $sql = "SELECT id FROM courses WHERE id = ? AND status = 'active'";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$course_id]);
        if (!$stmt->fetch()) {
            $error = 'Invalid course or the course is no longer active.';
        } else {
            // Check if student is already enrolled
            $check_sql = "SELECT id FROM enrollments 
                         WHERE student_id = ? AND course_id = ? AND status = 'active'";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$_SESSION['user_id'], $course_id]);
            
            if ($check_stmt->fetch()) {
                $error = 'You are already enrolled in this course.';
            } else {
                // Enroll the student
                $enroll_sql = "INSERT INTO enrollments (student_id, course_id, status) 
                              VALUES (?, ?, 'active')";
                $enroll_stmt = $conn->prepare($enroll_sql);
                $enroll_stmt->execute([$_SESSION['user_id'], $course_id]);
                
                $success = 'You have successfully enrolled in the course.';
                
                // Redirect to dashboard after successful enrollment
                header("refresh:2;url=dashboard.php");
            }
        }
    } catch (PDOException $e) {
        error_log("Error enrolling in course: " . $e->getMessage());
        $error = 'An error occurred while enrolling in the course. Please try again later.';
    }
}
?>

<link rel="stylesheet" href="../css/join-course.css">

<div class="join-course-container">
    <?php if ($error): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    <?php elseif ($success): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
        <div class="redirect-message">
            <p>Redirecting to dashboard...</p>
        </div>
    <?php elseif ($course): ?>
        <div class="course-preview">
            <h2>Join Course</h2>
            <div class="course-details">
                <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <p class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></p>
                <div class="course-info">
                    <p><i class="fas fa-user"></i> Lecturer: <?php echo htmlspecialchars($course['lecturer_name']); ?></p>
                    <p><i class="fas fa-calendar"></i> Duration: <?php echo date('M d, Y', strtotime($course['start_date'])); ?> - <?php echo date('M d, Y', strtotime($course['end_date'])); ?></p>
                    <?php if ($course['description']): ?>
                        <p><i class="fas fa-info-circle"></i> Description: <?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="join-form">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <div class="form-actions">
                        <button type="submit" name="join_course" class="btn-primary">
                            <i class="fas fa-user-plus"></i> Join Course
                        </button>
                        <a href="dashboard.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i>
            No course code provided.
        </div>
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

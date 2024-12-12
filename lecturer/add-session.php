<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Pre-select course if coming from course details page
$preSelectedCourseId = null;
if (isset($_GET['course_id'])) {
    $preSelectedCourseId = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
    
    // Verify that the course belongs to the current lecturer
    try {
        $courseStmt = $conn->prepare("SELECT course_name, course_code FROM courses WHERE id = ? AND lecturer_id = ?");
        $courseStmt->execute([$preSelectedCourseId, $_SESSION['user_id']]);
        $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$courseDetails) {
            $_SESSION['message'] = "You don't have permission to add sessions to this course.";
            $_SESSION['message_type'] = "error";
            header("Location: courses.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching course details: " . $e->getMessage());
        $_SESSION['message'] = "Error loading course details. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: courses.php");
        exit();
    }
}

// Fetch courses for the current lecturer
try {
    $stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE lecturer_id = ? ORDER BY course_name");
    $stmt->execute([$_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $_SESSION['message'] = "Error loading courses. Please try again.";
    $_SESSION['message_type'] = "error";
    $courses = [];
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Add New Session</h2>
        <a href="<?php echo $preSelectedCourseId ? 'view-course.php?id=' . $preSelectedCourseId : 'courses.php'; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to <?php echo $preSelectedCourseId ? 'Course Details' : 'Courses'; ?>
        </a>
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

    <div class="form-card">
        <form action="../actions/add-session-lecturer.php" method="POST" class="form-container">
            <?php if(isset($_GET['course_id'])): ?>
                <input type="hidden" name="from_course" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" class="form-control" required <?php echo $preSelectedCourseId ? 'disabled' : ''; ?>>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                <?php echo ($preSelectedCourseId == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($preSelectedCourseId): ?>
                    <input type="hidden" name="course_id" value="<?php echo $preSelectedCourseId; ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="session_name">Session Name</label>
                <input type="text" name="session_name" id="session_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="room">Room</label>
                <input type="text" name="room" id="room" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="session_date">Date</label>
                <input type="date" name="session_date" id="session_date" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Session</button>
                <a href="<?php echo $preSelectedCourseId ? 'view-course.php?id=' . $preSelectedCourseId : 'courses.php'; ?>" 
                   class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dateInput = document.getElementById('session_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Time validation
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function validateTimes() {
        if (startTimeInput.value && endTimeInput.value) {
            if (startTimeInput.value >= endTimeInput.value) {
                endTimeInput.setCustomValidity('End time must be after start time');
            } else {
                endTimeInput.setCustomValidity('');
            }
        }
    }

    startTimeInput.addEventListener('change', validateTimes);
    endTimeInput.addEventListener('change', validateTimes);
});
</script>

<?php include '../includes/footer.php'; ?>

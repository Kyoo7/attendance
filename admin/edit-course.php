<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$course_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$course_id) {
    header("Location: courses.php");
    exit();
}

// Fetch course details
try {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['message'] = "Course not found";
        $_SESSION['message_type'] = "error";
        header("Location: courses.php");
        exit();
    }

    // Fetch all lecturers for dropdown
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role = 'lecturer'");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching course: " . $e->getMessage());
    $_SESSION['message'] = "Error fetching course details";
    $_SESSION['message_type'] = "error";
    header("Location: courses.php");
    exit();
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h2>Edit Course</h2>
        <button class="btn-secondary" onclick="location.href='courses.php'">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="../actions/update-course.php" method="POST" class="add-course-form">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            
            <div class="form-group">
                <label for="course_code">Course Code*</label>
                <input type="text" id="course_code" name="course_code" value="<?php echo htmlspecialchars($course['course_code']); ?>" required>
            </div>

            <div class="form-group">
                <label for="course_name">Course Name*</label>
                <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="lecturer_id">Lecturer*</label>
                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">Select Lecturer</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?php echo $lecturer['id']; ?>" <?php echo ($lecturer['id'] == $course['lecturer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lecturer['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="total_sessions">Total Sessions*</label>
                <input type="number" id="total_sessions" name="total_sessions" value="<?php echo $course['total_sessions']; ?>" required min="1">
            </div>

            <div class="form-group">
                <label for="start_date">Start Date*</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $course['start_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date*</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $course['end_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status*</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo ($course['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($course['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="completed" <?php echo ($course['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Course
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.add-course-form');
    form.addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);

        if (endDate <= startDate) {
            e.preventDefault();
            alert('End date must be after start date');
            return false;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

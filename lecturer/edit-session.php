<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Get session ID
$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$session_id) {
    $_SESSION['message'] = "Invalid session ID.";
    $_SESSION['message_type'] = "error";
    header("Location: courses.php");
    exit();
}

try {
    // Fetch session details and verify ownership
    $stmt = $conn->prepare("
        SELECT s.*, c.course_name, c.course_code, c.lecturer_id 
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE s.id = ? AND c.lecturer_id = ?
    ");
    $stmt->execute([$session_id, $_SESSION['user_id']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        $_SESSION['message'] = "Session not found or you don't have permission to edit it.";
        $_SESSION['message_type'] = "error";
        header("Location: courses.php");
        exit();
    }

    // Fetch all courses for this lecturer
    $stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE lecturer_id = ? ORDER BY course_name");
    $stmt->execute([$_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching session details: " . $e->getMessage());
    $_SESSION['message'] = "Error loading session details. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: courses.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Edit Session</h2>
        <a href="view-course.php?id=<?php echo $session['course_id']; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Course
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
        <form action="../actions/update-session-lecturer.php" method="POST" class="form-container">
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">

            <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                <?php echo ($course['id'] == $session['course_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="session_name">Session Name</label>
                <input type="text" name="session_name" id="session_name" class="form-control" 
                       value="<?php echo htmlspecialchars($session['session_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3"
                          ><?php echo htmlspecialchars($session['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="room">Room</label>
                <input type="text" name="room" id="room" class="form-control" 
                       value="<?php echo htmlspecialchars($session['room']); ?>" required>
            </div>

            <div class="form-group">
                <label for="session_date">Date</label>
                <input type="date" name="session_date" id="session_date" class="form-control" 
                       value="<?php echo $session['date']; ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" 
                               value="<?php echo $session['start_time']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" 
                               value="<?php echo $session['end_time']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Session</button>
                <a href="view-course.php?id=<?php echo $session['course_id']; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today for future sessions
    const dateInput = document.getElementById('session_date');
    const sessionDate = new Date(dateInput.value);
    const today = new Date();
    
    // Only set minimum date if the session is in the future
    if (sessionDate >= today) {
        dateInput.min = today.toISOString().split('T')[0];
    }

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

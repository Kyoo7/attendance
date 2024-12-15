<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Validate course ID
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);

// If course_id is not provided, try to fetch it from the session
if (!$course_id) {
    $stmt = $conn->prepare("SELECT course_id FROM sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    $course_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course_result) {
        $course_id = $course_result['course_id'];
    } else {
        $_SESSION['message'] = "Invalid session or course not found.";
        $_SESSION['message_type'] = "error";
        header("Location: courses.php");
        exit();
    }
}

// Validate session ID
$session_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$session_id) {
    $_SESSION['message'] = "Invalid session ID.";
    $_SESSION['message_type'] = "error";
    header("Location: course-details.php?id=" . $course_id);
    exit();
}

try {
    // Fetch courses for dropdown
    $courseStmt = $conn->prepare("SELECT id, course_name, course_code FROM courses ORDER BY course_name");
    $courseStmt->execute();
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch session details
    $stmt = $conn->prepare("
        SELECT * FROM sessions 
        WHERE id = ? AND course_id = ?
    ");
    $stmt->execute([$session_id, $course_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        $_SESSION['message'] = "Session not found.";
        $_SESSION['message_type'] = "error";
        header("Location: course-details.php?id=" . $course_id);
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching session details: " . $e->getMessage());
    $_SESSION['message'] = "Error loading session details. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: course-details.php?id=" . $course_id);
    exit();
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Edit Session</h2>
        <a href="course-details.php?id=<?php echo $course_id; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Course Details
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
        <form action="../actions/update-session.php" method="POST" class="session-form">
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="course_id">Course*</label>
                    <select id="course_id" name="course_id" required class="form-control">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" 
                                <?php echo ($course['id'] == $session['course_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_name">Session Name*</label>
                    <input type="text" id="session_name" name="session_name" required 
                           class="form-control" placeholder="Enter session name"
                           value="<?php echo htmlspecialchars($session['session_name']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Session Description</label>
                    <textarea id="description" name="description" 
                              class="form-control" placeholder="Enter session description"><?php 
                                  echo htmlspecialchars($session['description']); 
                              ?></textarea>
                </div>

                <div class="form-group">
                    <label for="room">Room*</label>
                    <input type="text" id="room" name="room" required 
                           class="form-control" placeholder="Enter room number/name"
                           value="<?php echo htmlspecialchars($session['room']); ?>">
                </div>

                <div class="form-group">
                    <label for="session_date">Session Date*</label>
                    <input type="date" id="session_date" name="session_date" required 
                           class="form-control" 
                           value="<?php echo $session['session_date']; ?>"
                           min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time*</label>
                    <input type="time" id="start_time" name="start_time" required 
                           class="form-control"
                           value="<?php echo $session['start_time']; ?>">
                </div>

                <div class="form-group">
                    <label for="end_time">End Time*</label>
                    <input type="time" id="end_time" name="end_time" required 
                           class="form-control"
                           value="<?php echo $session['end_time']; ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Session
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate end time is after start time
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function validateTimes() {
        if (startTimeInput.value && endTimeInput.value) {
            if (endTimeInput.value <= startTimeInput.value) {
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

<?php require_once '../includes/footer.php'; ?>

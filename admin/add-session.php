<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only admin can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch courses for dropdown
try {
    $stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses ORDER BY course_name");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $_SESSION['message'] = "Error loading courses. Please try again.";
    $_SESSION['message_type'] = "error";
    $courses = [];
}

// Pre-select course if coming from course details page
$preSelectedCourseId = null;
if (isset($_GET['course_id'])) {
    $preSelectedCourseId = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
    
    // Fetch course details to pre-fill course name
    try {
        $courseStmt = $conn->prepare("SELECT course_name, course_code FROM courses WHERE id = ?");
        $courseStmt->execute([$preSelectedCourseId]);
        $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching course details: " . $e->getMessage());
    }
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>Add New Session</h2>
        <a href="<?php echo $preSelectedCourseId ? 'course-details.php?id=' . $preSelectedCourseId : 'sessions.php'; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to <?php echo $preSelectedCourseId ? 'Course Details' : 'Sessions'; ?>
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
        <form action="../actions/add-session.php" method="POST" class="form-container">
            <?php if(isset($_GET['course_id'])): ?>
                <input type="hidden" name="from_course" value="1">
            <?php endif; ?>

            <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" 
                                <?php echo ($preSelectedCourseId == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

            <div class="form-group">
                <label>Time Slot</label>
                <div class="time-slot-buttons">
                    <button type="button" class="btn-time-slot" data-slot="morning">8:30 AM - 11:30 AM</button>
                    <button type="button" class="btn-time-slot" data-slot="afternoon">1:30 PM - 4:30 PM</button>
                </div>
                <input type="hidden" name="time_slot" id="time_slot" required>
                <input type="hidden" name="start_time" id="start_time">
                <input type="hidden" name="end_time" id="end_time">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Session</button>
                <a href="<?php echo $preSelectedCourseId ? 'course-details.php?id=' . $preSelectedCourseId : 'sessions.php'; ?>" 
                   class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.time-slot-buttons {
    display: flex;
    gap: 10px;
    margin-top: 5px;
}

.btn-time-slot {
    flex: 1;
    padding: 10px;
    border: 2px solid #ddd;
    background: #fff;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-time-slot:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.btn-time-slot.active {
    background: #007bff;
    color: white;
    border-color: #0056b3;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dateInput = document.getElementById('session_date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Handle time slot selection
    const timeSlotButtons = document.querySelectorAll('.btn-time-slot');
    const timeSlotInput = document.getElementById('time_slot');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    timeSlotButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            timeSlotButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const slot = this.dataset.slot;
            timeSlotInput.value = slot;

            if (slot === 'morning') {
                startTimeInput.value = '08:30';
                endTimeInput.value = '11:30';
            } else if (slot === 'afternoon') {
                startTimeInput.value = '13:30';
                endTimeInput.value = '16:30';
            }
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!timeSlotInput.value) {
            e.preventDefault();
            alert('Please select a time slot');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>

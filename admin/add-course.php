<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch all lecturers
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE role = 'lecturer'");
$stmt->execute();
$lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $lecturer_id = $_POST['lecturer_id'];
    $description = trim($_POST['description']);
    $total_sessions = (int)$_POST['total_sessions'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $errors = [];
    
    // Validate input
    if (empty($course_code)) {
        $errors[] = "Course code is required";
    } elseif (strlen($course_code) > 10) {
        $errors[] = "Course code must be less than 10 characters";
    }
    
    if (empty($course_name)) {
        $errors[] = "Course name is required";
    }
    
    if (empty($lecturer_id)) {
        $errors[] = "Please select a lecturer";
    }
    
    if ($total_sessions < 1) {
        $errors[] = "Total sessions must be at least 1";
    }
    
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Start and end dates are required";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "End date must be after start date";
    }
    
    // Check if course code already exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
    $stmt->execute([$course_code]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Course code already exists";
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO courses (course_code, course_name, lecturer_id, description, 
                    total_sessions, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $course_code,
                $course_name,
                $lecturer_id,
                $description,
                $total_sessions,
                $start_date,
                $end_date
            ]);
            
            $_SESSION['message'] = "Course added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: courses.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Course creation error: " . $e->getMessage());
        }
    }
}
?>

<link rel="stylesheet" href="../css/admin-courses.css">

<div class="content-wrapper">
    <div class="page-header">
        <h2>Add New Course</h2>
        <a href="courses.php" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert error">
        <i class="fas fa-exclamation-circle"></i>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" class="add-course-form">
            <div class="form-group">
                <label for="course_code">Course Code*</label>
                <input type="text" id="course_code" name="course_code" 
                       value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>"
                       maxlength="10" required>
                <small>Maximum 10 characters</small>
            </div>

            <div class="form-group">
                <label for="course_name">Course Name*</label>
                <input type="text" id="course_name" name="course_name"
                       value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="lecturer_id">Lecturer*</label>
                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">Select Lecturer</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?php echo $lecturer['id']; ?>"
                                <?php echo (isset($_POST['lecturer_id']) && $_POST['lecturer_id'] == $lecturer['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lecturer['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Course Description</label>
                <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="total_sessions">Total Sessions*</label>
                    <input type="number" id="total_sessions" name="total_sessions" 
                           value="<?php echo isset($_POST['total_sessions']) ? htmlspecialchars($_POST['total_sessions']) : '10'; ?>"
                           min="1" required>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date*</label>
                    <input type="date" id="start_date" name="start_date"
                           value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date*</label>
                    <input type="date" id="end_date" name="end_date"
                           value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="window.location.href='courses.php'">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Create Course
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for start_date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').min = today;
    
    // Update end_date minimum when start_date changes
    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
        if (document.getElementById('end_date').value < this.value) {
            document.getElementById('end_date').value = this.value;
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

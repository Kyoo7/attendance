<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = $_GET['id'];

// Fetch course and verify ownership
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND lecturer_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    
    $errors = [];
    
    // Validate input
    if (empty($course_name)) {
        $errors[] = "Course name is required";
    }
    
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Start and end dates are required";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "End date must be after start date";
    }
    
    if (empty($errors)) {
        try {
            $sql = "UPDATE courses SET 
                    course_name = ?, 
                    description = ?, 
                    start_date = ?, 
                    end_date = ?,
                    status = ?
                    WHERE id = ? AND lecturer_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $course_name,
                $description,
                $start_date,
                $end_date,
                $status,
                $course_id,
                $_SESSION['user_id']
            ]);
            
            $_SESSION['message'] = "Course updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: view-course.php?id=" . $course_id);
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Course update error: " . $e->getMessage());
        }
    }
}
?>

<link rel="stylesheet" href="../css/admin-courses.css">

<div class="content-wrapper">
    <div class="page-header">
        <h2>Edit Course</h2>
        <a href="view-course.php?id=<?php echo $course_id; ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Course
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
        <form method="POST" class="course-form">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" value="<?php echo htmlspecialchars($course['course_code']); ?>" readonly disabled>
                <small>Course code cannot be changed</small>
            </div>

            <div class="form-group">
                <label for="course_name">Course Name*</label>
                <input type="text" id="course_name" name="course_name" 
                       value="<?php echo htmlspecialchars($course['course_name']); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="start_date">Start Date*</label>
                <input type="date" id="start_date" name="start_date" 
                       value="<?php echo htmlspecialchars($course['start_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date*</label>
                <input type="date" id="end_date" name="end_date" 
                       value="<?php echo htmlspecialchars($course['end_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status*</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo $course['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $course['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="view-course.php?id=<?php echo $course_id; ?>" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 20px auto;
}

.course-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

label {
    font-weight: 500;
    color: #333;
}

input, textarea, select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

input:disabled {
    background: #f8f9fa;
    cursor: not-allowed;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 2px rgba(74,144,226,0.2);
}

small {
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-primary, .btn-secondary {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.btn-primary {
    background: #4a90e2;
    color: white;
    border: none;
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-primary:hover {
    background: #357abd;
}

.btn-secondary:hover {
    background: #e9ecef;
}
</style>

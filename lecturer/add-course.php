<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_sessions = $_POST['total_sessions'];
    
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
                    start_date, end_date, total_sessions) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $course_code,
                $course_name,
                $_SESSION['user_id'], // Current lecturer's ID
                $description,
                $start_date,
                $end_date,
                $total_sessions
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
        <form method="POST" class="course-form">
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
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <input type="hidden" name="total_sessions" value="0">

            <div class="form-row">
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
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Create Course
                </button>
                <a href="courses.php" class="btn-secondary">Cancel</a>
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

input, textarea {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

input:focus, textarea:focus {
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

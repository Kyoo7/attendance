<?php
require_once '../includes/header.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="welcome-header">
        <h1>Add New User</h1>
        <div class="user-meta">
            <span><?php echo $_SESSION['email']; ?></span>
            <span class="badge badge-admin">Admin</span>
        </div>
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
        <form action="../actions/add-user.php" method="POST" class="user-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Full Name*</label>
                    <input type="text" id="full_name" name="full_name" required 
                           class="form-control" placeholder="Enter full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address*</label>
                    <input type="email" id="email" name="email" required 
                           class="form-control" placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label for="role">Role*</label>
                    <select id="role" name="role" required class="form-control">
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password*</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required 
                               class="form-control" placeholder="Enter password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Role-specific fields -->
            <div class="form-grid student-fields" style="display: none;">
                <div class="form-group">
                    <label for="student_id">Student ID*</label>
                    <input type="text" id="student_id" name="student_id" 
                           class="form-control" placeholder="Enter student ID">
                    <small class="form-text">Required for student accounts</small>
                </div>
            </div>

            <div class="form-grid lecturer-fields" style="display: none;">
                <div class="form-group">
                    <label for="department">Department*</label>
                    <input type="text" id="department" name="department" 
                           class="form-control" placeholder="Enter department">
                    <small class="form-text">Required for lecturer accounts</small>
                </div>
            </div>

            <div class="form-actions">
                <a href="users.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="reset" class="btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const studentFields = document.querySelector('.student-fields');
    const lecturerFields = document.querySelector('.lecturer-fields');
    const studentIdInput = document.getElementById('student_id');
    const departmentInput = document.getElementById('department');
    
    // Hide all role-specific fields first
    studentFields.style.display = 'none';
    lecturerFields.style.display = 'none';
    
    // Remove required attribute from all role-specific fields
    studentIdInput.removeAttribute('required');
    departmentInput.removeAttribute('required');
    
    // Show and make required the relevant fields based on selected role
    if (this.value === 'student') {
        studentFields.style.display = 'block';
        studentIdInput.setAttribute('required', 'required');
    } else if (this.value === 'lecturer') {
        lecturerFields.style.display = 'block';
        departmentInput.setAttribute('required', 'required');
    }
});

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const role = document.getElementById('role').value;
    const studentId = document.getElementById('student_id').value;
    const department = document.getElementById('department').value;
    
    if (role === 'student' && !studentId) {
        e.preventDefault();
        alert('Student ID is required for student accounts');
    } else if (role === 'lecturer' && !department) {
        e.preventDefault();
        alert('Department is required for lecturer accounts');
    }
});

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Ensure only lecturer can access
if ($_SESSION['role'] !== 'lecturer') {
    header("Location: ../index.php");
    exit();
}

// Fetch lecturer details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'lecturer'");
    $stmt->execute([$_SESSION['user_id']]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lecturer) {
        $_SESSION['message'] = "Error loading profile.";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching lecturer details: " . $e->getMessage());
    $_SESSION['message'] = "Error loading profile. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="page-header">
        <h2>My Profile</h2>
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

    <div class="profile-container">
        <!-- Profile Picture Section -->
        <div class="profile-picture-section">
            <div class="profile-picture">
                <img src="<?php echo !empty($lecturer['profile_picture']) ? '../uploads/profile_pictures/' . $lecturer['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                     alt="Profile Picture" id="profilePreview">
            </div>
            <form action="../actions/update-profile-picture.php" method="POST" enctype="multipart/form-data" id="profilePictureForm">
                <div class="upload-btn-wrapper">
                    <button class="btn btn-secondary" type="button" onclick="document.getElementById('profilePicture').click()">
                        <i class="fas fa-camera"></i> Change Picture
                    </button>
                    <input type="file" name="profile_picture" id="profilePicture" accept="image/*" style="display: none;">
                </div>
            </form>
        </div>

        <!-- Profile Information Section -->
        <div class="profile-info-section">
            <form action="../actions/update-lecturer-profile.php" method="POST" class="profile-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($lecturer['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($lecturer['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" class="form-control" 
                           value="<?php echo htmlspecialchars($lecturer['department']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_password">Current Password (required to save changes)</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="password-change-section">
                    <h3>Change Password</h3>
                    <p class="text-muted">Leave blank if you don't want to change your password</p>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-picture-section {
    text-align: center;
}

.profile-picture {
    width: 200px;
    height: 200px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e0e0e0;
}

.profile-picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-btn-wrapper {
    margin-top: 1rem;
}

.profile-info-section {
    padding: 1rem;
}

.password-change-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e0e0e0;
}

.password-change-section h3 {
    margin-bottom: 1rem;
    color: #333;
}

.text-muted {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.form-actions {
    margin-top: 2rem;
}
</style>

<script>
// Preview profile picture before upload
document.getElementById('profilePicture').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
        
        // Automatically submit the form when a file is selected
        document.getElementById('profilePictureForm').submit();
    }
});

// Password validation
document.querySelector('.profile-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword || confirmPassword) {
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('New password must be at least 6 characters long!');
            return;
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
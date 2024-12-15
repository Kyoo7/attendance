<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$user_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$user_id) {
    header("Location: users.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['message'] = "User not found";
        $_SESSION['message_type'] = "error";
        header("Location: users.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user: " . $e->getMessage());
    $_SESSION['message'] = "Error fetching user details";
    $_SESSION['message_type'] = "error";
    header("Location: users.php");
    exit();
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h2>Edit User</h2>
        <button class="btn-secondary" onclick="location.href='users.php'">
            <i class="fas fa-arrow-left"></i> Back to Users
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
        <form action="../actions/update-user.php" method="POST" class="add-form">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name*</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Role*</label>
                <select id="role" name="role" required>
                    <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                    <option value="lecturer" <?php echo ($user['role'] == 'lecturer') ? 'selected' : ''; ?>>Lecturer</option>
                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status*</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo (isset($user['status']) && $user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($user['status']) && $user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="new_password">New Password (leave blank to keep current)</label>
                <input type="password" id="new_password" name="new_password" minlength="6">
                <small class="form-text">Minimum 6 characters. Leave empty if you don't want to change the password.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

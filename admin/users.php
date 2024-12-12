<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get users based on filter with rate limiting
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Add rate limiting check
$last_request_time = isset($_SESSION['last_request_time']) ? $_SESSION['last_request_time'] : 0;
$current_time = time();
$time_difference = $current_time - $last_request_time;

// Set rate limit to 1 request per second
if ($time_difference < 1) {
    $_SESSION['message'] = "Please wait a moment before making another request.";
    $_SESSION['message_type'] = "error";
    header("Location: users.php");
    exit();
}

// Update last request time
$_SESSION['last_request_time'] = $current_time;

// Prepare the SQL query with LIMIT to prevent overload
$sql = "SELECT * FROM users WHERE 1=1";
if ($role_filter !== 'all') {
    $sql .= " AND role = :role";
}
if (!empty($search)) {
    $sql .= " AND (full_name LIKE :search OR email LIKE :search OR id LIKE :search)";
}
$sql .= " ORDER BY id ASC LIMIT 50"; // Add limit to prevent excessive results

try {
    $stmt = $conn->prepare($sql);
    if ($role_filter !== 'all') {
        $stmt->bindValue(':role', $role_filter);
    }
    if (!empty($search)) {
        $search_term = "%$search%";
        $stmt->bindValue(':search', $search_term);
    }
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "An error occurred while fetching users. Please try again.";
    $_SESSION['message_type'] = "error";
    error_log("Database error: " . $e->getMessage());
    $users = [];
}
?>

<link rel="stylesheet" href="../css/admin-users.css">

<div class="content-wrapper">
    <div class="welcome-header">
        <div class="welcome-info">
            <h1>Welcome, System Administrator</h1>
            <p class="welcome-subtitle">Manage your users and their access levels</p>
        </div>
        <div class="user-meta">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <span class="badge badge-admin">Admin</span>
        </div>
    </div>

    <div class="section-header">
        <h2>User Management</h2>
        <a href="add-user.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
    <div class="alert <?php echo $_SESSION['message_type']; ?>">
        <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        ?>
    </div>
    <?php endif; ?>

    <div class="filter-section">
        <div class="role-filters">
            <a href="?role=all<?php echo !empty($search) ? '&search='.$search : ''; ?>" 
               class="filter-btn <?php echo $role_filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> All Users
            </a>
            <a href="?role=student<?php echo !empty($search) ? '&search='.$search : ''; ?>" 
               class="filter-btn <?php echo $role_filter === 'student' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a href="?role=lecturer<?php echo !empty($search) ? '&search='.$search : ''; ?>" 
               class="filter-btn <?php echo $role_filter === 'lecturer' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> Lecturers
            </a>
        </div>
        <div class="search-box">
            <form action="" method="GET">
                <?php if($role_filter !== 'all'): ?>
                <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search by name, email, or ID..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($users)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <p>No users found matching your criteria</p>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td class="profile-cell">
                        <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : '../assets/images/default-profile.png'; ?>" 
                             alt="Profile" class="profile-img">
                    </td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo isset($user['status']) ? $user['status'] : 'active'; ?>">
                            <i class="fas fa-circle"></i> <?php echo isset($user['status']) ? ucfirst($user['status']) : 'Active'; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <button class="action-btn edit" onclick="location.href='edit-user.php?id=<?php echo $user['id']; ?>'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="pagination">
        <a href="#" class="page-btn prev" title="Previous Page">
            <i class="fas fa-chevron-left"></i>
        </a>
        <a href="#" class="page-btn active">1</a>
        <a href="#" class="page-btn">2</a>
        <a href="#" class="page-btn">3</a>
        <span class="page-dots">...</span>
        <a href="#" class="page-btn">10</a>
        <a href="#" class="page-btn next" title="Next Page">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
</div>

<script>
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../actions/delete-user.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_id';
        input.value = userId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

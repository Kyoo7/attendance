<?php
require_once '../includes/header.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Initialize variables
$activities = [];
$total_pages = 1;
$total_records = 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$limit = 20; // items per page
$offset = ($page - 1) * $limit;

try {
    // Check if activities table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'activities'");
    if (!$stmt->fetch()) {
        throw new PDOException("Activities table does not exist");
    }

    // Get total count of activities
    $stmt = $conn->query("SELECT COUNT(*) FROM activities");
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Get activities with user details
    $query = "
        SELECT 
            a.*,
            u.full_name as user_name,
            u.role as user_role
        FROM activities a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($activities)) {
        $_SESSION['message'] = "No activities found.";
        $_SESSION['message_type'] = "info";
    }

} catch (PDOException $e) {
    error_log("Error fetching activities: " . $e->getMessage());
    $_SESSION['message'] = "Error fetching activities. Please make sure the activities table exists.";
    $_SESSION['message_type'] = "error";
}

// Helper function to format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h2>All Activities</h2>
        <button class="btn-secondary" onclick="location.href='dashboard.php'">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
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

    <?php if (empty($activities)): ?>
        <div class="no-activities">
            <p>No activities found.</p>
        </div>
    <?php else: ?>
        <div class="activities-list">
            <?php foreach ($activities as $activity): ?>
                <div class="activity-item" data-type="<?php echo htmlspecialchars($activity['action_type']); ?>">
                    <div class="activity-icon">
                        <?php if ($activity['entity_type'] === 'user'): ?>
                            <i class="fas fa-user"></i>
                        <?php elseif ($activity['entity_type'] === 'course'): ?>
                            <i class="fas fa-book"></i>
                        <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-header">
                            <span class="activity-user"><?php echo htmlspecialchars($activity['user_name'] ?? 'Unknown User'); ?></span>
                            <span class="activity-role">(<?php echo htmlspecialchars($activity['user_role'] ?? 'Unknown Role'); ?>)</span>
                        </div>
                        <div class="activity-details">
                            <?php echo htmlspecialchars($activity['description'] ?? 'No description available'); ?>
                        </div>
                        <div class="activity-meta">
                            <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                            <span class="activity-type" data-type="<?php echo htmlspecialchars($activity['action_type']); ?>">
                                <?php echo ucfirst($activity['action_type'] ?? 'unknown'); ?> 
                                <?php echo $activity['entity_type'] ?? 'item'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>" class="page-link">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php
                $start = max(1, min($page - 2, $total_pages - 4));
                $end = min($total_pages, max(5, $page + 2));
                
                if ($start > 1): ?>
                    <a href="?page=1" class="page-link">1</a>
                    <?php if ($start > 2): ?>
                        <span class="page-link dots">...</span>
                    <?php endif;
                endif;

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endif;
                endfor;

                if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <span class="page-link dots">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?>" class="page-link"><?php echo $total_pages; ?></a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>" class="page-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

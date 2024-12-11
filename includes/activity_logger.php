<?php
function logActivity($conn, $userId, $actionType, $entityType, $entityId, $entityName, $description = '') {
    try {
        $stmt = $conn->prepare("
            INSERT INTO activities (user_id, action_type, entity_type, entity_id, entity_name, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $actionType, $entityType, $entityId, $entityName, $description]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}
?>

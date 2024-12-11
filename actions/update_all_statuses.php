<?php
require_once '../config/database.php';
require_once '../includes/session_update.php';

try {
    forceUpdateAllSessionStatuses($conn);
    echo "All session statuses have been updated successfully!";
} catch (Exception $e) {
    echo "Error updating session statuses: " . $e->getMessage();
}

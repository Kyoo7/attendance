<?php
require_once '../config/database.php';

function updateSessionStatus($session) {
    date_default_timezone_set('Asia/Bangkok'); // Set to your timezone
    $current_datetime = new DateTime('now');
    
    // Convert session times to DateTime objects
    $session_date = $session['date'];
    $session_start = new DateTime($session_date . ' ' . $session['start_time']);
    $session_end = new DateTime($session_date . ' ' . $session['end_time']);
    
    // Past session (either different day or same day but past end time)
    if ($current_datetime->format('Y-m-d') > $session_date || 
        ($current_datetime->format('Y-m-d') == $session_date && $current_datetime > $session_end)) {
        return 'completed';
    }
    
    // Current ongoing session
    if ($current_datetime->format('Y-m-d') == $session_date && 
        $current_datetime >= $session_start && 
        $current_datetime <= $session_end) {
        return 'ongoing';
    }
    
    // Future session or not started yet
    return 'scheduled';
}

function getSessionWithStatus($conn, $session_id) {
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        $new_status = updateSessionStatus($session);
        if ($new_status !== $session['status']) {
            // Update the status in database
            $update_stmt = $conn->prepare("UPDATE sessions SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $session_id]);
            $session['status'] = $new_status;
        }
    }
    
    return $session;
}

function getAllSessionsWithStatus($conn) {
    $stmt = $conn->query("SELECT * FROM sessions WHERE status != 'cancelled'");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sessions as &$session) {
        $new_status = updateSessionStatus($session);
        if ($new_status !== $session['status']) {
            // Update the status in database
            $update_stmt = $conn->prepare("UPDATE sessions SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $session['id']]);
            $session['status'] = $new_status;
        }
    }
    
    return $sessions;
}

// Function to update status for a specific session
function updateSpecificSessionStatus($conn, $session_id) {
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        $new_status = updateSessionStatus($session);
        if ($new_status !== $session['status']) {
            $update_stmt = $conn->prepare("UPDATE sessions SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $session_id]);
            return $new_status;
        }
        return $session['status'];
    }
    return null;
}

// Add a new function to force update all session statuses
function forceUpdateAllSessionStatuses($conn) {
    $stmt = $conn->query("SELECT * FROM sessions WHERE status != 'cancelled'");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sessions as $session) {
        $new_status = updateSessionStatus($session);
        if ($new_status !== $session['status']) {
            $update_stmt = $conn->prepare("UPDATE sessions SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $session['id']]);
        }
    }
}
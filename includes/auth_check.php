<?php
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Verify the remember me token
    $stmt = $conn->prepare("
        SELECT u.* 
        FROM users u 
        JOIN remember_me r ON u.id = r.user_id 
        WHERE r.token = ? AND r.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Log the user in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Refresh the remember me token
        $new_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Update the token in database
        $stmt = $conn->prepare("
            UPDATE remember_me 
            SET token = ?, expires_at = ? 
            WHERE user_id = ? AND token = ?
        ");
        $stmt->execute([$new_token, $expires, $user['id'], $token]);
        
        // Update the cookie
        setcookie('remember_token', $new_token, [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}
?>
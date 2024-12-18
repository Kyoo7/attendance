<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define allowed IP ranges (update these with your school's actual IP ranges)
$school_ip_ranges = [
    '172.16.70.0/23',    // Common local network range, replace with your school's range
    '127.0.0.1',         // Allow localhost for development
    '::1'                // Allow localhost IPv6
];

function ip_in_range($ip, $range) {
    if (strpos($range, '/') !== false) {
        // IP range in CIDR notation
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    } else {
        // Single IP address
        return $ip === $range;
    }
}

// Get client's IP address
$client_ip = $_SERVER['REMOTE_ADDR'];

// Check if client IP is in allowed ranges
$allowed = false;
foreach ($school_ip_ranges as $range) {
    if (ip_in_range($client_ip, $range)) {
        $allowed = true;
        break;
    }
}

// If not allowed and not already on the error page, redirect
if (!$allowed && !strpos($_SERVER['PHP_SELF'], 'access_denied.php')) {
    http_response_code(403);
    header('Location: /Attendance2/access_denied.php');
    exit();
}
?>
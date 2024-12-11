<?php
require_once '../config/database.php';

function getOverallStatistics($startDate = null, $endDate = null) {
    global $conn;
    
    // Log input parameters for debugging
    error_log("getOverallStatistics called with parameters: " . 
        "startDate = " . ($startDate ? $startDate : 'NULL') . 
        ", endDate = " . ($endDate ? $endDate : 'NULL')
    );

    $stats = [
        'total_students' => 0,
        'total_sessions' => 0,
        'avg_attendance' => 0,
        'monthly_attendance' => [],
        'course_data' => []
    ];

    try {
        // Validate date range if provided
        if ($startDate && $endDate) {
            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            if ($startTimestamp === false || $endTimestamp === false) {
                error_log("Invalid date format: startDate = $startDate, endDate = $endDate");
                return $stats;
            }
            
            if ($startTimestamp > $endTimestamp) {
                error_log("Invalid date range: Start date is after end date");
                return $stats;
            }
        }

        // Detailed database connection check
        if (!$conn) {
            error_log("Database connection is null");
            return $stats;
        }

        // Get total students
        $totalStudentsQuery = "SELECT COUNT(*) as count FROM users WHERE LOWER(role) = 'student'";
        $stmt = $conn->prepare($totalStudentsQuery);
        
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_students'] = (int)$row['count'];
            error_log("Total students found: " . $stats['total_students']);
        } else {
            error_log("Failed to execute total students query: " . print_r($stmt->errorInfo(), true));
        }

        // Get total sessions
        $sessionsQuery = "SELECT COUNT(*) as count FROM sessions";
        $stmt = $conn->prepare($sessionsQuery);
        
        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_sessions'] = $row['count'];
            error_log("Total sessions found: " . $stats['total_sessions']);
        } else {
            error_log("Failed to execute total sessions query: " . print_r($stmt->errorInfo(), true));
        }

        // Calculate average attendance with optional date filtering
        $attendanceQuery = "
            SELECT AVG(
                CASE WHEN status = 'present' THEN 1 ELSE 0 END
            ) * 100 as avg_attendance
            FROM attendance
        ";
        $params = [];
        
        if ($startDate && $endDate) {
            $attendanceQuery .= " WHERE date BETWEEN :start_date AND :end_date";
            $params = [
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];
        }
        
        $stmt = $conn->prepare($attendanceQuery);
        
        if ($stmt->execute($params)) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['avg_attendance'] = $row['avg_attendance'] ? round($row['avg_attendance'], 1) : 0;
            error_log("Average attendance calculated: " . $stats['avg_attendance']);
        } else {
            error_log("Failed to execute average attendance query: " . print_r($stmt->errorInfo(), true));
        }

        // Get monthly attendance data
        $monthlyQuery = "
            SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_rate
            FROM attendance
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 6
        ";
        $stmt = $conn->query($monthlyQuery);
        $stats['monthly_attendance'] = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        error_log("Monthly attendance data count: " . count($stats['monthly_attendance']));

        // Get course performance data
        $courseQuery = "
            SELECT 
                c.id,
                c.course_code,
                c.course_name,
                u.full_name as lecturer_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students,
                (
                    SELECT AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100
                    FROM attendance a
                    JOIN sessions s ON a.session_id = s.id
                    WHERE s.course_id = c.id
                ) as avg_attendance,
                c.status,
                (
                    SELECT COUNT(*) 
                    FROM sessions s 
                    WHERE s.course_id = c.id AND s.status = 'completed'
                ) * 100.0 / c.total_sessions as progress
            FROM courses c
            LEFT JOIN users u ON c.lecturer_id = u.id
            WHERE c.status = 'active'
            ORDER BY c.course_code
        ";
        $stmt = $conn->query($courseQuery);
        $stats['course_data'] = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        error_log("Course data count: " . count($stats['course_data']));

        // Final validation and logging
        if (empty($stats['course_data'])) {
            error_log("No course data found in report generation");
        }

        return $stats;
    } catch (PDOException $e) {
        error_log("Critical error in getOverallStatistics: " . $e->getMessage());
        error_log("Error details: " . print_r($e, true));
        // Return minimal stats to prevent complete failure
        return $stats;
    } catch (Exception $e) {
        error_log("Unexpected error in getOverallStatistics: " . $e->getMessage());
        return $stats;
    }
}

function getAttendanceTrend() {
    global $conn;
    try {
        $query = "
            SELECT 
                current.avg_attendance as current_rate,
                previous.avg_attendance as previous_rate
            FROM (
                SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance
                FROM attendance
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            ) as current,
            (
                SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance
                FROM attendance
                WHERE date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            ) as previous
        ";
        $stmt = $conn->query($query);
        if ($stmt && $result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $trend = $result['current_rate'] - $result['previous_rate'];
            return round($trend, 1);
        }
        return 0;
    } catch (PDOException $e) {
        error_log("Error getting attendance trend: " . $e->getMessage());
        return 0;
    }
}

function getStudentTrend() {
    global $conn;
    try {
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM users 
                 WHERE role = 'student' 
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as new_students,
                (SELECT COUNT(*) FROM users
                 WHERE role = 'student') as total_students
            FROM dual
        ";
        error_log("Debug - Running student trend query: " . $query);
        
        $stmt = $conn->query($query);
        if ($stmt && $result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            error_log("Debug - Student trend results: " . print_r($result, true));
            return $result['new_students'];
        }
        error_log("Debug - No results from student trend query");
        return 0;
    } catch (PDOException $e) {
        error_log("Error getting student trend: " . $e->getMessage());
        return 0;
    }
}
?>

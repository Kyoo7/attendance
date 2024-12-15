<?php
require_once '../config/database.php';

function getOverallStatistics($startDate = null, $endDate = null) {
    global $conn;
    
    $stats = [
        'total_students' => 0,
        'total_sessions' => 0,
        'avg_attendance' => 0,
        'monthly_attendance' => [],
        'course_data' => []
    ];

    try {
        // Get total active students
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'active'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_students'] = (int)$row['count'];

        // Get total sessions with date filter if provided
        $sessionsQuery = "SELECT COUNT(*) as count FROM sessions";
        $params = [];
        
        if ($startDate && $endDate) {
            $sessionsQuery .= " WHERE date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $stmt = $conn->prepare($sessionsQuery);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_sessions'] = (int)$row['count'];

        // Get monthly attendance data
        $monthlyQuery = "
            SELECT 
                DATE_FORMAT(s.date, '%Y-%m') as month,
                COUNT(DISTINCT s.id) as total_sessions,
                COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_count,
                COUNT(DISTINCT CASE WHEN a.status = 'late' THEN a.id END) as late_count,
                COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_count
            FROM sessions s
            LEFT JOIN attendance a ON s.id = a.session_id
        ";
        
        if ($startDate && $endDate) {
            $monthlyQuery .= " WHERE s.date BETWEEN ? AND ?";
        }
        
        $monthlyQuery .= " GROUP BY DATE_FORMAT(s.date, '%Y-%m') ORDER BY month DESC";
        
        $stmt = $conn->prepare($monthlyQuery);
        if ($startDate && $endDate) {
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt->execute();
        }
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalAttendance = $row['present_count'] + $row['late_count'] + $row['absent_count'];
            if ($totalAttendance > 0) {
                $stats['monthly_attendance'][] = [
                    'month' => $row['month'],
                    'attendance_rate' => round(($row['present_count'] / $totalAttendance) * 100, 1),
                    'late_rate' => round(($row['late_count'] / $totalAttendance) * 100, 1),
                    'absent_rate' => round(($row['absent_count'] / $totalAttendance) * 100, 1)
                ];
            }
        }

        // Calculate overall average attendance
        $avgQuery = "
            SELECT 
                COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_count,
                COUNT(DISTINCT CASE WHEN a.status = 'late' THEN a.id END) as late_count,
                COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_count
            FROM sessions s
            LEFT JOIN attendance a ON s.id = a.session_id
        ";
        
        if ($startDate && $endDate) {
            $avgQuery .= " WHERE s.date BETWEEN ? AND ?";
            $stmt = $conn->prepare($avgQuery);
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt = $conn->prepare($avgQuery);
            $stmt->execute();
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalAttendance = $row['present_count'] + $row['late_count'] + $row['absent_count'];
        if ($totalAttendance > 0) {
            $stats['avg_attendance'] = round(($row['present_count'] / $totalAttendance) * 100, 1);
        }

        // Get course performance data
        $courseQuery = "
            SELECT 
                c.id,
                c.course_code,
                c.course_name,
                u.full_name as lecturer_name,
                c.total_sessions,
                c.status,
                COUNT(DISTINCT e.student_id) as enrolled_students,
                COUNT(DISTINCT s.id) as completed_sessions,
                COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_count,
                COUNT(DISTINCT CASE WHEN a.status = 'late' THEN a.id END) as late_count,
                COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_count
            FROM courses c
            LEFT JOIN users u ON c.lecturer_id = u.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN sessions s ON c.id = s.course_id
            LEFT JOIN attendance a ON s.id = a.session_id
        ";
        
        if ($startDate && $endDate) {
            $courseQuery .= " WHERE (s.date BETWEEN ? AND ? OR s.date IS NULL)";
        }
        
        $courseQuery .= " GROUP BY c.id, c.course_code, c.course_name, u.full_name, c.total_sessions, c.status";
        
        $stmt = $conn->prepare($courseQuery);
        if ($startDate && $endDate) {
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt->execute();
        }
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $totalAttendance = $row['present_count'] + $row['late_count'] + $row['absent_count'];
            $progress = $row['total_sessions'] > 0 ? 
                round(($row['completed_sessions'] / $row['total_sessions']) * 100, 1) : 0;
            
            $stats['course_data'][] = [
                'course_code' => $row['course_code'],
                'course_name' => $row['course_name'],
                'lecturer_name' => $row['lecturer_name'],
                'total_students' => $row['enrolled_students'],
                'avg_attendance' => $totalAttendance > 0 ? 
                    round(($row['present_count'] / $totalAttendance) * 100, 1) : 0,
                'progress' => $progress,
                'status' => $row['status']
            ];
        }

    } catch (Exception $e) {
        error_log("Error in getOverallStatistics: " . $e->getMessage());
    }

    return $stats;
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

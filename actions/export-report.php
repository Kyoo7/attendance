<?php
require_once '../includes/header.php';
require_once '../includes/report_functions.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Get report data
$stats = getOverallStatistics($startDate, $endDate);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add headers
fputcsv($output, ['Attendance Report', date('Y-m-d H:i:s')]);
fputcsv($output, []);

// Overall Statistics
fputcsv($output, ['Overall Statistics']);
fputcsv($output, ['Total Students', $stats['total_students']]);
fputcsv($output, ['Total Sessions', $stats['total_sessions']]);
fputcsv($output, ['Average Attendance', number_format($stats['avg_attendance'], 1) . '%']);
fputcsv($output, []);

// Monthly Attendance
fputcsv($output, ['Monthly Attendance']);
fputcsv($output, ['Month', 'Attendance Rate']);
foreach ($stats['monthly_attendance'] as $data) {
    fputcsv($output, [
        date('F Y', strtotime($data['month'])),
        number_format($data['attendance_rate'], 1) . '%'
    ]);
}
fputcsv($output, []);

// Course Data
fputcsv($output, ['Course Details']);
fputcsv($output, ['Course Code', 'Course Name', 'Lecturer', 'Total Students', 'Avg. Attendance', 'Progress', 'Status']);
foreach ($stats['course_data'] as $course) {
    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['lecturer_name'],
        $course['total_students'],
        number_format($course['avg_attendance'], 1) . '%',
        number_format($course['progress'], 1) . '%',
        $course['status']
    ]);
}

// Close the output stream
fclose($output);
exit();
?>

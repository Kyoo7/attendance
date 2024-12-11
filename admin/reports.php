<?php
require_once '../includes/header.php';
require_once '../includes/report_functions.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Validate and sanitize date inputs
$startDate = null;
$endDate = null;

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    // Validate date format
    $startDateInput = filter_input(INPUT_GET, 'start_date', FILTER_VALIDATE_REGEXP, 
        ["options" => ["regexp" => "/^\d{4}-\d{2}-\d{2}$/"]]);
    $endDateInput = filter_input(INPUT_GET, 'end_date', FILTER_VALIDATE_REGEXP, 
        ["options" => ["regexp" => "/^\d{4}-\d{2}-\d{2}$/"]]);
    
    if ($startDateInput && $endDateInput) {
        // Additional strtotime validation
        $startTimestamp = strtotime($startDateInput);
        $endTimestamp = strtotime($endDateInput);
        
        if ($startTimestamp !== false && $endTimestamp !== false) {
            // Ensure start date is not after end date
            if ($startTimestamp <= $endTimestamp) {
                $startDate = $startDateInput;
                $endDate = $endDateInput;
            } else {
                error_log("Invalid date range: Start date is after end date");
            }
        } else {
            error_log("Invalid date format: start_date = $startDateInput, end_date = $endDateInput");
        }
    } else {
        error_log("Date input validation failed");
    }
}

// Get report data with error handling
try {
    $stats = getOverallStatistics($startDate, $endDate);
    
    // Validate stats
    if (!is_array($stats)) {
        throw new Exception("Invalid report data generated");
    }
    
    // Ensure minimum required keys exist
    $requiredKeys = ['total_students', 'total_sessions', 'avg_attendance', 'monthly_attendance', 'course_data'];
    foreach ($requiredKeys as $key) {
        if (!isset($stats[$key])) {
            throw new Exception("Missing required report data: $key");
        }
    }
} catch (Exception $e) {
    error_log("Error generating report: " . $e->getMessage());
    $stats = [
        'total_students' => 0,
        'total_sessions' => 0,
        'avg_attendance' => 0,
        'monthly_attendance' => [],
        'course_data' => []
    ];
}

// Handle AJAX requests for report updates
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($stats);
    exit();
}
?>

<link rel="stylesheet" href="../css/admin-reports.css">

<div class="dashboard-container">
    <div class="page-header">
        <h2>Reports & Analytics</h2>
        <div class="header-actions">
            <button class="btn-secondary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
            <button class="btn-primary" onclick="printReport()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-section">
        <div class="date-range">
            <input type="date" id="startDate" class="date-input" value="<?php echo $startDate ?? ''; ?>">
            <span>to</span>
            <input type="date" id="endDate" class="date-input" value="<?php echo $endDate ?? ''; ?>">
        </div>
        <button class="btn-primary" onclick="updateReport(event)">
            <i class="fas fa-sync"></i> Update Report
        </button>
    </div>

    <!-- Overall Statistics -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h3>Average Attendance</h3>
                <div class="stat-value"><?php echo isset($stats['avg_attendance']) ? number_format($stats['avg_attendance'], 1) : '0.0'; ?>%</div>
                <div class="stat-trend <?php echo isset($stats['avg_attendance']) && $stats['avg_attendance'] >= 0 ? 'trend-up' : 'trend-down'; ?>">
                    <i class="fas fa-arrow-<?php echo isset($stats['avg_attendance']) && $stats['avg_attendance'] >= 0 ? 'up' : 'down'; ?>"></i>
                    <?php echo isset($stats['avg_attendance']) ? abs($stats['avg_attendance']) : '0'; ?>%
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Sessions Completed</h3>
                <div class="stat-value"><?php echo isset($stats['total_sessions']) ? $stats['total_sessions'] : '0'; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Total Students</h3>
                <div class="stat-value"><?php echo isset($stats['total_students']) ? $stats['total_students'] : '0'; ?></div>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i> +<?php echo isset($stats['total_students']) ? $stats['total_students'] : '0'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="chart-section">
        <div class="chart-card">
            <h3>Monthly Attendance Overview</h3>
            <canvas id="attendanceChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Course Performance Distribution</h3>
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <!-- Detailed Reports Table -->
    <div class="table-section">
        <h3>Detailed Course Reports</h3>
        <div class="table-container">
            <?php if (isset($stats['course_data']) && is_array($stats['course_data']) && !empty($stats['course_data'])): ?>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Lecturer</th>
                            <th>Total Students</th>
                            <th>Avg. Attendance</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['course_data'] as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($course['lecturer_name'] ?? ''); ?></td>
                            <td><?php echo $course['total_students'] ?? '0'; ?></td>
                            <td><?php echo isset($course['avg_attendance']) ? number_format($course['avg_attendance'], 1) : '0.0'; ?>%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $course['progress'] ?? '0'; ?>%"></div>
                                </div>
                            </td>
                            <td><span class="status-badge <?php echo strtolower($course['status'] ?? 'unknown'); ?>"><?php echo ucfirst($course['status'] ?? 'Unknown'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No course data available</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for charts
    const monthlyData = <?php echo json_encode(isset($stats['monthly_attendance']) ? array_column($stats['monthly_attendance'], 'attendance_rate') : []); ?>;
    const monthLabels = <?php echo json_encode(isset($stats['monthly_attendance']) ? array_map(function($date) {
        return date('M', strtotime($date));
    }, array_column($stats['monthly_attendance'], 'month')) : []); ?>;

    const courseData = <?php echo json_encode(isset($stats['course_data']) && is_array($stats['course_data']) ? $stats['course_data'] : []); ?>;
    
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Overall Attendance',
                data: monthlyData,
                borderColor: '#800020',
                backgroundColor: 'rgba(128, 0, 32, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 60,
                    max: 100
                }
            }
        }
    });

    // Only create performance chart if we have course data
    if (courseData.length > 0) {
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: courseData.map(c => c.course_code || ''),
                datasets: [{
                    label: 'Average Attendance Rate',
                    data: courseData.map(c => c.avg_attendance || 0),
                    backgroundColor: '#800020',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
});

function updateReport(event) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Validate inputs
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    // Validate date range
    const startTimestamp = new Date(startDate).getTime();
    const endTimestamp = new Date(endDate).getTime();
    
    if (startTimestamp > endTimestamp) {
        alert('Start date must be before or equal to end date');
        return;
    }

    // Prepare URL
    const url = new URL(window.location.href);
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);

    // Show loading state
    const updateButton = event.target.closest('button');
    const originalContent = updateButton.innerHTML;
    updateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    updateButton.disabled = true;

    // Fetch report data
    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Validate response data
            const requiredKeys = ['total_students', 'total_sessions', 'avg_attendance', 'monthly_attendance', 'course_data'];
            const missingKeys = requiredKeys.filter(key => !data.hasOwnProperty(key));
            
            if (missingKeys.length > 0) {
                throw new Error(`Missing required data: ${missingKeys.join(', ')}`);
            }

            // Redirect to update the page with new parameters
            window.location.href = url.toString();
        })
        .catch(error => {
            console.error('Report update error:', error);
            
            // Detailed error messaging
            let errorMessage = 'Error updating report. ';
            if (error.message.includes('HTTP error')) {
                errorMessage += 'There was a problem connecting to the server.';
            } else if (error.message.includes('Missing required data')) {
                errorMessage += 'The server returned incomplete data.';
            } else {
                errorMessage += 'Please check your date range and try again.';
            }
            
            alert(errorMessage);
            
            // Restore button state
            updateButton.innerHTML = originalContent;
            updateButton.disabled = false;
        });
}

function exportReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Create export URL with current date range
    const url = new URL(window.location.origin + '/plswork/actions/export-report.php');
    if (startDate && endDate) {
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);
    }
    
    window.location.href = url.toString();
}

function printReport() {
    window.print();
}
</script>

<?php require_once '../includes/footer.php'; ?>

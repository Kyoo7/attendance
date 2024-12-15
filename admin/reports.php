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
            <div class="chart-wrapper" style="height: 300px;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Course Performance Distribution</h3>
            <div class="chart-wrapper" style="height: 300px;">
                <canvas id="performanceChart"></canvas>
            </div>
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
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['lecturer_name']); ?></td>
                            <td><?php echo number_format($course['total_students']); ?></td>
                            <td>
                                <div class="attendance-rate">
                                    <?php echo number_format($course['avg_attendance'], 1); ?>%
                                </div>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo number_format($course['progress'], 1); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo strtolower($course['status']); ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No course data available for the selected period</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Attendance Chart
    const monthlyData = <?php echo json_encode($stats['monthly_attendance']); ?>;
    new Chart(document.getElementById('attendanceChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Attendance Rate',
                data: monthlyData.map(item => item.attendance_rate),
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });

    // Course Performance Distribution Chart
    const courseData = <?php echo json_encode($stats['course_data']); ?>;
    const performanceRanges = {
        'Excellent': courseData.filter(c => c.avg_attendance >= 90).length,
        'Good': courseData.filter(c => c.avg_attendance >= 80 && c.avg_attendance < 90).length,
        'Average': courseData.filter(c => c.avg_attendance >= 70 && c.avg_attendance < 80).length,
        'Poor': courseData.filter(c => c.avg_attendance < 70).length
    };

    new Chart(document.getElementById('performanceChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(performanceRanges),
            datasets: [{
                data: Object.values(performanceRanges),
                backgroundColor: [
                    '#4CAF50', // Excellent - Green
                    '#2196F3', // Good - Blue
                    '#FFC107', // Average - Yellow
                    '#F44336'  // Poor - Red
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} courses (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});

function updateReport(event) {
    event.preventDefault();
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after end date');
        return;
    }

    window.location.href = `reports.php?start_date=${startDate}&end_date=${endDate}`;
}

function exportReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const params = new URLSearchParams({ start_date: startDate, end_date: endDate, format: 'csv' });
    window.location.href = `export_report.php?${params.toString()}`;
}

function printReport() {
    window.print();
}
</script>

<?php require_once '../includes/footer.php'; ?>

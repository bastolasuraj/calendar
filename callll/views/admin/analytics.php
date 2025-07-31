<?php require_once 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">
        <i class="fas fa-chart-bar me-2"></i>Analytics & Reports
    </h1>
    <div class="btn-group" role="group">
        <button class="btn btn-outline-primary" onclick="exportAnalytics()">
            <i class="fas fa-download me-1"></i>Export Report
        </button>
        <button class="btn btn-outline-secondary" onclick="refreshData()">
            <i class="fas fa-sync me-1"></i>Refresh
        </button>
    </div>
</div>

<!-- Overview Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Bookings
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Approved Rate
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $approvalRate = $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100, 1) : 0;
                            echo $approvalRate . '%';
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Review
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['pending']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            This Month
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo end($monthlyStats)['count']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Booking Trends Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-area me-2"></i>Booking Trends (Last 12 Months)
                </h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                         aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Chart Options:</div>
                        <a class="dropdown-item" href="#" onclick="changeChartType('line')">Line Chart</a>
                        <a class="dropdown-item" href="#" onclick="changeChartType('bar')">Bar Chart</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="exportChartData()">Export Data</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="bookingTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>Status Distribution
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Approved
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Pending
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Rejected
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Reports -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock me-2"></i>Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    <!-- Activity items would be loaded dynamically -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">New booking approved</h6>
                            <p class="timeline-text">Sarah Johnson's booking for Jan 15 was approved</p>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">New booking submitted</h6>
                            <p class="timeline-text">Michael Chen submitted a booking request</p>
                            <small class="text-muted">5 hours ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Booking updated</h6>
                            <p class="timeline-text">Emma Rodriguez changed her booking time</p>
                            <small class="text-muted">1 day ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Statistics -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-trophy me-2"></i>Key Metrics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="metric-card">
                            <div class="metric-value text-primary"><?php echo round($stats['total'] / 12, 1); ?></div>
                            <div class="metric-label">Avg/Month</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="metric-card">
                            <div class="metric-value text-success"><?php echo $stats['approved']; ?></div>
                            <div class="metric-label">Approved</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="metric-card">
                            <div class="metric-value text-danger"><?php echo $stats['rejected']; ?></div>
                            <div class="metric-label">Rejected</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="metric-card">
                            <div class="metric-value text-info">
                                <?php
                                $responseTime = $stats['approved'] + $stats['rejected'];
                                $responseRate = $stats['total'] > 0 ? round(($responseTime / $stats['total']) * 100, 1) : 0;
                                echo $responseRate . '%';
                                ?>
                            </div>
                            <div class="metric-label">Response Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Indicators -->
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Performance Indicators</h6>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Approval Rate</span>
                            <span><?php echo $approvalRate; ?>%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: <?php echo $approvalRate; ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Response Rate</span>
                            <span><?php echo $responseRate; ?>%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: <?php echo $responseRate; ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Pending Rate</span>
                            <span><?php echo $stats['total'] > 0 ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0; ?>%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: <?php echo $stats['total'] > 0 ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Export Options -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-download me-2"></i>Export Options
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Quick Exports</h6>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_PATH; ?>/admin/export" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-csv me-1"></i>All Bookings (CSV)
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/admin/export?status=approved" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-check me-1"></i>Approved Only
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/admin/export?status=pending" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-clock me-1"></i>Pending Only
                    </a>
                </div>
            </div>
            <div class="col-md-8">
                <h6>Custom Date Range</h6>
                <form class="row g-3" onsubmit="exportCustomRange(event)">
                    <div class="col-md-4">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                    <div class="col-md-4">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date">
                    </div>
                    <div class="col-md-4">
                        <label for="statusFilter" class="form-label">Status Filter</label>
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>Export Custom Range
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart.js configuration
    let bookingTrendsChart, statusChart;

    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Booking Trends Chart
        const trendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');

        const monthlyData = <?php echo json_encode($monthlyStats); ?>;
        const labels = monthlyData.map(item => item.month);
        const data = monthlyData.map(item => item.count);

        bookingTrendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bookings',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');

        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [<?php echo $stats['approved']; ?>, <?php echo $stats['pending']; ?>, <?php echo $stats['rejected']; ?>],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function changeChartType(type) {
        bookingTrendsChart.config.type = type;
        bookingTrendsChart.update();
    }

    function exportChartData() {
        const monthlyData = <?php echo json_encode($monthlyStats); ?>;
        const csvContent = "data:text/csv;charset=utf-8,"
            + "Month,Bookings\n"
            + monthlyData.map(row => `${row.month},${row.count}`).join("\n");

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "booking_trends.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportAnalytics() {
        window.open(`${BASE_PATH_JS}/admin/export`, '_blank'); // Fixed this URL
    }

    function exportCustomRange(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const params = new URLSearchParams();

        for (let [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }

        window.open(`${BASE_PATH_JS}/admin/export?` + params.toString(), '_blank'); // Fixed this URL
    }

    function refreshData() {
        location.reload();
    }
</script>

<style>
    .border-left-primary {
        border-left: 4px solid var(--primary-color) !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .chart-area {
        position: relative;
        height: 300px;
    }

    .chart-pie {
        position: relative;
        height: 200px;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 1rem;
    }

    .timeline-marker {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 1rem;
        margin-top: 0.25rem;
        flex-shrink: 0;
    }

    .timeline-content {
        flex-grow: 1;
    }

    .timeline-title {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .timeline-text {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .metric-card {
        padding: 1rem;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .metric-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-sm {
        height: 0.5rem;
    }

    .activity-timeline {
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<?php require_once 'views/templates/footer.php'; ?>

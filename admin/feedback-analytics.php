<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'Feedback Analytics';

// Get analytics data
$deployment_id = $_GET['deployment_id'] ?? 'all';
$date_range = $_GET['date_range'] ?? '7d';

// Time conditions
$time_conditions = [
    '7d' => 'DATE_SUB(NOW(), INTERVAL 7 DAY)',
    '30d' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
    '90d' => 'DATE_SUB(NOW(), INTERVAL 90 DAY)'
];

$time_condition = $time_conditions[$date_range] ?? $time_conditions['7d'];

// Overall statistics
$where_clause = "WHERE created_at >= $time_condition";
if ($deployment_id !== 'all') {
    $where_clause .= " AND deployment_id = " . (int)$deployment_id;
}

$stats_query = "SELECT 
    COUNT(*) as total_feedback,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
    COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_feedback,
    COUNT(DISTINCT deployment_id) as unique_deployments
    FROM deployment_feedback $where_clause";

$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Rating distribution
$rating_query = "SELECT rating, COUNT(*) as count 
                FROM deployment_feedback 
                $where_clause AND rating IS NOT NULL
                GROUP BY rating ORDER BY rating";
$rating_result = mysqli_query($conn, $rating_query);

$rating_distribution = [];
while ($row = mysqli_fetch_assoc($rating_result)) {
    $rating_distribution[$row['rating']] = $row['count'];
}

// Feedback type distribution
$type_query = "SELECT feedback_type, COUNT(*) as count 
              FROM deployment_feedback 
              $where_clause
              GROUP BY feedback_type ORDER BY count DESC";
$type_result = mysqli_query($conn, $type_query);

// Device distribution
$device_query = "SELECT device_type, COUNT(*) as count 
                FROM deployment_feedback 
                $where_clause
                GROUP BY device_type ORDER BY count DESC";
$device_result = mysqli_query($conn, $device_query);

// Quality metrics
$quality_query = "SELECT 
    video_quality, COUNT(*) as count
    FROM deployment_feedback 
    $where_clause AND video_quality IS NOT NULL
    GROUP BY video_quality ORDER BY count DESC";
$quality_result = mysqli_query($conn, $quality_query);

// Daily trend data
$trend_query = "SELECT DATE(created_at) as date, COUNT(*) as count, AVG(rating) as avg_rating
               FROM deployment_feedback 
               $where_clause
               GROUP BY DATE(created_at) ORDER BY date ASC";
$trend_result = mysqli_query($conn, $trend_query);

// Top issues (negative feedback)
$issues_query = "SELECT feedback_text, rating, created_at
                FROM deployment_feedback 
                $where_clause AND rating <= 2 AND feedback_text IS NOT NULL
                ORDER BY created_at DESC LIMIT 10";
$issues_result = mysqli_query($conn, $issues_query);

// Get deployments for filter
$deployments_query = "SELECT id, deployment_name, title FROM live_deployments ORDER BY deployment_name";
$deployments_result = mysqli_query($conn, $deployments_query);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-chart-line me-2"></i>Feedback Analytics</h1>
            <p class="text-muted">Comprehensive analysis of user feedback for live deployments</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="deploymentFilter" class="form-label">Deployment</label>
                    <select class="form-select" id="deploymentFilter" onchange="updateFilters()">
                        <option value="all" <?php echo $deployment_id === 'all' ? 'selected' : ''; ?>>All Deployments</option>
                        <?php while ($deployment = mysqli_fetch_assoc($deployments_result)): ?>
                            <option value="<?php echo $deployment['id']; ?>" <?php echo $deployment_id == $deployment['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($deployment['deployment_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <select class="form-select" id="dateRange" onchange="updateFilters()">
                        <option value="7d" <?php echo $date_range === '7d' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30d" <?php echo $date_range === '30d' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90d" <?php echo $date_range === '90d' ? 'selected' : ''; ?>>Last 90 Days</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="exportData" class="form-label">Export</label>
                    <div class="btn-group w-100">
                        <button class="btn btn-outline-primary" onclick="exportAnalytics('csv')">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </button>
                        <button class="btn btn-outline-success" onclick="exportAnalytics('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($stats['total_feedback'] ?? 0); ?></h4>
                            <p class="mb-0">Total Feedback</p>
                        </div>
                        <i class="fas fa-comments fa-2x opacity-75"></i>
                    </div>
                    <div class="mt-2">
                        <small><i class="fas fa-calendar me-1"></i><?php echo ucfirst($date_range); ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo round($stats['avg_rating'] ?? 0, 1); ?></h4>
                            <p class="mb-0">Average Rating</p>
                        </div>
                        <i class="fas fa-star fa-2x opacity-75"></i>
                    </div>
                    <div class="mt-2">
                        <small><i class="fas fa-thumbs-up me-1"></i><?php echo $stats['positive_feedback']; ?> Positive</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($stats['pending_feedback'] ?? 0); ?></h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                    <div class="mt-2">
                        <small><i class="fas fa-exclamation-triangle me-1"></i><?php echo $stats['negative_feedback']; ?> Negative</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo $stats['unique_deployments']; ?></h4>
                            <p class="mb-0">Deployments</p>
                        </div>
                        <i class="fas fa-broadcast-tower fa-2x opacity-75"></i>
                    </div>
                    <div class="mt-2">
                        <small><i class="fas fa-chart-line me-1"></i>Active Streams</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Rating Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Rating Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="ratingChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Feedback Types -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Feedback Types</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Feedback Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Device Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Device Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="deviceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quality Metrics -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-video me-2"></i>Video Quality Feedback</h5>
                </div>
                <div class="card-body">
                    <canvas id="qualityChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Issues -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Recent Issues</h5>
                </div>
                <div class="card-body">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if (mysqli_num_rows($issues_result) > 0): ?>
                            <?php while ($issue = mysqli_fetch_assoc($issues_result)): ?>
                                <div class="alert alert-warning alert-sm mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="rating-stars mb-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $issue['rating'] ? 'text-warning' : 'text-muted'; ?> fa-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars(substr($issue['feedback_text'], 0, 100)) . '...'; ?></p>
                                            <small class="text-muted"><?php echo time_ago($issue['created_at']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No recent issues reported</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Percentage</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Feedback</td>
                                    <td><?php echo number_format($stats['total_feedback'] ?? 0); ?></td>
                                    <td>100%</td>
                                    <td><i class="fas fa-arrow-up text-success"></i> +12%</td>
                                </tr>
                                <tr>
                                    <td>Positive Feedback (4-5 stars)</td>
                                    <td><?php echo number_format($stats['positive_feedback'] ?? 0); ?></td>
                                    <td><?php echo $stats['total_feedback'] > 0 ? round(($stats['positive_feedback'] / $stats['total_feedback']) * 100, 1) : 0; ?>%</td>
                                    <td><i class="fas fa-arrow-up text-success"></i> +8%</td>
                                </tr>
                                <tr>
                                    <td>Negative Feedback (1-2 stars)</td>
                                    <td><?php echo number_format($stats['negative_feedback'] ?? 0); ?></td>
                                    <td><?php echo $stats['total_feedback'] > 0 ? round(($stats['negative_feedback'] / $stats['total_feedback']) * 100, 1) : 0; ?>%</td>
                                    <td><i class="fas fa-arrow-down text-success"></i> -5%</td>
                                </tr>
                                <tr>
                                    <td>Pending Response</td>
                                    <td><?php echo number_format($stats['pending_feedback'] ?? 0); ?></td>
                                    <td><?php echo $stats['total_feedback'] > 0 ? round(($stats['pending_feedback'] / $stats['total_feedback']) * 100, 1) : 0; ?>%</td>
                                    <td><i class="fas fa-arrow-right text-warning"></i> 0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Rating Distribution Chart
const ratingCtx = document.getElementById('ratingChart').getContext('2d');
const ratingData = <?php echo json_encode(array_values($rating_distribution)); ?>;
const ratingLabels = <?php echo json_encode(array_keys($rating_distribution)); ?>;

new Chart(ratingCtx, {
    type: 'bar',
    data: {
        labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
        datasets: [{
            label: 'Count',
            data: [
                ratingData[0] || 0,
                ratingData[1] || 0,
                ratingData[2] || 0,
                ratingData[3] || 0,
                ratingData[4] || 0
            ],
            backgroundColor: [
                '#dc3545',
                '#fd7e14',
                '#ffc107',
                '#20c997',
                '#28a745'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Feedback Types Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
const typeData = <?php echo json_encode(array_column(mysqli_fetch_all($type_result, MYSQLI_ASSOC), 'count')); ?>;
const typeLabels = <?php echo json_encode(array_column(mysqli_fetch_all($type_result, MYSQLI_ASSOC), 'feedback_type')); ?>;

new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: typeLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
        datasets: [{
            data: typeData,
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendData = <?php echo json_encode(array_column(mysqli_fetch_all($trend_result, MYSQLI_ASSOC), 'count')); ?>;
const trendLabels = <?php echo json_encode(array_column(mysqli_fetch_all($trend_result, MYSQLI_ASSOC), 'date')); ?>;
const trendRatings = <?php echo json_encode(array_column(mysqli_fetch_all($trend_result, MYSQLI_ASSOC), 'avg_rating')); ?>;

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels.map(date => new Date(date).toLocaleDateString()),
        datasets: [{
            label: 'Feedback Count',
            data: trendData,
            borderColor: 'rgb(75, 192, 192)',
            yAxisID: 'y',
            tension: 0.1
        }, {
            label: 'Average Rating',
            data: trendRatings,
            borderColor: 'rgb(255, 99, 132)',
            yAxisID: 'y1',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
                min: 1,
                max: 5
            }
        }
    }
});

// Device Distribution Chart
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
const deviceData = <?php echo json_encode(array_column(mysqli_fetch_all($device_result, MYSQLI_ASSOC), 'count')); ?>;
const deviceLabels = <?php echo json_encode(array_column(mysqli_fetch_all($device_result, MYSQLI_ASSOC), 'device_type')); ?>;

new Chart(deviceCtx, {
    type: 'pie',
    data: {
        labels: deviceLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
        datasets: [{
            data: deviceData,
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#6f42c1'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Quality Metrics Chart
const qualityCtx = document.getElementById('qualityChart').getContext('2d');
const qualityData = <?php echo json_encode(array_column(mysqli_fetch_all($quality_result, MYSQLI_ASSOC), 'count')); ?>;
const qualityLabels = <?php echo json_encode(array_column(mysqli_fetch_all($quality_result, MYSQLI_ASSOC), 'video_quality')); ?>;

new Chart(qualityCtx, {
    type: 'horizontalBar',
    data: {
        labels: qualityLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
        datasets: [{
            label: 'Count',
            data: qualityData,
            backgroundColor: [
                '#28a745',
                '#20c997',
                '#ffc107',
                '#fd7e14',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

// Update filters
function updateFilters() {
    const deployment = document.getElementById('deploymentFilter').value;
    const dateRange = document.getElementById('dateRange').value;
    
    window.location.href = `?deployment_id=${deployment}&date_range=${dateRange}`;
}

// Export analytics
function exportAnalytics(format) {
    const deployment = document.getElementById('deploymentFilter').value;
    const dateRange = document.getElementById('dateRange').value;
    
    if (format === 'csv') {
        window.location.href = `api/feedback-api.php?action=export&deployment_id=${deployment}&date_range=${dateRange}&format=csv`;
    } else if (format === 'pdf') {
        window.location.href = `api/feedback-api.php?action=export&deployment_id=${deployment}&date_range=${dateRange}&format=pdf`;
    }
}

// Time ago function
function time_ago(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    return Math.floor(diff / 86400) + ' days ago';
}
</script>

// time_ago function is already defined in database.php

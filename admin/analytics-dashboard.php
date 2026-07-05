<?php
require_once '../config/database.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../login.php');
}

$page_title = 'Enhanced Analytics Dashboard';

// Get date range from request
$date_range = $_GET['date_range'] ?? '7d';
$start_date = '';
$end_date = '';

switch ($date_range) {
    case '1d':
        $start_date = date('Y-m-d 00:00:00');
        $end_date = date('Y-m-d 23:59:59');
        break;
    case '7d':
        $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $end_date = date('Y-m-d 23:59:59');
        break;
    case '30d':
        $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $end_date = date('Y-m-d 23:59:59');
        break;
    case '90d':
        $start_date = date('Y-m-d 00:00:00', strtotime('-90 days'));
        $end_date = date('Y-m-d 23:59:59');
        break;
    default:
        $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $end_date = date('Y-m-d 23:59:59');
}

// Get overall statistics
$total_views_query = "SELECT SUM(views) as total_views, COUNT(*) as unique_articles 
                  FROM news 
                  WHERE published_at BETWEEN '$start_date' AND '$end_date'";
$total_views_result = mysqli_query($conn, $total_views_query);
$total_stats = mysqli_fetch_assoc($total_views_result);

// Get external vs internal posts
$external_query = "SELECT 
                    COUNT(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 1 END) as external_posts,
                    COUNT(CASE WHEN source_url IS NULL OR source_url = '' THEN 1 END) as internal_posts,
                    SUM(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN views ELSE 0 END) as external_views,
                    SUM(CASE WHEN source_url IS NULL OR source_url = '' THEN views ELSE 0 END) as internal_views
                 FROM news 
                 WHERE published_at BETWEEN '$start_date' AND '$end_date'";
$external_result = mysqli_query($conn, $external_query);
$external_stats = mysqli_fetch_assoc($external_result);

// Get daily upload trends
$daily_query = "SELECT 
                    DATE(published_at) as upload_date,
                    COUNT(*) as posts_count,
                    SUM(views) as daily_views,
                    COUNT(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 1 END) as external_daily,
                    COUNT(CASE WHEN source_url IS NULL OR source_url = '' THEN 1 END) as internal_daily
                 FROM news 
                 WHERE published_at BETWEEN '$start_date' AND '$end_date'
                 GROUP BY DATE(published_at)
                 ORDER BY upload_date";
$daily_result = mysqli_query($conn, $daily_query);

// Get category performance
$category_stats_query = "SELECT 
                        c.id, c.name, c.color,
                        COUNT(n.id) as article_count,
                        SUM(n.views) as total_views
                     FROM categories c
                     LEFT JOIN news n ON c.id = n.category_id
                     WHERE n.published_at BETWEEN '$start_date' AND '$end_date'
                     GROUP BY c.id, c.name, c.color
                     ORDER BY total_views DESC";
$category_stats_result = mysqli_query($conn, $category_stats_query);

// Get top performing articles
$top_articles_query = "SELECT 
                        n.id, n.title, n.slug, n.views, n.published_at,
                        c.name as category_name, c.color as category_color,
                        CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 'External' ELSE 'Internal' END as post_type
                     FROM news n
                     LEFT JOIN categories c ON n.category_id = c.id
                     WHERE n.published_at BETWEEN '$start_date' AND '$end_date'
                     ORDER BY n.views DESC
                     LIMIT 10";
$top_articles_result = mysqli_query($conn, $top_articles_query);

// Get hourly activity
$hourly_query = "SELECT 
                    HOUR(published_at) as hour,
                    COUNT(*) as articles_count
                 FROM news 
                 WHERE published_at BETWEEN '$start_date' AND '$end_date'
                 GROUP BY HOUR(published_at)
                 ORDER BY hour";
$hourly_result = mysqli_query($conn, $hourly_query);

// Prepare data for charts
$daily_data = [];
$external_data = [];
$category_data = [];
$hourly_data = [];

// Process daily data
while ($daily = mysqli_fetch_assoc($daily_result)) {
    $daily_data[] = [
        'date' => date('M j', strtotime($daily['upload_date'])),
        'posts' => (int)$daily['posts_count'],
        'views' => (int)$daily['daily_views'],
        'external' => (int)$daily['external_daily'],
        'internal' => (int)$daily['internal_daily']
    ];
}

// Process category data
while ($category = mysqli_fetch_assoc($category_stats_result)) {
    $category_data[] = [
        'name' => $category['name'],
        'color' => $category['color'],
        'articles' => (int)$category['article_count'],
        'views' => (int)$category['total_views']
    ];
}

// Process hourly data
$max_hourly = 0;
$hourly_array = [];
while ($hour = mysqli_fetch_assoc($hourly_result)) {
    $hourly_array[$hour['hour']] = (int)$hour['articles_count'];
    $max_hourly = max($max_hourly, (int)$hour['articles_count']);
}

for ($h = 0; $h < 24; $h++) {
    $hourly_data[$h] = $hourly_array[$h] ?? 0;
}
?>

<?php include '../includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line me-2"></i>Enhanced Analytics Dashboard</h2>
                <div class="btn-group" role="group">
                    <a href="?date_range=1d" class="btn btn-outline-primary <?php echo $date_range == '1d' ? 'active' : ''; ?>">Today</a>
                    <a href="?date_range=7d" class="btn btn-outline-primary <?php echo $date_range == '7d' ? 'active' : ''; ?>">7 Days</a>
                    <a href="?date_range=30d" class="btn btn-outline-primary <?php echo $date_range == '30d' ? 'active' : ''; ?>">30 Days</a>
                    <a href="?date_range=90d" class="btn btn-outline-primary <?php echo $date_range == '90d' ? 'active' : ''; ?>">90 Days</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Views</h5>
                    <h3><?php echo number_format($total_stats['total_views'] ?? 0); ?></h3>
                    <small>From <?php echo date('M j, Y', strtotime($start_date)); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Posts</h5>
                    <h3><?php echo number_format($total_stats['unique_articles'] ?? 0); ?></h3>
                    <small>In selected period</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Internal Posts</h5>
                    <h3><?php echo number_format($external_stats['internal_posts'] ?? 0); ?></h3>
                    <small><?php 
                        $percentage = $external_stats['internal_posts'] + $external_stats['external_posts'] > 0 ? 
                            ($external_stats['internal_posts'] / ($external_stats['internal_posts'] + $external_stats['external_posts'])) * 100 : 0;
                        echo number_format($percentage, 1) . '%';
                    ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">External Posts</h5>
                    <h3><?php echo number_format($external_stats['external_posts'] ?? 0); ?></h3>
                    <small><?php 
                        $percentage = $external_stats['internal_posts'] + $external_stats['external_posts'] > 0 ? 
                            ($external_stats['external_posts'] / ($external_stats['internal_posts'] + $external_stats['external_posts'])) * 100 : 0;
                        echo number_format($percentage, 1) . '%';
                    ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Avg Views/Post</h5>
                    <h3><?php 
                        $avg = $total_stats['unique_articles'] > 0 ? $total_stats['total_views'] / $total_stats['unique_articles'] : 0;
                        echo number_format($avg, 1);
                    ?></h3>
                    <small>Per post</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area me-2"></i>News Upload Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="uploadTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Post Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="postTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Hourly Upload Activity</h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyActivityChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tags me-2"></i>Category Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Articles Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-trophy me-2"></i>Top Performing Articles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Views</th>
                                    <th>Published</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($news = mysqli_fetch_assoc($top_articles_result)): ?>
                                    <tr>
                                        <td>
                                            <a href="../news.php?slug=<?php echo $news['slug']; ?>" target="_blank">
                                                <?php echo htmlspecialchars($news['title'] ?? ''); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $news['post_type'] == 'External' ? 'info' : 'primary'; ?>">
                                                <?php echo $news['post_type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $news['category_color']; ?>; color: white;">
                                                <?php echo htmlspecialchars($news['category_name'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($news['views']); ?></strong>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($news['published_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Upload Trends Chart
const uploadCtx = document.getElementById('uploadTrendsChart').getContext('2d');
const uploadData = <?php echo json_encode($daily_data); ?>;

new Chart(uploadCtx, {
    type: 'line',
    data: {
        labels: uploadData.map(d => d.date),
        datasets: [{
            label: 'Total Posts',
            data: uploadData.map(d => d.posts),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Total Views',
            data: uploadData.map(d => d.views),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Post Type Distribution Chart
const postTypeCtx = document.getElementById('postTypeChart').getContext('2d');
const externalPosts = <?php echo $external_stats['external_posts'] ?? 0; ?>;
const internalPosts = <?php echo $external_stats['internal_posts'] ?? 0; ?>;

new Chart(postTypeCtx, {
    type: 'doughnut',
    data: {
        labels: ['External Posts', 'Internal Posts'],
        datasets: [{
            data: [externalPosts, internalPosts],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Hourly Activity Chart
const hourlyCtx = document.getElementById('hourlyActivityChart').getContext('2d');
const hourlyData = <?php echo json_encode(array_values($hourly_data)); ?>;
const hourlyLabels = <?php echo json_encode(array_keys($hourly_data)); ?>;

new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: hourlyLabels.map(h => h + ':00'),
        datasets: [{
            label: 'Articles Uploaded',
            data: hourlyData,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgb(75, 192, 192)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
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

// Category Performance Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryData = <?php echo json_encode($category_data); ?>;

new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: categoryData.map(c => c.name),
        datasets: [{
            data: categoryData.map(c => c.views),
            backgroundColor: categoryData.map(c => c.color),
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Auto-refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

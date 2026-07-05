<?php
require_once '../config/database.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../login.php');
}

$page_title = 'Website Performance Monitor';

// Get date range from request
$date_range = $_GET['date_range'] ?? '24h';
$start_date = '';
$end_date = '';

switch ($date_range) {
    case '1h':
        $start_date = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $end_date = date('Y-m-d H:i:s');
        break;
    case '24h':
        $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $end_date = date('Y-m-d H:i:s');
        break;
    case '7d':
        $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
        $end_date = date('Y-m-d H:i:s');
        break;
    case '30d':
        $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $end_date = date('Y-m-d H:i:s');
        break;
    default:
        $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $end_date = date('Y-m-d H:i:s');
}

// Real-time visitors (active sessions in last 30 minutes)
// Check if user_activity table exists, create it if not
$activity_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_activity'");
if (mysqli_num_rows($activity_check) == 0) {
    $create_activity_sql = "CREATE TABLE `user_activity` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `session_id` varchar(255) DEFAULT NULL,
        `news_id` int(11) DEFAULT NULL,
        `action` enum('view','share','comment','bookmark','like','dislike') NOT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `referrer` varchar(255) DEFAULT NULL,
        `duration` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_activity_user` (`user_id`),
        KEY `idx_activity_news` (`news_id`),
        KEY `idx_activity_action` (`action`),
        KEY `idx_activity_date` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_activity_sql);
}

$real_time_visitors_query = "SELECT COUNT(*) as total_visitors,
                            COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as logged_in_users,
                            COUNT(DISTINCT ip_address) as unique_ips
                            FROM user_activity 
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                            AND action = 'view'";
$real_time_result = mysqli_query($conn, $real_time_visitors_query);
if ($real_time_result) {
    $real_time_stats = mysqli_fetch_assoc($real_time_result);
} else {
    // Fallback values if user_activity table is empty or query fails
    $real_time_stats = [
        'total_visitors' => 0,
        'logged_in_users' => 0,
        'unique_ips' => 0
    ];
}

// Check if news_analytics table exists, create it if not
$analytics_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_analytics'");
if (mysqli_num_rows($analytics_check) == 0) {
    $create_analytics_sql = "CREATE TABLE `news_analytics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `news_id` int(11) NOT NULL,
        `views` int(11) DEFAULT 0,
        `unique_visitors` int(11) DEFAULT 0,
        `avg_read_time` int(11) DEFAULT 0,
        `date` date NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_news_date` (`news_id`,`date`),
        KEY `idx_analytics_news` (`news_id`),
        KEY `idx_analytics_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_analytics_sql);
}

// Most read articles
$most_read_query = "SELECT n.id, n.title, n.slug, n.views, n.published_at,
                   c.name as category_name, c.color as category_color,
                   COALESCE(ua.unique_visitors, 0) as unique_views, 
                   COALESCE(ua.avg_read_time, 0) as avg_read_time,
                   0 as engagement
                   FROM news n
                   LEFT JOIN categories c ON n.category_id = c.id
                   LEFT JOIN news_analytics ua ON n.id = ua.news_id AND ua.date = CURDATE()
                   WHERE n.status = 'published' AND n.published_at >= '$start_date'
                   ORDER BY n.views DESC
                   LIMIT 10";
$most_read_result = mysqli_query($conn, $most_read_query);
if (!$most_read_result) {
    // Fallback query without analytics join
    $most_read_query = "SELECT n.id, n.title, n.slug, n.views, n.published_at,
                       c.name as category_name, c.color as category_color,
                       0 as unique_views, 0 as avg_read_time, 0 as engagement
                       FROM news n
                       LEFT JOIN categories c ON n.category_id = c.id
                       WHERE n.status = 'published' AND n.published_at >= '$start_date'
                       ORDER BY n.views DESC
                       LIMIT 10";
    $most_read_result = mysqli_query($conn, $most_read_query);
}

// Traffic sources analysis
$traffic_sources_query = "SELECT 
                         CASE 
                             WHEN referrer LIKE '%google%' OR referrer LIKE '%bing%' OR referrer LIKE '%yahoo%' THEN 'Search Engines'
                             WHEN referrer LIKE '%facebook%' OR referrer LIKE '%twitter%' OR referrer LIKE '%instagram%' OR referrer LIKE '%linkedin%' THEN 'Social Media'
                             WHEN referrer IS NULL OR referrer = '' THEN 'Direct Traffic'
                             ELSE 'Other Referrals'
                         END as source_type,
                         COUNT(DISTINCT session_id) as visitors,
                         COUNT(*) as visits,
                         COUNT(DISTINCT news_id) as pages_viewed
                         FROM user_activity 
                         WHERE created_at BETWEEN '$start_date' AND '$end_date'
                         AND action = 'view'
                         GROUP BY source_type
                         ORDER BY visits DESC";
$traffic_sources_result = mysqli_query($conn, $traffic_sources_query);
if (!$traffic_sources_result) {
    // Fallback query using news data instead of user_activity
    $traffic_sources_query = "SELECT 
                             'Direct Traffic' as source_type,
                             COUNT(*) as visits,
                             COUNT(DISTINCT n.id) as pages_viewed,
                             COUNT(*) as visitors
                             FROM news n
                             WHERE n.status = 'published' 
                             AND n.published_at BETWEEN '$start_date' AND '$end_date'";
    $traffic_sources_result = mysqli_query($conn, $traffic_sources_query);
}

// Reporter performance
$reporter_performance_query = "SELECT u.id, u.name as reporter_name, u.email,
                              COUNT(n.id) as articles_published,
                              SUM(n.views) as total_views,
                              AVG(n.views) as avg_views_per_article,
                              MAX(n.views) as best_article_views,
                              COUNT(CASE WHEN n.published_at >= '$start_date' THEN 1 END) as recent_articles
                              FROM users u
                              LEFT JOIN news n ON u.id = n.author_id AND n.status = 'published'
                              WHERE u.role IN ('admin', 'editor', 'reporter')
                              GROUP BY u.id, u.name, u.email
                              HAVING articles_published > 0
                              ORDER BY total_views DESC
                              LIMIT 10";
$reporter_performance_result = mysqli_query($conn, $reporter_performance_query);

// Trending topics (based on recent news views)
$trending_topics_query = "SELECT 
                         c.name as topic, c.color,
                         COUNT(n.id) as articles,
                         SUM(n.views) as total_views,
                         AVG(n.views) as avg_views
                         FROM news n
                         JOIN categories c ON n.category_id = c.id
                         WHERE n.status = 'published' AND n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                         GROUP BY c.id, c.name, c.color
                         ORDER BY total_views DESC
                         LIMIT 8";
$trending_topics_result = mysqli_query($conn, $trending_topics_query);

// Initialize trending_data as empty array
$trending_data = [];

// Process trending topics data with error handling
if ($trending_topics_result) {
    while ($topic = mysqli_fetch_assoc($trending_topics_result)) {
        $trending_data[] = [
            'topic' => $topic['topic'] ?? 'Unknown',
            'color' => $topic['color'] ?? '#007bff',
            'interactions' => (int)($topic['total_views'] ?? 0),
            'articles' => (int)($topic['articles'] ?? 0),
            'unique_visitors' => (int)($topic['avg_views'] ?? 0),
            'avg_engagement_time' => (int)($topic['avg_views'] ?? 0)
        ];
    }
}
$hourly_trends_query = "SELECT 
                        HOUR(published_at) as hour,
                        COUNT(*) as articles_published,
                        SUM(views) as total_views
                        FROM news 
                        WHERE status = 'published' 
                        AND published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        GROUP BY HOUR(published_at)
                        ORDER BY hour";
$hourly_trends_result = mysqli_query($conn, $hourly_trends_query);

// Prepare data for charts
$traffic_sources_data = [];
$reporter_data = [];
$hourly_data = [];

// Process traffic sources data
while ($source = mysqli_fetch_assoc($traffic_sources_result)) {
    $traffic_sources_data[] = [
        'source' => $source['source_type'],
        'visitors' => (int)$source['visitors'],
        'visits' => (int)$source['visits'],
        'pages' => (int)$source['pages_viewed']
    ];
}

// Process reporter performance data
while ($reporter = mysqli_fetch_assoc($reporter_performance_result)) {
    $reporter_data[] = [
        'name' => $reporter['reporter_name'],
        'articles' => (int)$reporter['articles_published'],
        'total_views' => (int)$reporter['total_views'],
        'avg_views' => round($reporter['avg_views_per_article'], 1),
        'best_views' => (int)$reporter['best_article_views'],
        'recent' => (int)$reporter['recent_articles']
    ];
}

// Process hourly trends data
$hourly_array = [];
for ($h = 0; $h < 24; $h++) {
    $hourly_array[$h] = [
        'visitors' => 0,
        'visits' => 0,
        'pages' => 0
    ];
}

while ($hour = mysqli_fetch_assoc($hourly_trends_result)) {
    $hourly_array[$hour['hour']] = [
        'visitors' => (int)$hour['articles_published'],
        'visits' => (int)$hour['total_views'],
        'pages' => (int)$hour['total_views']
    ];
}

for ($h = 0; $h < 24; $h++) {
    $hourly_data[] = [
        'hour' => $h,
        'label' => $h . ':00',
        'visitors' => $hourly_array[$h]['visitors'],
        'visits' => $hourly_array[$h]['visits'],
        'pages' => $hourly_array[$h]['pages']
    ];
}
?>

<?php include '../includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line me-2"></i>Website Performance Monitor</h2>
                <div class="btn-group" role="group">
                    <a href="?date_range=1h" class="btn btn-outline-primary <?php echo $date_range == '1h' ? 'active' : ''; ?>">1 Hour</a>
                    <a href="?date_range=24h" class="btn btn-outline-primary <?php echo $date_range == '24h' ? 'active' : ''; ?>">24 Hours</a>
                    <a href="?date_range=7d" class="btn btn-outline-primary <?php echo $date_range == '7d' ? 'active' : ''; ?>">7 Days</a>
                    <a href="?date_range=30d" class="btn btn-outline-primary <?php echo $date_range == '30d' ? 'active' : ''; ?>">30 Days</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users me-2"></i>Live Visitors</h5>
                    <h2><?php echo number_format($real_time_stats['active_visitors'] ?? 0); ?></h2>
                    <small>Active in last 30 min</small>
                    <div class="mt-2">
                        <small><?php echo number_format($real_time_stats['logged_in_users'] ?? 0); ?> logged in</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-globe me-2"></i>Unique IPs</h5>
                    <h2><?php echo number_format($real_time_stats['unique_ips'] ?? 0); ?></h2>
                    <small>Last 30 minutes</small>
                    <div class="mt-2">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-fire me-2"></i>Trending Topics</h5>
                    <h2><?php echo is_array($trending_data) ? count($trending_data) : 0; ?></h2>
                    <small>Active topics today</small>
                    <div class="mt-2">
                        <small>Updated in real-time</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-newspaper me-2"></i>Top Article</h5>
                    <h6><?php 
                        $top_article = mysqli_fetch_assoc($most_read_result);
                        if ($top_article && isset($top_article['title'])) {
                            echo strlen($top_article['title']) > 25 ? substr(htmlspecialchars($top_article['title']), 0, 25) . '...' : htmlspecialchars($top_article['title']);
                        } else {
                            echo 'No articles available';
                        }
                    ?></h6>
                    <small><?php echo isset($top_article) ? number_format($top_article['views'] ?? 0) : '0'; ?> views</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area me-2"></i>Hourly Visitor Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Traffic Sources</h5>
                </div>
                <div class="card-body">
                    <canvas id="trafficSourcesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Read Articles & Trending Topics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bookmark me-2"></i>Most Read Articles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Views</th>
                                    <th>Category</th>
                                    <th>Engagement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($most_read_result, 0);
                                while ($article = mysqli_fetch_assoc($most_read_result)): 
                                ?>
                                    <tr>
                                        <td>
                                            <a href="../news.php?slug=<?php echo $article['slug']; ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                                <?php echo strlen($article['title']) > 30 ? substr(htmlspecialchars($article['title'] ?? ''), 0, 30) . '...' : htmlspecialchars($article['title'] ?? ''); ?>
                                            </a>
                                        </td>
                                        <td><strong><?php echo number_format($article['views']); ?></strong></td>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $article['category_color']; ?>; color: white;">
                                                <?php echo htmlspecialchars($article['category_name'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo number_format($article['engagement'] ?? 0); ?></small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-fire me-2"></i>Trending Topics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($trending_data)): ?>
                        <?php foreach ($trending_data as $index => $topic): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                    <div>
                                        <h6 class="mb-0" style="color: <?php echo $topic['color']; ?>;">
                                            <?php echo htmlspecialchars($topic['topic'] ?? ''); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo $topic['articles']; ?> articles • 
                                            <?php echo $topic['unique_visitors']; ?> visitors
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo number_format($topic['interactions']); ?></strong>
                                    <div class="small text-muted">interactions</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No trending topics available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporter Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-tie me-2"></i>Reporter Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Reporter</th>
                                    <th>Total Articles</th>
                                    <th>Total Views</th>
                                    <th>Avg Views/Article</th>
                                    <th>Best Article</th>
                                    <th>Recent Activity</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reporter_data as $reporter): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($reporter['name'] ?? ''); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($reporter['email'] ?? ''); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo number_format($reporter['articles']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($reporter['total_views']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo number_format($reporter['avg_views']); ?>
                                        </td>
                                        <td>
                                            <?php echo number_format($reporter['best_views']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $reporter['recent'] > 0 ? 'success' : 'secondary'; ?>">
                                                <?php echo $reporter['recent']; ?> recent
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $performance_score = ($reporter['total_views'] / $reporter['articles']) * ($reporter['recent'] > 0 ? 1.2 : 1);
                                            if ($performance_score > 1000) {
                                                echo '<span class="badge bg-success">Excellent</span>';
                                            } elseif ($performance_score > 500) {
                                                echo '<span class="badge bg-primary">Good</span>';
                                            } else {
                                                echo '<span class="badge bg-warning">Average</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
// Hourly Trends Chart
const hourlyCtx = document.getElementById('hourlyTrendsChart').getContext('2d');
const hourlyData = <?php echo json_encode($hourly_data); ?>;

new Chart(hourlyCtx, {
    type: 'line',
    data: {
        labels: hourlyData.map(d => d.label),
        datasets: [{
            label: 'Unique Visitors',
            data: hourlyData.map(d => d.visitors),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Total Visits',
            data: hourlyData.map(d => d.visits),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
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
                beginAtZero: true
            }
        }
    }
});

// Traffic Sources Chart
const trafficCtx = document.getElementById('trafficSourcesChart').getContext('2d');
const trafficData = <?php echo json_encode($traffic_sources_data); ?>;

new Chart(trafficCtx, {
    type: 'doughnut',
    data: {
        labels: trafficData.map(d => d.source),
        datasets: [{
            data: trafficData.map(d => d.visits),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
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

// Auto-refresh every 30 seconds for real-time monitoring
setInterval(() => {
    location.reload();
}, 30000);

// Add real-time indicator
document.addEventListener('DOMContentLoaded', function() {
    const lastUpdate = new Date();
    const updateIndicator = document.createElement('div');
    updateIndicator.className = 'position-fixed bottom-0 end-0 p-3';
    updateIndicator.style.zIndex = '11';
    updateIndicator.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-body">
                <i class="fas fa-circle text-success me-2"></i>
                Live monitoring - Last updated: ${lastUpdate.toLocaleTimeString()}
            </div>
        </div>
    `;
    document.body.appendChild(updateIndicator);
});
</script>

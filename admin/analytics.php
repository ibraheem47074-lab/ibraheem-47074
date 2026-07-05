<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if analytics tables exist
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_analytics'");
if (mysqli_num_rows($tables_check) === 0) {
    redirect('../install_analytics_simple.php');
}

// Get date range filter
$date_range = isset($_GET['date_range']) ? clean_input($_GET['date_range']) : '7days';
$where_clause = '';
switch ($date_range) {
    case 'today':
        $where_clause = "AND DATE(na.date) = CURDATE()";
        break;
    case '7days':
        $where_clause = "AND na.date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case '30days':
        $where_clause = "AND na.date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    case '90days':
        $where_clause = "AND na.date >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        break;
    case '1year':
        $where_clause = "AND na.date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
    default:
        $where_clause = "AND na.date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

// Get analytics summary
$summary_query = "SELECT 
    SUM(na.views) as total_views,
    SUM(na.unique_visitors) as total_unique_views,
    0 as total_shares,
    0 as total_comments,
    AVG(na.avg_read_time) as avg_read_time,
    0 as avg_bounce_rate,
    COUNT(DISTINCT na.news_id) as total_articles
    FROM news_analytics na
    WHERE 1=1 $where_clause";
$summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query));

// Get popular articles
$popular_query = "SELECT 
    n.id, n.title, n.slug, n.image, n.views,
    SUM(na.views) as analytics_views,
    0 as analytics_shares,
    0 as analytics_comments,
    c.name as category_name
    FROM news n
    LEFT JOIN news_analytics na ON n.id = na.news_id
    LEFT JOIN categories c ON n.category_id = c.id
    WHERE n.status = 'published' $where_clause
    GROUP BY n.id
    ORDER BY analytics_views DESC, n.views DESC
    LIMIT 10";
$popular_articles = mysqli_query($conn, $popular_query);

// Get category analytics
$category_query = "SELECT 
    c.id, c.name,
    COUNT(n.id) as total_articles,
    COALESCE(SUM(na.views), 0) as total_views,
    0 as total_shares,
    0 as total_comments
    FROM categories c
    LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published'
    LEFT JOIN news_analytics na ON n.id = na.news_id $where_clause
    GROUP BY c.id
    ORDER BY total_views DESC
    LIMIT 10";
$category_analytics = mysqli_query($conn, $category_query);

// Get daily analytics chart data
$chart_query = "SELECT 
    DATE(na.date) as chart_date,
    SUM(na.views) as daily_views,
    SUM(na.unique_visitors) as daily_unique_views,
    0 as daily_shares,
    0 as daily_comments
    FROM news_analytics na
    WHERE 1=1 $where_clause
    GROUP BY DATE(na.date)
    ORDER BY chart_date ASC
    LIMIT 30";
$chart_data = mysqli_query($conn, $chart_query);

// Get user activity stats using user_analytics table
$activity_query = "SELECT 
    'page_view' as action,
    COUNT(*) as count,
    COUNT(DISTINCT user_id) as unique_users
    FROM user_analytics 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY action
    ORDER BY count DESC
    LIMIT 10";
$activity_stats = mysqli_query($conn, $activity_query);

// Get search analytics using page_views table for search pages
$search_query = "SELECT 
    'search' as query,
    COUNT(*) as search_count,
    COUNT(DISTINCT ip_address) as unique_searches
    FROM page_views 
    WHERE page_type = 'search' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY query
    ORDER BY search_count DESC
    LIMIT 10";
$search_analytics = mysqli_query($conn, $search_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .admin-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .admin-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        .metric-change {
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .metric-change.positive {
            color: #28a745;
        }
        .metric-change.negative {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                        <small>Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-tags.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-polls.php">
                                <i class="fas fa-poll me-2"></i>Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-ads.php">
                                <i class="fas fa-ad me-2"></i>Advertisements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Analytics Dashboard</h1>
                        <small>Track your website performance and user engagement</small>
                    </div>
                    <div>
                        <form method="GET" class="d-flex">
                            <select name="date_range" class="form-select me-2" onchange="this.form.submit()">
                                <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="7days" <?php echo $date_range === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30days" <?php echo $date_range === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="90days" <?php echo $date_range === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                                <option value="1year" <?php echo $date_range === '1year' ? 'selected' : ''; ?>>Last Year</option>
                            </select>
                            <button type="button" class="btn btn-light" onclick="exportAnalytics()">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="metric-label">Total Views</div>
                                <div class="metric-value"><?php echo number_format($summary['total_views'] ?: 0 ?? 0); ?></div>
                                <div class="metric-change positive">
                                    <i class="fas fa-arrow-up me-1"></i>+12.5%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-success text-white">
                            <div class="card-body">
                                <div class="metric-label">Unique Visitors</div>
                                <div class="metric-value"><?php echo number_format($summary['total_unique_views'] ?: 0 ?? 0); ?></div>
                                <div class="metric-change positive">
                                    <i class="fas fa-arrow-up me-1"></i>+8.3%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-info text-white">
                            <div class="card-body">
                                <div class="metric-label">Total Shares</div>
                                <div class="metric-value"><?php echo number_format($summary['total_shares'] ?: 0 ?? 0); ?></div>
                                <div class="metric-change positive">
                                    <i class="fas fa-arrow-up me-1"></i>+15.7%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-warning text-white">
                            <div class="card-body">
                                <div class="metric-label">Comments</div>
                                <div class="metric-value"><?php echo number_format($summary['total_comments'] ?: 0 ?? 0); ?></div>
                                <div class="metric-change negative">
                                    <i class="fas fa-arrow-down me-1"></i>-3.2%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line me-2"></i>Traffic Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trafficChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Popular Articles -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-fire me-2"></i>Popular Articles</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Views</th>
                                                <th>Shares</th>
                                                <th>Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($article = mysqli_fetch_assoc($popular_articles)): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($article['image']): ?>
                                                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;" 
                                                                     class="me-3">
                                                            <?php endif; ?>
                                                            <div>
                                                                <a href="../news.php?slug=<?php echo $article['slug']; ?>" 
                                                                   class="text-decoration-none" target="_blank">
                                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                                    </td>
                                                    <td><?php echo number_format($article['analytics_views'] ?: $article['views'] ?? 0); ?></td>
                                                    <td><?php echo number_format($article['analytics_shares'] ?: 0 ?? 0); ?></td>
                                                    <td><?php echo number_format($article['analytics_comments'] ?: 0 ?? 0); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie me-2"></i>Category Performance</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Articles</th>
                                                <th>Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($category = mysqli_fetch_assoc($category_analytics)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td><?php echo $category['total_articles']; ?></td>
                                                    <td><?php echo number_format($category['total_views'] ?? 0); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity & Search Analytics -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-users me-2"></i>User Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Count</th>
                                                <th>Unique Users</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($activity = mysqli_fetch_assoc($activity_stats)): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php 
                                                            $action_labels = [
                                                                'view' => 'Page Views',
                                                                'share' => 'Shares',
                                                                'comment' => 'Comments',
                                                                'bookmark' => 'Bookmarks'
                                                            ];
                                                            echo $action_labels[$activity['action']] ?? ucfirst($activity['action']);
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo number_format($activity['count'] ?? 0); ?></td>
                                                    <td><?php echo number_format($activity['unique_users'] ?? 0); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-search me-2"></i>Search Analytics</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Query</th>
                                                <th>Searches</th>
                                                <th>Unique</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($search = mysqli_fetch_assoc($search_analytics)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($search['query']); ?></td>
                                                    <td><?php echo number_format($search['search_count'] ?? 0); ?></td>
                                                    <td><?php echo number_format($search['unique_searches'] ?? 0); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Traffic Chart
        const ctx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $chart_data_array = [];
                    while ($data = mysqli_fetch_assoc($chart_data)) {
                        $chart_data_array[] = $data;
                        echo "'" . date('M j', strtotime($data['chart_date'])) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Views',
                    data: [
                        <?php 
                        foreach ($chart_data_array as $data) {
                            echo $data['daily_views'] . ',';
                        }
                        ?>
                    ],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Unique Views',
                    data: [
                        <?php 
                        foreach ($chart_data_array as $data) {
                            echo $data['daily_unique_views'] . ',';
                        }
                        ?>
                    ],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export Analytics
        function exportAnalytics() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.open('analytics.php?' + params.toString(), '_blank');
        }
    </script>
</body>
</html>

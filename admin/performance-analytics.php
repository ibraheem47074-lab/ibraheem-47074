<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Get date range filter
$date_range = isset($_GET['date_range']) ? clean_input($_GET['date_range']) : '30days';
$where_clause = '';
switch ($date_range) {
    case 'today':
        $where_clause = "AND DATE(n.created_at) = CURDATE()";
        break;
    case '7days':
        $where_clause = "AND n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case '30days':
        $where_clause = "AND n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    case '90days':
        $where_clause = "AND n.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        break;
    case '1year':
        $where_clause = "AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
    default:
        $where_clause = "AND n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Monthly Performance Trends
$monthly_query = "SELECT 
    DATE_FORMAT(n.created_at, '%Y-%m') as month,
    COUNT(*) as articles_count,
    SUM(n.views) as total_views,
    AVG(n.views) as avg_views_per_article,
    COUNT(DISTINCT n.author_id) as active_authors,
    COUNT(DISTINCT n.category_id) as categories_used
    FROM news n 
    WHERE n.status = 'published' $where_clause
    GROUP BY DATE_FORMAT(n.created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12";
$monthly_data = mysqli_query($conn, $monthly_query);

// Top Categories Performance
$categories_query = "SELECT 
    c.name, c.color,
    COUNT(n.id) as articles_count,
    SUM(n.views) as total_views,
    AVG(n.views) as avg_views_per_article,
    0 as total_likes,
    0 as total_comments,
    (SUM(n.views) / COUNT(n.id)) as performance_score
    FROM categories c
    LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published' $where_clause
    GROUP BY c.id, c.name, c.color
    HAVING articles_count > 0
    ORDER BY performance_score DESC
    LIMIT 10";
$categories_data = mysqli_query($conn, $categories_query);

// Activity Overview
$activity_query = "SELECT 
    DATE(n.created_at) as activity_date,
    COUNT(*) as articles_published,
    SUM(n.views) as daily_views,
    COUNT(DISTINCT n.author_id) as active_reporters,
    0 as daily_likes,
    0 as daily_comments
    FROM news n 
    WHERE n.status = 'published' $where_clause
    GROUP BY DATE(n.created_at)
    ORDER BY activity_date DESC
    LIMIT 30";
$activity_data = mysqli_query($conn, $activity_query);

// Engagement Metrics
$engagement_query = "SELECT 
    COUNT(*) as total_articles,
    SUM(n.views) as total_views,
    0 as total_likes,
    0 as total_comments,
    0 as total_shares,
    AVG(n.views) as avg_views,
    0 as avg_likes,
    0 as avg_comments,
    0 as avg_engagement_score,
    COUNT(DISTINCT n.author_id) as total_authors,
    COUNT(CASE WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_articles
    FROM news n 
    WHERE n.status = 'published' $where_clause";
$engagement_data = mysqli_query($conn, $engagement_query);
$engagement_stats = mysqli_fetch_assoc($engagement_data);

// Top Performing Authors
$authors_query = "SELECT 
    u.id, u.name as author_name, u.email,
    COUNT(n.id) as articles_count,
    SUM(n.views) as total_views,
    AVG(n.views) as avg_views_per_article,
    MAX(n.views) as best_article_views,
    0 as total_likes,
    0 as total_comments
    FROM users u
    LEFT JOIN news n ON u.id = n.author_id AND n.status = 'published' $where_clause
    WHERE u.role IN ('admin', 'editor', 'reporter')
    GROUP BY u.id, u.name, u.email
    HAVING articles_count > 0
    ORDER BY total_views DESC
    LIMIT 10";
$authors_data = mysqli_query($conn, $authors_query);

// Content Performance by Type
$content_type_query = "SELECT 
    news_type,
    COUNT(*) as articles_count,
    SUM(views) as total_views,
    AVG(views) as avg_views,
    0 as total_likes,
    0 as total_comments
    FROM news n
    WHERE n.status = 'published' $where_clause
    GROUP BY n.news_type
    ORDER BY total_views DESC";
$content_type_data = mysqli_query($conn, $content_type_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Analytics - PK Live News Admin</title>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
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
        .performance-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .trend-up {
            color: #28a745;
        }
        .trend-down {
            color: #dc3545;
        }
        .activity-timeline {
            position: relative;
            padding-left: 30px;
        }
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .activity-item {
            position: relative;
            margin-bottom: 20px;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #667eea;
        }
        .engagement-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
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
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="performance-analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Performance Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-bar me-2"></i>General Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
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
                        <h1 class="h3 mb-0">My Performance Analytics</h1>
                        <small>Comprehensive performance metrics and trends analysis</small>
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
                            <button type="button" class="btn btn-light" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Key Performance Metrics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-gradient-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="metric-label">Total Articles</div>
                                        <div class="metric-value"><?php echo number_format($engagement_stats['total_articles'] ?? 0); ?></div>
                                        <div class="metric-change">
                                            <small><i class="fas fa-newspaper me-1"></i>Published</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-newspaper fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-gradient-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="metric-label">Total Views</div>
                                        <div class="metric-value"><?php echo number_format($engagement_stats['total_views'] ?? 0); ?></div>
                                        <div class="metric-change">
                                            <small><i class="fas fa-eye me-1"></i>Page Views</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-eye fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-gradient-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="metric-label">Engagement Rate</div>
                                        <div class="metric-value"><?php echo number_format($engagement_stats['avg_engagement_score'] ?? 0, 1); ?></div>
                                        <div class="metric-change">
                                            <small><i class="fas fa-heart me-1"></i>Average Score</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card bg-gradient-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="metric-label">Active Authors</div>
                                        <div class="metric-value"><?php echo number_format($engagement_stats['total_authors'] ?? 0); ?></div>
                                        <div class="metric-change">
                                            <small><i class="fas fa-users me-1"></i>Contributors</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-users fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance Trends -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-chart-area me-2"></i>Monthly Performance Trends</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchChart('views')">Views</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchChart('articles')">Articles</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="switchChart('authors')">Authors</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Top Categories -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-trophy me-2"></i>Top Categories</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="categoriesChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <?php while ($category = mysqli_fetch_assoc($categories_data)): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle me-2" style="width: 12px; height: 12px; background: <?php echo $category['color'] ?? '#667eea'; ?>;"></div>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></span>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary performance-badge"><?php echo number_format($category['total_views']); ?> views</span>
                                                <span class="badge bg-success performance-badge"><?php echo $category['articles_count']; ?> articles</span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Overview -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-day me-2"></i>Activity Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline" style="max-height: 350px; overflow-y: auto;">
                                    <?php while ($activity = mysqli_fetch_assoc($activity_data)): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold"><?php echo date('M j, Y', strtotime($activity['activity_date'])); ?></div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-newspaper me-1"></i><?php echo $activity['articles_published']; ?> articles
                                                        <i class="fas fa-eye ms-2 me-1"></i><?php echo number_format($activity['daily_views']); ?> views
                                                        <i class="fas fa-users ms-2 me-1"></i><?php echo $activity['active_reporters']; ?> active
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small text-muted">
                                                        <i class="fas fa-heart me-1"></i><?php echo $activity['daily_likes']; ?>
                                                        <i class="fas fa-comments ms-2 me-1"></i><?php echo $activity['daily_comments']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Engagement Metrics -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-heart-pulse me-2"></i>Engagement Metrics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center mb-4">
                                        <div class="engagement-ring bg-primary text-white mx-auto">
                                            <div>
                                                <div class="metric-value" style="font-size: 1.5rem;"><?php echo number_format($engagement_stats['avg_views'] ?? 0, 0); ?></div>
                                                <div class="small">Avg Views</div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-primary">Per Article</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-4">
                                        <div class="engagement-ring bg-success text-white mx-auto">
                                            <div>
                                                <div class="metric-value" style="font-size: 1.5rem;"><?php echo number_format($engagement_stats['total_likes'] ?? 0); ?></div>
                                                <div class="small">Total Likes</div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-success">Engagement</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-4">
                                        <div class="engagement-ring bg-info text-white mx-auto">
                                            <div>
                                                <div class="metric-value" style="font-size: 1.5rem;"><?php echo number_format($engagement_stats['total_comments'] ?? 0); ?></div>
                                                <div class="small">Comments</div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-info">Interactions</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center mb-4">
                                        <div class="engagement-ring bg-warning text-white mx-auto">
                                            <div>
                                                <div class="metric-value" style="font-size: 1.5rem;"><?php echo number_format($engagement_stats['total_shares'] ?? 0); ?></div>
                                                <div class="small">Shares</div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-warning">Social</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Authors -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-star me-2"></i>Top Performing Authors</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Author</th>
                                                <th>Articles</th>
                                                <th>Total Views</th>
                                                <th>Avg Views/Article</th>
                                                <th>Best Article</th>
                                                <th>Engagement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($author = mysqli_fetch_assoc($authors_data)): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                                <?php echo strtoupper(substr($author['author_name'], 0, 2)); ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold"><?php echo htmlspecialchars($author['author_name']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($author['email']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $author['articles_count']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo number_format($author['total_views']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="text-success"><?php echo number_format($author['avg_views_per_article'], 0); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="text-primary"><?php echo number_format($author['best_article_views']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1">
                                                            <span class="badge bg-danger"><?php echo $author['total_likes']; ?> <i class="fas fa-heart"></i></span>
                                                            <span class="badge bg-primary"><?php echo $author['total_comments']; ?> <i class="fas fa-comments"></i></span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Type Performance -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-layer-group me-2"></i>Content Type Performance</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php while ($content_type = mysqli_fetch_assoc($content_type_data)): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary"><?php echo ucfirst($content_type['news_type']); ?></h6>
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="fw-bold"><?php echo $content_type['articles_count']; ?></div>
                                                            <small class="text-muted">Articles</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="fw-bold"><?php echo number_format($content_type['total_views']); ?></div>
                                                            <small class="text-muted">Views</small>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="fw-bold"><?php echo number_format($content_type['avg_views'], 0); ?></div>
                                                            <small class="text-muted">Avg</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
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
        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        const monthlyData = <?php 
            $monthly_array = [];
            while ($data = mysqli_fetch_assoc($monthly_data)) {
                $monthly_array[] = $data;
            }
            echo json_encode($monthly_array);
        ?>;
        
        const monthlyTrendsChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Total Views',
                    data: monthlyData.map(d => d.total_views),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Articles Published',
                    data: monthlyData.map(d => d.articles_count),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat().format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Views'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Articles'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });

        // Categories Chart
        const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
        const categoriesData = <?php 
            mysqli_data_seek($categories_data, 0);
            $categories_array = [];
            while ($data = mysqli_fetch_assoc($categories_data)) {
                $categories_array[] = $data;
            }
            echo json_encode($categories_array);
        ?>;

        const categoriesChart = new Chart(categoriesCtx, {
            type: 'doughnut',
            data: {
                labels: categoriesData.map(d => d.name),
                datasets: [{
                    data: categoriesData.map(d => d.total_views),
                    backgroundColor: categoriesData.map(d => d.color || '#667eea'),
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = new Intl.NumberFormat().format(context.parsed);
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${label}: ${value} views (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Switch chart view
        function switchChart(type) {
            const chart = monthlyTrendsChart;
            
            switch(type) {
                case 'views':
                    chart.data.datasets[0].data = monthlyData.map(d => d.total_views);
                    chart.data.datasets[1].data = monthlyData.map(d => d.articles_count);
                    chart.data.datasets[0].label = 'Total Views';
                    chart.data.datasets[1].label = 'Articles Published';
                    break;
                case 'articles':
                    chart.data.datasets[0].data = monthlyData.map(d => d.articles_count);
                    chart.data.datasets[1].data = monthlyData.map(d => d.active_authors);
                    chart.data.datasets[0].label = 'Articles Published';
                    chart.data.datasets[1].label = 'Active Authors';
                    break;
                case 'authors':
                    chart.data.datasets[0].data = monthlyData.map(d => d.active_authors);
                    chart.data.datasets[1].data = monthlyData.map(d => d.categories_used);
                    chart.data.datasets[0].label = 'Active Authors';
                    chart.data.datasets[1].label = 'Categories Used';
                    break;
            }
            chart.update();
        }

        // Refresh data
        function refreshData() {
            location.reload();
        }

        // Auto-refresh every 5 minutes
        setInterval(() => {
            console.log('Auto-refreshing analytics data...');
        }, 300000);
    </script>
</body>
</html>

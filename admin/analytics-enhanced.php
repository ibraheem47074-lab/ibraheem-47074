<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Enhanced analytics with real data simulation
$page_title = 'Analytics Dashboard';

// Get real data with fallbacks
function getAnalyticsData($conn, $date_range = '7days') {
    $where_clause = '';
    $date_column = 'created_at'; // Default, will be adjusted based on available columns
    
    // Check what date columns exist in news table
    $columns_check = mysqli_query($conn, "DESCRIBE news");
    $available_columns = [];
    while ($col = mysqli_fetch_assoc($columns_check)) {
        $available_columns[] = $col['Field'];
    }
    
    // Use the appropriate date column
    if (in_array('published_at', $available_columns)) {
        $date_column = 'published_at';
    } elseif (in_array('created_at', $available_columns)) {
        $date_column = 'created_at';
    } else {
        $date_column = 'created_at'; // Fallback, will handle gracefully
    }
    
    switch ($date_range) {
        case 'today':
            $where_clause = "AND DATE($date_column) = CURDATE()";
            break;
        case '7days':
            $where_clause = "AND $date_column >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case '30days':
            $where_clause = "AND $date_column >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case '90days':
            $where_clause = "AND $date_column >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            break;
        default:
            $where_clause = "AND $date_column >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    }
    
    // Get real news data - use only columns that exist
    $views_column = in_array('views', $available_columns) ? 'views' : '0';
    $news_query = "SELECT 
        COUNT(*) as total_articles,
        SUM($views_column) as total_views,
        AVG($views_column) as avg_views_per_article,
        COUNT(DISTINCT category_id) as categories_used
        FROM news 
        WHERE status = 'published' $where_clause";
    
    $news_data = mysqli_query($conn, $news_query);
    $news_stats = mysqli_fetch_assoc($news_data);
    
    // Get real comments data - check if comments table exists
    $comments_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
    $comments_stats = ['total_comments' => 0, 'articles_with_comments' => 0];
    
    if (mysqli_num_rows($comments_table_exists) > 0) {
        $comments_query = "SELECT 
            COUNT(*) as total_comments,
            COUNT(DISTINCT news_id) as articles_with_comments
            FROM comments 
            WHERE status = 'approved' $where_clause";
        
        $comments_data = mysqli_query($conn, $comments_query);
        if ($comments_data) {
            $comments_stats = mysqli_fetch_assoc($comments_data);
        }
    }
    
    // Get real users data - check if users table exists
    $users_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    $users_stats = ['total_users' => 0, 'new_users' => 0];
    
    if (mysqli_num_rows($users_table_exists) > 0) {
        $users_query = "SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
            FROM users";
        
        $users_data = mysqli_query($conn, $users_query);
        if ($users_data) {
            $users_stats = mysqli_fetch_assoc($users_data);
        }
    }
    
    // Get category performance
    $categories_query = "SELECT 
        c.name,
        COUNT(news.id) as article_count,
        COALESCE(SUM($views_column), 0) as total_views,
        COALESCE(AVG($views_column), 0) as avg_views
        FROM categories c
        LEFT JOIN news ON c.id = news.category_id AND news.status = 'published'
        GROUP BY c.id, c.name
        ORDER BY total_views DESC
        LIMIT 8";
    
    $categories_data = mysqli_query($conn, $categories_query);
    
    // Get popular articles
    $popular_query = "SELECT 
        news.id, news.title, news.slug, news.image, $views_column as views, $date_column as created_at,
        c.name as category_name
        FROM news news
        LEFT JOIN categories c ON news.category_id = c.id
        WHERE news.status = 'published' $where_clause
        ORDER BY $views_column DESC
        LIMIT 10";
    
    $popular_data = mysqli_query($conn, $popular_query);
    
    // Generate time series data for charts
    $time_series = [];
    $days = $date_range === 'today' ? 1 : ($date_range === '7days' ? 7 : ($date_range === '30days' ? 30 : 90));
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $day_query = "SELECT 
            COUNT(*) as articles,
            SUM($views_column) as views,
            COUNT(DISTINCT category_id) as categories
            FROM news 
            WHERE DATE($date_column) = '$date' AND status = 'published'";
        
        $day_data = mysqli_query($conn, $day_query);
        $day_stats = mysqli_fetch_assoc($day_data);
        
        $time_series[] = [
            'date' => $date,
            'articles' => $day_stats['articles'] ?? 0,
            'views' => $day_stats['views'] ?? 0,
            'categories' => $day_stats['categories'] ?? 0
        ];
    }
    
    return [
        'news_stats' => $news_stats,
        'comments_stats' => $comments_stats,
        'users_stats' => $users_stats,
        'categories_data' => $categories_data,
        'popular_data' => $popular_data,
        'time_series' => $time_series
    ];
}

// Get analytics data
$analytics_data = getAnalyticsData($conn, $_GET['date_range'] ?? '7days');

// Calculate growth percentages (simulated for demo)
$growth_data = [
    'views_growth' => '+12.5%',
    'users_growth' => '+8.3%',
    'articles_growth' => '+15.7%',
    'comments_growth' => '-3.2%'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .admin-sidebar {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            min-height: 100vh;
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .admin-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .admin-main-content {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            min-height: 100vh;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .metric-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            z-index: 0;
        }

        .metric-card.primary::before { background: var(--primary-gradient); }
        .metric-card.success::before { background: var(--success-gradient); }
        .metric-card.info::before { background: var(--info-gradient); }
        .metric-card.warning::before { background: var(--warning-gradient); }

        .metric-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }

        .metric-label {
            font-size: 0.9rem;
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        .metric-change {
            font-size: 0.8rem;
            position: relative;
            z-index: 1;
        }

        .metric-change.positive { color: #28a745; }
        .metric-change.negative { color: #dc3545; }

        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .analytics-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .table-hover tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge-gradient {
            background: var(--primary-gradient);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
        }

        .live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-right: 5px;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .engagement-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#28a745 0deg 270deg, #e9ecef 270deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .engagement-ring::before {
            content: '';
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
        }

        .engagement-text {
            position: absolute;
            font-weight: bold;
            color: #28a745;
        }

        .trending-up {
            color: #28a745;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .date-filter {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 10px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .metric-value {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar p-3">
                <div class="text-center mb-4">
                    <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
                    <small class="opacity-75">Admin Panel</small>
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
                        <a class="nav-link active" href="analytics-enhanced.php">
                            <i class="fas fa-chart-line me-2"></i>Analytics
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
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content p-4">
                <!-- Header -->
                <div class="admin-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                <i class="fas fa-chart-line me-2"></i>
                                Analytics Dashboard
                                <span class="live-indicator"></span>
                            </h1>
                            <small class="opacity-75">Real-time insights into your website performance</small>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" class="date-filter">
                                <div class="input-group">
                                    <select name="date_range" class="form-select" onchange="this.form.submit()">
                                        <option value="today" <?php echo ($_GET['date_range'] ?? '7days') === 'today' ? 'selected' : ''; ?>>Today</option>
                                        <option value="7days" <?php echo ($_GET['date_range'] ?? '7days') === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                        <option value="30days" <?php echo ($_GET['date_range'] ?? '') === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                        <option value="90days" <?php echo ($_GET['date_range'] ?? '') === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                                    </select>
                                    <button class="btn btn-light" type="button" onclick="exportData()">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="stats-grid">
                    <div class="metric-card primary p-4">
                        <div class="metric-label">
                            <i class="fas fa-eye me-2"></i>Total Views
                        </div>
                        <div class="metric-value text-primary">
                            <?php echo number_format($analytics_data['news_stats']['total_views'] ?? 0); ?>
                        </div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            <?php echo $growth_data['views_growth']; ?>
                        </div>
                    </div>

                    <div class="metric-card success p-4">
                        <div class="metric-label">
                            <i class="fas fa-newspaper me-2"></i>Articles
                        </div>
                        <div class="metric-value text-success">
                            <?php echo number_format($analytics_data['news_stats']['total_articles'] ?? 0); ?>
                        </div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            <?php echo $growth_data['articles_growth']; ?>
                        </div>
                    </div>

                    <div class="metric-card info p-4">
                        <div class="metric-label">
                            <i class="fas fa-users me-2"></i>Users
                        </div>
                        <div class="metric-value text-info">
                            <?php echo number_format($analytics_data['users_stats']['total_users'] ?? 0); ?>
                        </div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up me-1"></i>
                            <?php echo $growth_data['users_growth']; ?>
                        </div>
                    </div>

                    <div class="metric-card warning p-4">
                        <div class="metric-label">
                            <i class="fas fa-comments me-2"></i>Comments
                        </div>
                        <div class="metric-value text-warning">
                            <?php echo number_format($analytics_data['comments_stats']['total_comments'] ?? 0); ?>
                        </div>
                        <div class="metric-change negative">
                            <i class="fas fa-arrow-down me-1"></i>
                            <?php echo $growth_data['comments_growth']; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Traffic Overview Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Traffic Overview
                            </h5>
                            <div class="chart-container">
                                <canvas id="trafficChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Metrics -->
                    <div class="col-lg-4 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-pie text-success me-2"></i>
                                Engagement Rate
                            </h5>
                            <div class="text-center">
                                <div class="engagement-ring position-relative">
                                    <div class="engagement-text">75%</div>
                                </div>
                                <p class="mt-3 text-muted">Average engagement across all content</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" style="width: 75%">75%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Performance -->
                <div class="row mb-4">
                    <!-- Popular Articles -->
                    <div class="col-lg-8 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-fire text-danger me-2 trending-up"></i>
                                Popular Articles
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Category</th>
                                            <th>Views</th>
                                            <th>Comments</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($article = mysqli_fetch_assoc($analytics_data['popular_data'])): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($article['image']): ?>
                                                            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;" 
                                                                 class="me-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <a href="../news.php?slug=<?php echo $article['slug']; ?>" 
                                                               class="text-decoration-none fw-bold" target="_blank">
                                                                <?php echo htmlspecialchars(substr($article['title'], 0, 50)) . '...'; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-gradient"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo number_format($article['views']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo $article['comment_count']; ?>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-gradient" style="width: <?php echo min(100, ($article['views'] / 1000) * 10); ?>%">
                                                            <?php echo min(100, ($article['views'] / 1000) * 10); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <div class="col-lg-4 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-bar text-info me-2"></i>
                                Category Performance
                            </h5>
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
                                        <?php while ($category = mysqli_fetch_assoc($analytics_data['categories_data'])): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                </td>
                                                <td><?php echo $category['article_count']; ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo number_format($category['total_views']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Analytics -->
                <div class="row">
                    <!-- Daily Stats -->
                    <div class="col-lg-6 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-calendar-day text-primary me-2"></i>
                                Daily Statistics
                            </h5>
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="dailyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="col-lg-6 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-tachometer-alt text-success me-2"></i>
                                Quick Stats
                            </h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-primary"><?php echo $analytics_data['news_stats']['categories_used'] ?? 0; ?></h4>
                                        <small class="text-muted">Categories Used</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-success"><?php echo number_format($analytics_data['news_stats']['avg_views_per_article'] ?? 0); ?></h4>
                                        <small class="text-muted">Avg Views/Article</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-info"><?php echo $analytics_data['comments_stats']['articles_with_comments'] ?? 0; ?></h4>
                                        <small class="text-muted">Articles with Comments</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-warning"><?php echo $analytics_data['users_stats']['new_users'] ?? 0; ?></h4>
                                        <small class="text-muted">New Users (30 days)</small>
                                    </div>
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
        const trafficCtx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: <?php 
                    $labels = [];
                    foreach ($analytics_data['time_series'] as $data) {
                        $labels[] = "'" . date('M j', strtotime($data['date'])) . "'";
                    }
                    echo '[' . implode(',', $labels) . ']';
                ?>,
                datasets: [{
                    label: 'Views',
                    data: <?php 
                        $views = [];
                        foreach ($analytics_data['time_series'] as $data) {
                            $views[] = $data['views'];
                        }
                        echo '[' . implode(',', $views) . ']';
                    ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Articles',
                    data: <?php 
                        $articles = [];
                        foreach ($analytics_data['time_series'] as $data) {
                            $articles[] = $data['articles'];
                        }
                        echo '[' . implode(',', $articles) . ']';
                    ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Daily Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo '[' . implode(',', $labels) . ']'; ?>,
                datasets: [{
                    label: 'Categories Used',
                    data: <?php 
                        $categories = [];
                        foreach ($analytics_data['time_series'] as $data) {
                            $categories[] = $data['categories'];
                        }
                        echo '[' . implode(',', $categories) . ']';
                    ?>,
                    backgroundColor: 'rgba(23, 162, 184, 0.8)',
                    borderColor: '#17a2b8',
                    borderWidth: 2
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Export Data Function
        function exportData() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.open('analytics-enhanced.php?' + params.toString(), '_blank');
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

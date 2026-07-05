<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Simple analytics that works with basic news table structure
function getSimpleAnalytics($conn) {
    // Get basic news stats - no date filtering
    $news_query = "SELECT 
        COUNT(*) as total_articles,
        COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles
        FROM news";
    
    $news_data = mysqli_query($conn, $news_query);
    $news_stats = mysqli_fetch_assoc($news_data);
    
    // Check if views column exists
    $columns_check = mysqli_query($conn, "DESCRIBE news");
    $has_views = false;
    $has_category = false;
    
    while ($col = mysqli_fetch_assoc($columns_check)) {
        if ($col['Field'] === 'views') $has_views = true;
        if ($col['Field'] === 'category_id') $has_category = true;
    }
    
    // Get views stats if column exists
    $views_stats = ['total_views' => 0, 'avg_views' => 0];
    if ($has_views) {
        $views_query = "SELECT 
            SUM(views) as total_views,
            AVG(views) as avg_views,
            MAX(views) as max_views
            FROM news WHERE status = 'published'";
        
        $views_data = mysqli_query($conn, $views_query);
        if ($views_data) {
            $views_stats = mysqli_fetch_assoc($views_data);
        }
    }
    
    // Get category stats if category_id exists
    $categories_data = [];
    if ($has_category) {
        $categories_query = "SELECT 
            c.name,
            COUNT(n.id) as article_count
            FROM categories c
            LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published'
            GROUP BY c.id, c.name
            ORDER BY article_count DESC
            LIMIT 8";
        
        $categories_result = mysqli_query($conn, $categories_query);
        while ($cat = mysqli_fetch_assoc($categories_result)) {
            $categories_data[] = $cat;
        }
    }
    
    // Get popular articles
    $popular_query = "SELECT 
        id, title, slug, image, status, category_id
        FROM news 
        WHERE status = 'published'";
    
    if ($has_views) {
        $popular_query .= " ORDER BY views DESC";
    } else {
        $popular_query .= " ORDER BY id DESC";
    }
    $popular_query .= " LIMIT 10";
    
    $popular_data = mysqli_query($conn, $popular_query);
    
    // Get users stats if table exists
    $users_stats = ['total_users' => 0];
    $users_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    
    if (mysqli_num_rows($users_table_exists) > 0) {
        $users_query = "SELECT COUNT(*) as total_users FROM users";
        $users_data = mysqli_query($conn, $users_query);
        if ($users_data) {
            $users_stats = mysqli_fetch_assoc($users_data);
        }
    }
    
    // Get comments stats if table exists
    $comments_stats = ['total_comments' => 0];
    $comments_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");
    
    if (mysqli_num_rows($comments_table_exists) > 0) {
        $comments_query = "SELECT COUNT(*) as total_comments FROM comments WHERE status = 'approved'";
        $comments_data = mysqli_query($conn, $comments_query);
        if ($comments_data) {
            $comments_stats = mysqli_fetch_assoc($comments_data);
        }
    }
    
    return [
        'news_stats' => $news_stats,
        'views_stats' => $views_stats,
        'categories_data' => $categories_data,
        'popular_data' => $popular_data,
        'users_stats' => $users_stats,
        'comments_stats' => $comments_stats,
        'has_views' => $has_views,
        'has_category' => $has_category
    ];
}

// Get analytics data
$analytics_data = getSimpleAnalytics($conn);
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

        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
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
                        <a class="nav-link active" href="analytics-simple.php">
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
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="stats-grid">
                    <div class="metric-card primary p-4">
                        <div class="metric-label">
                            <i class="fas fa-newspaper me-2"></i>Total Articles
                        </div>
                        <div class="metric-value text-primary">
                            <?php echo number_format($analytics_data['news_stats']['total_articles'] ?? 0); ?>
                        </div>
                        <div class="text-muted small mt-2">
                            Published: <?php echo number_format($analytics_data['news_stats']['published_articles'] ?? 0); ?>
                        </div>
                    </div>

                    <div class="metric-card success p-4">
                        <div class="metric-label">
                            <i class="fas fa-eye me-2"></i>Total Views
                        </div>
                        <div class="metric-value text-success">
                            <?php echo number_format($analytics_data['views_stats']['total_views'] ?? 0); ?>
                        </div>
                        <div class="text-muted small mt-2">
                            Avg: <?php echo number_format($analytics_data['views_stats']['avg_views'] ?? 0, 1); ?>
                        </div>
                    </div>

                    <div class="metric-card info p-4">
                        <div class="metric-label">
                            <i class="fas fa-users me-2"></i>Total Users
                        </div>
                        <div class="metric-value text-info">
                            <?php echo number_format($analytics_data['users_stats']['total_users'] ?? 0); ?>
                        </div>
                        <div class="text-muted small mt-2">
                            Registered users
                        </div>
                    </div>

                    <div class="metric-card warning p-4">
                        <div class="metric-label">
                            <i class="fas fa-comments me-2"></i>Comments
                        </div>
                        <div class="metric-value text-warning">
                            <?php echo number_format($analytics_data['comments_stats']['total_comments'] ?? 0); ?>
                        </div>
                        <div class="text-muted small mt-2">
                            Approved comments
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Status Overview Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-pie text-primary me-2"></i>
                                Article Status
                            </h5>
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="col-lg-6 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                System Information
                            </h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-primary"><?php echo $analytics_data['news_stats']['draft_articles'] ?? 0; ?></h4>
                                        <small class="text-muted">Draft Articles</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-success"><?php echo $analytics_data['news_stats']['published_articles'] ?? 0; ?></h4>
                                        <small class="text-muted">Published</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-info"><?php echo count($analytics_data['categories_data']); ?></h4>
                                        <small class="text-muted">Categories</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-warning"><?php echo $analytics_data['views_stats']['max_views'] ?? 0; ?></h4>
                                        <small class="text-muted">Max Views</small>
                                    </div>
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
                                <i class="fas fa-fire text-danger me-2"></i>
                                Recent Articles
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Status</th>
                                            <?php if ($analytics_data['has_views']): ?>
                                            <th>Views</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($article = mysqli_fetch_assoc($analytics_data['popular_data'])): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($article['image'])): ?>
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
                                                    <span class="badge <?php echo $article['status'] === 'published' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo ucfirst($article['status']); ?>
                                                    </span>
                                                </td>
                                                <?php if ($analytics_data['has_views']): ?>
                                                <td>
                                                    <strong><?php echo number_format($article['views'] ?? 0); ?></strong>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <?php if ($analytics_data['has_category'] && !empty($analytics_data['categories_data'])): ?>
                    <div class="col-lg-4 mb-4">
                        <div class="analytics-card p-4">
                            <h5 class="mb-4">
                                <i class="fas fa-chart-bar text-info me-2"></i>
                                Categories
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Articles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics_data['categories_data'] as $category): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $category['article_count']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Published', 'Draft'],
                datasets: [{
                    data: [
                        <?php echo $analytics_data['news_stats']['published_articles'] ?? 0; ?>,
                        <?php echo $analytics_data['news_stats']['draft_articles'] ?? 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

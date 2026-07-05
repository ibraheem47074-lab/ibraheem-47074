<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

// Get reporter-specific statistics
$user_id = $_SESSION['user_id'];

// Get reporter's own articles
$my_news_query = "SELECT n.*, c.name as category_name 
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE n.author_id = $user_id 
                  ORDER BY n.created_at DESC LIMIT 5";
$my_news = mysqli_query($conn, $my_news_query);

// Get reporter's statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id"))['count'];
$published_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'published'"))['count'];
$draft_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'draft'"))['count'];
$pending_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'pending'"))['count'];

// Get total views for reporter's articles
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news WHERE author_id = $user_id"))['total'] ?? 0;

// Get recent comments on reporter's articles
$recent_comments_query = "SELECT cm.*, n.title as news_title 
                         FROM comments cm 
                         LEFT JOIN news n ON cm.news_id = n.id 
                         WHERE n.author_id = $user_id 
                         ORDER BY cm.created_at DESC LIMIT 5";
$recent_comments = mysqli_query($conn, $recent_comments_query);

// Get enhanced performance data for current reporter
$performance_data_query = "SELECT 
                           COUNT(CASE WHEN status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as articles_this_month,
                           COUNT(CASE WHEN status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as articles_this_week,
                           COUNT(CASE WHEN status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as articles_today,
                           COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
                           COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_articles,
                           COALESCE(SUM(views), 0) as total_views,
                           COALESCE(AVG(views), 0) as avg_views_per_article,
                           COALESCE(SUM(likes), 0) as total_likes,
                           0 as total_comments,
                           COALESCE(MAX(views), 0) as best_article_views
                           FROM news 
                           WHERE author_id = $user_id";
$performance_data = mysqli_query($conn, $performance_data_query);
$performance = mysqli_fetch_assoc($performance_data);

// Get monthly performance trends
$monthly_trends_query = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as articles_count,
                        SUM(views) as total_views,
                        AVG(views) as avg_views
                        FROM news 
                        WHERE author_id = $user_id 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month";
$monthly_trends_result = mysqli_query($conn, $monthly_trends_query);

// Get category performance
$category_performance_query = "SELECT 
                               c.name as category_name,
                               COUNT(n.id) as articles_count,
                               SUM(n.views) as total_views,
                               AVG(n.views) as avg_views
                               FROM news n
                               LEFT JOIN categories c ON n.category_id = c.id
                               WHERE n.author_id = $user_id
                               GROUP BY c.id, c.name
                               ORDER BY total_views DESC
                               LIMIT 5";
$category_performance_result = mysqli_query($conn, $category_performance_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        .reporter-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .reporter-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .reporter-sidebar .nav-link:hover,
        .reporter-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .reporter-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .article-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-published { background-color: #d4edda; color: #155724; }
        .status-draft { background-color: #fff3cd; color: #856404; }
        .status-pending { background-color: #cce5ff; color: #004085; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block reporter-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Reporter Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="reporter-dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                                                <li class="nav-item">
                            <a class="nav-link" href="my-articles.php">
                                <i class="fas fa-list me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter-add-news.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Article
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>All News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my-comments.php">
                                <i class="fas fa-comments me-2"></i>My Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter-profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 reporter-main-content">
                <!-- Header -->
                <div class="reporter-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Reporter Dashboard <span class="badge bg-light text-dark ms-2">Standard</span></h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="reporter-dashboard-enhanced.php" class="btn btn-outline-light btn-sm me-2" title="Switch to Enhanced Dashboard">
                            <i class="fas fa-rocket me-1"></i>Enhanced
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="reporter-profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="my-articles.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $total_articles; ?></h3>
                                <p class="text-muted mb-0">Total Articles</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="my-articles.php?status=published" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3><?php echo $published_articles; ?></h3>
                                <p class="text-muted mb-0">Published</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="my-articles.php?status=pending" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-info text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $pending_articles; ?></h3>
                                <p class="text-muted mb-0">Pending Approval</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning text-white">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3><?php echo number_format($total_views); ?></h3>
                            <p class="text-muted mb-0">Total Views</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                                                        <div class="col-md-3 mb-2">
                                        <a href="my-articles.php" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-list me-1"></i>View All Articles
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="my-articles.php?status=draft" class="btn btn-outline-warning btn-sm w-100">
                                            <i class="fas fa-edit me-1"></i>Edit Drafts
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="reporter-profile.php" class="btn btn-outline-info btn-sm w-100">
                                            <i class="fas fa-user me-1"></i>Update Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Analytics -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Performance Analytics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <!-- Performance Trends Chart -->
                                    <div class="col-lg-8 mb-4">
                                        <h6 class="mb-3">Monthly Performance Trends</h6>
                                        <canvas id="performanceTrendsChart" height="120"></canvas>
                                    </div>
                                    
                                    <!-- Category Performance Chart -->
                                    <div class="col-lg-4 mb-4">
                                        <h6 class="mb-3">Top Categories</h6>
                                        <canvas id="categoryPerformanceChart" height="120"></canvas>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Activity Overview Chart -->
                                    <div class="col-lg-6 mb-4">
                                        <h6 class="mb-3">Activity Overview</h6>
                                        <canvas id="activityOverviewChart" height="100"></canvas>
                                    </div>
                                    
                                    <!-- Engagement Metrics Chart -->
                                    <div class="col-lg-6 mb-4">
                                        <h6 class="mb-3">Engagement Metrics</h6>
                                        <canvas id="engagementOverviewChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Articles -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>My Recent Articles</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Views</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($news = mysqli_fetch_assoc($my_news)): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars(substr($news['title'], 0, 50)) . '...'; ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($news['category_name'] ?? ''); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="article-status status-<?php echo $news['status']; ?>">
                                                            <?php echo ucfirst($news['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $news['views']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <?php if ($news['status'] === 'draft' || $news['status'] === 'pending'): ?>
                                                                <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($news['status'] === 'draft'): ?>
                                                                <a href="submit-for-approval.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-success btn-sm">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="my-articles.php" class="btn btn-primary">View All Articles</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Comments -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Comments on My Articles</h6>
                            </div>
                            <div class="card-body">
                                <?php while ($comment = mysqli_fetch_assoc($recent_comments)): ?>
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1 small"><?php echo htmlspecialchars(substr($comment['comment'], 0, 80)) . '...'; ?></p>
                                        <small class="text-muted">On: <?php echo htmlspecialchars(substr($comment['news_title'], 0, 30)) . '...'; ?></small>
                                    </div>
                                <?php endwhile; ?>
                                <div class="text-center mt-3">
                                    <a href="my-comments.php" class="btn btn-sm btn-outline-primary">View All Comments</a>
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
        // Initialize charts with error handling
        try {
            // Prepare performance data for charts
            const performanceData = <?php 
                $monthly_data = [];
                if ($monthly_trends_result) {
                    while ($trend = mysqli_fetch_assoc($monthly_trends_result)) {
                        $month = $trend['month'] ?? 'Unknown';
                        $articles = (int)($trend['articles_count'] ?? 0);
                        $views = (int)($trend['total_views'] ?? 0);
                        $avg_views = round($trend['avg_views'] ?? 0, 1);
                        
                        // Only add valid data
                        if ($month !== 'Unknown' && $articles >= 0 && $views >= 0) {
                            $monthly_data[] = [
                                'month' => $month,
                                'articles' => $articles,
                                'views' => $views,
                                'avg_views' => $avg_views
                            ];
                        }
                    }
                }
                echo json_encode($monthly_data);
            ?>;

        const currentPerformance = <?php echo json_encode([
            'articles_today' => (int)($performance['articles_today'] ?? 0),
            'articles_this_week' => (int)($performance['articles_this_week'] ?? 0),
            'articles_this_month' => (int)($performance['articles_this_month'] ?? 0),
            'draft_articles' => (int)($performance['draft_articles'] ?? 0),
            'pending_articles' => (int)($performance['pending_articles'] ?? 0),
            'total_views' => (int)($performance['total_views'] ?? 0),
            'avg_views' => round($performance['avg_views_per_article'] ?? 0, 1),
            'total_likes' => (int)($performance['total_likes'] ?? 0),
            'total_comments' => (int)($performance['total_comments'] ?? 0),
            'best_article_views' => (int)($performance['best_article_views'] ?? 0)
        ]); ?>;

        const categoryData = <?php 
            $cat_data = [];
            if ($category_performance_result) {
                while ($cat = mysqli_fetch_assoc($category_performance_result)) {
                    $cat_data[] = [
                        'category' => $cat['category_name'] ?? 'Unknown',
                        'articles' => (int)($cat['articles_count'] ?? 0),
                        'views' => (int)($cat['total_views'] ?? 0),
                        'avg_views' => round($cat['avg_views'] ?? 0, 1)
                    ];
                }
            }
            echo json_encode($cat_data);
        ?>;

        // Performance Trends Chart
        console.log('Performance Data:', performanceData);
        
        // Validate data before processing
        if (!performanceData || !Array.isArray(performanceData) || performanceData.length === 0) {
            console.warn('No valid performance data available for charts');
            return; // Exit early if no data
        }
        
        // Check for invalid data points
        const validData = performanceData.filter(d => 
            d && typeof d === 'object' && 
            typeof d.articles === 'number' && 
            typeof d.views === 'number' &&
            d.articles >= 0 && 
            d.views >= 0
        );
        
        console.log('Valid data points:', validData.length, 'out of', performanceData.length);
        
        if (validData.length === 0) {
            console.warn('No valid data points found for charts');
            // Show no data message in chart container
            const chartContainer = document.getElementById('performanceTrendsChart');
            if (chartContainer) {
                const ctx = chartContainer.getContext('2d');
                ctx.font = '14px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No performance data available', chartContainer.width / 2, chartContainer.height / 2);
            }
            return;
        }
        
        if (validData.length > 0) {
            // Process data to ensure labels and data are synchronized
            const processedData = validData.map((d, index) => {
                let label;
                try {
                    // Ensure unique labels to prevent "increasing increasing"
                    if (!d.month || d.month === 'Unknown') {
                        label = `Month ${index + 1}`;
                    } else {
                        const date = new Date(d.month + '-01');
                        if (isNaN(date.getTime())) {
                            label = d.month; // Fallback to raw month string
                        } else {
                            const formatted = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                            label = formatted || d.month; // Ensure non-empty label
                        }
                    }
                } catch (e) {
                    console.error('Date formatting error:', e, 'for month:', d.month);
                    label = `Month ${index + 1}`; // Fallback label
                }
                
                return {
                    label: label,
                    articles: d.articles || 0,
                    views: d.views || 0,
                    avg_views: d.avg_views || 0
                };
            });

            const trendsCtx = document.getElementById('performanceTrendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: processedData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Articles Published',
                            data: processedData.map(d => d.articles),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Views',
                            data: processedData.map(d => d.views),
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Articles'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Views'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        }

        // Category Performance Chart
        console.log('Category Data:', categoryData);
        if (categoryData && categoryData.length > 0) {
            const categoryCtx = document.getElementById('categoryPerformanceChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(d => d.category),
                    datasets: [{
                        data: categoryData.map(d => d.views),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderWidth: 2
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
                                    return `${label}: ${value.toLocaleString()} views (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Activity Overview Chart
        console.log('Current Performance:', currentPerformance);
        const activityCtx = document.getElementById('activityOverviewChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: ['Today', 'This Week', 'This Month', 'Drafts', 'Pending'],
                datasets: [{
                    label: 'Articles',
                    data: [
                        currentPerformance.articles_today,
                        currentPerformance.articles_this_week,
                        currentPerformance.articles_this_month,
                        currentPerformance.draft_articles,
                        currentPerformance.pending_articles
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderWidth: 2
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

        // Engagement Metrics Chart
        const engagementCtx = document.getElementById('engagementOverviewChart').getContext('2d');
        new Chart(engagementCtx, {
            type: 'radar',
            data: {
                labels: ['Total Views', 'Total Likes', 'Total Comments', 'Avg Views/Article', 'Best Article'],
                datasets: [{
                    label: 'My Performance',
                    data: [
                        currentPerformance.total_views / 1000, // Scale down for visualization
                        currentPerformance.total_likes * 10, // Scale up for visibility
                        currentPerformance.total_comments * 10, // Scale up for visibility
                        currentPerformance.avg_views,
                        currentPerformance.best_article_views / 100 // Scale down
                    ],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const labels = ['Total Views', 'Total Likes', 'Total Comments', 'Avg Views/Article', 'Best Article'];
                                const values = [
                                    currentPerformance.total_views,
                                    currentPerformance.total_likes,
                                    currentPerformance.total_comments,
                                    currentPerformance.avg_views,
                                    currentPerformance.best_article_views
                                ];
                                const index = context.dataIndex;
                                return `${labels[index]}: ${values[index].toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });
        
        } catch (error) {
            console.error('Error initializing charts:', error);
            // Display error message to user
            const chartsContainer = document.querySelector('.card-body');
            if (chartsContainer) {
                chartsContainer.innerHTML = '<div class="alert alert-warning">Unable to load performance charts. Please refresh the page.</div>';
            }
        }
    </script>
</body>
</html>

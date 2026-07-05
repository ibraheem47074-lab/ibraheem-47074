<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Get current editor's ID
$editor_id = $_SESSION['user_id'];

// Get date range filters
$start_date = isset($_GET['start_date']) ? clean_input($_GET['start_date']) : date('Y-m-01', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? clean_input($_GET['end_date']) : date('Y-m-d');

// Overall statistics for current editor
$editor_total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $editor_id"))['count'];
$editor_published_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $editor_id AND status = 'published'"))['count'];
$editor_draft_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $editor_id AND status = 'draft'"))['count'];
$editor_total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news WHERE author_id = $editor_id"))['total'] ?? 0;
$editor_total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments cm JOIN news n ON cm.news_id = n.id WHERE n.author_id = $editor_id"))['count'];

// Date range statistics for current editor
$where_date = "WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND author_id = $editor_id";
$editor_articles_in_range = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news $where_date"))['count'];
$editor_views_in_range = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news $where_date"))['total'] ?? 0;
$editor_comments_in_range = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments cm JOIN news n ON cm.news_id = n.id WHERE DATE(cm.created_at) BETWEEN '$start_date' AND '$end_date' AND n.author_id = $editor_id"))['count'];

// Author statistics
$author_stats_query = "SELECT u.name, u.email, COUNT(n.id) as articles_count, 
                        SUM(n.views) as total_views, 
                        COUNT(CASE WHEN n.status = 'published' THEN 1 END) as published_count
                        FROM users u 
                        LEFT JOIN news n ON u.id = n.author_id 
                        WHERE u.role IN ('admin', 'editor', 'reporter', 'author')
                        GROUP BY u.id, u.name, u.email 
                        HAVING articles_count > 0 
                        ORDER BY total_views DESC 
                        LIMIT 10";
$author_stats = mysqli_query($conn, $author_stats_query);

// Category statistics
$category_stats_query = "SELECT c.name, COUNT(n.id) as articles_count, SUM(n.views) as total_views
                        FROM categories c 
                        LEFT JOIN news n ON c.id = n.category_id 
                        GROUP BY c.id, c.name 
                        HAVING articles_count > 0 
                        ORDER BY articles_count DESC 
                        LIMIT 10";
$category_stats = mysqli_query($conn, $category_stats_query);

// Editor's popular articles
$popular_articles_query = "SELECT n.title, n.slug, n.views, n.created_at, c.name as category_name, n.status
                          FROM news n 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          WHERE n.author_id = $editor_id
                          ORDER BY n.views DESC 
                          LIMIT 10";
$popular_articles = mysqli_query($conn, $popular_articles_query);

// Editor's recent activity
$recent_activity_query = "SELECT 'article' as type, n.title COLLATE utf8mb4_unicode_ci as title, n.created_at, n.status
                        FROM news n 
                        WHERE n.author_id = $editor_id
                        ORDER BY created_at DESC 
                        LIMIT 10";
$recent_activity = mysqli_query($conn, $recent_activity_query);

// Daily views for chart (Editor's articles)
$daily_views_query = "SELECT DATE(created_at) as date, COUNT(*) as articles, SUM(views) as views
                      FROM news 
                      WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND author_id = $editor_id
                      GROUP BY DATE(created_at) 
                      ORDER BY date";
$daily_views = mysqli_query($conn, $daily_views_query);

// Monthly trends (Editor's articles)
$monthly_trends_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                         COUNT(*) as articles, SUM(views) as views,
                         COUNT(CASE WHEN status = 'published' THEN 1 END) as published
                         FROM news 
                         WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH) AND author_id = $editor_id
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                         ORDER BY month";
$monthly_trends = mysqli_query($conn, $monthly_trends_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - PK Live News Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .editor-header {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            color: white;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid #4834d4;
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
            margin-bottom: 1rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(72, 52, 212, 0.05);
        }
        .activity-item {
            border-left: 3px solid #4834d4;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/editor-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-chart-line me-3"></i>My Articles Dashboard</h2>
                <p class="text-muted">Analytics and insights for your published articles.</p>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Apply Filter
                                </button>
                                <a href="editor-statistics.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Reset
                                </a>
                            </div>
                            <div class="col-md-3 text-end">
                                <small class="text-muted">
                                    Period: <?php echo date('M d, Y', strtotime($start_date)); ?> - 
                                    <?php echo date('M d, Y', strtotime($end_date)); ?>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon bg-primary text-white">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3><?php echo number_format($editor_total_articles); ?></h3>
                    <p class="text-muted mb-0">My Articles</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> <?php echo $editor_articles_in_range; ?> in selected period
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo number_format($editor_published_articles); ?></h3>
                    <p class="text-muted mb-0">Published Articles</p>
                    <small class="text-muted">
                        <?php echo $editor_draft_articles; ?> drafts
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon bg-warning text-white">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3><?php echo number_format($editor_total_views); ?></h3>
                    <p class="text-muted mb-0">Total Views</p>
                    <small class="text-info">
                        <i class="fas fa-arrow-up"></i> <?php echo number_format($editor_views_in_range); ?> in selected period
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon bg-info text-white">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3><?php echo number_format($editor_total_comments); ?></h3>
                    <p class="text-muted mb-0">Comments on My Articles</p>
                    <small class="text-info">
                        <i class="fas fa-arrow-up"></i> <?php echo $editor_comments_in_range; ?> in selected period
                    </small>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Daily Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Content Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Trends (Last 12 Months)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- My Articles Management -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>My Articles</h5>
                        <div>
                            <a href="add-news.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>New Article
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $my_articles_query = "SELECT n.id, n.title, n.slug, n.status, n.views, n.created_at, c.name as category_name
                                                     FROM news n 
                                                     LEFT JOIN categories c ON n.category_id = c.id 
                                                     WHERE n.author_id = $editor_id
                                                     ORDER BY n.created_at DESC 
                                                     LIMIT 10";
                                    $my_articles = mysqli_query($conn, $my_articles_query);
                                    while ($article = mysqli_fetch_assoc($my_articles)): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars(substr($article['title'], 0, 40)); ?>...</strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($article['category_name']); ?> • <?php echo date('M d, Y', strtotime($article['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($article['status'] == 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php elseif ($article['status'] == 'draft'): ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($article['views']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../<?php echo $article['slug']; ?>" target="_blank" class="btn btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my-articles.php" class="btn btn-outline-primary">View All My Articles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Categories Performance -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>My Categories Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Articles</th>
                                        <th>Total Views</th>
                                        <th>Avg Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $my_category_stats_query = "SELECT c.name, COUNT(n.id) as articles_count, SUM(n.views) as total_views
                                                             FROM categories c 
                                                             LEFT JOIN news n ON c.id = n.category_id AND n.author_id = $editor_id
                                                             GROUP BY c.id, c.name 
                                                             HAVING articles_count > 0 
                                                             ORDER BY articles_count DESC 
                                                             LIMIT 10";
                                    $my_category_stats = mysqli_query($conn, $my_category_stats_query);
                                    while ($category = mysqli_fetch_assoc($my_category_stats)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $category['articles_count']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($category['total_views']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo number_format($category['articles_count'] > 0 ? $category['total_views'] / $category['articles_count'] : 0, 1); ?>
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

        <div class="row">
            <!-- Popular Articles -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>My Most Popular Articles</h5>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($article = mysqli_fetch_assoc($popular_articles)): ?>
                                        <tr>
                                            <td>
                                                <a href="../<?php echo $article['slug']; ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars(substr($article['title'], 0, 60)) . '...'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($article['status'] == 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php elseif ($article['status'] == 'draft'): ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($article['views']); ?></strong>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>My Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($activity = mysqli_fetch_assoc($recent_activity)): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>
                                            <?php echo $activity['type'] === 'article' ? '📝' : '💬'; ?>
                                            <?php echo ucfirst($activity['type']); ?>
                                        </strong>
                                        <p class="mb-1 small">
                                            <?php echo htmlspecialchars(substr($activity['title'], 0, 50)) . '...'; ?>
                                        </p>
                                        <small class="text-muted">
                                            By <?php echo htmlspecialchars($activity['author_name'] ?? 'Unknown'); ?>
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <?php echo time_ago($activity['created_at']); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php 
                    $labels = [];
                    $articlesData = [];
                    $viewsData = [];
                    while ($day = mysqli_fetch_assoc($daily_views)) {
                        $labels[] = date('M d', strtotime($day['date']));
                        $articlesData[] = $day['articles'];
                        $viewsData[] = $day['views'] ?? 0;
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    label: 'Articles',
                    data: <?php echo json_encode($articlesData); ?>,
                    borderColor: '#4834d4',
                    backgroundColor: 'rgba(72, 52, 212, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Views',
                    data: <?php echo json_encode($viewsData); ?>,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Published', 'Draft', 'Pending'],
                datasets: [{
                    data: [
                        <?php echo $editor_published_articles; ?>,
                        <?php echo $editor_draft_articles; ?>,
                        <?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'pending' AND author_id = $editor_id"))['count']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php 
                    $labels = [];
                    $articlesData = [];
                    $publishedData = [];
                    $viewsData = [];
                    while ($month = mysqli_fetch_assoc($monthly_trends)) {
                        $labels[] = date('M Y', strtotime($month['month'] . '-01'));
                        $articlesData[] = $month['articles'];
                        $publishedData[] = $month['published'];
                        $viewsData[] = $month['views'] ?? 0;
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    label: 'Total Articles',
                    data: <?php echo json_encode($articlesData); ?>,
                    backgroundColor: '#4834d4'
                }, {
                    label: 'Published Articles',
                    data: <?php echo json_encode($publishedData); ?>,
                    backgroundColor: '#28a745'
                }, {
                    label: 'Views',
                    data: <?php echo json_encode($viewsData); ?>,
                    backgroundColor: '#f39c12',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

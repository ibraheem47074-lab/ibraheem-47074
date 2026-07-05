<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$tag_id = (int)($_GET['id'] ?? 0);

// Get tag information
$tag_query = "SELECT * FROM tags WHERE id = ?";
$stmt = mysqli_prepare($conn, $tag_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tag = mysqli_fetch_assoc($result);

if (!$tag) {
    redirect('manage-tags-complete.php');
}

// Get date range filter
$date_range = isset($_GET['date_range']) ? clean_input($_GET['date_range']) : '30days';
$where_clause = '';
switch ($date_range) {
    case 'today':
        $where_clause = "AND DATE(n.published_at) = CURDATE()";
        break;
    case '7days':
        $where_clause = "AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case '30days':
        $where_clause = "AND n.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    case '90days':
        $where_clause = "AND n.published_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        break;
    case '1year':
        $where_clause = "AND n.published_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
}

// Get tag analytics data
$analytics_query = "SELECT 
                    n.id,
                    n.title,
                    n.slug,
                    n.views,
                    n.published_at,
                    c.name as category_name,
                    u.name as author_name,
                    COUNT(DISTINCT co.id) as comment_count,
                    COUNT(DISTINCT b.id) as bookmark_count,
                    (n.views * 0.1 + COUNT(DISTINCT co.id) * 0.3 + COUNT(DISTINCT b.id) * 0.2) as engagement_score
                    FROM news n
                    LEFT JOIN news_tags nt ON n.id = nt.news_id
                    LEFT JOIN categories c ON n.category_id = c.id
                    LEFT JOIN users u ON n.author_id = u.id
                    LEFT JOIN comments co ON n.id = co.news_id
                    LEFT JOIN bookmarks b ON n.id = b.news_id
                    WHERE nt.tag_id = ? AND n.status = 'published' $where_clause
                    GROUP BY n.id
                    ORDER BY engagement_score DESC
                    LIMIT 20";

$stmt = mysqli_prepare($conn, $analytics_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$analytics_result = mysqli_stmt_get_result($stmt);

// Get overall statistics
$stats_query = "SELECT 
                COUNT(DISTINCT n.id) as total_articles,
                SUM(n.views) as total_views,
                AVG(n.views) as avg_views,
                MAX(n.views) as max_views,
                COUNT(DISTINCT n.author_id) as unique_authors,
                COUNT(DISTINCT c.id) as unique_categories,
                COUNT(DISTINCT co.id) as total_comments,
                COUNT(DISTINCT b.id) as total_bookmarks
                FROM news n
                LEFT JOIN news_tags nt ON n.id = nt.news_id
                LEFT JOIN categories c ON n.category_id = c.id
                LEFT JOIN users u ON n.author_id = u.id
                LEFT JOIN comments co ON n.id = co.news_id
                LEFT JOIN bookmarks b ON n.id = b.news_id
                WHERE nt.tag_id = ? AND n.status = 'published' $where_clause";

$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get monthly trend data
$trend_query = "SELECT 
                 DATE_FORMAT(n.published_at, '%Y-%m') as month,
                 COUNT(*) as articles_count,
                 SUM(n.views) as total_views
                 FROM news n
                 LEFT JOIN news_tags nt ON n.id = nt.news_id
                 WHERE nt.tag_id = ? AND n.status = 'published' 
                 AND n.published_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(n.published_at, '%Y-%m')
                 ORDER BY month ASC";

$stmt = mysqli_prepare($conn, $trend_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$trend_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Analytics: <?php echo htmlspecialchars($tag['name']); ?> - PK Live News Admin</title>
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
        .tag-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .tag-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .engagement-score {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: bold;
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
                        <h4><i class="fas fa-tags me-2"></i>PK Live News</h4>
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
                            <a class="nav-link active" href="manage-tags-complete.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
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
                <!-- Tag Header -->
                <div class="tag-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="tag-color" style="background-color: <?php echo htmlspecialchars($tag['color']); ?>;"></span>
                            <div>
                                <h1 class="h3 mb-0"><?php echo htmlspecialchars($tag['name']); ?> Analytics</h1>
                                <small class="opacity-75"><?php echo htmlspecialchars($tag['description'] ?? 'No description'); ?></small>
                            </div>
                        </div>
                        <div>
                            <a href="manage-tags-complete.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Tags
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="id" value="<?php echo $tag_id; ?>">
                            <div class="col-md-8">
                                <label class="form-label">Date Range</label>
                                <select class="form-select" name="date_range" onchange="this.form.submit()">
                                    <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="7days" <?php echo $date_range === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                    <option value="30days" <?php echo $date_range === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                    <option value="90days" <?php echo $date_range === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                                    <option value="1year" <?php echo $date_range === '1year' ? 'selected' : ''; ?>>Last Year</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="btn-group w-100">
                                    <a href="edit-tag.php?id=<?php echo $tag_id; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-2"></i>Edit Tag
                                    </a>
                                    <a href="view-tag-news.php?id=<?php echo $tag_id; ?>" class="btn btn-outline-info">
                                        <i class="fas fa-newspaper me-2"></i>View News
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_articles'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Total Articles</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_views'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Total Views</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo round($stats['avg_views'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Avg Views</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['max_views'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Max Views</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['unique_authors'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Unique Authors</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_comments'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Total Comments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_bookmarks'] ?: 0 ?? 0); ?></h3>
                                <p class="mb-0">Total Bookmarks</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Monthly Trend (Last 12 Months)</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Articles -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy me-2"></i>Top Performing Articles</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($analytics_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Views</th>
                                            <th>Comments</th>
                                            <th>Bookmarks</th>
                                            <th>Engagement</th>
                                            <th>Published</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($article = mysqli_fetch_assoc($analytics_result)): ?>
                                            <tr>
                                                <td>
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" 
                                                       class="text-decoration-none" target="_blank">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($article['author_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                                </td>
                                                <td>
                                                    <?php echo number_format($article['views'] ?: 0 ?? 0); ?>
                                                </td>
                                                <td>
                                                    <?php echo number_format($article['comment_count'] ?: 0 ?? 0); ?>
                                                </td>
                                                <td>
                                                    <?php echo number_format($article['bookmark_count'] ?: 0 ?? 0); ?>
                                                </td>
                                                <td>
                                                    <span class="engagement-score">
                                                        <?php echo round($article['engagement_score'] ?: 0, 1 ?? 0); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo format_date($article['published_at']); ?></small>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h4>No analytics data available</h4>
                                <p class="text-muted">No articles found for this tag in the selected time period.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prepare trend chart data
        const trendData = <?php
            $trend_array = [];
            while ($trend = mysqli_fetch_assoc($trend_result)) {
                $trend_array[] = $trend;
            }
            echo json_encode($trend_array);
        ?>;

        const labels = trendData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });

        const articlesData = trendData.map(item => item.articles_count);
        const viewsData = trendData.map(item => parseInt(item.total_views));

        // Create trend chart
        const ctx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Articles',
                    data: articlesData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Views',
                    data: viewsData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
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
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Articles Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Views'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
require_once '../config/database.php';
require_once '../includes/sentiment_analysis.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'Real-Time Sentiment Analysis Dashboard';

// Handle real-time analysis request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze_all'])) {
    $analysis_results = [];
    
    // Analyze by news sources/channels
    $sources_query = "SELECT DISTINCT 
                     CASE 
                         WHEN n.source_url LIKE '%cnn%' THEN 'CNN'
                         WHEN n.source_url LIKE '%bbc%' THEN 'BBC'
                         WHEN n.source_url LIKE '%ary%' THEN 'ARY News'
                         WHEN n.source_url LIKE '%reuters%' THEN 'Reuters'
                         WHEN n.source_url LIKE '%geo%' THEN 'Geo News'
                         WHEN n.source_url LIKE '%dawn%' THEN 'Dawn News'
                         WHEN n.source_url LIKE '%tribune%' THEN 'Express Tribune'
                         WHEN n.source_url IS NOT NULL AND n.source_url != '' THEN 'Other External'
                         ELSE 'PK Live News'
                     END as source_name,
                     COUNT(*) as total_articles,
                     SUM(CASE WHEN n.sentiment_label = 'positive' THEN 1 ELSE 0 END) as positive_count,
                     SUM(CASE WHEN n.sentiment_label = 'negative' THEN 1 ELSE 0 END) as negative_count,
                     SUM(CASE WHEN n.sentiment_label = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
                     AVG(n.sentiment_score) as avg_sentiment_score,
                     MAX(n.published_at) as latest_article
                     FROM news n 
                     WHERE n.sentiment_label IS NOT NULL 
                     AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     GROUP BY source_name
                     ORDER BY total_articles DESC";
    
    $sources_result = mysqli_query($conn, $sources_query);
    while ($source = mysqli_fetch_assoc($sources_result)) {
        $analysis_results['sources'][] = $source;
    }
    
    // Analyze by categories
    $categories_query = "SELECT c.name as category_name, COUNT(*) as total_articles,
                         SUM(CASE WHEN n.sentiment_label = 'positive' THEN 1 ELSE 0 END) as positive_count,
                         SUM(CASE WHEN n.sentiment_label = 'negative' THEN 1 ELSE 0 END) as negative_count,
                         SUM(CASE WHEN n.sentiment_label = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
                         AVG(n.sentiment_score) as avg_sentiment_score,
                         MAX(n.published_at) as latest_article
                         FROM news n 
                         LEFT JOIN categories c ON n.category_id = c.id 
                         WHERE n.sentiment_label IS NOT NULL 
                         AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         GROUP BY c.id, c.name
                         ORDER BY total_articles DESC";
    
    $categories_result = mysqli_query($conn, $categories_query);
    while ($category = mysqli_fetch_assoc($categories_result)) {
        $analysis_results['categories'][] = $category;
    }
    
    // Get hourly sentiment trends (last 24 hours)
    $hourly_query = "SELECT HOUR(created_at) as hour, sentiment_label, COUNT(*) as count
                     FROM news 
                     WHERE sentiment_label IS NOT NULL 
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                     GROUP BY HOUR(created_at), sentiment_label
                     ORDER BY hour";
    
    $hourly_result = mysqli_query($conn, $hourly_query);
    $hourly_data = [];
    while ($hour = mysqli_fetch_assoc($hourly_result)) {
        $hourly_data[] = $hour;
    }
    $analysis_results['hourly_trends'] = $hourly_data;
    
    // Get top positive and negative articles
    $top_positive_query = "SELECT n.title, n.sentiment_score, n.published_at, c.name as category_name
                          FROM news n 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          WHERE n.sentiment_label = 'positive' 
                          AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                          ORDER BY n.sentiment_score DESC 
                          LIMIT 5";
    
    $top_negative_query = "SELECT n.title, n.sentiment_score, n.published_at, c.name as category_name
                          FROM news n 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          WHERE n.sentiment_label = 'negative' 
                          AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                          ORDER BY n.sentiment_score ASC 
                          LIMIT 5";
    
    $top_positive_result = mysqli_query($conn, $top_positive_query);
    $top_negative_result = mysqli_query($conn, $top_negative_query);
    
    $analysis_results['top_positive'] = [];
    $analysis_results['top_negative'] = [];
    
    while ($article = mysqli_fetch_assoc($top_positive_result)) {
        $analysis_results['top_positive'][] = $article;
    }
    
    while ($article = mysqli_fetch_assoc($top_negative_result)) {
        $analysis_results['top_negative'][] = $article;
    }
    
    // Return JSON response for AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($analysis_results);
        exit;
    }
}

// Get basic statistics for initial display
$total_news_query = "SELECT COUNT(*) as total FROM news WHERE sentiment_label IS NOT NULL";
$total_result = mysqli_query($conn, $total_news_query);
$total_news = mysqli_fetch_assoc($total_result)['total'];

$sentiment_stats_query = "SELECT sentiment_label, COUNT(*) as count, AVG(sentiment_score) as avg_score 
                          FROM news 
                          WHERE sentiment_label IS NOT NULL 
                          GROUP BY sentiment_label";
$sentiment_stats_result = mysqli_query($conn, $sentiment_stats_query);

// Get recent sentiment analysis
$recent_query = "SELECT n.title, n.sentiment_label, n.sentiment_score, n.published_at, c.name as category_name
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                WHERE n.sentiment_label IS NOT NULL 
                ORDER BY n.created_at DESC 
                LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-responsive {
            border-radius: 10px;
        }
        .progress {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<?php
// Get sentiment statistics
$total_news_query = "SELECT COUNT(*) as total FROM news WHERE sentiment_label IS NOT NULL";
$total_result = mysqli_query($conn, $total_news_query);
$total_news = mysqli_fetch_assoc($total_result)['total'];

$sentiment_stats_query = "SELECT sentiment_label, COUNT(*) as count, AVG(sentiment_score) as avg_score 
                          FROM news 
                          WHERE sentiment_label IS NOT NULL 
                          GROUP BY sentiment_label";
$sentiment_stats_result = mysqli_query($conn, $sentiment_stats_query);

// Get recent sentiment analysis
$recent_query = "SELECT n.title, n.sentiment_label, n.sentiment_score, n.published_at, c.name as category_name
                FROM news n 
                LEFT JOIN categories c ON n.category_id = c.id 
                WHERE n.sentiment_label IS NOT NULL 
                ORDER BY n.created_at DESC 
                LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);

// Get sentiment by category
$category_sentiment_query = "SELECT c.name, COUNT(*) as count, 
                             SUM(CASE WHEN n.sentiment_label = 'positive' THEN 1 ELSE 0 END) as positive,
                             SUM(CASE WHEN n.sentiment_label = 'negative' THEN 1 ELSE 0 END) as negative,
                             SUM(CASE WHEN n.sentiment_label = 'neutral' THEN 1 ELSE 0 END) as neutral,
                             AVG(n.sentiment_score) as avg_score
                             FROM news n 
                             LEFT JOIN categories c ON n.category_id = c.id 
                             WHERE n.sentiment_label IS NOT NULL 
                             GROUP BY c.id, c.name 
                             ORDER BY count DESC 
                             LIMIT 10";
$category_sentiment_result = mysqli_query($conn, $category_sentiment_query);

// Get sentiment trends (last 30 days)
$trends_query = "SELECT DATE(created_at) as date, sentiment_label, COUNT(*) as count
                FROM news 
                WHERE sentiment_label IS NOT NULL 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at), sentiment_label
                ORDER BY date DESC";
$trends_result = mysqli_query($conn, $trends_query);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar collapse">
            <div class="position-sticky pt-3">
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
                        <a class="nav-link" href="live-stream.php">
                            <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sentiment-dashboard.php">
                            <i class="fas fa-brain me-2"></i>Sentiment Analysis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../architecture.php">
                            <i class="fas fa-sitemap me-2"></i>System Architecture
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

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">📊 Real-Time Sentiment Analysis Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-danger me-2" onclick="startRealTimeAnalysis()">
                        <i class="fas fa-brain me-2"></i>Analyze All News Channels
                    </button>
                    <a href="update_existing_sentiment.php" class="btn btn-outline-primary">
                        <i class="fas fa-sync me-2"></i>Update Existing News
                    </a>
                </div>
            </div>

            <!-- Real-Time Analysis Results (Hidden by default) -->
            <div id="realTimeAnalysisResults" class="mb-4" style="display: none;">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-brain me-2"></i>Comprehensive News Channel Analysis
                            <span class="badge bg-light text-danger ms-2">Real-Time</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="analysisLoading" class="text-center py-4">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Analyzing...</span>
                            </div>
                            <p class="mt-2">Analyzing all news channels...</p>
                        </div>
                        
                        <div id="analysisContent" style="display: none;">
                            <!-- News Sources Analysis -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6><i class="fas fa-newspaper me-2"></i>News Sources Sentiment Analysis</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="sourcesAnalysisTable">
                                            <thead>
                                                <tr>
                                                    <th>News Channel</th>
                                                    <th>Total Articles</th>
                                                    <th>😊 Positive</th>
                                                    <th>😔 Negative</th>
                                                    <th>😐 Neutral</th>
                                                    <th>Avg Score</th>
                                                    <th>Latest Article</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories Analysis -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6><i class="fas fa-tags me-2"></i>Categories Sentiment Analysis</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="categoriesAnalysisTable">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Total Articles</th>
                                                    <th>😊 Positive</th>
                                                    <th>😔 Negative</th>
                                                    <th>😐 Neutral</th>
                                                    <th>Avg Score</th>
                                                    <th>Latest Article</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Articles -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-arrow-up text-success me-2"></i>Top Positive Articles</h6>
                                    <div id="topPositiveArticles"></div>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-arrow-down text-danger me-2"></i>Top Negative Articles</h6>
                                    <div id="topNegativeArticles"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Analyzed</h5>
                            <h2 class="mb-0"><?php echo number_format($total_news); ?></h2>
                        </div>
                    </div>
                </div>
                <?php while ($stat = mysqli_fetch_assoc($sentiment_stats_result)): ?>
                    <div class="col-md-3">
                        <?php 
                        $bg_colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                        $icons = ['positive' => '😊', 'negative' => '😔', 'neutral' => '😐'];
                        $bg_color = $bg_colors[$stat['sentiment_label']] ?? 'secondary';
                        $icon = $icons[$stat['sentiment_label']] ?? '😐';
                        ?>
                        <div class="card bg-<?php echo $bg_color; ?> text-white">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $icon; ?> <?php echo ucfirst($stat['sentiment_label']); ?></h5>
                                <h2 class="mb-0"><?php echo number_format($stat['count']); ?></h2>
                                <small>Avg Score: <?php echo round($stat['avg_score'], 2); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sentiment Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sentimentChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Category Sentiment Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Analysis -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Sentiment Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Sentiment</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
                                            <tr>
                                                <td>
                                                    <a href="../news.php?slug=<?php echo urlencode($recent['slug'] ?? ''); ?>" target="_blank">
                                                        <?php echo htmlspecialchars(substr($recent['title'] ?? '', 0, 60)) . (strlen($recent['title'] ?? '') > 60 ? '...' : ''); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($recent['category_name'] ?? ''); ?></td>
                                                <td>
                                                    <?php 
                                                    $colors = ['positive' => 'success', 'negative' => 'danger', 'neutral' => 'secondary'];
                                                    $icons = ['positive' => '😊', 'negative' => '😔', 'neutral' => '😐'];
                                                    $color = $colors[$recent['sentiment_label']] ?? 'secondary';
                                                    $icon = $icons[$recent['sentiment_label']] ?? '😐';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo $icon; ?> <?php echo ucfirst($recent['sentiment_label']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <?php 
                                                        $score_percent = (($recent['sentiment_score'] + 1) / 2) * 100; // Convert -1 to 1 range to 0-100%
                                                        $progress_color = $recent['sentiment_score'] > 0.1 ? 'bg-success' : ($recent['sentiment_score'] < -0.1 ? 'bg-danger' : 'bg-secondary');
                                                        ?>
                                                        <div class="progress-bar <?php echo $progress_color; ?>" style="width: <?php echo $score_percent; ?>%">
                                                            <?php echo $recent['sentiment_score']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo format_date($recent['published_at']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sentiment by Category</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Total</th>
                                            <th>Positive</th>
                                            <th>Negative</th>
                                            <th>Neutral</th>
                                            <th>Avg Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($cat = mysqli_fetch_assoc($category_sentiment_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name'] ?? ''); ?></td>
                                                <td><?php echo number_format($cat['count']); ?></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        😊 <?php echo number_format($cat['positive']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        😔 <?php echo number_format($cat['negative']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        😐 <?php echo number_format($cat['neutral']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <?php 
                                                        $score_percent = (($cat['avg_score'] + 1) / 2) * 100;
                                                        $progress_color = $cat['avg_score'] > 0.1 ? 'bg-success' : ($cat['avg_score'] < -0.1 ? 'bg-danger' : 'bg-secondary');
                                                        ?>
                                                        <div class="progress-bar <?php echo $progress_color; ?>" style="width: <?php echo $score_percent; ?>%">
                                                            <?php echo round($cat['avg_score'], 2); ?>
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
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sentiment Distribution Chart
const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
<?php
// Reset result pointer for JavaScript data
mysqli_data_seek($sentiment_stats_result, 0);
$sentiment_data = [];
while ($stat = mysqli_fetch_assoc($sentiment_stats_result)) {
    $sentiment_data[] = [
        'label' => ucfirst($stat['sentiment_label']),
        'count' => $stat['count']
    ];
}
?>
const sentimentData = <?php echo json_encode($sentiment_data); ?>;

new Chart(sentimentCtx, {
    type: 'doughnut',
    data: {
        labels: sentimentData.map(item => item.label),
        datasets: [{
            data: sentimentData.map(item => item.count),
            backgroundColor: ['#28a745', '#dc3545', '#6c757d'],
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

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
<?php
$category_data = [];
mysqli_data_seek($category_sentiment_result, 0);
while ($cat = mysqli_fetch_assoc($category_sentiment_result)) {
    $category_data[] = [
        'name' => $cat['name'],
        'positive' => $cat['positive'],
        'negative' => $cat['negative'],
        'neutral' => $cat['neutral']
    ];
}
?>
const categoryData = <?php echo json_encode($category_data); ?>;

new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: categoryData.map(item => item.name),
        datasets: [
            {
                label: 'Positive',
                data: categoryData.map(item => item.positive),
                backgroundColor: '#28a745'
            },
            {
                label: 'Negative',
                data: categoryData.map(item => item.negative),
                backgroundColor: '#dc3545'
            },
            {
                label: 'Neutral',
                data: categoryData.map(item => item.neutral),
                backgroundColor: '#6c757d'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true
            },
            y: {
                stacked: true
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Real-Time Analysis Function
function startRealTimeAnalysis() {
    const resultsDiv = document.getElementById('realTimeAnalysisResults');
    const loadingDiv = document.getElementById('analysisLoading');
    const contentDiv = document.getElementById('analysisContent');
    
    // Show results section and loading
    resultsDiv.style.display = 'block';
    loadingDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    
    // Create form data
    const formData = new FormData();
    formData.append('analyze_all', 'true');
    formData.append('ajax', 'true');
    
    // Send AJAX request
    fetch('sentiment-dashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading, show content
        loadingDiv.style.display = 'none';
        contentDiv.style.display = 'block';
        
        // Populate sources table
        const sourcesTable = document.querySelector('#sourcesAnalysisTable tbody');
        sourcesTable.innerHTML = '';
        
        if (data.sources && data.sources.length > 0) {
            data.sources.forEach(source => {
                const row = document.createElement('tr');
                const positivePercent = source.total_articles > 0 ? (source.positive_count / source.total_articles * 100).toFixed(1) : 0;
                const negativePercent = source.total_articles > 0 ? (source.negative_count / source.total_articles * 100).toFixed(1) : 0;
                const neutralPercent = source.total_articles > 0 ? (source.neutral_count / source.total_articles * 100).toFixed(1) : 0;
                
                row.innerHTML = `
                    <td><strong>${source.source_name}</strong></td>
                    <td>${source.total_articles}</td>
                    <td><span class="badge bg-success">${source.positive_count} (${positivePercent}%)</span></td>
                    <td><span class="badge bg-danger">${source.negative_count} (${negativePercent}%)</span></td>
                    <td><span class="badge bg-secondary">${source.neutral_count} (${neutralPercent}%)</span></td>
                    <td>${source.avg_sentiment_score ? source.avg_sentiment_score.toFixed(3) : 'N/A'}</td>
                    <td><small>${new Date(source.latest_article).toLocaleDateString()}</small></td>
                `;
                sourcesTable.appendChild(row);
            });
        }
        
        // Populate categories table
        const categoriesTable = document.querySelector('#categoriesAnalysisTable tbody');
        categoriesTable.innerHTML = '';
        
        if (data.categories && data.categories.length > 0) {
            data.categories.forEach(category => {
                const row = document.createElement('tr');
                const positivePercent = category.total_articles > 0 ? (category.positive_count / category.total_articles * 100).toFixed(1) : 0;
                const negativePercent = category.total_articles > 0 ? (category.negative_count / category.total_articles * 100).toFixed(1) : 0;
                const neutralPercent = category.total_articles > 0 ? (category.neutral_count / category.total_articles * 100).toFixed(1) : 0;
                
                row.innerHTML = `
                    <td><strong>${category.category_name || 'Uncategorized'}</strong></td>
                    <td>${category.total_articles}</td>
                    <td><span class="badge bg-success">${category.positive_count} (${positivePercent}%)</span></td>
                    <td><span class="badge bg-danger">${category.negative_count} (${negativePercent}%)</span></td>
                    <td><span class="badge bg-secondary">${category.neutral_count} (${neutralPercent}%)</span></td>
                    <td>${category.avg_sentiment_score ? category.avg_sentiment_score.toFixed(3) : 'N/A'}</td>
                    <td><small>${new Date(category.latest_article).toLocaleDateString()}</small></td>
                `;
                categoriesTable.appendChild(row);
            });
        }
        
        // Populate top positive articles
        const positiveDiv = document.getElementById('topPositiveArticles');
        positiveDiv.innerHTML = '';
        
        if (data.top_positive && data.top_positive.length > 0) {
            data.top_positive.forEach(article => {
                const articleDiv = document.createElement('div');
                articleDiv.className = 'card mb-2 border-success';
                articleDiv.innerHTML = `
                    <div class="card-body py-2">
                        <h6 class="card-title mb-1">${article.title.substring(0, 80)}...</h6>
                        <small class="text-success">Score: ${article.sentiment_score.toFixed(3)} | ${article.category_name}</small>
                    </div>
                `;
                positiveDiv.appendChild(articleDiv);
            });
        }
        
        // Populate top negative articles
        const negativeDiv = document.getElementById('topNegativeArticles');
        negativeDiv.innerHTML = '';
        
        if (data.top_negative && data.top_negative.length > 0) {
            data.top_negative.forEach(article => {
                const articleDiv = document.createElement('div');
                articleDiv.className = 'card mb-2 border-danger';
                articleDiv.innerHTML = `
                    <div class="card-body py-2">
                        <h6 class="card-title mb-1">${article.title.substring(0, 80)}...</h6>
                        <small class="text-danger">Score: ${article.sentiment_score.toFixed(3)} | ${article.category_name}</small>
                    </div>
                `;
                negativeDiv.appendChild(articleDiv);
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingDiv.innerHTML = '<div class="alert alert-danger">Error loading analysis data. Please try again.</div>';
    });
}
</script>

</body>
</html>

<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Helper function for time ago (if not defined elsewhere)
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M d, Y', $time);
        }
    }
}

// Get overall statistics with error checking
$total_news_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news");
$total_news = $total_news_result ? mysqli_fetch_assoc($total_news_result)['count'] : 0;

$published_news_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status IN ('published', 'active', 'live', 'featured')");
$published_news = $published_news_result ? mysqli_fetch_assoc($published_news_result)['count'] : 0;

$pending_news_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'pending'");
$pending_news = $pending_news_result ? mysqli_fetch_assoc($pending_news_result)['count'] : 0;

$draft_news_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'draft'");
$draft_news = $draft_news_result ? mysqli_fetch_assoc($draft_news_result)['count'] : 0;

$total_comments_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments");
$total_comments = $total_comments_result ? mysqli_fetch_assoc($total_comments_result)['count'] : 0;

// Check if status column exists in comments table
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'status'");
$pending_comments = (mysqli_num_rows($column_check) > 0) 
    ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending'"))['count'] 
    : 0;

// Debug: Check if tables exist and have data
$news_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'news'");
$comments_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'comments'");

$debug_info = [
    'news_table_exists' => mysqli_num_rows($news_table_exists) > 0,
    'comments_table_exists' => mysqli_num_rows($comments_table_exists) > 0,
    'total_news_raw' => $total_news,
    'published_news_raw' => $published_news,
    'pending_news_raw' => $pending_news,
    'total_comments_raw' => $total_comments
];

// Get reporter-submitted articles pending approval
$pending_reporter_articles_query = "SELECT n.*, c.name as category_name, u.name as author_name, u.email as author_email
                                   FROM news n 
                                   LEFT JOIN categories c ON n.category_id = c.id 
                                   LEFT JOIN users u ON n.author_id = u.id 
                                   WHERE n.status = 'pending' 
                                   ORDER BY n.created_at DESC";
$pending_reporter_articles = mysqli_query($conn, $pending_reporter_articles_query);

// Get articles submitted today by reporters
$today_submissions_query = "SELECT n.*, c.name as category_name, u.name as author_name
                            FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            LEFT JOIN users u ON n.author_id = u.id 
                            WHERE n.status = 'pending' AND DATE(n.created_at) = CURDATE()
                            ORDER BY n.created_at DESC";
$today_submissions = mysqli_query($conn, $today_submissions_query);

// Get recently reviewed articles (editor actions)
$recently_reviewed_query = "SELECT n.*, c.name as category_name, u.name as author_name
                           FROM news n 
                           LEFT JOIN categories c ON n.category_id = c.id 
                           LEFT JOIN users u ON n.author_id = u.id 
                           WHERE n.status IN ('published', 'rejected') AND n.updated_at > n.created_at
                           ORDER BY n.updated_at DESC LIMIT 10";
$recently_reviewed = mysqli_query($conn, $recently_reviewed_query);

// Get reporter performance stats
$reporter_stats_query = "SELECT u.name, u.email, COUNT(n.id) as total_submitted, 
                         COUNT(CASE WHEN n.status = 'pending' THEN 1 END) as pending,
                         COUNT(CASE WHEN n.status = 'published' THEN 1 END) as published,
                         COUNT(CASE WHEN n.status = 'rejected' THEN 1 END) as rejected,
                         MAX(n.created_at) as last_submission
                         FROM users u 
                         LEFT JOIN news n ON u.id = n.author_id 
                         WHERE u.role = 'reporter'
                         GROUP BY u.id, u.name, u.email 
                         ORDER BY total_submitted DESC";
$reporter_stats = mysqli_query($conn, $reporter_stats_query);

// Get editor criteria and workflow status
$editor_criteria = [
    'review_pending' => $pending_news,
    'approved_today' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published' AND DATE(updated_at) = CURDATE()"))['count'],
    'rejected_today' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'rejected' AND DATE(updated_at) = CURDATE()"))['count'],
    'avg_review_time' => '2.5 hours' // This would be calculated from actual data
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .editor-header {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            color: white;
        }
        .editor-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .editor-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .editor-sidebar .nav-link:hover,
        .editor-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .editor-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
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
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .criteria-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        .criteria-card:hover {
            transform: translateY(-3px);
        }
        .workflow-step {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #4834d4;
            position: relative;
        }
        .workflow-step.high-priority {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .workflow-step.medium-priority {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .workflow-step.low-priority {
            border-left-color: #28a745;
            background: #f8fff8;
        }
        .reporter-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #f39c12;
            transition: transform 0.3s ease;
        }
        .reporter-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block editor-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Editor Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="editor-dashboard-enhanced.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-dashboard.php">
                                <i class="fas fa-home me-2"></i>Regular Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-comments.php">
                                <i class="fas fa-comments me-2"></i>Manage Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-polls.php">
                                <i class="fas fa-poll me-2"></i>Manage Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-statistics.php">
                                <i class="fas fa-chart-line me-2"></i>Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-profile.php">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 editor-main-content">
                <!-- Header -->
                <div class="editor-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Editor Dashboard <span class="badge bg-warning text-dark ms-2">Enhanced</span></h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="editor-dashboard.php" class="btn btn-outline-light btn-sm me-2" title="Switch to Regular Dashboard">
                            <i class="fas fa-home me-1"></i>Regular
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="editor-profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                
                <!-- Editor Criteria Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-clipboard-check me-2"></i>Editor Criteria & Responsibilities</h4>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="criteria-card">
                                    <h5><i class="fas fa-newspaper me-2"></i>Manage Articles</h5>
                                    <p class="mb-0 small">Review and publish news</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="criteria-card">
                                    <h5><i class="fas fa-user-check me-2"></i>Review Reporters</h5>
                                    <p class="mb-0 small">Approve/reject submissions</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="criteria-card">
                                    <h5><i class="fas fa-comments me-2"></i>Moderate Comments</h5>
                                    <p class="mb-0 small">Manage user interactions</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="criteria-card">
                                    <h5><i class="fas fa-chart-line me-2"></i>View Statistics</h5>
                                    <p class="mb-0 small">Track performance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $total_news; ?></h3>
                                <p class="text-muted mb-0">Total Articles</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php?status=published" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3><?php echo $published_news; ?></h3>
                                <p class="text-muted mb-0">Published</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php?status=pending" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $pending_news; ?></h3>
                                <p class="text-muted mb-0">Pending Approval</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="editor-comments.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-info text-white">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3><?php echo $total_comments; ?></h3>
                                <p class="text-muted mb-0">Total Comments</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Editor Workflow Status -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-tasks me-2"></i>Today's Editor Activity</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="workflow-step <?php echo $editor_criteria['review_pending'] > 0 ? 'high-priority' : 'low-priority'; ?>">
                                    <h6><i class="fas fa-eye me-2"></i>Review Pending</h6>
                                    <p class="mb-0"><strong><?php echo $editor_criteria['review_pending']; ?></strong> articles waiting</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step <?php echo $editor_criteria['approved_today'] > 0 ? 'low-priority' : 'medium-priority'; ?>">
                                    <h6><i class="fas fa-check me-2"></i>Approved Today</h6>
                                    <p class="mb-0"><strong><?php echo $editor_criteria['approved_today']; ?></strong> articles published</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step <?php echo $editor_criteria['rejected_today'] > 0 ? 'medium-priority' : 'low-priority'; ?>">
                                    <h6><i class="fas fa-times me-2"></i>Rejected Today</h6>
                                    <p class="mb-0"><strong><?php echo $editor_criteria['rejected_today']; ?></strong> articles returned</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step medium-priority">
                                    <h6><i class="fas fa-clock me-2"></i>Avg Review Time</h6>
                                    <p class="mb-0"><strong><?php echo $editor_criteria['avg_review_time']; ?></strong> per article</p>
                                </div>
                            </div>
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
                                                                        <div class="col-md-2 mb-2">
                                        <a href="manage-news.php?status=pending" class="btn btn-warning btn-sm w-100">
                                            <i class="fas fa-clock me-1"></i>Review Pending
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="editor-comments.php?status=pending" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-comments me-1"></i>Moderate Comments
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="editor-polls.php" class="btn btn-purple btn-sm w-100" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none;">
                                            <i class="fas fa-poll me-1"></i>Manage Polls
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="editor-statistics.php" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-chart-line me-1"></i>View Stats
                                        </a>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <a href="manage-categories.php" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-tags me-1"></i>Categories
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Reporter Submissions for Review -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Reporter Submissions for Review</h5>
                                <small class="text-muted">Articles submitted by reporters awaiting your approval</small>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($pending_reporter_articles) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Reporter</th>
                                                    <th>Category</th>
                                                    <th>Submitted</th>
                                                    <th>Priority</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($article = mysqli_fetch_assoc($pending_reporter_articles)): ?>
                                                    <?php
                                                    $priority = 'medium';
                                                    $priority_class = 'medium-priority';
                                                    $time_ago = time() - strtotime($article['created_at']);
                                                    if ($time_ago < 3600) { // Less than 1 hour
                                                        $priority = 'high';
                                                        $priority_class = 'high-priority';
                                                    } elseif ($time_ago > 86400) { // More than 1 day
                                                        $priority = 'low';
                                                        $priority_class = 'low-priority';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                                <?php echo htmlspecialchars(substr($article['title'], 0, 50)) . '...'; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($article['author_email']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name'] ?? ''); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo time_ago($article['created_at']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'success'); ?>">
                                                                <?php echo ucfirst($priority); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                    <i class="fas fa-edit"></i> Review
                                                                </a>
                                                                <a href="publish-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Publish this article?')">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </a>
                                                                <a href="reject-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject this article?')">
                                                                    <i class="fas fa-times"></i> Reject
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="manage-news.php?status=pending" class="btn btn-primary">View All Pending Submissions</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">No pending submissions from reporters</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Submissions -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Submissions</h5>
                                <small class="text-muted">New articles submitted today</small>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($today_submissions) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($today_submissions)): ?>
                                        <div class="workflow-step high-priority mb-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars(substr($article['title'], 0, 40)) . '...'; ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        By: <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <span class="article-status status-pending">Pending</span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Review
                                                    </a>
                                                    <a href="publish-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Publish this article?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No new submissions today</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reporter Performance Overview -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Reporter Performance Overview</h5>
                                <small class="text-muted">Track reporter activity and submission quality</small>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($reporter_stats) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reporter</th>
                                                    <th>Total Submitted</th>
                                                    <th>Pending</th>
                                                    <th>Published</th>
                                                    <th>Rejected</th>
                                                    <th>Last Activity</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($reporter = mysqli_fetch_assoc($reporter_stats)): ?>
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($reporter['name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($reporter['email']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $reporter['total_submitted']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-warning"><?php echo $reporter['pending']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success"><?php echo $reporter['published']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger"><?php echo $reporter['rejected']; ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo $reporter['last_submission'] ? time_ago($reporter['last_submission']) : 'Never'; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="manage-news.php?author=<?php echo $reporter['email']; ?>" class="btn btn-outline-primary btn-sm">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No reporter activity found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recently Reviewed -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recently Reviewed</h5>
                                <small class="text-muted">Your recent editorial actions</small>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($recently_reviewed) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($recently_reviewed)): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars(substr($article['title'], 0, 40)) . '...'; ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        By: <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <span class="article-status status-<?php echo $article['status']; ?>">
                                                        <?php echo ucfirst($article['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Reviewed: <?php echo time_ago($article['updated_at']); ?>
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No recent editorial actions</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

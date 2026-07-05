<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Get dashboard statistics
$total_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$published_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published'"))['count'];
$pending_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'pending'"))['count'];
$draft_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'draft'"))['count'];

// Get comments statistics
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'];
$pending_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending'"))['count'] ?? 0;

// Get polls statistics
$polls_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'polls'")) > 0;
$total_polls = 0;
if ($polls_table_exists) {
    $total_polls = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM polls"))['count'];
}

// Get recent news
$recent_news_query = "SELECT n.*, c.name as category_name, u.name as author_name 
                     FROM news n 
                     LEFT JOIN categories c ON n.category_id = c.id 
                     LEFT JOIN users u ON n.author_id = u.id 
                     ORDER BY n.created_at DESC LIMIT 5";
$recent_news = mysqli_query($conn, $recent_news_query);

// Get pending articles for approval
$pending_articles_query = "SELECT n.*, c.name as category_name, u.name as author_name 
                          FROM news n 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          LEFT JOIN users u ON n.author_id = u.id 
                          WHERE n.status = 'pending' 
                          ORDER BY n.created_at DESC LIMIT 5";
$pending_articles = mysqli_query($conn, $pending_articles_query);

// Get recent comments
$recent_comments_query = "SELECT cm.*, n.title as news_title 
                         FROM comments cm 
                         LEFT JOIN news n ON cm.news_id = n.id 
                         ORDER BY cm.created_at DESC LIMIT 5";
$recent_comments = mysqli_query($conn, $recent_comments_query);

// Get active poll for sidebar widget
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT p.*, po.option_text, po.votes, po.id as option_id 
     FROM polls p 
     LEFT JOIN poll_options po ON p.id = po.poll_id 
     WHERE p.status = 'active' AND (p.ends_at IS NULL OR p.ends_at > NOW()) 
     ORDER BY p.id DESC, po.id ASC LIMIT 1"
));

// Get poll options if active poll exists
$poll_options = [];
if ($active_poll) {
    $poll_id = $active_poll['id'];
    $options_query = "SELECT * FROM poll_options WHERE poll_id = $poll_id ORDER BY id ASC";
    $options_result = mysqli_query($conn, $options_query);
    while ($option = mysqli_fetch_assoc($options_result)) {
        $poll_options[] = $option;
    }
}

// Get popular news (most viewed)
$popular_news_query = "SELECT n.*, c.name as category_name 
                      FROM news n 
                      LEFT JOIN categories c ON n.category_id = c.id 
                      WHERE n.status = 'published' 
                      ORDER BY n.views DESC LIMIT 5";
$popular_news = mysqli_query($conn, $popular_news_query);
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
        .priority-high { border-left: 4px solid #dc3545; }
        .priority-medium { border-left: 4px solid #ffc107; }
        .priority-low { border-left: 4px solid #28a745; }
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
                            <a class="nav-link active" href="editor-dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
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
                        <h1 class="h3 mb-0">Editor Dashboard</h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
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

                <!-- Additional Stats Row -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="manage-news.php?status=draft" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-secondary text-white">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h3><?php echo $draft_news; ?></h3>
                                <p class="text-muted mb-0">Draft Articles</p>
                            </div>
                        </a>
                    </div>
                    <?php if ($pending_comments > 0): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="editor-comments.php?status=pending" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h3><?php echo $pending_comments; ?></h3>
                                <p class="text-muted mb-0">Pending Comments</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($polls_table_exists): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="editor-polls.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-purple text-white" style="background: linear-gradient(45deg, #667eea, #764ba2);">
                                    <i class="fas fa-poll"></i>
                                </div>
                                <h3><?php echo $total_polls; ?></h3>
                                <p class="text-muted mb-0">Active Polls</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="editor-statistics.php" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-gradient text-white" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3><i class="fas fa-chart-bar"></i></h3>
                                <p class="text-muted mb-0">View Statistics</p>
                            </div>
                        </a>
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

                <!-- Poll Widget -->
                <?php if ($active_poll): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-poll me-2"></i>Active Poll</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="poll-question"><?php echo htmlspecialchars($active_poll['question']); ?></h6>
                                        <?php if (!empty($active_poll['description'])): ?>
                                            <p class="poll-description text-muted small"><?php echo htmlspecialchars($active_poll['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="poll-options">
                                            <?php foreach ($poll_options as $option): ?>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="small"><?php echo htmlspecialchars($option['option_text']); ?></span>
                                                    <span class="badge bg-secondary"><?php echo $option['votes']; ?> votes</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>Created: <?php echo date('M d, Y', strtotime($active_poll['created_at'])); ?>
                                                <?php if (!empty($active_poll['ends_at'])): ?>
                                                    <span class="ms-3"><i class="fas fa-clock me-1"></i>Ends: <?php echo date('M d, Y', strtotime($active_poll['ends_at'])); ?></span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <?php 
                                            $total_votes = array_sum(array_column($poll_options, 'votes'));
                                            foreach ($poll_options as $option): 
                                                $percentage = $total_votes > 0 ? round(($option['votes'] / $total_votes) * 100, 1) : 0;
                                            ?>
                                                <div class="poll-result mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="small"><?php echo htmlspecialchars($option['option_text']); ?></span>
                                                        <span class="small text-muted"><?php echo $percentage; ?>%</span>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background: linear-gradient(45deg, #667eea, #764ba2);"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="text-center mt-2">
                                                <small class="text-muted"><?php echo $total_votes; ?> total votes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="editor-polls.php" class="btn btn-purple btn-sm" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none;">
                                        <i class="fas fa-cog me-1"></i>Manage Polls
                                    </a>
                                    <a href="view-poll-results.php?id=<?php echo $active_poll['id']; ?>" class="btn btn-outline-secondary btn-sm ms-2">
                                        <i class="fas fa-chart-bar me-1"></i>View Results
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Pending Articles -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Articles for Approval</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($pending_articles) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($pending_articles)): ?>
                                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom priority-high">
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    By <?php echo htmlspecialchars($article['author_name']); ?> • 
                                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Review
                                                </a>
                                                <a href="publish-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Publish this article?')">
                                                    <i class="fas fa-check"></i> Publish
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="text-center mt-3">
                                        <a href="manage-news.php?status=pending" class="btn btn-primary">View All Pending</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">No pending articles</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Comments -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Comments</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($recent_comments) > 0): ?>
                                    <?php while ($comment = mysqli_fetch_assoc($recent_comments)): ?>
                                        <div class="d-flex mb-3 pb-3 border-bottom">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                                    <small class="text-muted"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                                                </div>
                                                <p class="mb-1 small"><?php echo htmlspecialchars(substr($comment['comment'], 0, 80)) . '...'; ?></p>
                                                <small class="text-muted">On: <?php echo htmlspecialchars(substr($comment['news_title'], 0, 30)) . '...'; ?></small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="text-center mt-3">
                                        <a href="editor-comments.php" class="btn btn-primary">Manage All Comments</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No comments yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular News -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Popular News Articles</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Views</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($popular = mysqli_fetch_assoc($popular_news)): ?>
                                                <tr>
                                                    <td>
                                                        <a href="../news.php?slug=<?php echo $popular['slug']; ?>" class="text-decoration-none" target="_blank">
                                                            <?php echo htmlspecialchars(substr($popular['title'], 0, 60)) . '...'; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($popular['category_name'] ?? ''); ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo $popular['views']; ?></strong>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($popular['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit-news.php?id=<?php echo $popular['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="../news.php?slug=<?php echo $popular['slug']; ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

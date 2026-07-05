<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get reporter-specific statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id"))['count'];
$published_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'published'"))['count'];
$pending_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'pending'"))['count'];
$draft_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'draft'"))['count'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news WHERE author_id = $user_id"))['total'] ?? 0;

// Get recent articles by this reporter
$recent_articles_query = "SELECT n.*, c.name as category_name 
                         FROM news n 
                         LEFT JOIN categories c ON n.category_id = c.id 
                         WHERE n.author_id = $user_id 
                         ORDER BY n.created_at DESC LIMIT 5";
$recent_articles = mysqli_query($conn, $recent_articles_query);

// Get pending articles (waiting for editor approval)
$pending_approval_query = "SELECT n.*, c.name as category_name 
                          FROM news n 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          WHERE n.author_id = $user_id AND n.status = 'pending' 
                          ORDER BY n.created_at DESC LIMIT 5";
$pending_approval = mysqli_query($conn, $pending_approval_query);

// Get comments on reporter's articles
$comments_query = "SELECT cm.*, n.title as news_title 
                   FROM comments cm 
                   LEFT JOIN news n ON cm.news_id = n.id 
                   WHERE n.author_id = $user_id 
                   ORDER BY cm.created_at DESC LIMIT 5";
$comments = mysqli_query($conn, $comments_query);

// Get editor feedback/approval history
$editor_feedback_query = "SELECT n.*, n.updated_at as review_date
                         FROM news n 
                         WHERE n.author_id = $user_id AND n.status IN ('published', 'rejected') 
                         ORDER BY n.updated_at DESC LIMIT 5";
$editor_feedback = mysqli_query($conn, $editor_feedback_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Dashboard - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            border-left: 4px solid #f39c12;
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
        .ability-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        .ability-card:hover {
            transform: translateY(-3px);
        }
        .workflow-step {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #f39c12;
            position: relative;
        }
        .workflow-step.completed {
            border-left-color: #28a745;
        }
        .workflow-step.pending {
            border-left-color: #ffc107;
        }
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
                                <i class="fas fa-newspaper me-2"></i>My Articles
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
                        <h1 class="h3 mb-0">Reporter Dashboard <span class="badge bg-warning text-dark ms-2">Enhanced</span></h1>
                        <small>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="reporter-dashboard.php" class="btn btn-outline-light btn-sm me-2" title="Switch to Regular Dashboard">
                            <i class="fas fa-home me-1"></i>Regular
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

                <!-- Reporter Abilities Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-tasks me-2"></i>My Reporter Abilities</h4>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="ability-card">
                                    <h5><i class="fas fa-plus-circle me-2"></i>Create Articles</h5>
                                    <p class="mb-0 small">Write and create news articles</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="ability-card">
                                    <h5><i class="fas fa-edit me-2"></i>Edit Own Articles</h5>
                                    <p class="mb-0 small">Modify your draft articles</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="ability-card">
                                    <h5><i class="fas fa-paper-plane me-2"></i>Submit for Approval</h5>
                                    <p class="mb-0 small">Send to editor for review</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="ability-card">
                                    <h5><i class="fas fa-chart-line me-2"></i>View Statistics</h5>
                                    <p class="mb-0 small">Track your article performance</p>
                                </div>
                            </div>
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
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $pending_articles; ?></h3>
                                <p class="text-muted mb-0">Pending Approval</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="my-articles.php?status=draft" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-secondary text-white">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h3><?php echo $draft_articles; ?></h3>
                                <p class="text-muted mb-0">Draft Articles</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Workflow Status -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="fas fa-tasks me-2"></i>My Article Workflow</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="workflow-step completed">
                                    <h6><i class="fas fa-pen me-2"></i>Create Article</h6>
                                    <p class="mb-0 small text-muted">Write your news article</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step completed">
                                    <h6><i class="fas fa-edit me-2"></i>Edit Draft</h6>
                                    <p class="mb-0 small text-muted">Review and modify</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step <?php echo $pending_articles > 0 ? 'pending' : 'completed'; ?>">
                                    <h6><i class="fas fa-paper-plane me-2"></i>Submit to Editor</h6>
                                    <p class="mb-0 small text-muted">Send for approval</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="workflow-step <?php echo $published_articles > 0 ? 'completed' : 'pending'; ?>">
                                    <h6><i class="fas fa-check-circle me-2"></i>Get Published</h6>
                                    <p class="mb-0 small text-muted">Editor approval</p>
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
                                                                        <div class="col-md-3 mb-2">
                                        <a href="my-articles.php?status=draft" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-edit me-1"></i>View Drafts
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="my-articles.php?status=pending" class="btn btn-warning btn-sm w-100">
                                            <i class="fas fa-clock me-1"></i>Check Pending
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="reporter-profile.php" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-user me-1"></i>My Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Pending Approval (Waiting for Editor) -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Waiting for Editor Approval</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($pending_approval) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($pending_approval)): ?>
                                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($article['category_name'] ?? ''); ?> • 
                                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="article-status status-pending">Pending</span>
                                                <small class="d-block text-muted mt-1">
                                                    <i class="fas fa-user me-1"></i>Editor Review
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="text-center mt-3">
                                        <a href="my-articles.php?status=pending" class="btn btn-primary">View All Pending</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">No articles waiting for approval</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Editor Feedback -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Editor Feedback</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($editor_feedback) > 0): ?>
                                    <?php while ($feedback = mysqli_fetch_assoc($editor_feedback)): ?>
                                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="edit-news.php?id=<?php echo $feedback['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($feedback['title']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    Reviewed by: Editor
                                                </small>
                                            </div>
                                            <div>
                                                <span class="article-status status-<?php echo $feedback['status']; ?>">
                                                    <?php echo ucfirst($feedback['status']); ?>
                                                </span>
                                                <small class="d-block text-muted mt-1">
                                                    <?php echo time_ago($feedback['review_date']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="text-center mt-3">
                                        <a href="my-articles.php?status=published" class="btn btn-primary">View All Feedback</a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No editor feedback yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Articles -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>My Recent Articles</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($recent_articles) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Views</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($article = mysqli_fetch_assoc($recent_articles)): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                                <?php echo htmlspecialchars(substr($article['title'], 0, 40)) . '...'; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name'] ?? ''); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="article-status status-<?php echo $article['status']; ?>">
                                                                <?php echo ucfirst($article['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $article['views']; ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if ($article['status'] === 'draft'): ?>
                                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="submit_approval.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-warning btn-sm" onclick="return confirm('Submit for editor approval?')">
                                                                        <i class="fas fa-paper-plane"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if ($article['status'] === 'published'): ?>
                                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-newspaper fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No articles yet. Start creating your first article!</p>
                                                                            </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Comments -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Comments on My Articles</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($comments) > 0): ?>
                                    <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                                <small class="text-muted"><?php echo time_ago($comment['created_at']); ?></small>
                                            </div>
                                            <p class="mb-1 small"><?php echo htmlspecialchars(substr($comment['comment'], 0, 80)) . '...'; ?></p>
                                            <small class="text-muted">On: <?php echo htmlspecialchars(substr($comment['news_title'], 0, 30)) . '...'; ?></small>
                                        </div>
                                    <?php endwhile; ?>
                                    <div class="text-center mt-3">
                                        <a href="my-comments.php" class="btn btn-primary">View All Comments</a>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../config/database.php';

// Check if user is logged in and is editor or admin
if (!is_logged_in() || !in_array($_SESSION['user_role'], ['editor', 'admin'])) {
    redirect('login.php');
}

// Handle approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $article_id = (int)$_POST['article_id'];
    
    if ($_POST['action'] === 'approve') {
        $publish_date = clean_input($_POST['publish_date'] ?? 'NOW()');
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        if ($publish_date === 'NOW()') {
            $query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $article_id);
        } else {
            $query = "UPDATE news SET status = 'published', published_at = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $publish_date, $article_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // If featured, update all other articles to not be featured
            if ($featured) {
                mysqli_query($conn, "UPDATE news SET status = 'published' WHERE status = 'featured'");
                mysqli_query($conn, "UPDATE news SET status = 'featured' WHERE id = $article_id");
            }
            
            // Notify reporter
            $article_query = "SELECT author_id, title FROM news WHERE id = $article_id";
            $article_result = mysqli_query($conn, $article_query);
            $article = mysqli_fetch_assoc($article_result);
            
            $success = "Article '{$article['title']}' has been approved and published!";
        } else {
            $error = "Error approving article. Please try again.";
        }
    }
    
    if ($_POST['action'] === 'reject') {
        $rejection_reason = clean_input($_POST['rejection_reason']);
        
        $query = "UPDATE news SET status = 'draft' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $article_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // You could send an email to the reporter with the rejection reason here
            $article_query = "SELECT author_id, title FROM news WHERE id = $article_id";
            $article_result = mysqli_query($conn, $article_query);
            $article = mysqli_fetch_assoc($article_result);
            
            $success = "Article '{$article['title']}' has been rejected and returned to draft.";
        } else {
            $error = "Error rejecting article. Please try again.";
        }
    }
    
    if ($_POST['action'] === 'request_changes') {
        $changes_requested = clean_input($_POST['changes_requested']);
        
        // Store changes requested in article content or a separate table
        $query = "UPDATE news SET status = 'draft' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $article_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $article_query = "SELECT author_id, title FROM news WHERE id = $article_id";
            $article_result = mysqli_query($conn, $article_query);
            $article = mysqli_fetch_assoc($article_result);
            
            $success = "Changes requested for '{$article['title']}'. Article returned to draft.";
        } else {
            $error = "Error requesting changes. Please try again.";
        }
    }
}

// Get pending articles
$pending_query = "SELECT n.*, u.name as author_name, u.email as author_email, c.name as category_name 
                 FROM news n 
                 JOIN users u ON n.author_id = u.id 
                 LEFT JOIN categories c ON n.category_id = c.id 
                 WHERE n.status = 'draft' 
                 ORDER BY n.created_at ASC";
$pending_articles = mysqli_query($conn, $pending_query);

// Get recently approved articles
$recent_approved_query = "SELECT n.*, u.name as author_name, c.name as category_name 
                          FROM news n 
                          JOIN users u ON n.author_id = u.id 
                          LEFT JOIN categories c ON n.category_id = c.id 
                          WHERE n.status IN ('published', 'featured') 
                          AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                          ORDER BY n.published_at DESC LIMIT 5";
$recent_approved = mysqli_query($conn, $recent_approved_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_pending,
    (SELECT COUNT(*) FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as approved_this_week,
    (SELECT COUNT(*) FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as approved_this_month
    FROM news WHERE status = 'draft'";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Approval - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .editor-header {
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
        .approval-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 5px solid #ffc107;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .article-preview {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .article-preview:hover {
            transform: translateY(-2px);
        }
        .urgent-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        .approval-actions {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .content-preview {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: #f8f9fa;
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
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
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
                            <a class="nav-link active" href="editor_approval.php">
                                <i class="fas fa-check-circle me-2"></i>Editor Approval
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_permissions.php">
                                <i class="fas fa-users-cog me-2"></i>Permissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="website_control.php">
                                <i class="fas fa-cogs me-2"></i>Website Control
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
                <!-- Header -->
                <div class="editor-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-check-circle me-2"></i>Editor Approval
                        </h1>
                        <small>Review and approve articles submitted by reporters</small>
                    </div>
                    <div>
                        <?php if ($stats['total_pending'] > 0): ?>
                            <span class="urgent-badge">
                                <i class="fas fa-clock me-1"></i><?php echo $stats['total_pending']; ?> Pending
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?php echo $stats['total_pending']; ?></h3>
                            <p class="mb-0">Pending Approval</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?php echo $stats['approved_this_week']; ?></h3>
                            <p class="mb-0">Approved This Week</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?php echo $stats['approved_this_month']; ?></h3>
                            <p class="mb-0">Approved This Month</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Pending Articles -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>Pending Articles
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($pending_articles) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($pending_articles)): ?>
                                        <div class="approval-card">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <h5 class="mb-0 me-3">
                                                            <?php echo htmlspecialchars($article['title']); ?>
                                                        </h5>
                                                        <?php if ($article['is_breaking']): ?>
                                                            <span class="urgent-badge">BREAKING</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <span class="badge bg-secondary me-2">
                                                            <i class="fas fa-user me-1"></i>
                                                            <?php echo htmlspecialchars($article['author_name']); ?>
                                                        </span>
                                                        <span class="badge bg-info me-2">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?>
                                                        </span>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo time_ago($article['created_at']); ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="content-preview mb-3">
                                                        <?php echo nl2br(htmlspecialchars(substr(strip_tags($article['content']), 0, 500))) . '...'; ?>
                                                    </div>
                                                    
                                                    <?php if ($article['image']): ?>
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-image me-1"></i>Image attached
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($article['video_url']): ?>
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-video me-1"></i>Video attached
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="text-end">
                                                        <div class="mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope me-1"></i>
                                                                <?php echo htmlspecialchars($article['author_email']); ?>
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="btn-group-vertical w-100">
                                                            <button class="btn btn-success" onclick="showApprovalForm(<?php echo $article['id']; ?>)">
                                                                <i class="fas fa-check me-2"></i>Approve
                                                            </button>
                                                            <button class="btn btn-warning" onclick="showChangesForm(<?php echo $article['id']; ?>)">
                                                                <i class="fas fa-edit me-2"></i>Request Changes
                                                            </button>
                                                            <button class="btn btn-danger" onclick="showRejectionForm(<?php echo $article['id']; ?>)">
                                                                <i class="fas fa-times me-2"></i>Reject
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Approval Form (Hidden by default) -->
                                            <div id="approvalForm-<?php echo $article['id']; ?>" class="approval-actions" style="display: none;">
                                                <h6 class="mb-3">Approve Article</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Publish Date</label>
                                                        <select name="publish_date" class="form-select">
                                                            <option value="NOW()">Immediately</option>
                                                            <option value="<?php echo date('Y-m-d H:i:s', strtotime('+1 hour')); ?>">In 1 hour</option>
                                                            <option value="<?php echo date('Y-m-d H:i:s', strtotime('+6 hours')); ?>">In 6 hours</option>
                                                            <option value="<?php echo date('Y-m-d 09:00:00', strtotime('tomorrow')); ?>">Tomorrow 9 AM</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="featured" id="featured-<?php echo $article['id']; ?>">
                                                            <label class="form-check-label" for="featured-<?php echo $article['id']; ?>">
                                                                Make this featured article
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-secondary me-2" onclick="hideApprovalForm(<?php echo $article['id']; ?>)">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check me-2"></i>Approve & Publish
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            
                                            <!-- Changes Form (Hidden by default) -->
                                            <div id="changesForm-<?php echo $article['id']; ?>" class="approval-actions" style="display: none;">
                                                <h6 class="mb-3">Request Changes</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="request_changes">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Changes Required</label>
                                                        <textarea name="changes_requested" class="form-control" rows="4" 
                                                                  placeholder="Specify what changes are needed..." required></textarea>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-secondary me-2" onclick="hideChangesForm(<?php echo $article['id']; ?>)">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="fas fa-edit me-2"></i>Request Changes
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            
                                            <!-- Rejection Form (Hidden by default) -->
                                            <div id="rejectionForm-<?php echo $article['id']; ?>" class="approval-actions" style="display: none;">
                                                <h6 class="mb-3">Reject Article</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Rejection Reason</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="4" 
                                                                  placeholder="Explain why this article is being rejected..." required></textarea>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-secondary me-2" onclick="hideRejectionForm(<?php echo $article['id']; ?>)">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this article?')">
                                                            <i class="fas fa-times me-2"></i>Reject Article
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                        <h4 class="text-muted">All caught up!</h4>
                                        <p class="text-muted">No articles pending approval at the moment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recently Approved -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Recently Approved
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                <?php if (mysqli_num_rows($recent_approved) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($recent_approved)): ?>
                                        <div class="article-preview">
                                            <h6 class="mb-2">
                                                <a href="../news.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </h6>
                                            
                                            <div class="mb-2">
                                                <span class="badge bg-success me-2">Published</span>
                                                <span class="badge bg-info me-2">
                                                    <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="text-muted small">
                                                <div class="mb-1">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?php echo htmlspecialchars($article['author_name']); ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y h:i A', strtotime($article['published_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No articles approved in the last week</p>
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
    <script>
        function showApprovalForm(articleId) {
            hideAllForms(articleId);
            document.getElementById('approvalForm-' + articleId).style.display = 'block';
        }
        
        function hideApprovalForm(articleId) {
            document.getElementById('approvalForm-' + articleId).style.display = 'none';
        }
        
        function showChangesForm(articleId) {
            hideAllForms(articleId);
            document.getElementById('changesForm-' + articleId).style.display = 'block';
        }
        
        function hideChangesForm(articleId) {
            document.getElementById('changesForm-' + articleId).style.display = 'none';
        }
        
        function showRejectionForm(articleId) {
            hideAllForms(articleId);
            document.getElementById('rejectionForm-' + articleId).style.display = 'block';
        }
        
        function hideRejectionForm(articleId) {
            document.getElementById('rejectionForm-' + articleId).style.display = 'none';
        }
        
        function hideAllForms(exceptId) {
            const forms = document.querySelectorAll('[id^="approvalForm-"], [id^="changesForm-"], [id^="rejectionForm-"]');
            forms.forEach(form => {
                if (!form.id.includes(exceptId)) {
                    form.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

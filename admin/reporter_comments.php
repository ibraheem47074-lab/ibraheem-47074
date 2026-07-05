<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

// Handle comment reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reply') {
        $comment_id = (int)$_POST['comment_id'];
        $reply_text = clean_input($_POST['reply_text']);
        
        // Get the news article to ensure it belongs to this reporter
        $news_query = "SELECT n.id FROM news n 
                      JOIN comments c ON c.news_id = n.id 
                      WHERE c.id = $comment_id AND n.author_id = $reporter_id";
        $news_result = mysqli_query($conn, $news_query);
        
        if (mysqli_num_rows($news_result) > 0) {
            $news = mysqli_fetch_assoc($news_result);
            
            // Insert reply as a new comment
            $reply_query = "INSERT INTO comments (news_id, user_id, comment, status, created_at) 
                           VALUES (?, ?, ?, 'approved', NOW())";
            
            $stmt = mysqli_prepare($conn, $reply_query);
            mysqli_stmt_bind_param($stmt, 'iis', $news['id'], $reporter_id, $reply_text);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Reply posted successfully!";
            } else {
                $error = "Error posting reply. Please try again.";
            }
        } else {
            $error = "You can only reply to comments on your own articles.";
        }
    }
    
    if ($_POST['action'] === 'approve') {
        $comment_id = (int)$_POST['comment_id'];
        
        // Verify the comment belongs to reporter's article
        $verify_query = "SELECT c.id FROM comments c 
                        JOIN news n ON c.news_id = n.id 
                        WHERE c.id = $comment_id AND n.author_id = $reporter_id";
        $verify_result = mysqli_query($conn, $verify_query);
        
        if (mysqli_num_rows($verify_result) > 0) {
            $approve_query = "UPDATE comments SET status = 'approved' WHERE id = $comment_id";
            mysqli_query($conn, $approve_query);
            $success = "Comment approved!";
        }
    }
    
    if ($_POST['action'] === 'reject') {
        $comment_id = (int)$_POST['comment_id'];
        
        // Verify the comment belongs to reporter's article
        $verify_query = "SELECT c.id FROM comments c 
                        JOIN news n ON c.news_id = n.id 
                        WHERE c.id = $comment_id AND n.author_id = $reporter_id";
        $verify_result = mysqli_query($conn, $verify_query);
        
        if (mysqli_num_rows($verify_result) > 0) {
            $reject_query = "UPDATE comments SET status = 'rejected' WHERE id = $comment_id";
            mysqli_query($conn, $reject_query);
            $success = "Comment rejected!";
        }
    }
}

// Get comments on reporter's articles
$status_filter = $_GET['status'] ?? 'all';
$where_clause = "WHERE n.author_id = $reporter_id";

if ($status_filter !== 'all') {
    $where_clause .= " AND c.status = '$status_filter'";
}

$comments_query = "SELECT c.*, n.title as news_title, n.slug as news_slug 
                  FROM comments c 
                  JOIN news n ON c.news_id = n.id 
                  $where_clause 
                  ORDER BY c.created_at DESC";
$comments = mysqli_query($conn, $comments_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM comments c 
    JOIN news n ON c.news_id = n.id 
    WHERE n.author_id = $reporter_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Management - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .comment-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .comment-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 15px;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        .status-rejected {
            background-color: #dc3545;
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .reply-form {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
                            <a class="nav-link" href="reporter_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_news.php">
                                <i class="fas fa-newspaper me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link bg-danger text-white" href="breaking_news_reporter.php">
                                <i class="fas fa-bolt me-2"></i>Breaking News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live_reporter.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_profile.php">
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
                        <h1 class="h3 mb-0">
                            <i class="fas fa-comments me-2"></i>Comments Management
                        </h1>
                        <small>Engage with your readers and moderate comments</small>
                    </div>
                    <div>
                        <?php if ($stats['pending'] > 0): ?>
                            <span class="badge bg-warning">
                                <i class="fas fa-clock me-1"></i><?php echo $stats['pending']; ?> pending
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
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['total']; ?></h4>
                            <small>Total Comments</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['pending']; ?></h4>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['approved']; ?></h4>
                            <small>Approved</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['rejected']; ?></h4>
                            <small>Rejected</small>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-section">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Filter Comments</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <a href="reporter_comments.php?status=all" 
                                   class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All (<?php echo $stats['total']; ?>)
                                </a>
                                <a href="reporter_comments.php?status=pending" 
                                   class="btn <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                    Pending (<?php echo $stats['pending']; ?>)
                                </a>
                                <a href="reporter_comments.php?status=approved" 
                                   class="btn <?php echo $status_filter === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                                    Approved (<?php echo $stats['approved']; ?>)
                                </a>
                                <a href="reporter_comments.php?status=rejected" 
                                   class="btn <?php echo $status_filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                    Rejected (<?php echo $stats['rejected']; ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments List -->
                <?php if (mysqli_num_rows($comments) > 0): ?>
                    <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                        <div class="comment-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-user me-2"></i>
                                                <?php echo htmlspecialchars($comment['name']); ?>
                                                <?php if ($comment['user_id']): ?>
                                                    <small class="text-muted">(Registered User)</small>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if ($comment['email']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <?php echo htmlspecialchars($comment['email']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="status-badge status-<?php echo $comment['status']; ?>">
                                            <?php echo ucfirst($comment['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-newspaper me-1"></i>
                                            On article: 
                                            <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>" target="_blank" class="text-decoration-none">
                                                <?php echo htmlspecialchars($comment['news_title']); ?>
                                            </a>
                                        </small>
                                    </div>
                                    
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-end">
                                        <?php if ($comment['status'] === 'pending'): ?>
                                            <div class="mb-2">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success me-1">
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this comment?')">
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($comment['status'] === 'approved'): ?>
                                            <button class="btn btn-sm btn-primary mb-2" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                                <i class="fas fa-reply me-1"></i>Reply
                                            </button>
                                        <?php endif; ?>
                                        
                                        <div class="text-muted small">
                                            <?php if ($comment['user_id']): ?>
                                                <i class="fas fa-user-check me-1"></i>Verified User
                                            <?php else: ?>
                                                <i class="fas fa-user me-1"></i>Guest User
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reply Form (Hidden by default) -->
                            <div id="replyForm-<?php echo $comment['id']; ?>" class="reply-form" style="display: none;">
                                <h6 class="mb-3">Post a Reply</h6>
                                <form method="POST">
                                    <input type="hidden" name="action" value="reply">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <div class="mb-3">
                                        <textarea name="reply_text" class="form-control" rows="3" 
                                                  placeholder="Write your reply..." required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i>Post Reply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No comments found</h4>
                        <p class="text-muted">
                            <?php if ($status_filter !== 'all'): ?>
                                No comments with status "<?php echo $status_filter; ?>" found.
                                <a href="reporter_comments.php">View all comments</a>
                            <?php else: ?>
                                No comments on your articles yet. Readers will start commenting when your articles get published!
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleReplyForm(commentId) {
            const replyForm = document.getElementById('replyForm-' + commentId);
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            
            if (replyForm.style.display === 'block') {
                replyForm.querySelector('textarea').focus();
            }
        }
        
        // Auto-refresh pending comments every 30 seconds
        <?php if ($status_filter === 'pending' && $stats['pending'] > 0): ?>
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>

<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_comment'])) {
        $comment_id = $_POST['comment_id'];
        $query = "UPDATE comments SET status = 'approved', moderated_by = ?, moderated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $_SESSION['user_id'], $comment_id);
        mysqli_stmt_execute($stmt);
        $success = "Comment approved successfully!";
    } elseif (isset($_POST['reject_comment'])) {
        $comment_id = $_POST['comment_id'];
        $reason = clean_input($_POST['reason'] ?? '');
        $query = "UPDATE comments SET status = 'rejected', rejection_reason = ?, moderated_by = ?, moderated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sii', $reason, $_SESSION['user_id'], $comment_id);
        mysqli_stmt_execute($stmt);
        $success = "Comment rejected successfully!";
    } elseif (isset($_POST['delete_comment'])) {
        $comment_id = $_POST['comment_id'];
        $query = "DELETE FROM comments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $comment_id);
        mysqli_stmt_execute($stmt);
        $success = "Comment deleted successfully!";
    }
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["c.status = ?"];
$params = [$status];
$types = 's';

if ($status === 'all') {
    $where_conditions = [];
    $params = [];
    $types = '';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM comments c $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $count_query);
}
$total_comments = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total_comments / $per_page);

// Get comments with pagination
$comments_query = "SELECT c.*, n.title as news_title, n.slug as news_slug, u.name as user_name, u.email as user_email
                   FROM comments c 
                   LEFT JOIN news n ON c.news_id = n.id 
                   LEFT JOIN users u ON c.user_id = u.id 
                   $where_clause
                   ORDER BY c.created_at DESC 
                   LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $comments_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $comments = mysqli_stmt_get_result($stmt);
} else {
    $comments = mysqli_query($conn, $comments_query);
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam
    FROM comments";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get today's moderation stats
$today_stats_query = "SELECT 
    COUNT(*) as today_moderated,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as today_approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as today_rejected
    FROM comments 
    WHERE DATE(moderated_at) = CURDATE() AND moderated_by = ?";
$stmt = mysqli_prepare($conn, $today_stats_query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$today_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Helper function for time ago
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Comments Management - PK Live News</title>
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
            text-decoration: none;
            color: inherit;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            color: inherit;
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
        .comment-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
            border-left: 4px solid #4834d4;
        }
        .comment-card:hover {
            transform: translateY(-2px);
        }
        .comment-card.pending { border-left-color: #ffc107; }
        .comment-card.approved { border-left-color: #28a745; }
        .comment-card.rejected { border-left-color: #dc3545; }
        .comment-card.spam { border-left-color: #6f42c1; }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-spam { background-color: #e2d9f3; color: #383d41; }
        .priority-high { border-left-color: #dc3545; background: #fff5f5; }
        .priority-medium { border-left-color: #ffc107; background: #fffbf0; }
        .priority-low { border-left-color: #28a745; background: #f8fff8; }
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
                            <a class="nav-link" href="editor-dashboard-enhanced.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor_news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="editor_comments_enhanced.php">
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
                            <a class="nav-link" href="editor_profile_enhanced.php">
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
                        <h1 class="h3 mb-0">Comments Management</h1>
                        <small>Moderate and manage user comments on articles</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="editor_profile_enhanced.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=all" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <h3><?php echo $stats['total']; ?></h3>
                                <p class="text-muted mb-0">Total Comments</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=pending" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-warning text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h3><?php echo $stats['pending']; ?></h3>
                                <p class="text-muted mb-0">Pending Review</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=approved" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3><?php echo $stats['approved']; ?></h3>
                                <p class="text-muted mb-0">Approved</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=rejected" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-danger text-white">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <h3><?php echo $stats['rejected']; ?></h3>
                                <p class="text-muted mb-0">Rejected</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Today's Activity -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Moderation Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4><?php echo $today_stats['today_moderated']; ?></h4>
                                <small class="text-muted">Comments Moderated</small>
                            </div>
                            <div class="col-md-3">
                                <h4><?php echo $today_stats['today_approved']; ?></h4>
                                <small class="text-muted">Approved</small>
                            </div>
                            <div class="col-md-3">
                                <h4><?php echo $today_stats['today_rejected']; ?></h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                            <div class="col-md-3">
                                <h4><?php echo $today_stats['today_moderated'] > 0 ? round(($today_stats['today_approved'] / $today_stats['today_moderated']) * 100, 1) : 0; ?>%</h4>
                                <small class="text-muted">Approval Rate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments List -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-comments me-2"></i>Comments
                            <span class="badge bg-secondary ms-2"><?php echo $total_comments; ?> total</span>
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <a href="?status=pending" class="btn btn-outline-warning <?php echo $status === 'pending' ? 'active' : ''; ?>">
                                Pending
                            </a>
                            <a href="?status=approved" class="btn btn-outline-success <?php echo $status === 'approved' ? 'active' : ''; ?>">
                                Approved
                            </a>
                            <a href="?status=rejected" class="btn btn-outline-danger <?php echo $status === 'rejected' ? 'active' : ''; ?>">
                                Rejected
                            </a>
                            <a href="?status=all" class="btn btn-outline-secondary <?php echo $status === 'all' ? 'active' : ''; ?>">
                                All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($comments) > 0): ?>
                            <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                                <?php
                                $priority_class = '';
                                $time_ago = time() - strtotime($comment['created_at']);
                                if ($comment['status'] === 'pending' && $time_ago < 3600) { // Pending less than 1 hour
                                    $priority_class = 'priority-high';
                                } elseif ($comment['status'] === 'approved') {
                                    $priority_class = 'priority-low';
                                } else {
                                    $priority_class = 'priority-medium';
                                }
                                ?>
                                <div class="comment-card <?php echo $priority_class; ?> <?php echo $comment['status']; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($comment['user_name'] ?? 'Anonymous'); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($comment['user_email'] ?? 'No email'); ?>)</small>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-newspaper me-1"></i>
                                                <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($comment['news_title']); ?>
                                                </a>
                                            </small>
                                        </div>
                                        <div>
                                            <span class="status-badge status-<?php echo $comment['status']; ?>">
                                                <?php echo ucfirst($comment['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            Posted: <?php echo time_ago($comment['created_at']); ?>
                                            <?php if ($comment['moderated_at']): ?>
                                                <br>
                                                <i class="fas fa-edit me-1"></i>
                                                Moderated: <?php echo time_ago($comment['moderated_at']); ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($comment['status'] === 'pending'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="approve_comment" value="1">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-success" onclick="return confirm('Approve this comment?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $comment['id']; ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                                
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="delete_comment" value="1">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this comment?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank" class="btn btn-outline-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="delete_comment" value="1">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this comment?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($comment['rejection_reason']): ?>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-muted">
                                                <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($comment['rejection_reason']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?php echo $comment['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Comment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="reject_comment" value="1">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rejection Reason (Optional)</label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="Why are you rejecting this comment?"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject Comment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Comments pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No comments found</h5>
                                <p class="text-muted">No comments match your current filters.</p>
                                <a href="editor_comments_enhanced.php" class="btn btn-primary">Clear Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

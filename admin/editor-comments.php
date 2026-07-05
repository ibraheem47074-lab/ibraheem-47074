<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Handle comment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $comment_id = (int)$_GET['id'];
    
    switch ($_GET['action']) {
        case 'approve':
            $query = "UPDATE comments SET status = 'approved' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Comment approved successfully!";
            }
            break;
            
        case 'reject':
            $query = "UPDATE comments SET status = 'rejected' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Comment rejected successfully!";
            }
            break;
            
        case 'delete':
            $query = "DELETE FROM comments WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Comment deleted successfully!";
            }
            break;
    }
}

// Get comments with filtering and pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtering
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_status)) {
    $where_conditions[] = "cm.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(cm.name LIKE ? OR cm.email LIKE ? OR cm.comment LIKE ? OR n.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ssss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total comments count
$count_query = "SELECT COUNT(*) as total 
               FROM comments cm 
               LEFT JOIN news n ON cm.news_id = n.id 
               $where_clause";
$stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_comments = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Get comments
$comments_query = "SELECT cm.*, n.title as news_title, n.slug as news_slug 
                   FROM comments cm 
                   LEFT JOIN news n ON cm.news_id = n.id 
                   $where_clause 
                   ORDER BY cm.created_at DESC 
                   LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($conn, $comments_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$comments = mysqli_stmt_get_result($stmt);

// Calculate pagination
$total_pages = ceil($total_comments / $per_page);

// Get statistics
$total_comments_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments"))['count'];
$pending_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending' OR status IS NULL"))['count'];
$approved_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'approved'"))['count'];
$rejected_comments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'rejected'"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - PK Live News Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .editor-header {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            color: white;
        }
        .comment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .comment-card {
            border-left: 4px solid #4834d4;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .comment-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .comment-card.pending { border-left-color: #ffc107; }
        .comment-card.approved { border-left-color: #28a745; }
        .comment-card.rejected { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'includes/editor-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-comments me-3"></i>Manage Comments</h2>
                <p class="text-muted">Moderate and manage all comments on news articles.</p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Total Comments</h5>
                                <h3 class="font-weight-bold"><?php echo $total_comments_all; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-comments fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Pending</h5>
                                <h3 class="font-weight-bold"><?php echo $pending_comments; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Approved</h5>
                                <h3 class="font-weight-bold"><?php echo $approved_comments; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-uppercase text-muted mb-1">Rejected</h5>
                                <h3 class="font-weight-bold"><?php echo $rejected_comments; ?></h3>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search comments...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                    <a href="editor-comments.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo $total_comments; ?> comments</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <?php if ($filter_status === 'pending' || empty($filter_status)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="bulkActionsForm">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <select name="bulk_action" class="form-select form-select-sm" id="bulkActionSelect">
                                        <option value="">Bulk Actions</option>
                                        <option value="approve_all">Approve All Pending</option>
                                        <option value="reject_all">Reject All Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkAction()">
                                        <i class="fas fa-check me-1"></i>Apply Action
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Comments List</h5>
                        <div>
                            <a href="editor-dashboard.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($comments) > 0): ?>
                        <div class="card-body p-0">
                            <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                                <?php 
                                $status = $comment['status'] ?? 'pending';
                                $status_class = $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending');
                                ?>
                                <div class="comment-card <?php echo $status_class; ?> p-3">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="fas fa-user me-2"></i>
                                                        <?php echo htmlspecialchars($comment['name']); ?>
                                                        <small class="text-muted">(&lt;<?php echo htmlspecialchars($comment['email']); ?>&gt;)</small>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <span class="comment-status status-<?php echo $status; ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <strong>On article:</strong>
                                                <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($comment['news_title']); ?>
                                                </a>
                                            </div>
                                            
                                            <div class="comment-content">
                                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                            </div>
                                            
                                            <?php if (!empty($comment['website'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-globe me-1"></i>
                                                        <a href="<?php echo htmlspecialchars($comment['website']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($comment['website']); ?>
                                                        </a>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="d-flex flex-column align-items-end">
                                                <div class="btn-group mb-2">
                                                    <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>#comment-<?php echo $comment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i> View
                                                    </a>
                                                    <?php if ($status === 'pending' || $status === 'rejected'): ?>
                                                        <a href="editor-comments.php?action=approve&id=<?php echo $comment['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success" onclick="return confirm('Approve this comment?')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($status === 'pending' || $status === 'approved'): ?>
                                                        <a href="editor-comments.php?action=reject&id=<?php echo $comment['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning" onclick="return confirm('Reject this comment?')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="editor-comments.php?action=delete&id=<?php echo $comment['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this comment permanently?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                                <?php if (!empty($comment['ip_address'])): ?>
                                                    <small class="text-muted">IP: <?php echo htmlspecialchars($comment['ip_address']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Comments pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5>No comments found</h5>
                            <p class="text-muted">No comments match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            if (!action) {
                alert('Please select an action to perform.');
                return false;
            }
            
            let confirmMessage = '';
            switch (action) {
                case 'approve_all':
                    confirmMessage = 'Approve all pending comments? This will make them visible on the website.';
                    break;
                case 'reject_all':
                    confirmMessage = 'Reject all pending comments? This will hide them from the website.';
                    break;
            }
            
            return confirm(confirmMessage);
        }
    </script>
</body>
</html>

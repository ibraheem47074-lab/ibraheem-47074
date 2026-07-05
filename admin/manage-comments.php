<?php
require_once '../config/database.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $comment_id = $_GET['delete'];
    
    // Delete comment
    $delete_query = "DELETE FROM comments WHERE id = $comment_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $success = "Comment deleted successfully!";
    } else {
        $error = "Error deleting comment!";
    }
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $comment_id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['pending', 'approved', 'rejected'])) {
        $update_query = "UPDATE comments SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $comment_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Comment status updated successfully!";
        } else {
            $error = "Error updating comment status!";
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
$news_filter = isset($_GET['news_filter']) ? (int)$_GET['news_filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter != 'all') {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if ($news_filter != 'all') {
    $where_conditions[] = "c.news_id = ?";
    $params[] = $news_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get comments with news and user info
$comments_query = "SELECT c.*, n.title as news_title, n.slug as news_slug, u.name as user_name
                  FROM comments c 
                  LEFT JOIN news n ON c.news_id = n.id 
                  LEFT JOIN users u ON c.user_id = u.id 
                  $where_clause
                  ORDER BY c.created_at DESC 
                  LIMIT $per_page OFFSET $offset";

// Execute query with parameters
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $comments_query);
    $types = str_repeat('s', count($params ?? []));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $comments_result = mysqli_stmt_get_result($stmt);
} else {
    $comments_result = mysqli_query($conn, $comments_query);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM comments c $where_clause";
if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $total_result = mysqli_stmt_get_result($count_stmt);
} else {
    $total_result = mysqli_query($conn, $count_query);
}
$total_records = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get news articles for filter dropdown
$news_query = "SELECT id, title FROM news ORDER BY title ASC";
$news_result = mysqli_query($conn, $news_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments - PK Live News Admin</title>
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
        .comment-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .comment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .comment-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #dee2e6;
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .filter-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
                        <h4><i class="fas fa-newspaper me-2"></i>PK Live News</h4>
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
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
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
                            <a class="nav-link active" href="manage-comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
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
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Manage Comments</h1>
                        <small>Moderate and manage user comments</small>
                    </div>
                    <div>
                        <button class="btn btn-light" onclick="toggleBulkActions()">
                            <i class="fas fa-check-square me-2"></i>Bulk Actions
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-select" id="status_filter" name="status_filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="news_filter" class="form-label">News Article</label>
                            <select class="form-select" id="news_filter" name="news_filter" onchange="this.form.submit()">
                                <option value="all" <?php echo $news_filter == 'all' ? 'selected' : ''; ?>>All Articles</option>
                                <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                                    <option value="<?php echo $news['id']; ?>" <?php echo $news_filter == $news['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="per_page" class="form-label">Per Page</label>
                            <select class="form-select" id="per_page" name="per_page" onchange="this.form.submit()">
                                <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Comments List -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            Comments (<?php echo number_format($total_records ?? 0); ?> total)
                            <?php if ($status_filter != 'all'): ?>
                                - <?php echo ucfirst($status_filter); ?>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Bulk Actions (Hidden by default) -->
                        <div id="bulkActions" class="d-none mb-3">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="bulkApprove()">
                                    <i class="fas fa-check me-2"></i>Approve Selected
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="bulkReject()">
                                    <i class="fas fa-times me-2"></i>Reject Selected
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-2"></i>Delete Selected
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>Comment</th>
                                        <th>Author</th>
                                        <th>News Article</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
                                        <tr class="comment-card">
                                            <td>
                                                <input type="checkbox" class="comment-checkbox" value="<?php echo $comment['id']; ?>">
                                            </td>
                                            <td>
                                                <div class="comment-text">
                                                    <?php echo htmlspecialchars($comment['comment']); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php 
                                                    if ($comment['user_id']) {
                                                        echo 'By registered user';
                                                    } else {
                                                        echo 'By guest: ' . htmlspecialchars($comment['name']);
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($comment['user_name']) {
                                                    echo '<strong>' . htmlspecialchars($comment['user_name']) . '</strong>';
                                                } else {
                                                    echo '<span class="text-muted">Guest</span><br><small>' . htmlspecialchars($comment['name']) . '</small>';
                                                }
                                                ?>
                                                <?php if ($comment['email']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($comment['email']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($comment['news_title']): ?>
                                                    <a href="../news.php?slug=<?php echo $comment['news_slug']; ?>" target="_blank">
                                                        <?php echo htmlspecialchars($comment['news_title']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Article deleted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $status_color = $status_colors[$comment['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $status_color; ?> status-badge">
                                                    <?php echo ucfirst($comment['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y - h:i A', strtotime($comment['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons d-flex gap-1">
                                                    <?php if ($comment['status'] != 'approved'): ?>
                                                        <a href="?id=<?php echo $comment['id']; ?>&status=approved" class="btn btn-sm btn-outline-success" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($comment['status'] != 'rejected'): ?>
                                                        <a href="?id=<?php echo $comment['id']; ?>&status=rejected" class="btn btn-sm btn-outline-warning" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="edit-comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <a href="?delete=<?php echo $comment['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this comment?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($comments_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5>No comments found</h5>
                                <p class="text-muted">
                                    <?php if ($status_filter != 'all' || $news_filter != 'all'): ?>
                                        Try adjusting your filters
                                    <?php else: ?>
                                        No comments have been posted yet
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Comments pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status_filter=<?php echo $status_filter; ?>&news_filter=<?php echo $news_filter; ?>&per_page=<?php echo $per_page; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status_filter=<?php echo $status_filter; ?>&news_filter=<?php echo $news_filter; ?>&per_page=<?php echo $per_page; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status_filter=<?php echo $status_filter; ?>&news_filter=<?php echo $news_filter; ?>&per_page=<?php echo $per_page; ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comment Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo number_format($total_records ?? 0); ?></h3>
                                <p class="mb-0">Total Comments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $pending_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-warning"><?php echo $pending_count; ?></h3>
                                <p class="mb-0">Pending Approval</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $approved_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'approved'")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-success"><?php echo $approved_count; ?></h3>
                                <p class="mb-0">Approved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $rejected_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status = 'rejected'")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-danger"><?php echo $rejected_count; ?></h3>
                                <p class="mb-0">Rejected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Comment Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Action</th>
                                        <th>Comment</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_query = "SELECT c.*, n.title as news_title 
                                                   FROM comments c 
                                                   LEFT JOIN news n ON c.news_id = n.id 
                                                   ORDER BY c.created_at DESC LIMIT 10";
                                    $recent_result = mysqli_query($conn, $recent_query);
                                    ?>
                                    <?php while ($comment = mysqli_fetch_assoc($recent_result)): ?>
                                        <tr>
                                            <td><?php echo date('h:i A', strtotime($comment['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_colors[$comment['status']] ?? 'secondary'; ?>">
                                                    <?php echo ucfirst($comment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($comment['comment'], 0, 50)) . '...'; ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($comment['name'] ?: 'Guest'); ?></small>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle bulk actions
        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            bulkActions.classList.toggle('d-none');
        }

        // Select all checkboxes
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.comment-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        // Bulk approve
        function bulkApprove() {
            const selected = getSelectedComments();
            if (selected.length === 0) {
                alert('Please select comments to approve');
                return;
            }
            
            if (confirm(`Approve ${selected.length} comments?`)) {
                location.href = `bulk-comments.php?action=approve&ids=${selected.join(',')}`;
            }
        }

        // Bulk reject
        function bulkReject() {
            const selected = getSelectedComments();
            if (selected.length === 0) {
                alert('Please select comments to reject');
                return;
            }
            
            if (confirm(`Reject ${selected.length} comments?`)) {
                location.href = `bulk-comments.php?action=reject&ids=${selected.join(',')}`;
            }
        }

        // Bulk delete
        function bulkDelete() {
            const selected = getSelectedComments();
            if (selected.length === 0) {
                alert('Please select comments to delete');
                return;
            }
            
            if (confirm(`Delete ${selected.length} comments? This action cannot be undone.`)) {
                location.href = `bulk-comments.php?action=delete&ids=${selected.join(',')}`;
            }
        }

        // Get selected comment IDs
        function getSelectedComments() {
            const checkboxes = document.querySelectorAll('.comment-checkbox:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }

        // Auto-refresh for pending comments
        let refreshInterval;
        
        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                // Only refresh if viewing pending comments
                const statusFilter = document.getElementById('status_filter');
                if (statusFilter && statusFilter.value === 'pending') {
                    location.reload();
                }
            }, 30000); // Refresh every 30 seconds
        }
        
        // Start auto-refresh
        startAutoRefresh();

        // Confirm delete actions
        document.querySelectorAll('[onclick*="delete"]').forEach(element => {
            element.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });

        // Quick approve/reject with keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                const selected = getSelectedComments();
                if (selected.length > 0) {
                    switch(e.key) {
                        case 'a':
                            e.preventDefault();
                            bulkApprove();
                            break;
                        case 'r':
                            e.preventDefault();
                            bulkReject();
                            break;
                        case 'd':
                            e.preventDefault();
                            bulkDelete();
                            break;
                    }
                }
            }
        });
    </script>
</body>
</html>

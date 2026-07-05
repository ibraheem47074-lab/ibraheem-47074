<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle article submission for approval
if (isset($_GET['action']) && $_GET['action'] === 'submit' && isset($_GET['id'])) {
    $article_id = (int)$_GET['id'];
    
    // Verify article belongs to current user
    $check_query = "SELECT id FROM news WHERE id = ? AND author_id = ? AND status = 'draft'";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 'ii', $article_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE news SET status = 'pending' WHERE id = ? AND author_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ii', $article_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Article submitted for approval successfully!";
        } else {
            $error = "Failed to submit article for approval!";
        }
    } else {
        $error = "Invalid article or permission denied!";
    }
}

// Handle article deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $article_id = (int)$_GET['id'];
    
    // Verify article belongs to current user and is draft
    $check_query = "SELECT id FROM news WHERE id = ? AND author_id = ? AND status = 'draft'";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, 'ii', $article_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $delete_query = "DELETE FROM news WHERE id = ? AND author_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'ii', $article_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Article deleted successfully!";
        } else {
            $error = "Failed to delete article!";
        }
    } else {
        $error = "Invalid article or permission denied!";
    }
}

// Get articles with filtering and pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtering
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build WHERE clause
$where_conditions = ["author_id = $user_id"];
$params = [];
$types = '';

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total articles count
$count_query = "SELECT COUNT(*) as total FROM news $where_clause";
$stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_articles = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Build WHERE clause for articles query with aliases
$where_conditions_with_alias = ["n.author_id = $user_id"];
if (!empty($filter_status)) {
    $where_conditions_with_alias[] = "n.status = ?";
}
if (!empty($search)) {
    $where_conditions_with_alias[] = "(n.title LIKE ? OR n.content LIKE ?)";
}

$where_clause_with_alias = 'WHERE ' . implode(' AND ', $where_conditions_with_alias);

// Get articles
$articles_query = "SELECT n.*, c.name as category_name 
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  $where_clause_with_alias 
                  ORDER BY n.created_at DESC 
                  LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($conn, $articles_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$articles = mysqli_stmt_get_result($stmt);

// Calculate pagination
$total_pages = ceil($total_articles / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Articles - PK Live News Reporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
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
    </style>
</head>
<body>
    <?php include 'includes/reporter-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-newspaper me-3"></i>My Articles</h2>
                <p class="text-muted">Manage your news articles and track their status.</p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search articles...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo $filter_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                                    <option value="published" <?php echo $filter_status === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                    <a href="my-articles.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo $total_articles; ?> articles</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-list me-2"></i>Articles List</h5>
                                            </div>

                    <?php if (mysqli_num_rows($articles) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($article = mysqli_fetch_assoc($articles)): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 100)) . '...'; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?></span>
                                            </td>
                                            <td>
                                                <span class="article-status status-<?php echo $article['status']; ?>">
                                                    <?php echo ucfirst($article['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $article['views'] ?? 0; ?></td>
                                            <td>
                                                <small><?php echo date('M d, Y', strtotime($article['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" class="btn btn-outline-info btn-sm" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($article['status'] === 'draft' || $article['status'] === 'pending'): ?>
                                                        <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($article['status'] === 'draft'): ?>
                                                        <a href="my-articles.php?action=submit&id=<?php echo $article['id']; ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('Submit this article for approval?')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </a>
                                                        <a href="my-articles.php?action=delete&id=<?php echo $article['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this draft article?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Articles pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5>No articles found</h5>
                            <p class="text-muted">Start creating your first article!</p>
                                                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

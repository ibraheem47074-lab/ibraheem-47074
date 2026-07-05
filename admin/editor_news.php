<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

if ($status !== 'all') {
    $where_conditions[] = "n.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($category !== 'all') {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM news n $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $count_query);
}
$total_articles = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total_articles / $per_page);

// Get articles with pagination
$articles_query = "SELECT n.*, c.name as category_name, u.name as author_name, u.email as author_email
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  LEFT JOIN users u ON n.author_id = u.id 
                  $where_clause
                  ORDER BY n.created_at DESC 
                  LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $articles_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $articles = mysqli_stmt_get_result($stmt);
} else {
    $articles = mysqli_query($conn, $articles_query);
}

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = mysqli_query($conn, $categories_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM news";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

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
    <title>Editor News Management - PK Live News</title>
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
        .article-row {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
            border-left: 4px solid #4834d4;
        }
        .article-row:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-published { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #cce5ff; color: #004085; }
        .status-draft { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .breaking-news-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .priority-high { border-left-color: #dc3545; }
        .priority-medium { border-left-color: #ffc107; }
        .priority-low { border-left-color: #28a745; }
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
                            <a class="nav-link active" href="editor_news.php">
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
                        <h1 class="h3 mb-0">News Management</h1>
                        <small>Review, edit, and manage all news articles</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="add-news.php" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-plus me-1"></i>Add News
                        </a>
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=all" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-primary text-white">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                                <h3><?php echo $stats['total']; ?></h3>
                                <p class="text-muted mb-0">Total Articles</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="?status=published" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3><?php echo $stats['published']; ?></h3>
                                <p class="text-muted mb-0">Published</p>
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
                        <a href="?status=draft" class="text-decoration-none">
                            <div class="stats-card">
                                <div class="stats-icon bg-secondary text-white">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h3><?php echo $stats['drafts']; ?></h3>
                                <p class="text-muted mb-0">Drafts</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search articles...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Articles List -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-newspaper me-2"></i>Articles
                            <span class="badge bg-secondary ms-2"><?php echo $total_articles; ?> total</span>
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <a href="editor_news.php" class="btn btn-outline-secondary <?php echo empty($status) && empty($category) && empty($search) ? 'active' : ''; ?>">
                                All
                            </a>
                            <a href="?status=pending" class="btn btn-outline-warning <?php echo $status === 'pending' ? 'active' : ''; ?>">
                                Pending
                            </a>
                            <a href="?status=published" class="btn btn-outline-success <?php echo $status === 'published' ? 'active' : ''; ?>">
                                Published
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($articles) > 0): ?>
                            <?php while ($article = mysqli_fetch_assoc($articles)): ?>
                                <?php
                                $priority = 'medium';
                                $priority_class = '';
                                $time_ago = time() - strtotime($article['created_at']);
                                if ($article['status'] === 'pending' && $time_ago < 3600) { // Pending less than 1 hour
                                    $priority = 'high';
                                    $priority_class = 'priority-high';
                                } elseif ($article['status'] === 'published' || $article['status'] === 'rejected') {
                                    $priority = 'low';
                                    $priority_class = 'priority-low';
                                }
                                ?>
                                <div class="article-row <?php echo $priority_class; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if ($article['is_breaking']): ?>
                                                    <span class="breaking-news-badge me-2">BREAKING</span>
                                                <?php endif; ?>
                                                <h5 class="mb-0 me-2">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h5>
                                                <span class="status-badge status-<?php echo $article['status']; ?>">
                                                    <?php echo ucfirst($article['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-muted mb-2">
                                                <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) . '...'; ?>
                                            </p>
                                            
                                            <div class="d-flex align-items-center text-muted small">
                                                <span class="me-3">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?>
                                                </span>
                                                <span class="me-3">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                                <span class="me-3">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo number_format($article['views']); ?> views
                                                </span>
                                                <span class="me-3">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo time_ago($article['created_at']); ?>
                                                </span>
                                                <?php if ($article['updated_at'] !== $article['created_at']): ?>
                                                    <span>
                                                        <i class="fas fa-edit me-1"></i>
                                                        Updated <?php echo time_ago($article['updated_at']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="../news.php?slug=<?php echo $article['slug']; ?>" class="btn btn-outline-info" target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($article['status'] === 'pending'): ?>
                                                    <a href="publish-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-success" onclick="return confirm('Publish this article?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="reject-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Reject this article?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                <?php endif; ?>
                                                <a href="delete-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this article?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Article pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>">
                                                    Next
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No articles found</h5>
                                <p class="text-muted">No articles match your current filters.</p>
                                <a href="editor_news.php" class="btn btn-primary">Clear Filters</a>
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

<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

// Handle filters
$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["n.author_id = $reporter_id"];
$params = [];

if ($status !== 'all') {
    if ($status === 'breaking') {
        $where_conditions[] = "n.is_breaking = 1";
    } else {
        $where_conditions[] = "n.status = ?";
        $params[] = $status;
    }
}

if ($category !== 'all') {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_query = "SELECT COUNT(*) as total FROM news n $where_clause";
$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_articles = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_articles / $per_page);

// Get articles
$articles_query = "SELECT n.*, c.name as category_name 
                   FROM news n 
                   LEFT JOIN categories c ON n.category_id = c.id 
                   $where_clause 
                   ORDER BY n.created_at DESC 
                   LIMIT $per_page OFFSET $offset";

$articles_stmt = mysqli_prepare($conn, $articles_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($articles_stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($articles_stmt);
$articles = mysqli_stmt_get_result($articles_stmt);

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = mysqli_query($conn, $categories_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
    SUM(CASE WHEN is_breaking = 1 THEN 1 ELSE 0 END) as breaking,
    SUM(views) as total_views
    FROM news WHERE author_id = $reporter_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Articles - PK Live News</title>
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
        .article-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .article-card:hover {
            transform: translateY(-2px);
        }
        .breaking-news-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 15px;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
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
                            <a class="nav-link active" href="reporter_news.php">
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
                            <a class="nav-link" href="reporter_comments.php">
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
                        <h1 class="h3 mb-0">My Articles</h1>
                        <small>Manage and edit your news articles</small>
                    </div>
                    <div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['total']; ?></h4>
                            <small>Total Articles</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['published']; ?></h4>
                            <small>Published</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo $stats['drafts']; ?></h4>
                            <small>Drafts</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h4><?php echo number_format($stats['total_views'] ?: 0); ?></h4>
                            <small>Total Views</small>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="breaking" <?php echo $status === 'breaking' ? 'selected' : ''; ?>>Breaking News</option>
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
                            <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Articles List -->
                <?php if (mysqli_num_rows($articles) > 0): ?>
                    <?php while ($article = mysqli_fetch_assoc($articles)): ?>
                        <div class="article-card">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start">
                                        <?php if ($article['image']): ?>
                                            <img src="../uploads/news/<?php echo htmlspecialchars($article['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                                 class="me-3" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <div class="me-3 bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 120px; height: 80px; border-radius: 8px;">
                                                <i class="fas fa-newspaper text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="mb-0 me-2">
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h5>
                                                <?php if ($article['is_breaking']): ?>
                                                    <span class="breaking-news-badge">BREAKING</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-muted mb-2">
                                                <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) . '...'; ?>
                                            </p>
                                            
                                            <div class="d-flex align-items-center text-muted small">
                                                <span class="me-3">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                                <span class="me-3">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo number_format($article['views']); ?> views
                                                </span>
                                                <span>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="mb-2">
                                            <?php
                                            $status_class = $article['status'] == 'published' ? 'bg-success' : 
                                                           ($article['status'] == 'draft' ? 'bg-warning' : 'bg-info');
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst($article['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="btn-group">
                                            <a href="edit-news.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="../news.php?slug=<?php echo $article['slug']; ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteArticle(<?php echo $article['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <?php if ($article['status'] === 'draft'): ?>
                                            <button class="btn btn-sm btn-success mt-2" onclick="submitForApproval(<?php echo $article['id']; ?>)">
                                                <i class="fas fa-paper-plane me-1"></i>Submit for Approval
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No articles found</h4>
                        <p class="text-muted">
                            <?php if (!empty($search) || $status !== 'all' || $category !== 'all'): ?>
                                Try adjusting your filters or <a href="reporter_news.php">view all articles</a>
                            <?php else: ?>
                                You haven't written any articles yet.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteArticle(id) {
            if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
                window.location.href = 'delete_news.php?id=' + id;
            }
        }
        
        function submitForApproval(id) {
            if (confirm('Submit this article for editor approval?')) {
                window.location.href = 'submit_approval.php?id=' + id;
            }
        }
    </script>
</body>
</html>

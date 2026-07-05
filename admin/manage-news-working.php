<?php
// PK Live News - Working Manage News Page
require_once '../config/database.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $news_id = $_GET['delete'];
    
    // Add ownership check for editors (admin can delete all)
    if (!is_admin()) {
        $check_query = "SELECT author_id FROM news WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'i', $news_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $news_author = mysqli_fetch_assoc($result);
        
        if ($news_author['author_id'] != $_SESSION['user_id']) {
            $error = "You can only delete your own news articles!";
            header('Location: manage-news-working.php?error=' . urlencode($error));
            exit;
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Get news to delete files
    $news_query = "SELECT image, video_path FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $news_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $news = mysqli_fetch_assoc($result);
    
    // Delete news
    $delete_query = "DELETE FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete image file if exists
        if ($news && $news['image'] && file_exists('../' . $news['image'])) {
            unlink('../' . $news['image']);
        }
        
        // Delete video file if exists
        if ($news && $news['video_path'] && file_exists('../' . $news['video_path'])) {
            unlink('../' . $news['video_path']);
        }
        
        // Delete related comments using prepared statement
        $delete_comments = "DELETE FROM comments WHERE news_id = ?";
        $comments_stmt = mysqli_prepare($conn, $delete_comments);
        mysqli_stmt_bind_param($comments_stmt, 'i', $news_id);
        mysqli_stmt_execute($comments_stmt);
        mysqli_stmt_close($comments_stmt);
        
        $success = "News article deleted successfully!";
    } else {
        $error = "Error deleting news article!";
    }
    mysqli_stmt_close($stmt);
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $news_id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['draft', 'published', 'featured'])) {
        $update_query = "UPDATE news SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $news_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News status updated successfully!";
        } else {
            $error = "Error updating news status!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!is_admin()) {
    // Editors can only see their own articles
    $where_conditions[] = "n.author_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category_filter > 0) {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if (!empty($status_filter)) {
    $where_conditions[] = "n.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
$count_query = "SELECT COUNT(*) as total FROM news n $where_clause";
$count_stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get news articles
$query = "SELECT n.*, c.name as category_name, u.name as author_name 
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          $where_clause 
          ORDER BY n.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
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
            background: white;
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        .news-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .news-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-published { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-featured { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4">PK Live News</h4>
                    <nav class="nav flex-column">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="manage-news-working.php" class="nav-link active">
                            <i class="fas fa-newspaper me-2"></i> Manage News
                        </a>
                        <a href="add-news.php" class="nav-link">
                            <i class="fas fa-plus me-2"></i> Add News
                        </a>
                        <a href="manage-categories.php" class="nav-link">
                            <i class="fas fa-tags me-2"></i> Categories
                        </a>
                        <a href="manage-users.php" class="nav-link">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                        <a href="rss_import.php" class="nav-link">
                            <i class="fas fa-rss me-2"></i> RSS Import
                        </a>
                        <a href="../index.php" class="nav-link">
                            <i class="fas fa-home me-2"></i> View Site
                        </a>
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 admin-main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage News Articles</h2>
                    <div>
                        <a href="add-news.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Article
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
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

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($status_filter == 'published') ? 'selected' : ''; ?>>Published</option>
                                    <option value="featured" <?php echo ($status_filter == 'featured') ? 'selected' : ''; ?>>Featured</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- News Articles -->
                <div class="card">
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="30%">Title</th>
                                            <th width="15%">Category</th>
                                            <th width="10%">Author</th>
                                            <th width="10%">Status</th>
                                            <th width="15%">Date</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($news = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $news['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(substr($news['title'], 0, 50)); ?>...</strong>
                                                    <?php if (!empty($news['image'])): ?>
                                                        <br><small class="text-muted"><i class="fas fa-image me-1"></i> Has image</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($news['category_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($news['author_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $news['status']; ?>">
                                                        <?php echo ucfirst($news['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="../news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($news['status'] != 'published'): ?>
                                                            <a href="?status=published&id=<?php echo $news['id']; ?>" class="btn btn-outline-success">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="?delete=<?php echo $news['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this article?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo urlencode($status_filter); ?>">
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
                                <h5>No news articles found</h5>
                                <p class="text-muted">Start by adding your first news article.</p>
                                <a href="add-news.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Article
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo $total_records; ?></h5>
                                <p class="card-text">Total Articles</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success"><?php echo mysqli_num_rows($result); ?></h5>
                                <p class="card-text">Showing</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info"><?php echo $page; ?></h5>
                                <p class="card-text">Current Page</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?php echo $total_pages; ?></h5>
                                <p class="card-text">Total Pages</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close statements
if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($count_stmt)) mysqli_stmt_close($count_stmt);
if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
?>

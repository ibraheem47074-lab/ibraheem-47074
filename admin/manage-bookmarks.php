<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if bookmarks tables exist
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'");
if (mysqli_num_rows($tables_check) === 0) {
    redirect('../install_bookmarks_simple.php');
}

// Handle bookmark operations
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $bookmark_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM bookmarks WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $bookmark_id);
    mysqli_stmt_execute($stmt);
    redirect('manage-bookmarks.php?deleted=1');
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get filters
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : '';
$folder_filter = isset($_GET['folder']) ? clean_input($_GET['folder']) : '';

// Build query
$where_clause = "WHERE 1=1";
$params = [];
$types = '';

if (!empty($user_filter)) {
    $where_clause .= " AND b.user_id = ?";
    $params[] = $user_filter;
    $types .= 'i';
}

if (!empty($folder_filter)) {
    $where_clause .= " AND b.folder = ?";
    $params[] = $folder_filter;
    $types .= 's';
}

// Get bookmarks with pagination
$query = "SELECT b.*, n.title as news_title, n.slug as news_slug, n.excerpt, n.image as news_image,
          c.name as category_name, u.name as user_name, u.email as user_email
          FROM bookmarks b
          LEFT JOIN news n ON b.news_id = n.id
          LEFT JOIN categories c ON n.category_id = c.id
          LEFT JOIN users u ON b.user_id = u.id
          $where_clause
          ORDER BY b.created_at DESC
          LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM bookmarks b $where_clause";
if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $per_page);

// Get users for filter
$users_query = "SELECT DISTINCT u.id, u.name, u.email, COUNT(b.id) as bookmark_count
                FROM users u
                LEFT JOIN bookmarks b ON u.id = b.user_id
                GROUP BY u.id
                ORDER BY bookmark_count DESC, u.name ASC";
$users_result = mysqli_query($conn, $users_query);

// Get folders for filter
$folders_query = "SELECT folder, COUNT(*) as count
                 FROM bookmarks 
                 WHERE folder IS NOT NULL AND folder != ''
                 GROUP BY folder
                 ORDER BY count DESC, folder ASC";
$folders_result = mysqli_query($conn, $folders_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_bookmarks,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT news_id) as unique_articles,
    COUNT(DISTINCT folder) as total_folders
    FROM bookmarks";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get popular bookmarked articles
$popular_query = "SELECT n.*, COUNT(b.id) as bookmark_count, c.name as category_name
                 FROM news n
                 LEFT JOIN bookmarks b ON n.id = b.news_id
                 LEFT JOIN categories c ON n.category_id = c.id
                 WHERE n.status = 'published'
                 GROUP BY n.id
                 HAVING bookmark_count > 0
                 ORDER BY bookmark_count DESC, n.published_at DESC
                 LIMIT 10";
$popular_articles = mysqli_query($conn, $popular_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookmarks - PK Live News Admin</title>
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
        .bookmark-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .bookmark-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .folder-badge {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
                            <a class="nav-link" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-tags.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage-bookmarks.php">
                                <i class="fas fa-bookmark me-2"></i>Bookmarks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
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
                            <a class="nav-link" href="manage-comments.php">
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
                        <h1 class="h3 mb-0">Manage Bookmarks</h1>
                        <small>Monitor user bookmarks and engagement</small>
                    </div>
                    <div>
                        <button class="btn btn-light" onclick="exportBookmarks()">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Bookmark deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_bookmarks'] ?? 0); ?></h4>
                                        <small>Total Bookmarks</small>
                                    </div>
                                    <i class="fas fa-bookmark fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['unique_users'] ?? 0); ?></h4>
                                        <small>Active Users</small>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['unique_articles'] ?? 0); ?></h4>
                                        <small>Bookmarked Articles</small>
                                    </div>
                                    <i class="fas fa-newspaper fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_folders'] ?? 0); ?></h4>
                                        <small>Folders</small>
                                    </div>
                                    <i class="fas fa-folder fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="user" class="form-label">User</label>
                                <select name="user" id="user" class="form-select">
                                    <option value="">All Users</option>
                                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                                <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?> 
                                            (<?php echo $user['bookmark_count']; ?> bookmarks)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="folder" class="form-label">Folder</label>
                                <select name="folder" id="folder" class="form-select">
                                    <option value="">All Folders</option>
                                    <?php while ($folder = mysqli_fetch_assoc($folders_result)): ?>
                                        <option value="<?php echo htmlspecialchars($folder['folder']); ?>" 
                                                <?php echo $folder_filter === $folder['folder'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($folder['folder']); ?> 
                                            (<?php echo $folder['count']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Apply Filters
                                    </button>
                                    <a href="manage-bookmarks.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <!-- Bookmarks List -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bookmark me-2"></i>Bookmarks</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Article</th>
                                                    <th>Folder</th>
                                                    <th>Notes</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($bookmark = mysqli_fetch_assoc($result)): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="user-avatar me-2">
                                                                    <?php echo strtoupper(substr($bookmark['user_name'], 0, 1)); ?>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold"><?php echo htmlspecialchars($bookmark['user_name']); ?></div>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($bookmark['user_email']); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($bookmark['news_image']): ?>
                                                                    <img src="<?php echo htmlspecialchars($bookmark['news_image']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($bookmark['news_title']); ?>" 
                                                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;" 
                                                                         class="me-3">
                                                                <?php endif; ?>
                                                                <div>
                                                                    <a href="../news.php?slug=<?php echo $bookmark['news_slug']; ?>" 
                                                                       class="text-decoration-none fw-bold" target="_blank">
                                                                        <?php echo htmlspecialchars($bookmark['news_title']); ?>
                                                                    </a>
                                                                    <div>
                                                                        <span class="badge bg-info"><?php echo htmlspecialchars($bookmark['category_name']); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($bookmark['folder']): ?>
                                                                <span class="folder-badge">
                                                                    <i class="fas fa-folder me-1"></i>
                                                                    <?php echo htmlspecialchars($bookmark['folder']); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Default</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($bookmark['notes']): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars(substr($bookmark['notes'], 0, 50)) . '...'; ?></small>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <small><?php echo format_date($bookmark['created_at']); ?></small>
                                                        </td>
                                                        <td>
                                                            <a href="manage-bookmarks.php?delete=<?php echo $bookmark['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this bookmark?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <?php if ($total_pages > 1): ?>
                                        <nav aria-label="Bookmarks pagination" class="mt-4">
                                            <ul class="pagination justify-content-center">
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $user_filter ? '&user=' . $user_filter : ''; ?><?php echo $folder_filter ? '&folder=' . urlencode($folder_filter) : ''; ?>">
                                                            <i class="fas fa-chevron-left"></i> Previous
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php
                                                $start_page = max(1, $page - 2);
                                                $end_page = min($total_pages, $page + 2);
                                                
                                                for ($i = $start_page; $i <= $end_page; $i++):
                                                ?>
                                                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $user_filter ? '&user=' . $user_filter : ''; ?><?php echo $folder_filter ? '&folder=' . urlencode($folder_filter) : ''; ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php if ($page < $total_pages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $user_filter ? '&user=' . $user_filter : ''; ?><?php echo $folder_filter ? '&folder=' . urlencode($folder_filter) : ''; ?>">
                                                            Next <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
                                        <h5>No bookmarks found</h5>
                                        <p class="text-muted">No bookmarks match the current filters.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Bookmarked Articles -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-fire me-2"></i>Most Bookmarked Articles</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($popular_articles) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($popular_articles)): ?>
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if ($article['image']): ?>
                                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;" 
                                                     class="me-3">
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6>
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" 
                                                       class="text-decoration-none" target="_blank">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-bookmark me-1"></i>
                                                        <?php echo $article['bookmark_count']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No bookmarked articles yet.</p>
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
        // Export Bookmarks
        function exportBookmarks() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.open('manage-bookmarks.php?' + params.toString(), '_blank');
        }
    </script>
</body>
</html>

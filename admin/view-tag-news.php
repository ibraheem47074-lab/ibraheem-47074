<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$tag_id = (int)($_GET['id'] ?? 0);

// Get tag information
$tag_query = "SELECT * FROM tags WHERE id = ?";
$stmt = mysqli_prepare($conn, $tag_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tag = mysqli_fetch_assoc($result);

if (!$tag) {
    redirect('manage-tags-complete.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build query
$where_conditions = ["nt.tag_id = ?"];
$params = [$tag_id];
$types = 'i';

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "n.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total records
$count_query = "SELECT COUNT(*) as total FROM news n 
                LEFT JOIN news_tags nt ON n.id = nt.news_id 
                $where_clause";

$stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get news with this tag
$news_query = "SELECT n.*, c.name as category_name, u.name as author_name,
                n.created_at as tagged_at
                FROM news n 
                LEFT JOIN news_tags nt ON n.id = nt.news_id
                LEFT JOIN categories c ON n.category_id = c.id 
                LEFT JOIN users u ON n.author_id = u.id 
                $where_clause
                ORDER BY n.created_at DESC 
                LIMIT $per_page OFFSET $offset";

$stmt = mysqli_prepare($conn, $news_query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$news_result = mysqli_stmt_get_result($stmt);

// Get tag statistics
$stats_query = "SELECT 
                COUNT(nt.news_id) as total_news,
                COUNT(DISTINCT n.author_id) as unique_authors,
                MAX(n.published_at) as last_used,
                AVG(n.views) as avg_views
                FROM news_tags nt
                LEFT JOIN news n ON nt.news_id = n.id
                WHERE nt.tag_id = ?";
$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News for Tag: <?php echo htmlspecialchars($tag['name']); ?> - PK Live News Admin</title>
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
        .news-image-thumb {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .tag-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .tag-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
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
                        <h4><i class="fas fa-tags me-2"></i>PK Live News</h4>
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
                            <a class="nav-link active" href="manage-tags-complete.php">
                                <i class="fas fa-tags me-2"></i>Manage Tags
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
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
                <!-- Tag Header -->
                <div class="tag-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="tag-color" style="background-color: <?php echo htmlspecialchars($tag['color'] ?? '#007bff'); ?>;"></span>
                            <div>
                                <h1 class="h3 mb-0"><?php echo htmlspecialchars($tag['name']); ?></h1>
                                <small class="opacity-75"><?php echo htmlspecialchars($tag['description'] ?? 'No description'); ?></small>
                            </div>
                        </div>
                        <div>
                            <a href="manage-tags-complete.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Tags
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_news'] ?? 0); ?></h3>
                                <p class="mb-0">Total News</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['unique_authors'] ?? 0); ?></h3>
                                <p class="mb-0">Unique Authors</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo round($stats['avg_views'] ?? 0, 0); ?></h3>
                                <p class="mb-0">Avg Views</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo format_date($stats['last_used']); ?></h3>
                                <p class="mb-0">Last Used</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="id" value="<?php echo $tag_id; ?>">
                            <div class="col-md-4">
                                <label class="form-label">Search News</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search news...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All</option>
                                    <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="featured" <?php echo $status_filter === 'featured' ? 'selected' : ''; ?>>Featured</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="edit-tag.php?id=<?php echo $tag_id; ?>" class="btn btn-success w-100">
                                    <i class="fas fa-edit me-2"></i>Edit Tag
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- News List -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-newspaper me-2"></i>News with Tag: <?php echo htmlspecialchars($tag['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($news_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>News</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Views</th>
                                            <th>Status</th>
                                            <th>Tagged Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($news['image']): ?>
                                                            <img src="../<?php echo htmlspecialchars($news['image']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                                                 class="news-image-thumb me-3">
                                                        <?php endif; ?>
                                                        <div>
                                                            <a href="../news.php?slug=<?php echo $news['slug']; ?>" 
                                                               class="text-decoration-none" target="_blank">
                                                                <?php echo htmlspecialchars($news['title']); ?>
                                                            </a>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars(substr($news['excerpt'] ?? '', 0, 100)); ?>...
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($news['author_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($news['category_name']); ?></span>
                                                </td>
                                                <td>
                                                    <?php echo number_format($news['views'] ?? 0); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'published' => 'bg-success',
                                                        'draft' => 'bg-warning',
                                                        'featured' => 'bg-primary'
                                                    ];
                                                    $status_class = $status_class[$news['status']] ?? 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($news['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo format_date($news['tagged_at']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit-news.php?id=<?php echo $news['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="../news.php?slug=<?php echo $news['slug']; ?>" 
                                                           class="btn btn-sm btn-outline-info" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="?id=<?php echo $tag_id; ?>&remove=<?php echo $news['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Remove tag from this news article?')">
                                                            <i class="fas fa-unlink"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h4>No news found</h4>
                                <p class="text-muted">
                                    <?php if (!empty($search)): ?>
                                        No news found matching "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        No news has been tagged with "<?php echo htmlspecialchars($tag['name']); ?>" yet
                                    <?php endif; ?>
                                </p>
                                <a href="add-news.php?tag=<?php echo $tag_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add News with this Tag
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="News pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?php echo $tag_id; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
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
                                    <a class="page-link" href="?id=<?php echo $tag_id; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?id=<?php echo $tag_id; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

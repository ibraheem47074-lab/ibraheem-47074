<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tag_id = $_GET['delete'];
    
    // Get tag to check for related data
    $tag_query = "SELECT * FROM tags WHERE id = ?";
    $stmt = mysqli_prepare($conn, $tag_query);
    mysqli_stmt_bind_param($stmt, 'i', $tag_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tag = mysqli_fetch_assoc($result);
    
    if ($tag) {
        // Delete tag (this will cascade delete news_tags relationships)
        $delete_query = "DELETE FROM tags WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $tag_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Tag '{$tag['name']}' deleted successfully!";
        } else {
            $error = "Error deleting tag!";
        }
    }
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tag_id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['active', 'inactive'])) {
        $update_query = "UPDATE tags SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $tag_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Tag status updated successfully!";
        } else {
            $error = "Error updating tag status!";
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$sort_by = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'usage_count';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(t.name LIKE ? OR t.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "t.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sort options
$sort_clause = '';
switch ($sort_by) {
    case 'name':
        $sort_clause = 'ORDER BY t.name ASC';
        break;
    case 'created':
        $sort_clause = 'ORDER BY t.created_at DESC';
        break;
    case 'usage_count':
    default:
        $sort_clause = 'ORDER BY news_count DESC, t.name ASC';
        break;
}

// Get total records
$count_query = "SELECT COUNT(*) as total FROM tags t $where_clause";
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $count_query);
}
$total_records = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get tags
$tags_query = "SELECT t.*, 
                COUNT(nt.news_id) as news_count,
                MAX(n.published_at) as last_used
                FROM tags t 
                LEFT JOIN news_tags nt ON t.id = nt.tag_id
                LEFT JOIN news n ON nt.news_id = n.id AND n.status = 'published'
                $where_clause
                GROUP BY t.id 
                $sort_clause
                LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $tags_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $tags_result = mysqli_stmt_get_result($stmt);
} else {
    $tags_result = mysqli_query($conn, $tags_query);
}

// Get tag statistics
$stats_query = "SELECT 
                COUNT(*) as total_tags,
                COALESCE(SUM(usage_counts.usage_count), 0) as total_usage,
                COALESCE(AVG(usage_counts.usage_count), 0) as avg_usage
                FROM tags t
                LEFT JOIN (
                    SELECT tag_id, COUNT(*) as usage_count
                    FROM news_tags
                    GROUP BY tag_id
                ) usage_counts ON t.id = usage_counts.tag_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tags - PK Live News Admin</title>
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
        .tag-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .tag-card:hover {
            transform: translateY(-2px);
        }
        .tag-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .usage-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        .usage-fill {
            height: 100%;
            background: #007bff;
            transition: width 0.3s ease;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Manage Tags</h1>
                        <small>Organize and manage content tags</small>
                    </div>
                    <div>
                        <a href="add-tag.php" class="btn btn-light">
                            <i class="fas fa-plus me-2"></i>Add New Tag
                        </a>
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_tags'] ?? 0); ?></h3>
                                <p class="mb-0">Total Tags</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo number_format($stats['total_usage'] ?? 0); ?></h3>
                                <p class="mb-0">Total Usage</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3 class="mb-0"><?php echo round($stats['avg_usage'] ?? 0, 1); ?></h3>
                                <p class="mb-0">Avg Usage</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search tags...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort By</label>
                                <select class="form-select" name="sort">
                                    <option value="usage_count" <?php echo $sort_by === 'usage_count' ? 'selected' : ''; ?>>Usage Count</option>
                                    <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                                    <option value="created" <?php echo $sort_by === 'created' ? 'selected' : ''; ?>>Created Date</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tags List -->
                <div class="row">
                    <?php if (mysqli_num_rows($tags_result) > 0): ?>
                        <?php while ($tag = mysqli_fetch_assoc($tags_result)): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card tag-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <span class="tag-color" style="background-color: #007bff;"></span>
                                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($tag['name'] ?? ''); ?></h5>
                                            </div>
                                            <div>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text text-muted small mb-3">
                                            <?php echo htmlspecialchars(substr($tag['description'] ?? 'No description', 0, 100)); ?>
                                        </p>
                                        
                                        <!-- Usage Statistics -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Usage Count</span>
                                                <strong><?php echo number_format($tag['usage_count'] ?? 0); ?></strong>
                                            </div>
                                            <div class="usage-bar">
                                                <div class="usage-fill" style="width: <?php echo min(100, ($tag['usage_count'] / 10) * 100); ?>%;"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- News Count -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Used in News</span>
                                                <strong><?php echo number_format($tag['news_count'] ?? 0); ?></strong>
                                            </div>
                                        </div>
                                        
                                        <!-- Last Used -->
                                        <?php if ($tag['last_used']): ?>
                                            <div class="text-muted small">
                                                <i class="fas fa-clock me-1"></i>
                                                Last used: <?php echo format_date($tag['last_used']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Action Buttons -->
                                        <div class="d-flex gap-2">
                                            <a href="edit-tag.php?id=<?php echo $tag['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="view-tag-news.php?id=<?php echo $tag['id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-newspaper me-1"></i>View News
                                            </a>
                                            <a href="tag-analytics.php?id=<?php echo $tag['id']; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-chart-line me-1"></i>Analytics
                                            </a>
                                            <?php if (($tag['usage_count'] ?? 0) == 0): ?>
                                                <a href="?delete=<?php echo $tag['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this tag?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <h4>No tags found</h4>
                                <p class="text-muted">
                                    <?php if (!empty($search)): ?>
                                        No tags found matching "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        No tags have been created yet
                                    <?php endif; ?>
                                </p>
                                <a href="add-tag.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create First Tag
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Tags pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">
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
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_by); ?>">
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

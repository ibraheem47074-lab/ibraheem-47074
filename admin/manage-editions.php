<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if news_editions table exists, if not redirect to installation
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    redirect('../install_now.php');
}

// Handle edition deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $edition_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM news_editions WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $edition_id);
    mysqli_stmt_execute($stmt);
    redirect('manage-editions.php?deleted=1');
}

// Handle status changes
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edition_id = (int)$_GET['id'];
    $action = clean_input($_GET['action']);
    
    if ($action === 'publish') {
        $status = 'published';
        $published_at = date('Y-m-d H:i:s');
        $query = "UPDATE news_editions SET status = ?, published_at = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $published_at, $edition_id);
    } elseif ($action === 'archive') {
        $status = 'archived';
        $query = "UPDATE news_editions SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $edition_id);
    }
    
    if (isset($stmt)) {
        mysqli_stmt_execute($stmt);
        redirect('manage-editions.php?updated=1');
    }
}

// Get filter parameters
$edition_filter = isset($_GET['edition_type']) ? clean_input($_GET['edition_type']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($edition_filter)) {
    $where_conditions[] = "ne.edition_type = ?";
    $params[] = $edition_filter;
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "ne.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(n.title LIKE ? OR ne.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get editions with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$query = "SELECT ne.*, 
          GROUP_CONCAT(n.title SEPARATOR ', ') as news_titles,
          GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
          GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') as author_names,
          COUNT(ea.article_id) as article_count
          FROM news_editions ne
          LEFT JOIN edition_articles ea ON ne.id = ea.edition_id
          LEFT JOIN news n ON ea.article_id = n.id
          LEFT JOIN categories c ON n.category_id = c.id
          LEFT JOIN users u ON n.author_id = u.id
          $where_clause
          GROUP BY ne.id
          ORDER BY ne.created_at DESC 
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
$count_query = "SELECT COUNT(DISTINCT ne.id) as total FROM news_editions ne
                LEFT JOIN edition_articles ea ON ne.id = ea.edition_id
                LEFT JOIN news n ON ea.article_id = n.id
                $where_clause";

if (!empty($params)) {
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($conn, $count_query);
}

$total_editions = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_editions / $per_page);

// Get edition types for filter - using status instead since edition_type doesn't exist
$edition_types = mysqli_query($conn, "SELECT DISTINCT status FROM news_editions ORDER BY status");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Editions - PK Live News Admin</title>
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
        .edition-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 0.8em;
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
                            <a class="nav-link active" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
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
                        <h1 class="h3 mb-0">Manage News Editions</h1>
                        <small>Manage different editions of news articles</small>
                    </div>
                    <div>
                        <a href="add-edition.php" class="btn btn-light me-2">
                            <i class="fas fa-plus me-2"></i>Add Edition
                        </a>
                        <a href="edition-templates.php" class="btn btn-light">
                            <i class="fas fa-palette me-2"></i>Templates
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Edition deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Edition updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="edition_type" class="form-label">Edition Type</label>
                                <select class="form-select" id="edition_type" name="edition_type">
                                    <option value="">All Types</option>
                                    <?php while ($type = mysqli_fetch_assoc($edition_types)): ?>
                                        <option value="<?php echo htmlspecialchars($type['status']); ?>" <?php echo $edition_filter === $type['status'] ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(htmlspecialchars($type['status'])); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>><?php echo htmlspecialchars('Draft'); ?></option>
                                    <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>><?php echo htmlspecialchars('Published'); ?></option>
                                    <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>><?php echo htmlspecialchars('Archived'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="Search news title or edition name">
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
                </div>

                <!-- Editions Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Edition Name</th>
                                            <th>News Article</th>
                                            <th>Type</th>
                                            <th>Category</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Published</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($edition = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($edition['title'] ?? 'Untitled Edition'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">Created: <?php echo format_date($edition['created_at']); ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $news_titles = $edition['news_titles'] ?? '';
                                                    if (!empty($news_titles)) {
                                                        $titles_array = explode(', ', $news_titles);
                                                        $first_title = $titles_array[0] ?? 'Untitled News';
                                                        echo htmlspecialchars(substr($first_title, 0, 50)) . (strlen($first_title) > 50 ? '...' : '');
                                                    } else {
                                                        echo 'No articles';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $edition_color = $edition['color'] ?? '#007bff';
                                                    $edition_icon = $edition['icon'] ?? 'fa-layer-group';
                                                    $edition_status = $edition['status'] ?? 'Unknown';
                                                    ?>
                                                    <span class="edition-badge" style="background-color: <?php echo htmlspecialchars($edition_color); ?>; color: white;">
                                                        <i class="fas <?php echo htmlspecialchars($edition_icon); ?> me-1"></i>
                                                        <?php echo ucfirst($edition_status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(!empty($edition['category_names']) ? $edition['category_names'] : 'N/A'); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars(!empty($edition['author_names']) ? $edition['author_names'] : 'N/A'); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = 'bg-secondary';
                                                    switch ($edition['status'] ?? 'unknown') {
                                                        case 'published':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'draft':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'archived':
                                                            $status_class = 'bg-secondary';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="status-badge <?php echo $status_class; ?> text-white">
                                                        <?php echo ucfirst($edition['status'] ?? 'Unknown'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $edition['priority'] ?? 0; ?>
                                                </td>
                                                <td>
                                                    <?php echo !empty($edition['published_at']) ? format_date($edition['published_at']) : 'Not published'; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="edit-edition.php?id=<?php echo $edition['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if (($edition['status'] ?? '') === 'draft'): ?>
                                                            <a href="manage-editions.php?action=publish&id=<?php echo $edition['id']; ?>" class="btn btn-sm btn-outline-success" title="Publish">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (($edition['status'] ?? '') === 'published'): ?>
                                                            <a href="manage-editions.php?action=archive&id=<?php echo $edition['id']; ?>" class="btn btn-sm btn-outline-warning" title="Archive">
                                                                <i class="fas fa-archive"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="manage-editions.php?delete=<?php echo $edition['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" 
                                                           onclick="return confirm('Are you sure you want to delete this edition?')">
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
                                <nav aria-label="Editions pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&edition_type=<?php echo $edition_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                                <h5>No editions found</h5>
                                <p class="text-muted">No news editions match your criteria.</p>
                                <a href="add-edition.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add First Edition
                                </a>
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

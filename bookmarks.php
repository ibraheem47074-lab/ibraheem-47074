<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle bookmark actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_folder') {
        $folder_name = clean_input($_POST['folder_name']);
        
        if (empty($folder_name)) {
            $error = "Folder name is required";
        } else {
            // Check if bookmark_folders table exists
            $bookmark_folders_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmark_folders'")->num_rows > 0;
            
            if (!$bookmark_folders_table_exists) {
                $error = "Bookmark folders feature is not available";
            } else {
                $query = "INSERT INTO bookmark_folders (user_id, name) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'is', $user_id, $folder_name);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Folder created successfully!";
                } else {
                    $error = "Error creating folder: " . mysqli_error($conn);
                }
            }
        }
    } elseif ($action === 'move_bookmark') {
        $bookmark_id = (int)$_POST['bookmark_id'];
        $folder_id = (int)$_POST['folder_id'];
        
        // Check if bookmarks table exists
        $bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;
        
        if (!$bookmarks_table_exists) {
            $error = "Bookmarks feature is not available";
        } else {
            $query = "UPDATE bookmarks SET folder_id = ? WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'iii', $folder_id, $bookmark_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Bookmark moved successfully!";
            } else {
                $error = "Error moving bookmark: " . mysqli_error($conn);
            }
        }
    } elseif ($action === 'delete_bookmark') {
        $bookmark_id = (int)$_POST['bookmark_id'];
        
        // Check if bookmarks table exists
        $bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;
        
        if (!$bookmarks_table_exists) {
            $error = "Bookmarks feature is not available";
        } else {
            $query = "DELETE FROM bookmarks WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ii', $bookmark_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Bookmark deleted successfully!";
            } else {
                $error = "Error deleting bookmark: " . mysqli_error($conn);
            }
        }
    } elseif ($action === 'delete_folder') {
        $folder_id = (int)$_POST['folder_id'];
        
        // Move bookmarks to uncategorized first
        $query = "UPDATE bookmarks SET folder_id = NULL WHERE folder_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $folder_id, $user_id);
        mysqli_stmt_execute($stmt);
        
        // Then delete the folder
        $query = "DELETE FROM bookmark_folders WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $folder_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Folder deleted successfully!";
        } else {
            $error = "Error deleting folder: " . mysqli_error($conn);
        }
    }
}

// Get filter criteria
$category_filter = $_GET['category'] ?? '';
$date_filter = $_GET['date'] ?? '';
$folder_filter = $_GET['folder'] ?? '';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date_desc';

// Build WHERE clause
$where_conditions = ["b.user_id = ?"];
$params = [$user_id];
$types = "i";

if ($category_filter) {
    $where_conditions[] = "n.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(b.created_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $where_conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
            break;
    }
}

if ($folder_filter) {
    if ($folder_filter === 'uncategorized') {
        $where_conditions[] = "b.folder_id IS NULL";
    } else {
        $where_conditions[] = "b.folder_id = ?";
        $params[] = $folder_filter;
        $types .= "i";
    }
}

if ($search_query) {
    $where_conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Build ORDER BY clause
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'date_asc':
        $order_by .= "b.created_at ASC";
        break;
    case 'title_asc':
        $order_by .= "n.title ASC";
        break;
    case 'title_desc':
        $order_by .= "n.title DESC";
        break;
    default:
        $order_by .= "b.created_at DESC";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get bookmarks with pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Check if tables exist before querying
$bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;
$bookmark_folders_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmark_folders'")->num_rows > 0;

if (!$bookmarks_table_exists) {
    $bookmarks = [];
    $total_results = 0;
    $total_pages = 0;
} else {
    $count_query = "SELECT COUNT(*) as total FROM bookmarks b 
                    LEFT JOIN news n ON b.news_id = n.id 
                    $where_clause";
    $count_stmt = mysqli_prepare($conn, $count_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($count_stmt);
    $total_results = mysqli_stmt_get_result($count_stmt)->fetch_assoc()['total'];
    $total_pages = ceil($total_results / $per_page);

    $bookmarks_query = "SELECT b.*, n.title, n.slug, n.image, n.excerpt, n.published_at, 
                               c.name as category_name, c.slug as category_slug," . 
                               ($bookmark_folders_table_exists ? "bf.name as folder_name" : "NULL as folder_name") . "
                    FROM bookmarks b 
                    LEFT JOIN news n ON b.news_id = n.id 
                    LEFT JOIN categories c ON n.category_id = c.id" . 
                    ($bookmark_folders_table_exists ? "LEFT JOIN bookmark_folders bf ON b.folder_id = bf.id" : "") . "
                    $where_clause 
                    $order_by 
                    LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";

    $bookmarks_stmt = mysqli_prepare($conn, $bookmarks_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($bookmarks_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($bookmarks_stmt);
    $bookmarks_result = mysqli_stmt_get_result($bookmarks_stmt);

    $bookmarks = [];
    while ($row = mysqli_fetch_assoc($bookmarks_result)) {
        $bookmarks[] = $row;
    }
}

// Get folders
$folders = [];
if ($bookmark_folders_table_exists) {
    $folders_query = "SELECT * FROM bookmark_folders WHERE user_id = ? ORDER BY name ASC";
    $folders_stmt = mysqli_prepare($conn, $folders_query);
    mysqli_stmt_bind_param($folders_stmt, 'i', $user_id);
    mysqli_stmt_execute($folders_stmt);
    $folders_result = mysqli_stmt_get_result($folders_stmt);

    while ($row = mysqli_fetch_assoc($folders_result)) {
        $folders[] = $row;
    }
}

// Get categories
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookmarks - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .bookmarks-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .bookmark-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .bookmark-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .bookmark-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .bookmark-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .bookmark-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .folder-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .criteria-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .criteria-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
        }
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
            border-radius: 10px;
            padding: 8px 20px;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        .pagination .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: none;
            color: #667eea;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <!-- Header -->
        <div class="bookmarks-header text-center">
            <h1><i class="fas fa-bookmark me-3"></i>My Bookmarks</h1>
            <p class="mb-0">Save and organize your favorite articles</p>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo number_format($total_results); ?></div>
                    <div>Total Bookmarks</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo mysqli_num_rows($folders_result); ?></div>
                    <div>Folders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php 
                        $week_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = $user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
                        echo number_format($week_count);
                    ?></div>
                    <div>This Week</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php 
                        $month_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = $user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
                        echo number_format($month_count);
                    ?></div>
                    <div>This Month</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h4 class="mb-4">
                <i class="fas fa-filter me-2"></i>Filter & Sort Bookmarks
            </h4>
            
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Search bookmarks...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Date Range</label>
                    <select class="form-select" name="date">
                        <option value="">All Time</option>
                        <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter == 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $date_filter == 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="year" <?php echo $date_filter == 'year' ? 'selected' : ''; ?>>This Year</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Folder</label>
                    <select class="form-select" name="folder">
                        <option value="">All Folders</option>
                        <option value="uncategorized" <?php echo $folder_filter == 'uncategorized' ? 'selected' : ''; ?>>Uncategorized</option>
                        <?php 
                        mysqli_data_seek($folders_result, 0);
                        while ($folder = mysqli_fetch_assoc($folders_result)): 
                        ?>
                            <option value="<?php echo $folder['id']; ?>" 
                                    <?php echo $folder_filter == $folder['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($folder['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="date_desc" <?php echo $sort_by == 'date_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="date_asc" <?php echo $sort_by == 'date_asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title_asc" <?php echo $sort_by == 'title_asc' ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="title_desc" <?php echo $sort_by == 'title_desc' ? 'selected' : ''; ?>>Title Z-A</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Bookmarks Criteria Section -->
        <div class="criteria-section">
            <h4 class="mb-4">
                <i class="fas fa-list-check me-2"></i>Bookmark Criteria & Management
            </h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="criteria-card">
                        <h6><i class="fas fa-folder-plus me-2"></i>Create New Folder</h6>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="action" value="add_folder">
                            <input type="text" class="form-control" name="folder_name" 
                                   placeholder="Enter folder name" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create
                            </button>
                        </form>
                    </div>
                    
                    <div class="criteria-card">
                        <h6><i class="fas fa-cog me-2"></i>Auto-Bookmark Settings</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="auto_bookmark_read" name="auto_bookmark_read">
                            <label class="form-check-label" for="auto_bookmark_read">
                                Auto-bookmark articles I read
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="auto_bookmark_liked" name="auto_bookmark_liked">
                            <label class="form-check-label" for="auto_bookmark_liked">
                                Auto-bookmark articles I like
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_bookmark_commented" name="auto_bookmark_commented">
                            <label class="form-check-label" for="auto_bookmark_commented">
                                Auto-bookmark articles I comment on
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="criteria-card">
                        <h6><i class="fas fa-bell me-2"></i>Bookmark Notifications</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_bookmark_reminder" name="notify_bookmark_reminder">
                            <label class="form-check-label" for="notify_bookmark_reminder">
                                Weekly bookmark reminders
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="notify_bookmark_full" name="notify_bookmark_full">
                            <label class="form-check-label" for="notify_bookmark_full">
                                Alert when bookmark limit reached
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="notify_bookmark_digest" name="notify_bookmark_digest">
                            <label class="form-check-label" for="notify_bookmark_digest">
                                Daily bookmark digest
                            </label>
                        </div>
                    </div>
                    
                    <div class="criteria-card">
                        <h6><i class="fas fa-share-alt me-2"></i>Sharing Settings</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="public_bookmarks" name="public_bookmarks">
                            <label class="form-check-label" for="public_bookmarks">
                                Make my bookmarks public
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="share_bookmarks" name="share_bookmarks">
                            <label class="form-check-label" for="share_bookmarks">
                                Allow others to see my bookmark folders
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" onclick="saveBookmarkCriteria()">
                    <i class="fas fa-save me-2"></i>Save Criteria Settings
                </button>
                <button class="btn btn-outline-primary" onclick="exportBookmarks()">
                    <i class="fas fa-download me-2"></i>Export Bookmarks
                </button>
                <button class="btn btn-outline-danger" onclick="clearOldBookmarks()">
                    <i class="fas fa-trash me-2"></i>Clear Old Bookmarks
                </button>
            </div>
        </div>

        <!-- Bookmarks Grid -->
        <?php if (mysqli_num_rows($bookmarks_result) > 0): ?>
            <div class="row">
                <?php while ($bookmark = mysqli_fetch_assoc($bookmarks_result)): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="bookmark-card">
                            <?php if (!empty($bookmark['image'])): ?>
                                <img src="<?php echo htmlspecialchars($bookmark['image']); ?>" 
                                     class="bookmark-image" alt="<?php echo htmlspecialchars($bookmark['title']); ?>">
                            <?php endif; ?>
                            
                            <h5 class="bookmark-title">
                                <a href="news.php?slug=<?php echo $bookmark['slug']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($bookmark['title']); ?>
                                </a>
                            </h5>
                            
                            <div class="bookmark-meta">
                                <i class="fas fa-folder me-1"></i>
                                <?php if ($bookmark['folder_name']): ?>
                                    <span class="folder-badge"><?php echo htmlspecialchars($bookmark['folder_name']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Uncategorized</span>
                                <?php endif; ?>
                                
                                <br>
                                <i class="fas fa-tag me-1"></i>
                                <?php if ($bookmark['category_name']): ?>
                                    <?php echo htmlspecialchars($bookmark['category_name']); ?>
                                <?php endif; ?>
                                
                                <br>
                                <i class="fas fa-clock me-1"></i>
                                Bookmarked on <?php echo date('M j, Y', strtotime($bookmark['created_at'])); ?>
                            </div>
                            
                            <?php if (!empty($bookmark['excerpt'])): ?>
                                <p class="text-muted small mb-3">
                                    <?php echo substr(htmlspecialchars($bookmark['excerpt']), 0, 150) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="moveBookmark(<?php echo $bookmark['id']; ?>)">
                                    <i class="fas fa-folder-open"></i> Move
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteBookmark(<?php echo $bookmark['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Bookmark pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_filter; ?>&date=<?php echo $date_filter; ?>&folder=<?php echo $folder_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&date=<?php echo $date_filter; ?>&folder=<?php echo $folder_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_filter; ?>&date=<?php echo $date_filter; ?>&folder=<?php echo $folder_filter; ?>&search=<?php echo urlencode($search_query); ?>&sort=<?php echo $sort_by; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bookmark"></i>
                <h4>No bookmarks found</h4>
                <p>Start bookmarking your favorite articles to see them here!</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-newspaper me-2"></i>Browse Articles
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Move bookmark to folder
        function moveBookmark(bookmarkId) {
            const folders = <?php 
                $folders_json = [];
                mysqli_data_seek($folders_result, 0);
                while ($folder = mysqli_fetch_assoc($folders_result)) {
                    $folders_json[] = [
                        'id' => $folder['id'],
                        'name' => $folder['name']
                    ];
                }
                echo json_encode($folders_json);
            ?>;
            
            let folderOptions = '<option value="">Uncategorized</option>';
            folders.forEach(folder => {
                folderOptions += `<option value="${folder.id}">${folder.name}</option>`;
            });
            
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Move Bookmark</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="moveBookmarkForm">
                                <input type="hidden" name="action" value="move_bookmark">
                                <input type="hidden" name="bookmark_id" value="${bookmarkId}">
                                <div class="mb-3">
                                    <label class="form-label">Select Folder</label>
                                    <select class="form-select" name="folder_id" required>
                                        ${folderOptions}
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="submitMoveBookmark()">Move</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        }
        
        // Submit move bookmark form
        function submitMoveBookmark() {
            const form = document.getElementById('moveBookmarkForm');
            const formData = new FormData(form);
            
            fetch('bookmarks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error moving bookmark');
            });
        }
        
        // Delete bookmark
        function deleteBookmark(bookmarkId) {
            if (confirm('Are you sure you want to delete this bookmark?')) {
                const formData = new FormData();
                formData.append('action', 'delete_bookmark');
                formData.append('bookmark_id', bookmarkId);
                
                fetch('bookmarks.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting bookmark');
                });
            }
        }
        
        // Save bookmark criteria
        function saveBookmarkCriteria() {
            const criteria = {
                auto_bookmark_read: document.getElementById('auto_bookmark_read').checked,
                auto_bookmark_liked: document.getElementById('auto_bookmark_liked').checked,
                auto_bookmark_commented: document.getElementById('auto_bookmark_commented').checked,
                notify_bookmark_reminder: document.getElementById('notify_bookmark_reminder').checked,
                notify_bookmark_full: document.getElementById('notify_bookmark_full').checked,
                notify_bookmark_digest: document.getElementById('notify_bookmark_digest').checked,
                public_bookmarks: document.getElementById('public_bookmarks').checked,
                share_bookmarks: document.getElementById('share_bookmarks').checked
            };
            
            fetch('api/update_bookmark_criteria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(criteria)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bookmark criteria updated successfully!');
                } else {
                    alert('Error updating criteria: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating criteria. Please try again.');
            });
        }
        
        // Export bookmarks
        function exportBookmarks() {
            window.open('api/export_bookmarks.php', '_blank');
        }
        
        // Clear old bookmarks
        function clearOldBookmarks() {
            if (confirm('Are you sure you want to clear bookmarks older than 6 months? This action cannot be undone.')) {
                fetch('api/clear_old_bookmarks.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Cleared ${data.count} old bookmarks successfully!`);
                        location.reload();
                    } else {
                        alert('Error clearing bookmarks: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error clearing bookmarks. Please try again.');
                });
            }
        }
    </script>
</body>
</html>

<?php
require_once '../config/database.php';
require_once '../includes/web_scraper.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle add RSS source
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_source'])) {
    $name = clean_input($_POST['name']);
    $rss_url = clean_input($_POST['rss_url']);
    $website_url = clean_input($_POST['website_url']);
    $category_id = (int)$_POST['category_id'];
    $scrape_frequency = (int)$_POST['scrape_frequency'];
    $source_type = clean_input($_POST['source_type']);
    
    if (empty($name) || empty($rss_url)) {
        $error = 'Source name and RSS feed URL are required fields';
    } elseif (!filter_var($rss_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid RSS feed URL';
    } elseif (!filter_var($website_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid website URL';
    } else {
        // Test RSS feed before adding
        $rss_test = @simplexml_load_file($rss_url);
        if ($rss_test === false) {
            $error = 'RSS feed URL is not accessible or invalid. Please check the URL.';
        } else {
            $insert = "INSERT INTO news_sources (name, url, rss_url, type, category_id, scrape_frequency, status) 
                      VALUES (?, ?, ?, ?, ?, ?, 'active')";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, 'ssssii', $name, $website_url, $rss_url, $source_type, $category_id, $scrape_frequency);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "RSS source added successfully! Feed validation passed.";
            } else {
                $error = "Error adding RSS source: " . mysqli_error($conn);
            }
        }
    }
}

// Handle bulk add sources
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_add_sources'])) {
    $sources_text = $_POST['sources_text'];
    $category_id = (int)$_POST['category_id'];
    
    if (empty($sources_text)) {
        $error = 'Please provide sources data';
    } else {
        $lines = explode("\n", trim($sources_text));
        $added_count = 0;
        $error_count = 0;
        
        foreach ($lines as $line) {
            $parts = explode("|", trim($line));
            if (count($parts) >= 2) {
                $name = trim($parts[0]);
                $rss_url = trim($parts[1]);
                $website_url = isset($parts[2]) ? trim($parts[2]) : $rss_url;
                
                if (!empty($name) && !empty($rss_url)) {
                    // Check if already exists
                    $check = "SELECT id FROM news_sources WHERE name = ? OR rss_url = ?";
                    $stmt = mysqli_prepare($conn, $check);
                    mysqli_stmt_bind_param($stmt, 'ss', $name, $rss_url);
                    mysqli_stmt_execute($stmt);
                    
                    if (mysqli_stmt_get_result($stmt)->num_rows == 0) {
                        $insert = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) 
                                  VALUES (?, ?, ?, 'rss', ?, 'active')";
                        $stmt = mysqli_prepare($conn, $insert);
                        mysqli_stmt_bind_param($stmt, 'sssi', $name, $website_url, $rss_url, $category_id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $added_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
            }
        }
        
        if ($added_count > 0) {
            $success = "Successfully added $added_count sources" . ($error_count > 0 ? " ($error_count failed)" : "");
        } else {
            $error = "No sources were added. Please check the format.";
        }
    }
}

// Handle delete RSS source
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $source_id = $_GET['delete'];
    
    // Check if source has imported news
    $check_news = "SELECT COUNT(*) as count FROM news WHERE source_url IN (SELECT rss_url FROM news_sources WHERE id = ?)";
    $stmt = mysqli_prepare($conn, $check_news);
    mysqli_stmt_bind_param($stmt, 'i', $source_id);
    mysqli_stmt_execute($stmt);
    $news_count = mysqli_stmt_get_result($stmt)->fetch_assoc()['count'];
    
    if ($news_count > 0) {
        $error = "Cannot delete source - it has imported $news_count news articles. Deactivate it instead.";
    } else {
        $delete = "DELETE FROM news_sources WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, 'i', $source_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "RSS source deleted successfully!";
        } else {
            $error = "Error deleting RSS source!";
        }
    }
}

// Handle toggle RSS source status
if (isset($_GET['toggle']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $source_id = $_GET['id'];
    $new_status = $_GET['toggle'] === 'activate' ? 'active' : 'inactive';
    
    $update = "UPDATE news_sources SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $source_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "RSS source status updated!";
    } else {
        $error = "Error updating RSS source status!";
    }
}

// Handle test RSS source
if (isset($_GET['test']) && is_numeric($_GET['test'])) {
    $source_id = $_GET['test'];
    
    $query = "SELECT name, rss_url FROM news_sources WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $source_id);
    mysqli_stmt_execute($stmt);
    $source = mysqli_stmt_get_result($stmt)->fetch_assoc();
    
    if ($source) {
        $rss_test = @simplexml_load_file($source['rss_url']);
        if ($rss_test !== false) {
            $item_count = count($rss_test->channel->item ?? $rss_test->entry ?? []);
            $success = "RSS feed test successful! Found $item_count items in {$source['name']}.";
        } else {
            $error = "RSS feed test failed for {$source['name']}. Feed is not accessible.";
        }
    }
}

// Handle bulk operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selected_sources = $_POST['selected_sources'] ?? [];
    $action = $_POST['bulk_action'];
    
    if (empty($selected_sources)) {
        $error = "Please select sources to perform bulk action.";
    } else {
        $processed = 0;
        
        foreach ($selected_sources as $source_id) {
            if ($action === 'activate') {
                $update = "UPDATE news_sources SET status = 'active' WHERE id = ?";
            } elseif ($action === 'deactivate') {
                $update = "UPDATE news_sources SET status = 'inactive' WHERE id = ?";
            } elseif ($action === 'delete') {
                $update = "DELETE FROM news_sources WHERE id = ?";
            }
            
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, 'i', $source_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $processed++;
            }
        }
        
        $success = "Bulk action completed successfully! Processed $processed sources.";
    }
}

// Handle import all sources
if (isset($_GET['import_all'])) {
    $success = "Import started! This may take a few moments...";
    // You can redirect to the import script here
    header("refresh:2;url=../cron_import_news.php?cron_key=pk_live_news_2024_cron");
}

// Check if news_sources table exists
$table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'")) > 0;

// Get RSS sources (only if table exists)
$sources_result = null;
if ($table_exists) {
    $sources_query = "SELECT ns.*, c.name as category_name,
                     (SELECT COUNT(*) FROM news WHERE source_url COLLATE utf8mb4_unicode_ci = ns.rss_url COLLATE utf8mb4_unicode_ci) as imported_count,
                     (SELECT MAX(created_at) FROM news WHERE source_url COLLATE utf8mb4_unicode_ci = ns.rss_url COLLATE utf8mb4_unicode_ci) as last_import_date
                     FROM news_sources ns 
                     LEFT JOIN categories c ON ns.category_id = c.id 
                     WHERE ns.type = 'rss' OR ns.type IS NULL
                     ORDER BY ns.created_at DESC";
    $sources_result = mysqli_query($conn, $sources_query);
}

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");

// Get statistics
$stats = [];
if ($table_exists) {
    $stats['total'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' OR type IS NULL"))['count'];
    $stats['active'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE (type = 'rss' OR type IS NULL) AND status = 'active'"))['count'];
    $stats['inactive'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE (type = 'rss' OR type IS NULL) AND status = 'inactive'"))['count'];
    $stats['imported_today'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE DATE(created_at) = CURDATE()"))['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News Sources - PK Live News Admin</title>
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
        .source-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active { background-color: #28a745; }
        .status-inactive { background-color: #dc3545; }
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
                            <a class="nav-link active" href="manage-sources.php">
                                <i class="fas fa-rss me-2"></i>RSS Sources
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
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
                        <h1 class="h3 mb-0">RSS News Sources Management</h1>
                        <small>Configure and manage RSS feeds for automatic news import</small>
                    </div>
                    <div>
                        <a href="../setup_rss_sources.php" class="btn btn-success me-2" target="_blank">
                            <i class="fas fa-magic me-2"></i>Setup All Sources
                        </a>
                        <a href="../cron_import_news.php?cron_key=pk_live_news_2024_cron" class="btn btn-warning me-2" target="_blank">
                            <i class="fas fa-download me-2"></i>Import All News
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSourceModal">
                            <i class="fas fa-plus me-2"></i>Add RSS Source
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['total'] ?? 0; ?></h4>
                                        <p class="card-text">Total Sources</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-rss fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['active'] ?? 0; ?></h4>
                                        <p class="card-text">Active Sources</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['inactive'] ?? 0; ?></h4>
                                        <p class="card-text">Inactive Sources</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title"><?php echo $stats['imported_today'] ?? 0; ?></h4>
                                        <p class="card-text">Imported Today</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-newspaper fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
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

                <!-- RSS Sources Table -->
                <?php if ($table_exists): ?>
                    <!-- Bulk Actions Bar -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkAddModal">
                                            <i class="fas fa-upload me-2"></i>Bulk Add Sources
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="window.location.href='?import_all'">
                                            <i class="fas fa-download me-2"></i>Import All Sources
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary" onclick="selectAllSources()">
                                            <i class="fas fa-check-square me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="deselectAllSources()">
                                            <i class="fas fa-square me-1"></i>Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="bulkActionForm">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAllSources()">
                                                </th>
                                                <th>Source Name</th>
                                                <th>RSS Feed URL</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Imported Articles</th>
                                                <th>Last Import</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($sources_result && mysqli_num_rows($sources_result) > 0): ?>
                                                <?php while ($source = mysqli_fetch_assoc($sources_result)): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input source-checkbox" name="selected_sources[]" value="<?php echo $source['id']; ?>">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="source-status status-<?php echo $source['status']; ?>"></span>
                                                            <strong><?php echo htmlspecialchars($source['name']); ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo htmlspecialchars($source['rss_url'] ?? $source['url']); ?>" target="_blank" class="text-decoration-none">
                                                            <?php echo htmlspecialchars(substr($source['rss_url'] ?? $source['url'], 0, 50)) . '...'; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($source['category_name'] ?? 'Uncategorized'); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $source['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($source['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo number_format($source['imported_count'] ?? 0); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if ($source['last_import_date']) {
                                                            echo date('M d, H:i', strtotime($source['last_import_date']));
                                                        } else {
                                                            echo '<span class="text-muted">Never</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="?test=<?php echo $source['id']; ?>" class="btn btn-outline-primary" title="Test RSS Feed">
                                                                <i class="fas fa-plug"></i>
                                                            </a>
                                                            <?php if ($source['status'] == 'active'): ?>
                                                                <a href="?toggle=deactivate&id=<?php echo $source['id']; ?>" class="btn btn-outline-warning" title="Deactivate">
                                                                    <i class="fas fa-pause"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="?toggle=activate&id=<?php echo $source['id']; ?>" class="btn btn-outline-success" title="Activate">
                                                                    <i class="fas fa-play"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="?delete=<?php echo $source['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this source?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        <div class="py-4">
                                                            <i class="fas fa-rss fa-3x text-muted mb-3"></i>
                                                            <h5 class="text-muted">No RSS sources found</h5>
                                                            <p class="text-muted">Add RSS sources to start importing news automatically.</p>
                                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSourceModal">
                                                                <i class="fas fa-plus me-2"></i>Add Your First RSS Source
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Bulk Action Controls -->
                                <div class="row mt-3" id="bulkActionControls" style="display: none;">
                                    <div class="col-md-6">
                                        <select name="bulk_action" class="form-select" required>
                                            <option value="">Select bulk action...</option>
                                            <option value="activate">Activate Selected</option>
                                            <option value="deactivate">Deactivate Selected</option>
                                            <option value="delete">Delete Selected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="bulk_action_submit" class="btn btn-primary">
                                            <i class="fas fa-play me-2"></i>Execute Bulk Action
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="deselectAllSources()">
                                            <i class="fas fa-times me-2"></i>Clear Selection
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Installation Required -->
                    <div class="card border-warning">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-rss fa-3x text-warning mb-3"></i>
                            <h4 class="text-warning">RSS Sources Table Not Found</h4>
                            <p class="text-muted mb-4">
                                The RSS sources table needs to be installed before you can manage RSS feeds.
                                This table is required for the RSS news import functionality.
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="../rss_feed_fixer.php" class="btn btn-warning" target="_blank">
                                    <i class="fas fa-tools me-2"></i>
                                    Setup RSS Feeds
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add Source Modal -->
    <div class="modal fade" id="addSourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add RSS Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_source" value="1">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Source Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="BBC News, CNN, etc.">
                        </div>
                        
                        <div class="mb-3">
                            <label for="website_url" class="form-label">Website URL *</label>
                            <input type="url" class="form-control" id="website_url" name="website_url" required
                                   placeholder="https://example.com">
                            <small class="text-muted">Main website URL for the news source</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rss_url" class="form-label">RSS Feed URL *</label>
                            <input type="url" class="form-control" id="rss_url" name="rss_url" required
                                   placeholder="https://example.com/rss.xml">
                            <small class="text-muted">Direct RSS feed URL for automatic news import</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="scrape_frequency" class="form-label">Scrape Frequency (minutes)</label>
                                    <select class="form-select" id="scrape_frequency" name="scrape_frequency">
                                        <option value="15">15 minutes</option>
                                        <option value="30" selected>30 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="120">2 hours</option>
                                        <option value="240">4 hours</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="source_type" class="form-label">Source Type</label>
                            <select class="form-select" id="source_type" name="source_type">
                                <option value="rss" selected>RSS Feed</option>
                                <option value="scrape">Web Scraping</option>
                            </select>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add RSS Source
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Add Sources Modal -->
    <div class="modal fade" id="bulkAddModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>Bulk Add RSS Sources
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="bulk_add_sources" value="1">
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Default Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                // Reset categories pointer
                                mysqli_data_seek($categories, 0);
                                while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sources_text" class="form-label">Sources Data</label>
                            <textarea class="form-control" id="sources_text" name="sources_text" rows="10" required
                                      placeholder="Enter sources in the following format (one per line):&#10;Source Name | RSS Feed URL | Website URL&#10;&#10;Example:&#10;BBC News | https://feeds.bbci.co.uk/news/rss.xml | https://www.bbc.com/news&#10;CNN | https://rss.cnn.com/rss/edition.rss | https://www.cnn.com"></textarea>
                            <small class="text-muted">
                                Format: <code>Source Name | RSS Feed URL | Website URL</code><br>
                                Website URL is optional. If not provided, RSS URL will be used.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Quick Tips:</h6>
                            <ul class="mb-0">
                                <li>Each source should be on a new line</li>
                                <li>Use the pipe character (|) to separate fields</li>
                                <li>Website URL is optional - RSS URL will be used if not provided</li>
                                <li>Duplicate sources will be automatically skipped</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Add Sources
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bulk selection functions
        function selectAllSources() {
            const checkboxes = document.querySelectorAll('.source-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            selectAllCheckbox.checked = true;
            showBulkActionControls();
        }
        
        function deselectAllSources() {
            const checkboxes = document.querySelectorAll('.source-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
            hideBulkActionControls();
        }
        
        function toggleAllSources() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.source-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            if (selectAllCheckbox.checked) {
                showBulkActionControls();
            } else {
                hideBulkActionControls();
            }
        }
        
        function showBulkActionControls() {
            const controls = document.getElementById('bulkActionControls');
            if (controls) {
                controls.style.display = 'block';
            }
        }
        
        function hideBulkActionControls() {
            const controls = document.getElementById('bulkActionControls');
            if (controls) {
                controls.style.display = 'none';
            }
        }
        
        // Monitor checkbox changes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.source-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedBoxes = document.querySelectorAll('.source-checkbox:checked');
                    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                    
                    if (checkedBoxes.length > 0) {
                        showBulkActionControls();
                    } else {
                        hideBulkActionControls();
                    }
                    
                    // Update select all checkbox state
                    const totalCheckboxes = document.querySelectorAll('.source-checkbox').length;
                    selectAllCheckbox.checked = checkedBoxes.length === totalCheckboxes;
                    selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalCheckboxes;
                });
            });
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>

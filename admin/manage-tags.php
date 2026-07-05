<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if tags table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tags'");
if (mysqli_num_rows($table_check) === 0) {
    redirect('../install_tags_simple.php');
}

// Handle tag operations
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tag_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM tags WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $tag_id);
    mysqli_stmt_execute($stmt);
    redirect('manage-tags.php?deleted=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $name = clean_input($_POST['name']);
    $slug = slugify($name);
    
    if (!empty($name)) {
        $query = "INSERT INTO tags (name, slug) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        mysqli_stmt_execute($stmt);
        redirect('manage-tags.php?added=1');
    }
}

// Get tags with usage statistics
$tags_query = "SELECT t.*, COUNT(nt.news_id) as usage_count 
               FROM tags t 
               LEFT JOIN news_tags nt ON t.id = nt.tag_id 
               GROUP BY t.id 
               ORDER BY usage_count DESC, t.name ASC";
$tags_result = mysqli_query($conn, $tags_query);

// Get trending tags (last 7 days)
$trending_query = "SELECT t.*, COUNT(nt.news_id) as recent_mentions 
                 FROM tags t 
                 LEFT JOIN news_tags nt ON t.id = nt.tag_id 
                 LEFT JOIN news n ON nt.news_id = n.id 
                 WHERE n.status = 'published' 
                   AND n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 GROUP BY t.id 
                 HAVING recent_mentions > 0
                 ORDER BY recent_mentions DESC, t.name ASC 
                 LIMIT 10";
$trending_result = mysqli_query($conn, $trending_query);
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
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .tag-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .tag-color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }
        .trending-badge {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
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
                            <a class="nav-link" href="live-stream.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Stream
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sentiment-dashboard.php">
                                <i class="fas fa-brain me-2"></i>Sentiment Analysis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../architecture.php">
                                <i class="fas fa-sitemap me-2"></i>System Architecture
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
                        <small>Organize and categorize news content</small>
                    </div>
                    <div>
                        <a href="manage-sources.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-rss me-2"></i>Manage Sources
                        </a>
                        <a href="scrape-news.php" class="btn btn-success me-2">
                            <i class="fas fa-spider me-2"></i>Scrape News
                        </a>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addTagModal">
                            <i class="fas fa-plus me-2"></i>Add Tag
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($_GET['added'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Tag added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Tag deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo mysqli_num_rows($tags_result); ?></h4>
                                        <small>Total Tags</small>
                                    </div>
                                    <i class="fas fa-tags fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo mysqli_num_rows($trending_result); ?></h4>
                                        <small>Trending</small>
                                    </div>
                                    <i class="fas fa-fire fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">
                                            <?php 
                                            $active_tags = 0;
                                            mysqli_data_seek($tags_result, 0);
                                            while ($tag = mysqli_fetch_assoc($tags_result)) {
                                                // All tags are considered active since status column doesn't exist
                                                $active_tags++;
                                            }
                                            echo $active_tags;
                                            ?>
                                        </h4>
                                        <small>Active</small>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0">
                                            <?php 
                                            // Get news sources count
                                            $sources_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
                                            if (mysqli_num_rows($sources_table_check) > 0) {
                                                $sources_count_query = "SELECT COUNT(*) as count FROM news_sources WHERE status = 'active'";
                                                $sources_count = mysqli_query($conn, $sources_count_query)->fetch_assoc()['count'];
                                                echo $sources_count;
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </h4>
                                        <small>News Sources</small>
                                    </div>
                                    <i class="fas fa-rss fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="manage-sources.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-plus me-2"></i>Add News Source
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="scrape-news.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-spider me-2"></i>Scrape Articles
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-info w-100" onclick="showTagAnalytics()">
                                            <i class="fas fa-chart-line me-2"></i>Tag Analytics
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button class="btn btn-outline-warning w-100" onclick="exportTags()">
                                            <i class="fas fa-download me-2"></i>Export Tags
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- All Tags -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-tags me-2"></i>All Tags</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php mysqli_data_seek($tags_result, 0); ?>
                                    <?php while ($tag = mysqli_fetch_assoc($tags_result)): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="tag-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="tag-color-preview" style="background-color: #007bff;"></span>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($tag['name']); ?></h6>
                                                        </div>
                                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($tag['description'] ?? ''); ?></p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-newspaper me-1"></i>
                                                                <?php echo $tag['usage_count']; ?> articles
                                                            </small>
                                                            <div>
                                                                <span class="badge bg-success">Active</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ms-2">
                                                        <a href="manage-tags.php?delete=<?php echo $tag['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this tag?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trending Tags -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-fire me-2"></i>Trending Tags (7 Days)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($trending_result) > 0): ?>
                                    <?php while ($tag = mysqli_fetch_assoc($trending_result)): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <span class="tag-color-preview" style="background-color: #28a745;"></span>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($tag['name']); ?></div>
                                                    <small class="text-muted"><?php echo $tag['recent_mentions']; ?> mentions</small>
                                                </div>
                                            </div>
                                            <span class="trending-badge">
                                                <i class="fas fa-arrow-up me-1"></i>Trending
                                            </span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No trending tags in the last 7 days</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- News Sources Summary -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6><i class="fas fa-rss me-2"></i>News Sources</h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $sources_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
                                if (mysqli_num_rows($sources_table_check) > 0): 
                                    $recent_sources_query = "SELECT ns.*, c.name as category_name 
                                                             FROM news_sources ns 
                                                             LEFT JOIN categories c ON ns.category_id = c.id 
                                                             WHERE ns.status = 'active' 
                                                             ORDER BY ns.last_scraped DESC 
                                                             LIMIT 5";
                                    $recent_sources_result = mysqli_query($conn, $recent_sources_query);
                                    
                                    if (mysqli_num_rows($recent_sources_result) > 0):
                                        while ($source = mysqli_fetch_assoc($recent_sources_result)):
                                ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <div class="fw-bold small"><?php echo htmlspecialchars($source['name']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($source['category_name'] ?? 'Uncategorized'); ?>
                                                        <?php if ($source['last_scraped']): ?>
                                                            • <?php echo date('M d', strtotime($source['last_scraped'])); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $source['type'] === 'rss' ? 'info' : 'secondary'; ?> text-uppercase">
                                                    <?php echo $source['type']; ?>
                                                </span>
                                            </div>
                                <?php 
                                        endwhile;
                                    else:
                                        echo '<p class="text-muted text-center small">No active sources found</p>';
                                    endif;
                                else:
                                    echo '<p class="text-muted text-center small"><a href="install_news_sources.php">Install news sources table</a></p>';
                                endif; 
                                ?>
                                <div class="mt-2">
                                    <a href="manage-sources.php" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-cog me-1"></i>Manage All Sources
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Tag Modal -->
    <div class="modal fade" id="addTagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_tag" value="1">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        // Show tag analytics
        function showTagAnalytics() {
            alert('Tag Analytics feature would show detailed usage statistics, trends, and insights for your tags. This could be expanded into a full analytics dashboard.');
        }

        // Export tags
        function exportTags() {
            window.location.href = 'export-tags.php';
        }

        // Auto-refresh functionality for trending tags
        setInterval(function() {
            // You could implement an AJAX call here to refresh trending tags
            console.log('Checking for new trending tags...');
        }, 30000); // Check every 30 seconds
    </script>
</body>
</html>

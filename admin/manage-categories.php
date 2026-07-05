<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Handle bulk delete for specific categories
if (isset($_GET['bulk_delete']) && $_GET['bulk_delete'] == 'bbc_cnn') {
    // Get the categories to delete
    $categories_query = "SELECT id, name, slug FROM categories WHERE slug IN ('bbc-world', 'cnn-international')";
    $categories_result = mysqli_query($conn, $categories_query);
    
    // Get default category (Politics)
    $default_query = "SELECT id, name FROM categories WHERE id = 1 LIMIT 1";
    $default_result = mysqli_query($conn, $default_query);
    $default_category = mysqli_fetch_assoc($default_result);
    
    if ($default_category) {
        mysqli_begin_transaction($conn);
        
        try {
            $deleted_count = 0;
            $reassigned_count = 0;
            
            while ($category = mysqli_fetch_assoc($categories_result)) {
                // Reassign news articles
                $reassign_query = "UPDATE news SET category_id = " . $default_category['id'] . " WHERE category_id = " . $category['id'];
                if (mysqli_query($conn, $reassign_query)) {
                    $reassigned_count += mysqli_affected_rows($conn);
                }
                
                // Delete category
                $delete_query = "DELETE FROM categories WHERE id = " . $category['id'];
                if (mysqli_query($conn, $delete_query)) {
                    $deleted_count++;
                }
            }
            
            mysqli_commit($conn);
            $success = "Successfully deleted $deleted_count categories and reassigned $reassigned_count news articles to '{$default_category['name']}' category.";
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error during bulk deletion: " . $e->getMessage();
        }
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category has news articles
    $news_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE category_id = $category_id");
    $news_count = mysqli_fetch_assoc($news_check)['count'];
    
    // Check if category has news sources
    $sources_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM news_sources WHERE category_id = $category_id");
    $sources_count = mysqli_fetch_assoc($sources_check)['count'];
    
    // Check if category has alert categories
    $alerts_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM alert_categories WHERE category_id = $category_id");
    $alerts_count = mysqli_fetch_assoc($alerts_check)['count'];
    
    // Check if category has analytics data
    $analytics_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM category_analytics WHERE category_id = $category_id");
    $analytics_count = mysqli_fetch_assoc($analytics_check)['count'];
    
    $error_messages = [];
    if ($news_count > 0) {
        $error_messages[] = "$news_count news articles";
    }
    if ($sources_count > 0) {
        $error_messages[] = "$sources_count news sources";
    }
    if ($alerts_count > 0) {
        $error_messages[] = "$alerts_count alert associations";
    }
    if ($analytics_count > 0) {
        $error_messages[] = "$analytics_count analytics records";
    }
    
    if (!empty($error_messages)) {
        $error = "Cannot delete category - it contains: " . implode(', ', $error_messages) . ". Please remove or reassign these records first.";
    } else {
        // Get category image to delete
        $category_query = "SELECT image FROM categories WHERE id = $category_id";
        $category_result = mysqli_query($conn, $category_query);
        $category = mysqli_fetch_assoc($category_result);
        
        // Start transaction for safe deletion
        mysqli_begin_transaction($conn);
        
        try {
            // Delete category (cascade will handle related records)
            $delete_query = "DELETE FROM categories WHERE id = $category_id";
            
            if (mysqli_query($conn, $delete_query)) {
                // Delete image file if exists
                if ($category && $category['image'] && file_exists('../' . $category['image'])) {
                    unlink('../' . $category['image']);
                }
                
                // Commit transaction
                mysqli_commit($conn);
                $success = "Category deleted successfully!";
            } else {
                throw new Exception("Error deleting category: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error = "Error deleting category: " . $e->getMessage();
        }
    }
}

// Handle status change
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $category_id = $_GET['id'];
    $status = $_GET['status'];
    
    if (in_array($status, ['active', 'inactive'])) {
        $update_query = "UPDATE categories SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Category status updated successfully!";
        } else {
            $error = "Error updating category status!";
        }
    }
}

// Get all categories with related counts (with error handling)
// Check if news_sources table exists first
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
$news_sources_exists = mysqli_num_rows($table_check) > 0;

if ($news_sources_exists) {
    $categories_query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                         (SELECT COUNT(*) FROM news_sources WHERE category_id = c.id) as sources_count,
                         (SELECT CASE WHEN COUNT(*) > 0 THEN COUNT(*) ELSE 0 END FROM alert_categories WHERE category_id = c.id) as alerts_count,
                         (SELECT CASE WHEN COUNT(*) > 0 THEN COUNT(*) ELSE 0 END FROM category_analytics WHERE category_id = c.id) as analytics_count
                         FROM categories c 
                         ORDER BY c.name ASC";
} else {
    $categories_query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                         0 as sources_count,
                         (SELECT CASE WHEN COUNT(*) > 0 THEN COUNT(*) ELSE 0 END FROM alert_categories WHERE category_id = c.id) as alerts_count,
                         (SELECT CASE WHEN COUNT(*) > 0 THEN COUNT(*) ELSE 0 END FROM category_analytics WHERE category_id = c.id) as analytics_count
                         FROM categories c 
                         ORDER BY c.name ASC";
}

try {
    $categories_result = mysqli_query($conn, $categories_query);
    if (!$categories_result) {
        // Fallback query without problematic tables
        if ($news_sources_exists) {
            $categories_query = "SELECT c.*, 
                                 (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                                 (SELECT COUNT(*) FROM news_sources WHERE category_id = c.id) as sources_count,
                                 0 as alerts_count,
                                 0 as analytics_count
                                 FROM categories c 
                                 ORDER BY c.name ASC";
        } else {
            $categories_query = "SELECT c.*, 
                                 (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                                 0 as sources_count,
                                 0 as alerts_count,
                                 0 as analytics_count
                                 FROM categories c 
                                 ORDER BY c.name ASC";
        }
        $categories_result = mysqli_query($conn, $categories_query);
    }
} catch (Exception $e) {
    // Check if news_sources table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
    if (mysqli_num_rows($table_check) > 0) {
        // Table exists, use it
        $categories_query = "SELECT c.*, 
                             (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                             (SELECT COUNT(*) FROM news_sources WHERE category_id = c.id) as sources_count,
                             0 as alerts_count,
                             0 as analytics_count
                             FROM categories c 
                             ORDER BY c.name ASC";
    } else {
        // Table doesn't exist, skip it
        $categories_query = "SELECT c.*, 
                             (SELECT COUNT(*) FROM news WHERE category_id = c.id) as news_count,
                             0 as sources_count,
                             0 as alerts_count,
                             0 as analytics_count
                             FROM categories c 
                             ORDER BY c.name ASC";
    }
    $categories_result = mysqli_query($conn, $categories_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - PK Live News Admin</title>
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
        .category-image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                            <a class="nav-link active" href="manage-categories.php">
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
                        <h1 class="h3 mb-0">Manage Categories</h1>
                        <small>Organize your news into categories</small>
                    </div>
                    <div>
                        <a href="add-category.php" class="btn btn-light me-2">
                            <i class="fas fa-plus me-2"></i>Add Category
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

                <!-- Categories Table -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Categories (<?php echo mysqli_num_rows($categories_result); ?> total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Related Records</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <tr>
                                            <td>
                                                <?php if (isset($category['image']) && $category['image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($category['image']); ?>" alt="Category Image" class="category-image-thumb">
                                                <?php else: ?>
                                                    <div class="category-image-thumb bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-tag text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($category['slug']); ?></code>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 50)) . '...'; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $total_related = $category['news_count'] + $category['sources_count'] + $category['alerts_count'] + $category['analytics_count'];
                                                if ($total_related > 0) {
                                                    echo '<span class="badge bg-warning">' . $total_related . ' related records</span>';
                                                    echo '<br><small class="text-muted">';
                                                    $parts = [];
                                                    if ($category['news_count'] > 0) $parts[] = $category['news_count'] . ' news';
                                                    if ($category['sources_count'] > 0) $parts[] = $category['sources_count'] . ' sources';
                                                    if ($category['alerts_count'] > 0) $parts[] = $category['alerts_count'] . ' alerts';
                                                    if ($category['analytics_count'] > 0) $parts[] = $category['analytics_count'] . ' analytics';
                                                    echo implode(', ', $parts);
                                                    echo '</small>';
                                                } else {
                                                    echo '<span class="badge bg-success">No related records</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = $category['status'] == 'active' ? 'bg-success' : 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($category['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons d-flex gap-1">
                                                    <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($category['status'] != 'active'): ?>
                                                        <a href="?id=<?php echo $category['id']; ?>&status=active" class="btn btn-sm btn-outline-success" title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($category['status'] != 'inactive'): ?>
                                                        <a href="?id=<?php echo $category['id']; ?>&status=inactive" class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php 
                                                    $total_related = $category['news_count'] + $category['sources_count'] + $category['alerts_count'] + $category['analytics_count'];
                                                    if ($total_related == 0): 
                                                    ?>
                                                        <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-danger" disabled title="Cannot delete - contains related records">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (mysqli_num_rows($categories_result) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <h5>No categories found</h5>
                                <p class="text-muted">Start by adding your first category</p>
                                <a href="add-category.php" class="btn btn-danger">
                                    <i class="fas fa-plus me-2"></i>Add First Category
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Category Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><?php echo mysqli_num_rows($categories_result); ?></h3>
                                <p class="mb-0">Total Categories</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $active_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories WHERE status = 'active'")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-success"><?php echo $active_count; ?></h3>
                                <p class="mb-0">Active Categories</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php
                                $total_news = mysqli_query($conn, "SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
                                ?>
                                <h3 class="text-info"><?php echo $total_news; ?></h3>
                                <p class="mb-0">Total News Articles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

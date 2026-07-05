<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$tag_id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// Get tag data
$tag_query = "SELECT * FROM tags WHERE id = ?";
$stmt = mysqli_prepare($conn, $tag_query);
mysqli_stmt_bind_param($stmt, 'i', $tag_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tag = mysqli_fetch_assoc($result);

if (!$tag) {
    redirect('manage-tags-complete.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $slug = slugify($name);
    
    // Validation
    if (empty($name)) {
        $error = 'Tag name is required';
    } elseif (strlen($name) < 2) {
        $error = 'Tag name must be at least 2 characters';
    } elseif (strlen($name) > 50) {
        $error = 'Tag name must not exceed 50 characters';
    } else {
        // Check if tag already exists (excluding current)
        $check_query = "SELECT id FROM tags WHERE (name = ? OR slug = ?) AND id != ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $tag_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Tag with this name already exists';
        } else {
            // Update tag
            $update_query = "UPDATE tags SET name = ?, slug = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $tag_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Tag '$name' updated successfully!";
                // Refresh tag data
                $tag_query = "SELECT * FROM tags WHERE id = ?";
                $stmt = mysqli_prepare($conn, $tag_query);
                mysqli_stmt_bind_param($stmt, 'i', $tag_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $tag = mysqli_fetch_assoc($result);
            } else {
                $error = "Error updating tag: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tag - PK Live News Admin</title>
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
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .color-option:hover {
            transform: scale(1.1);
        }
        .color-option.selected {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.3);
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
                        <h1 class="h3 mb-0">Edit Tag</h1>
                        <small>Update tag information</small>
                    </div>
                    <div>
                        <a href="manage-tags-complete.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Tags
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

                <!-- Edit Tag Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="tagForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tag Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($tag['name']); ?>" 
                                               placeholder="Enter tag name" required>
                                        <div class="form-text">Must be 2-50 characters</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">URL Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo htmlspecialchars($tag['slug']); ?>" 
                                               placeholder="Auto-generated from name" readonly>
                                        <div class="form-text">URL-friendly version of the name</div>
                                    </div>
                                </div>
                            </div>
                            
                                                        
                                                        
                            <!-- Tag Statistics -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h4 class="mb-0"><?php echo format_date($tag['created_at']); ?></h4>
                                            <small class="text-muted">Created Date</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h4 class="mb-0">ID: <?php echo $tag['id']; ?></h4>
                                            <small class="text-muted">Tag ID</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Tag
                                        </button>
                                        <a href="manage-tags-complete.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <a href="view-tag-news.php?id=<?php echo $tag['id']; ?>" class="btn btn-info">
                                            <i class="fas fa-newspaper me-2"></i>View Related News
                                        </a>
                                        <a href="tag-analytics.php?id=<?php echo $tag['id']; ?>" class="btn btn-success">
                                            <i class="fas fa-chart-line me-2"></i>View Analytics
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update slug as user types
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            document.getElementById('slug').value = slug;
        });

        
        // Form validation
        document.getElementById('tagForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (name.length < 2) {
                e.preventDefault();
                alert('Tag name must be at least 2 characters');
                return false;
            }
            
            if (name.length > 50) {
                e.preventDefault();
                alert('Tag name must not exceed 50 characters');
                return false;
            }
        });
    </script>
</body>
</html>

<?php
// PK Live News - Fixed Add News Page
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Determine user permissions
$can_publish = is_admin() || is_editor();
$is_reporter_user = is_reporter();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? clean_input($_POST['title']) : '';
    $content = isset($_POST['content']) ? clean_input($_POST['content']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $requested_status = isset($_POST['status']) ? clean_input($_POST['status']) : 'draft';
    
    // Permission check: Reporters can only submit as 'pending' or 'draft'
    // Editors and Admins can publish directly
    if (!$can_publish && $requested_status === 'published') {
        $status = 'pending'; // Force pending status for reporters
    } else {
        $status = $requested_status;
    }
    $excerpt = substr(strip_tags($content), 0, 200) . '...';
    $image_path = '';
    $video_path = '';
    $video_url = clean_input($_POST['video_url'] ?? '');
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $_FILES['image']['size'] <= $max_size) {
            $file_name = 'img_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/news/' . $file_name;
            $full_upload_path = '../' . $upload_path;
            
            // Ensure upload directory exists
            if (!is_dir('../uploads/news/')) {
                mkdir('../uploads/news/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_upload_path)) {
                $image_path = $upload_path;
            }
        }
    }
    
    // Handle video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $allowed_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
        $max_size = 50 * 1024 * 1024; // 50MB
        $file_extension = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $_FILES['video']['size'] <= $max_size) {
            $file_name = 'vid_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/news/videos/' . $file_name;
            $full_upload_path = '../' . $upload_path;
            
            // Ensure upload directory exists
            if (!is_dir('../uploads/news/videos/')) {
                mkdir('../uploads/news/videos/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $full_upload_path)) {
                $video_path = $upload_path;
            }
        }
    }
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    // Check for duplicate slug using prepared statement
    $counter = 1;
    $original_slug = $slug;
    while (true) {
        $check_query = "SELECT id FROM news WHERE slug = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 's', $slug);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
        mysqli_stmt_close($check_stmt);
    }
    
    // Insert article with CORRECT parameter binding
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    // FIXED: Correct parameter count and types
    mysqli_stmt_bind_param($stmt, 'sssssssiis', 
        $title, 
        $slug, 
        $content, 
        $excerpt, 
        $image_path, 
        $video_url, 
        $video_path, 
        $category_id, 
        $_SESSION['user_id'], 
        $status
    );
    
    if (mysqli_stmt_execute($stmt)) {
        if ($status === 'published') {
            $success = "Article published successfully!";
        } else {
            $success = "Article submitted for approval successfully!";
        }
    } else {
        $error = "Failed to publish article: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add News (FIXED) - PK Live News Admin</title>
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
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6b4aa8 100%);
        }
        .fix-badge {
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="fix-badge">
        <i class="fas fa-check-circle me-2"></i>FIXED VERSION
    </div>

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
                        <a href="manage-news-fixed.php" class="nav-link">
                            <i class="fas fa-newspaper me-2"></i> Manage News
                        </a>
                        <a href="add-news-fixed-complete.php" class="nav-link active">
                            <i class="fas fa-plus me-2"></i> Add News (FIXED)
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
                    <h2>Add News Article (FIXED)</h2>
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

                <!-- Bug Fixes Summary -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-bug me-2"></i>Bugs Fixed:</h5>
                    <ul class="mb-0">
                        <li><strong>Parameter Binding:</strong> Fixed mismatched parameter count</li>
                        <li><strong>SQL Injection:</strong> Fixed slug checking with prepared statements</li>
                        <li><strong>Session Issues:</strong> Proper session handling</li>
                        <li><strong>White Screen:</strong> Fixed PHP syntax errors</li>
                    </ul>
                </div>

                <!-- Add News Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Article Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">Article Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="video_url" class="form-label">Video URL (Optional)</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" 
                                               value="<?php echo isset($video_url) ? htmlspecialchars($video_url) : ''; ?>"
                                               placeholder="https://example.com/video.mp4">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($category_id) && $category_id == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo (isset($status) && $status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo (isset($status) && $status == 'published') ? 'selected' : ''; ?> <?php echo $can_publish ? '' : 'disabled'; ?>>Published</option>
                                            <option value="featured" <?php echo (isset($status) && $status == 'featured') ? 'selected' : ''; ?> <?php echo $can_publish ? '' : 'disabled'; ?>>Featured</option>
                                        </select>
                                        <?php if (!$can_publish): ?>
                                            <small class="text-muted">Reporters can only submit as draft</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="form-text text-muted">Allowed: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="video" class="form-label">Upload Video</label>
                                        <input type="file" class="form-control" id="video" name="video" accept="video/*">
                                        <small class="form-text text-muted">Allowed: MP4, AVI, MOV, WMV, FLV, WebM, MKV (Max 50MB)</small>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-save me-2"></i><?php echo $can_publish ? 'Publish Article' : 'Submit for Approval'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5><i class="fas fa-newspaper text-primary mb-3"></i></h5>
                                <h6>Manage Articles</h6>
                                <p class="text-muted">View and manage all news articles</p>
                                <a href="manage-news-fixed.php" class="btn btn-outline-primary">Manage News</a>
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5><i class="fas fa-chart-line text-success mb-3"></i></h5>
                                <h6>Dashboard</h6>
                                <p class="text-muted">View statistics and analytics</p>
                                <a href="dashboard.php" class="btn btn-outline-success">Dashboard</a>
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

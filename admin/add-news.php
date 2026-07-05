<?php
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
    
    // Insert article
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, created_at, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
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
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add News - PK Live News Admin</title>
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
        }
        .admin-header {
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
                            <a class="nav-link active" href="add-news.php">
                                <i class="fas fa-plus me-2"></i>Add News
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
                        <h1 class="h3 mb-0">Add News Article</h1>
                        <small>Create and publish new news content with images and videos</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                        </span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add News Article
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Content *</label>
                                <textarea name="content" class="form-control" rows="10" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-image me-2"></i>Image Upload
                                            <small class="text-muted">(Max: 5MB, JPG/PNG/GIF/WebP)</small>
                                        </label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-video me-2"></i>Video Upload
                                            <small class="text-muted">(Max: 50MB, MP4/AVI/MOV)</small>
                                        </label>
                                        <input type="file" name="video" class="form-control" accept="video/*">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-link me-2"></i>Video URL (Optional)
                                    <small class="text-muted">(YouTube, Vimeo, etc.)</small>
                                </label>
                                <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <?php if ($can_publish): ?>
                                        <option value="published">Published</option>
                                    <?php else: ?>
                                        <option value="pending">Submit for Approval</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (!$can_publish): ?>
                                    <small class="text-muted">Your article will be reviewed by an editor before publishing.</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Article
                                </button>
                                <a href="manage-news.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to News
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

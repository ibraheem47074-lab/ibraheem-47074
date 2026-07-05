<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

// Check if user has permission to add news
if (!function_exists('can_add_news')) {
    // Basic permission check if can_add_news function doesn't exist
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'reporter' && $_SESSION['user_role'] !== 'senior_reporter' && $_SESSION['user_role'] !== 'junior_reporter') {
        redirect('reporter-dashboard.php');
    }
} else {
    if (!can_add_news()) {
        redirect('reporter-dashboard.php');
    }
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $tags = trim($_POST['tags'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $news_type = 'manual'; // Reporter articles are manual
    
    // Validate required fields
    if (empty($title) || empty($content)) {
        $error = "Title and content are required fields.";
    } else {
        // Generate slug
        $slug = slugify($title);
        
        // Check if slug already exists
        $article_id = $_POST['article_id'] ?? 0;
        $slug_check_query = "SELECT id FROM news WHERE slug = ? AND id != ?";
        $slug_check_stmt = mysqli_prepare($conn, $slug_check_query);
        mysqli_stmt_bind_param($slug_check_stmt, 'si', $slug, $article_id);
        mysqli_stmt_execute($slug_check_stmt);
        $slug_check_result = mysqli_stmt_get_result($slug_check_stmt);
        
        if (mysqli_num_rows($slug_check_result) > 0) {
            $slug .= '-' . time();
        }
        
        // Handle file uploads
        $image_path = '';
        $video_url = '';
        $video_path = '';
        
        // Image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $image_path = '../uploads/news/' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image_path = 'uploads/news/' . $image_name;
            }
        }
        
        // Video upload
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video_name = time() . '_' . basename($_FILES['video']['name']);
            $video_path = '../uploads/videos/' . $video_name;
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                $video_path = 'uploads/videos/' . $video_name;
            }
        }
        
        // External video URL
        if (!empty($_POST['video_url'])) {
            $video_url = trim($_POST['video_url']);
        }
        
        // Insert article
        $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, is_breaking, published_at, news_type) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssiisss', $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path, $category_id, $user_id, $status, $is_breaking, $news_type);
        
        if (mysqli_stmt_execute($stmt)) {
            $article_id = mysqli_insert_id($conn);
            
            $success = "Article created successfully! <a href='my-articles.php' class='alert-link'>View My Articles</a> or <a href='reporter-add-news.php' class='alert-link'>Create Another</a>";
            
            // Clear form
            $_POST = [];
        } else {
            $error = "Error creating article: " . mysqli_error($conn);
        }
    }
}

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f39c12;
            box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
        }
        .btn-primary {
            background-color: #f39c12;
            border-color: #f39c12;
        }
        .btn-primary:hover {
            background-color: #e67e22;
            border-color: #e67e22;
        }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s;
        }
        .upload-area:hover {
            border-color: #f39c12;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block reporter-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Reporter Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="reporter-dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my-articles.php">
                                <i class="fas fa-list me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reporter-add-news.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Article
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>All News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my-comments.php">
                                <i class="fas fa-comments me-2"></i>My Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter-profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 reporter-main-content">
                <!-- Header -->
                <div class="reporter-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">Create New Article</h1>
                        <small>Write and publish your news article</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="reporter-dashboard.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="reporter-profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Article Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Article Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt/Summary</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                                        <small class="text-muted">Brief summary of your article (optional)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" class="form-control" id="tags" name="tags" 
                                               value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                               placeholder="Enter tags separated by commas">
                                        <small class="text-muted">e.g., politics, sports, technology</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : ''; ?>>Submit for Review</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                                   <?php echo (isset($_POST['is_breaking'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_breaking">
                                                Breaking News
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Featured Image</label>
                                        <div class="upload-area">
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <small class="text-muted">Upload article image (JPG, PNG, GIF)</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Video Upload</label>
                                        <div class="upload-area">
                                            <input type="file" class="form-control" id="video" name="video" accept="video/*">
                                            <small class="text-muted">Upload video file (MP4, AVI, MOV)</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="video_url" class="form-label">Or Video URL</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" 
                                               value="<?php echo htmlspecialchars($_POST['video_url'] ?? ''); ?>"
                                               placeholder="https://youtube.com/watch?v=...">
                                        <small class="text-muted">YouTube or other video platform URL</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="reporter-dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i><?php echo (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'Submit for Review' : 'Save Draft'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            // You can display the slug if needed
        });

        // Character counter for content
        document.getElementById('content').addEventListener('input', function() {
            const content = this.value;
            const wordCount = content.trim().split(/\s+/).length;
            // You can display word count if needed
        });
    </script>
</body>
</html>

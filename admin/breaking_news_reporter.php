<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $video_url = clean_input($_POST['video_url']);
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/news/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image = $filename;
            }
        }
    }
    
    // Create slug
    $slug = create_slug($title);
    
    // Check if slug exists and make unique
    $slug_check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug'");
    if (mysqli_num_rows($slug_check) > 0) {
        $slug .= '-' . time();
    }
    
    // Insert breaking news
    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, category_id, author_id, status, is_breaking, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', 1, NOW())";
    
    $excerpt = substr(strip_tags($content), 0, 200) . '...';
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssssiii', $title, $slug, $content, $excerpt, $image, $video_url, $category_id, $reporter_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Breaking news published successfully!";
    } else {
        $error = "Error publishing breaking news. Please try again.";
    }
}

// Get my breaking news
$breaking_news_query = "SELECT n.*, c.name as category_name 
                        FROM news n 
                        LEFT JOIN categories c ON n.category_id = c.id 
                        WHERE n.author_id = $reporter_id AND n.is_breaking = 1 
                        ORDER BY n.created_at DESC";
$breaking_news = mysqli_query($conn, $breaking_news_query);

// Get categories
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breaking News - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .reporter-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .reporter-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .reporter-sidebar .nav-link:hover,
        .reporter-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .reporter-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .breaking-news-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #ff6b6b;
        }
        .breaking-news-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            border-left: 4px solid #ff6b6b;
            transition: transform 0.3s ease;
        }
        .breaking-news-item:hover {
            transform: translateY(-2px);
        }
        .breaking-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        .urgent-indicator {
            color: #ff6b6b;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
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
                            <a class="nav-link" href="reporter_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_news.php">
                                <i class="fas fa-newspaper me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link bg-danger text-white active" href="breaking_news_reporter.php">
                                <i class="fas fa-bolt me-2"></i>Breaking News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live_reporter.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_profile.php">
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
                        <h1 class="h3 mb-0">
                            <i class="fas fa-bolt me-2"></i>Breaking News
                        </h1>
                        <small class="urgent-indicator">Post urgent updates quickly</small>
                    </div>
                    <div>
                        <span class="breaking-badge">
                            <i class="fas fa-bolt me-1"></i>LIVE UPDATES
                        </span>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Breaking News Form -->
                    <div class="col-lg-6 mb-4">
                        <div class="breaking-news-form">
                            <h4 class="mb-4">
                                <i class="fas fa-bolt text-danger me-2"></i>Post Breaking News
                            </h4>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-heading me-1"></i>Headline <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="title" class="form-control form-control-lg" 
                                           placeholder="Breaking: Major event happening now..." required>
                                    <small class="text-muted">Keep it short and impactful</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Content <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="content" id="content" class="form-control" rows="8" 
                                              placeholder="What's happening? Provide key details..." required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tag me-1"></i>Category
                                        </label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-video me-1"></i>Video URL (Optional)
                                        </label>
                                        <input type="url" name="video_url" class="form-control" 
                                               placeholder="https://youtube.com/watch?v=...">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-camera me-1"></i>Image (Optional)
                                    </label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload a relevant image for the breaking news</small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-bolt me-2"></i>PUBLISH BREAKING NEWS
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Breaking News -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>My Breaking News
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                <?php if (mysqli_num_rows($breaking_news) > 0): ?>
                                    <?php while ($news = mysqli_fetch_assoc($breaking_news)): ?>
                                        <div class="breaking-news-item">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <a href="../news.php?slug=<?php echo $news['slug']; ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($news['title']); ?>
                                                    </a>
                                                </h6>
                                                <span class="breaking-badge">BREAKING</span>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 120)) . '...'; ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <span>
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($news['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                                <span>
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo number_format($news['views']); ?> views
                                                </span>
                                                <span>
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo time_ago($news['created_at']); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <a href="edit-news.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="../news.php?slug=<?php echo $news['slug']; ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-bolt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No breaking news posted yet</h5>
                                        <p class="text-muted">Use the form to post urgent updates</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Breaking News Guidelines
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-check text-success me-2"></i>When to post breaking news:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-angle-right me-2"></i>Natural disasters or accidents</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Election results and major political events</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Sports championship results</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Major business announcements</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Public safety alerts</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Best practices:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-angle-right me-2"></i>Verify information before posting</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Keep headlines short and clear</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Include key details in first paragraph</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Update as new information comes in</li>
                                    <li><i class="fas fa-angle-right me-2"></i>Cite sources when possible</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#content').summernote({
                height: 200,
                placeholder: 'What\'s happening? Provide key details...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        });
    </script>
</body>
</html>

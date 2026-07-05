<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and has appropriate role
// Admins and editors can edit any article
// Reporters can only edit their own articles
if (!is_logged_in()) {
    redirect('login.php');
}

$can_edit_any = is_admin() || is_editor();
$is_reporter_user = is_reporter();

$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($news_id === 0) {
    redirect('manage-news.php');
}

// Get news article
$query = "SELECT n.*, c.name as category_name,
           COALESCE(n.published_at, n.created_at) as real_post_time
           FROM news n 
           LEFT JOIN categories c ON n.category_id = c.id 
           WHERE n.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $news_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if (!$news) {
    redirect('manage-news.php');
}

// Check permissions: reporters can only edit their own articles
if (!$can_edit_any) {
    if (!$is_reporter_user || $news['author_id'] != $_SESSION['user_id']) {
        redirect('manage-news.php');
    }
}

// Get categories for dropdown
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $slug = clean_input($_POST['slug']);
    $content = $_POST['content'];
    $excerpt = clean_input($_POST['excerpt']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $video_url = clean_input($_POST['video_url'] ?? '');
 // Handle published_at logic
    if (!empty($_POST['published_at'])) {
        // Use the provided date from form
        $published_at = $_POST['published_at'];
    } elseif ($status === 'published' || $status === 'featured') {
        // If publishing and no date provided, use current date/time
        $published_at = date('Y-m-d H:i:s');
    } else {
        // Otherwise keep the existing published_at value
        $published_at = $news['published_at'];
    }
    $image_path = $news['image']; // Keep existing image by default
    
    // Generate slug if empty OR if slug was auto-generated (to check for duplicates)
    $slug_was_provided = !empty($_POST['slug']);
    if (empty($slug) || !$slug_was_provided) {
        $base_slug = slugify($title);
        $slug = $base_slug;
        
        // Only check for duplicates if slug was auto-generated
        if (!$slug_was_provided) {
            // Check if base slug exists and generate unique slug
            $counter = 1;
            while (true) {
                $duplicate_query = "SELECT id FROM news WHERE slug = ? AND id != ?";
                $dup_stmt = mysqli_prepare($conn, $duplicate_query);
                mysqli_stmt_bind_param($dup_stmt, 'si', $slug, $news_id);
                mysqli_stmt_execute($dup_stmt);
              
                $duplicate_result = mysqli_stmt_get_result($dup_stmt);
                
                if (mysqli_num_rows($duplicate_result) == 0) {
                    break; // Slug is unique
                }
                
                // Generate alternative slug
                if ($counter == 1) {
                    $slug = $base_slug . '-' . date('Y-m-d');
                } else {
                    $slug = $base_slug . '-' . $counter;
                }
                $counter++;
            }
            mysqli_stmt_close($dup_stmt);
        }
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $file_name = uniqid() . '.' . $file_extension;
                $upload_path = UPLOAD_PATH . 'news/' . $file_name;
                
                if (!file_exists('../' . UPLOAD_PATH . 'news/')) {
                    mkdir('../' . UPLOAD_PATH . 'news/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $upload_path)) {
                    // Delete old image if exists
                    if ($news['image'] && file_exists('../' . $news['image'])) {
                        unlink('../' . $news['image']);
                    }
                    $image_path = $upload_path;
                } else {
                    $error = "Error uploading image file";
                }
            } else {
                $error = "File size too large. Maximum size is " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
            }
        } else {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        }
    }
    
    if (empty($error)) {
        // Update news article
        $query = "UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, 
                  category_id = ?, status = ?, is_breaking = ?, image = ?, video_url = ?, published_at = ?
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssisissis', $title, $slug, $content, $excerpt, 
                               $category_id, $status, $is_breaking, $image_path, $video_url, $published_at, $news_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News article updated successfully!";
          // Refresh news data - FIX: Get fresh data from database
            $refresh_query = "SELECT n.*, c.name as category_name FROM news n LEFT JOIN categories c ON n.category_id = c.id WHERE n.id = ?";
            $refresh_stmt = mysqli_prepare($conn, $refresh_query);
            mysqli_stmt_bind_param($refresh_stmt, 'i', $news_id);
            mysqli_stmt_execute($refresh_stmt);
            $refresh_result = mysqli_stmt_get_result($refresh_stmt);
            $news = mysqli_fetch_assoc($refresh_result);
            mysqli_stmt_close($refresh_stmt);
        } else {
            $error = "Error updating news article: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.css" rel="stylesheet">
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
        .form-label {
            font-weight: 600;
        }
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
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
                        <h1 class="h3 mb-0">Edit News Article</h1>
                        <small>Update news article content and settings</small>
                    </div>
                    <div>
                        <a href="manage-news.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to News
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

                <!-- Edit News Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-eye me-2"></i>Current Post Preview
                        </h5>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-2"><?php echo htmlspecialchars($news['title']); ?></h6>
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <strong>
                                            <?php 
                                            $display_date = !empty($news['real_post_time']) ? $news['real_post_time'] : $news['created_at'];
                                            echo format_date_realtime($display_date); 
                                            ?>
                                        </strong>
                                        <span class="ms-3">
                                            <i class="fas fa-eye me-1"></i> <?php echo number_format($news['views']); ?> views
                                        </span>
                                    </div>
                                    <p class="mb-2"><?php echo htmlspecialchars(substr($news['excerpt'] ?? '', 0, 150)) . '...'; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <?php if ($news['image']): ?>
                                        <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                                             class="img-fluid rounded" alt="Article image" style="max-height: 120px;">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($news['category_name'] ?? 'Uncategorized'); ?></span>
                                <?php if ($news['is_breaking']): ?>
                                    <span class="badge bg-danger ms-2">BREAKING</span>
                                <?php endif; ?>
                                <span class="badge bg-secondary ms-2"><?php echo ucfirst($news['status']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-edit me-2"></i>Edit Article Details
                        </h5>
                        <form method="POST" enctype="multipart/form-data" id="newsForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Title -->
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Article Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($news['title']); ?>" 
                                               placeholder="Enter article title" 
                                               required maxlength="255">
                                    </div>

                                    <!-- Slug -->
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">URL Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo htmlspecialchars($news['slug']); ?>" 
                                               placeholder="url-friendly-title">
                                        <small class="text-muted">Leave empty to auto-generate from title</small>
                                    </div>

                                    <!-- Content -->
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Article Content *</label>
                                        <textarea class="form-control" id="content" name="content" rows="15" 
                                                  placeholder="Write your article content here..." required><?php echo htmlspecialchars($news['content']); ?></textarea>
                                    </div>

                                    <!-- Excerpt -->
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Article Excerpt</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="Brief summary of the article"><?php echo htmlspecialchars($news['excerpt']); ?></textarea>
                                        <small class="text-muted">Short description for article preview</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Image Upload -->
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Article Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">Upload new image to replace current</small>
                                        
                                        <!-- Current Image Preview -->
                                        <?php if ($news['image']): ?>
                                            <div class="mt-3">
                                                <h6>Current Image:</h6>
                                                <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                                                     class="image-preview" alt="Current article image">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Category -->
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo ($news['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo ($news['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($news['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                            <option value="featured" <?php echo ($news['status'] == 'featured') ? 'selected' : ''; ?>>Featured</option>
                                        </select>
                                    </div>

                                    <!-- Published Date/Time -->
                                    <div class="mb-3">
                                        <label for="published_at" class="form-label">Published Date & Time</label>
                                        <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                               value="<?php echo !empty($news['published_at']) && $news['published_at'] !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : ''; ?>">
                                        <small class="text-muted">Leave empty to use current time when publishing</small>
                                    </div>

                                    <!-- Breaking -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                                   value="1" <?php echo $news['is_breaking'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_breaking">
                                                Breaking News
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Article
                                        </button>
                                        <a href="manage-news.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
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
    <script src="https://cdn.tiny.cloud/1/dmo8p48m5mmp3grrp8sig5nn4e044nvf0uq2rghq6y70f4iq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: Arial, sans-serif; }'
        });

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            document.getElementById('slug').value = slug;
        });

        // Form validation
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = tinymce.get('content').getContent();
            const categoryId = document.getElementById('category_id').value;
            
            if (!title) {
                e.preventDefault();
                alert('Please enter article title');
                return false;
            }
            
            if (!content || content.trim() === '') {
                e.preventDefault();
                alert('Please enter article content');
                return false;
            }
            
            if (!categoryId) {
                e.preventDefault();
                alert('Please select a category');
                return false;
            }
            
            return true;
        });

        // Auto-save draft
        let autoSaveTimer;
        function autoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = new FormData(document.getElementById('newsForm'));
                formData.append('auto_save', '1');
                
                fetch('edit-news.php?id=<?php echo $news_id; ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(data => console.log('Auto-saved'))
                  .catch(error => console.error('Auto-save failed:', error));
            }, 30000); // Auto-save after 30 seconds of inactivity
        }

        // Listen for changes
        document.getElementById('title').addEventListener('input', autoSave);
        document.getElementById('slug').addEventListener('input', autoSave);
        document.getElementById('excerpt').addEventListener('input', autoSave);
        
        // TinyMCE change event
        tinymce.get('content').on('change', autoSave);
    </script>
</body>
</html>

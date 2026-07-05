<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Check if news_editions table exists, if not redirect to installation
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    redirect('../install_now.php');
}

$edition_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($edition_id === 0) {
    redirect('manage-editions.php');
}

// Get edition details
$query = "SELECT ne.*, 
          GROUP_CONCAT(n.title SEPARATOR ', ') as news_titles,
          GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names
          FROM news_editions ne
          LEFT JOIN edition_articles ea ON ne.id = ea.edition_id
          LEFT JOIN news n ON ea.article_id = n.id
          LEFT JOIN categories c ON n.category_id = c.id
          WHERE ne.id = ?
          GROUP BY ne.id";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $edition_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    redirect('manage-editions.php');
}

$edition = mysqli_fetch_assoc($result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title'] ?? '');
    $edition_type = clean_input($_POST['edition_type'] ?? '');
    $content = $_POST['content'] ?? '';
    $priority = (int)($_POST['priority'] ?? 0);
    $status = clean_input($_POST['status'] ?? '');
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : ($edition['published_at'] ?? date('Y-m-d H:i:s'));
    
    // Handle additional images
    $additional_images = [];
    if (isset($_POST['existing_images'])) {
        $additional_images = $_POST['existing_images'];
    }
    
    if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
        foreach ($_FILES['additional_images']['name'] as $key => $name) {
            if ($_FILES['additional_images']['error'][$key] == 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_extensions)) {
                    if ($_FILES['additional_images']['size'][$key] <= MAX_FILE_SIZE) {
                        $file_name = uniqid() . '.' . $file_extension;
                        $upload_path = UPLOAD_PATH . 'editions/' . $file_name;
                        $full_upload_path = '../' . $upload_path;
                        
                        // Ensure upload directory exists
                        $upload_dir = dirname($full_upload_path);
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$key], $full_upload_path)) {
                            $additional_images[] = $upload_path;
                        }
                    }
                }
            }
        }
    }
    
    // Validation
    if (empty($title) || empty($edition_type)) {
        $error = 'Edition name and edition type are required fields';
    } else {
        // Update edition
        $additional_images_json = !empty($additional_images) ? json_encode($additional_images) : NULL;
        $query = "UPDATE news_editions SET title = ?, edition_type = ?, content = ?, 
                 additional_images = ?, priority = ?, status = ?, published_at = ? 
                 WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssissi', 
            $title, $edition_type, $content, $additional_images_json, 
            $priority, $status, $published_at, $edition_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News edition updated successfully!";
            // Refresh edition data
            $result = mysqli_query($conn, "SELECT * FROM news_editions WHERE id = $edition_id");
            $edition = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating news edition: " . mysqli_error($conn);
        }
    }
}

// Get news articles
$news_query = "SELECT n.*, c.name as category_name FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.status IN ('published', 'featured') 
               ORDER BY n.published_at DESC";
$news_result = mysqli_query($conn, $news_query);

// Get edition categories
$edition_categories = mysqli_query($conn, "SELECT * FROM edition_categories WHERE status = 'active' ORDER BY name ASC");

// Parse existing images
$existing_images = [];
if (!empty($edition['additional_images'])) {
    $existing_images = json_decode($edition['additional_images'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Edition - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/dmo8p48m5mmp3grrp8sig5nn4e044nvf0uq2rghq6y70f4iq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        .edition-type-card {
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .edition-type-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .edition-type-card.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            position: relative;
            max-width: 150px;
            max-height: 100px;
            border-radius: 5px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-label {
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
                            <a class="nav-link active" href="manage-editions.php">
                                <i class="fas fa-layer-group me-2"></i>News Editions
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
                        <h1 class="h3 mb-0">Edit News Edition</h1>
                        <small>Update edition for: <?php echo htmlspecialchars($edition['title'] ?? 'Untitled Edition'); ?></small>
                    </div>
                    <div>
                        <a href="manage-editions.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Editions
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

                <!-- Edit Edition Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="editionForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- News Article Selection -->
                                    <div class="mb-3">
                                        <label for="news_id" class="form-label">News Article</label>
                                        <select class="form-select" id="news_id" name="news_id" disabled>
                                            <option value="" selected>
                                                <?php 
                                                $news_titles = $edition['news_titles'] ?? 'No articles';
                                                echo htmlspecialchars($news_titles);
                                                ?> 
                                                (<?php echo htmlspecialchars($edition['category_names'] ?? 'No categories'); ?>)
                                            </option>
                                        </select>
                                        <small class="text-muted">News article cannot be changed after edition creation</small>
                                    </div>

                                    <!-- Edition Name -->
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Edition Name *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($edition['title'] ?? ''); ?>" required>
                                    </div>

                                    <!-- Edition Type Selection -->
                                    <div class="mb-3">
                                        <label class="form-label">Edition Type *</label>
                                        <div class="row">
                                            <?php 
                                            mysqli_data_seek($edition_categories, 0);
                                            while ($category = mysqli_fetch_assoc($edition_categories)): 
                                            ?>
                                                <div class="col-md-4 mb-3">
                                                    <div class="card edition-type-card <?php echo ($edition['edition_type'] ?? '') === $category['slug'] ? 'selected' : ''; ?>" 
                                                         onclick="selectEditionType('<?php echo $category['slug']; ?>', this)">
                                                        <div class="card-body text-center">
                                                            <i class="fas <?php echo $category['icon']; ?> fa-2x mb-2" style="color: <?php echo $category['color']; ?>;"></i>
                                                            <h6><?php echo htmlspecialchars($category['name']); ?></h6>
                                                            <small><?php echo htmlspecialchars($category['description']); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        <input type="hidden" id="edition_type" name="edition_type" value="<?php echo htmlspecialchars($edition['edition_type'] ?? 'morning'); ?>" required>
                                    </div>

                                    <!-- Content -->
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Edition Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($edition['content'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- Existing Images -->
                                    <?php if (!empty($existing_images)): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Current Images</label>
                                            <div class="image-preview-container">
                                                <?php foreach ($existing_images as $index => $image): ?>
                                                    <div class="position-relative">
                                                        <img src="../<?php echo htmlspecialchars($image); ?>" class="image-preview" alt="Edition image">
                                                        <input type="hidden" name="existing_images[]" value="<?php echo htmlspecialchars($image); ?>">
                                                        <button type="button" class="remove-image" onclick="removeExistingImage(this)">×</button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Additional Images -->
                                    <div class="mb-3">
                                        <label for="additional_images" class="form-label">Add More Images</label>
                                        <input type="file" class="form-control" id="additional_images" name="additional_images[]" 
                                               accept="image/*" multiple onchange="previewMultipleImages(event)">
                                        <div class="image-preview-container" id="imagePreviewContainer"></div>
                                        <small class="text-muted">You can upload multiple images. Allowed formats: JPG, PNG, GIF. Max size: 5MB each</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Priority -->
                                    <div class="mb-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <input type="number" class="form-control" id="priority" name="priority" 
                                               value="<?php echo htmlspecialchars($edition['priority'] ?? 0); ?>" min="0" max="100">
                                        <small class="text-muted">Higher numbers = higher priority</small>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo ($edition['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($edition['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="archived" <?php echo ($edition['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>

                                    <!-- Published Date -->
                                    <div class="mb-3">
                                        <label for="published_at" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                               value="<?php echo !empty($edition['published_at']) ? date('Y-m-d\TH:i', strtotime($edition['published_at'])) : ''; ?>">
                                    </div>

                                    <!-- News Details -->
                                    <div class="mb-3">
                                        <label class="form-label">News Article Details</label>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6><?php echo htmlspecialchars($edition['title'] ?? 'Untitled Edition'); ?></h6>
                                                <p class="text-muted mb-0">Categories: <?php echo htmlspecialchars($edition['category_names'] ?? 'No categories'); ?></p>
                                                <small class="text-muted">
                                                    <?php 
                                                    $news_titles = $edition['news_titles'] ?? '';
                                                    if (!empty($news_titles)) {
                                                        $titles_array = explode(', ', $news_titles);
                                                        $first_title = $titles_array[0] ?? '';
                                                        echo '<i class="fas fa-newspaper me-1"></i>' . htmlspecialchars($first_title);
                                                    } else {
                                                        echo '<i class="fas fa-newspaper me-1"></i>No articles';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Edition
                                        </button>
                                        <a href="manage-editions.php" class="btn btn-outline-secondary">
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
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 300,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                      alignleft aligncenter alignright alignjustify | \
                      bullist numlist outdent indent | removeformat | help',
            content_css: [
                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                '//www.tiny.cloud/css/codepen.min.css'
            ]
        });

        // Edition type selection
        function selectEditionType(type, element) {
            // Remove previous selection
            document.querySelectorAll('.edition-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            element.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('edition_type').value = type;
        }

        // Remove existing image
        function removeExistingImage(button) {
            if (confirm('Are you sure you want to remove this image?')) {
                button.parentElement.remove();
            }
        }

        // Preview multiple images
        function previewMultipleImages(event) {
            const files = event.target.files;
            const container = document.getElementById('imagePreviewContainer');
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    container.appendChild(img);
                }
                
                reader.readAsDataURL(file);
            }
        }

        // Form validation
        document.getElementById('editionForm').addEventListener('submit', function(e) {
            const editionName = document.getElementById('title').value;
            const editionType = document.getElementById('edition_type').value;
            
            if (!editionName || !editionType) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    </script>
</body>
</html>

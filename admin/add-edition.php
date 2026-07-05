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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $news_id = (int)$_POST['news_id'];
    $edition_name = clean_input($_POST['edition_name']);
    $edition_type = clean_input($_POST['edition_type']);
    $content = $_POST['content'];
    $priority = (int)$_POST['priority'];
    $status = clean_input($_POST['status']);
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
    
    // Handle additional images
    $additional_images = [];
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
    if (empty($news_id) || empty($edition_name) || empty($edition_type)) {
        $error = 'News article, edition name, and edition type are required fields';
    } else {
        // Insert edition with correct column names
        $additional_images_json = !empty($additional_images) ? json_encode($additional_images) : NULL;
        $query = "INSERT INTO news_editions (news_id, edition_name, edition_type, content, additional_images, status, published_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'issssss', 
            $news_id, $edition_name, $edition_type, $content, $additional_images_json, $status, $published_at
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "News edition added successfully!";
            // Clear form
            $_POST = array();
        } else {
            $error = "Error adding news edition: " . mysqli_error($conn);
        }
    }
}

// Get news articles
$news_query = "SELECT n.*, c.name as category_name FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.status IN ('published', 'featured') 
               ORDER BY n.published_at DESC";
$news_result = mysqli_query($conn, $news_query);

// Note: edition_categories table doesn't exist - using hardcoded options instead
$edition_categories = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Edition - PK Live News Admin</title>
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
            max-width: 150px;
            max-height: 100px;
            border-radius: 5px;
            object-fit: cover;
            border: 1px solid #ddd;
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
                        <h1 class="h3 mb-0">Add News Edition</h1>
                        <small>Create a new edition for a news article</small>
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

                <!-- Add Edition Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="editionForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- News Article Selection -->
                                    <div class="mb-3">
                                        <label for="news_id" class="form-label">Select News Article *</label>
                                        <select class="form-select" id="news_id" name="news_id" required onchange="loadNewsDetails()">
                                            <option value="">Select News Article</option>
                                            <?php while ($news = mysqli_fetch_assoc($news_result)): ?>
                                                <option value="<?php echo $news['id']; ?>" <?php echo isset($_POST['news_id']) && $_POST['news_id'] == $news['id'] ? 'selected' : ''; ?>
                                                        data-title="<?php echo htmlspecialchars($news['title']); ?>"
                                                        data-category="<?php echo htmlspecialchars($news['category_name']); ?>">
                                                    <?php echo htmlspecialchars($news['title']); ?> 
                                                    (<?php echo htmlspecialchars($news['category_name']); ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Edition Name -->
                                    <div class="mb-3">
                                        <label for="edition_name" class="form-label">Edition Name *</label>
                                        <input type="text" class="form-control" id="edition_name" name="edition_name" 
                                               value="<?php echo isset($_POST['edition_name']) ? htmlspecialchars($_POST['edition_name']) : ''; ?>" 
                                               placeholder="e.g., Morning Update, Evening Summary" required>
                                    </div>

                                    <!-- Edition Type Selection -->
                                    <div class="mb-3">
                                        <label class="form-label">Edition Type *</label>
                                        <div class="row">
                                            <?php 
                                            // Default edition types since edition_categories table doesn't exist
                                            $default_edition_types = [
                                                ['slug' => 'daily', 'name' => 'Daily Edition', 'icon' => 'fa-newspaper', 'color' => '#007bff', 'description' => 'Regular daily news edition'],
                                                ['slug' => 'breaking', 'name' => 'Breaking News', 'icon' => 'fa-exclamation-triangle', 'color' => '#dc3545', 'description' => 'Urgent breaking news updates'],
                                                ['slug' => 'weekly', 'name' => 'Weekly Digest', 'icon' => 'fa-calendar-week', 'color' => '#28a745', 'description' => 'Weekly summary and highlights']
                                            ];
                                            
                                            foreach ($default_edition_types as $category): 
                                            ?>
                                                <div class="col-md-4 mb-3">
                                                    <div class="card edition-type-card" onclick="selectEditionType('<?php echo $category['slug']; ?>', this)">
                                                        <div class="card-body text-center">
                                                            <i class="fas <?php echo $category['icon']; ?> fa-2x mb-2" style="color: <?php echo $category['color']; ?>;"></i>
                                                            <h6><?php echo htmlspecialchars($category['name']); ?></h6>
                                                            <small><?php echo htmlspecialchars($category['description']); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <input type="hidden" id="edition_type" name="edition_type" required>
                                    </div>

                                    <!-- Content -->
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Edition Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="10"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    </div>

                                    <!-- Additional Images -->
                                    <div class="mb-3">
                                        <label for="additional_images" class="form-label">Additional Images</label>
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
                                               value="<?php echo isset($_POST['priority']) ? htmlspecialchars($_POST['priority']) : '0'; ?>" 
                                               min="0" max="100">
                                        <small class="text-muted">Higher numbers = higher priority</small>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>

                                    <!-- Published Date -->
                                    <div class="mb-3">
                                        <label for="published_at" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                               value="<?php echo isset($_POST['published_at']) ? htmlspecialchars($_POST['published_at']) : date('Y-m-d\TH:i'); ?>">
                                    </div>

                                    <!-- News Details Preview -->
                                    <div class="mb-3" id="newsDetails" style="display: none;">
                                        <label class="form-label">Selected News Details</label>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 id="selectedNewsTitle"></h6>
                                                <p class="text-muted mb-0" id="selectedNewsCategory"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Edition
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

        // Load news details
        function loadNewsDetails() {
            const select = document.getElementById('news_id');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('selectedNewsTitle').textContent = selectedOption.dataset.title;
                document.getElementById('selectedNewsCategory').textContent = 'Category: ' + selectedOption.dataset.category;
                document.getElementById('newsDetails').style.display = 'block';
            } else {
                document.getElementById('newsDetails').style.display = 'none';
            }
        }

        // Preview multiple images
        function previewMultipleImages(event) {
            const files = event.target.files;
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';
            
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
            const newsId = document.getElementById('news_id').value;
            const editionName = document.getElementById('edition_name').value;
            const editionType = document.getElementById('edition_type').value;
            
            if (!newsId || !editionName || !editionType) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    </script>
</body>
</html>

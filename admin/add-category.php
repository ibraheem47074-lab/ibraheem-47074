<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $status = clean_input($_POST['status']);
    
    // Validation
    if (empty($name)) {
        $error = 'Category name is required';
    } else {
        // Generate slug
        $slug = slugify($name);
        
        // Check if slug already exists
        $slug_check = mysqli_query($conn, "SELECT id FROM categories WHERE slug = '$slug'");
        if (mysqli_num_rows($slug_check) > 0) {
            $slug .= '-' . time();
        }
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                    $file_name = uniqid() . '.' . $file_extension;
                    $upload_path = UPLOAD_PATH . 'categories/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $upload_path)) {
                        $image_path = $upload_path;
                    } else {
                        $error = 'Error uploading image';
                    }
                } else {
                    $error = 'File size too large. Maximum size is 5MB';
                }
            } else {
                $error = 'Invalid file type. Only JPG, PNG, and GIF files are allowed';
            }
        }
        
        if (empty($error)) {
            // Check if image column exists in categories table
            $column_check = mysqli_query($conn, "SHOW COLUMNS FROM categories LIKE 'image'");
            $has_image_column = (mysqli_num_rows($column_check) > 0);
            
            // Insert category with or without image column
            if ($has_image_column) {
                $query = "INSERT INTO categories (name, slug, description, image, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sssss', $name, $slug, $description, $image_path, $status);
            } else {
                $query = "INSERT INTO categories (name, slug, description, status) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssss', $name, $slug, $description, $status);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Category added successfully!";
                // Clear form
                $_POST = array();
            } else {
                $error = "Error adding category: " . mysqli_error($conn);
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
    <title>Add Category - PK Live News Admin</title>
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
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
            display: none;
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
                        <h1 class="h3 mb-0">Add Category</h1>
                        <small>Create a new news category</small>
                    </div>
                    <div>
                        <a href="manage-categories.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Categories
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

                <!-- Add Category Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="categoryForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                               required>
                                        <small class="text-muted">The slug will be automatically generated from the name</small>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Brief description of this category"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo isset($_POST['status']) && $_POST['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo isset($_POST['status']) && $_POST['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Image Upload -->
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Category Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                        <img id="imagePreview" class="image-preview" alt="Image preview">
                                        <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size: 5MB</small>
                                    </div>

                                    <!-- Preview -->
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Preview</h6>
                                            <div id="categoryPreview">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="bg-secondary text-white p-3 rounded" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-tag"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0" id="previewName">Category Name</h6>
                                                        <small class="text-muted" id="previewSlug">category-slug</small>
                                                    </div>
                                                </div>
                                                <p class="mt-2 mb-0 small text-muted" id="previewDescription">Category description will appear here...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Category
                                        </button>
                                        <a href="manage-categories.php" class="btn btn-outline-secondary">
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
        // Image preview
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    // Update preview with image
                    updatePreviewWithImage(e.target.result);
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                updatePreviewWithImage(null);
            }
        }

        // Update preview
        function updatePreview() {
            const name = document.getElementById('name').value;
            const description = document.getElementById('description').value;
            const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            
            document.getElementById('previewName').textContent = name || 'Category Name';
            document.getElementById('previewSlug').textContent = slug || 'category-slug';
            document.getElementById('previewDescription').textContent = description || 'Category description will appear here...';
        }

        // Update preview with image
        function updatePreviewWithImage(imageSrc) {
            const previewIcon = document.querySelector('#categoryPreview .bg-secondary');
            
            if (imageSrc) {
                previewIcon.style.backgroundImage = `url(${imageSrc})`;
                previewIcon.style.backgroundSize = 'cover';
                previewIcon.style.backgroundPosition = 'center';
                previewIcon.innerHTML = '';
            } else {
                previewIcon.style.backgroundImage = 'none';
                previewIcon.innerHTML = '<i class="fas fa-tag"></i>';
            }
        }

        // Real-time preview update
        document.getElementById('name').addEventListener('input', updatePreview);
        document.getElementById('description').addEventListener('input', updatePreview);

        // Form validation
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('Category name is required');
                return false;
            }
            
            if (name.length < 2) {
                e.preventDefault();
                alert('Category name must be at least 2 characters long');
                return false;
            }
        });

        // Auto-generate slug preview
        document.getElementById('name').addEventListener('blur', function() {
            const name = this.value.trim();
            if (name) {
                const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                document.getElementById('previewSlug').textContent = slug;
            }
        });

        // Initialize preview on page load
        updatePreview();
    </script>
</body>
</html>

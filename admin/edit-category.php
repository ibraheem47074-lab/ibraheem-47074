<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($category_id === 0) {
    redirect('manage-categories.php');
}

// Get category
$query = "SELECT * FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    redirect('manage-categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $slug = clean_input($_POST['slug']);
    $description = clean_input($_POST['description']);
    $status = clean_input($_POST['status']);
    $image_path = $category['image']; // Keep existing image by default
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = create_slug($name);
    }
    
    // Check if slug already exists (excluding current category)
    $check_query = "SELECT id FROM categories WHERE slug = ? AND id != ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'si', $slug, $category_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Category slug already exists. Please choose a different slug.";
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $file_name = uniqid() . '.' . $file_extension;
                $upload_path = UPLOAD_PATH . 'categories/' . $file_name;
                
                if (!file_exists('../' . UPLOAD_PATH . 'categories/')) {
                    mkdir('../' . UPLOAD_PATH . 'categories/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $upload_path)) {
                    // Delete old image if exists
                    if ($category['image'] && file_exists('../' . $category['image'])) {
                        unlink('../' . $category['image']);
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
        // Update category
        $query = "UPDATE categories SET name = ?, slug = ?, description = ?, status = ?, image = ? 
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $name, $slug, $description, $status, $image_path, $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Category updated successfully!";
            // Refresh category data
            $category = array_merge($category, $_POST);
            $category['image'] = $image_path;
        } else {
            $error = "Error updating category: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - PK Live News Admin</title>
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
                        <h1 class="h3 mb-0">Edit Category</h1>
                        <small>Update category information and settings</small>
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

                <!-- Edit Category Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="categoryForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($category['name']); ?>" 
                                               placeholder="Enter category name" 
                                               required maxlength="100">
                                    </div>

                                    <!-- Slug -->
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">URL Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo htmlspecialchars($category['slug']); ?>" 
                                               placeholder="url-friendly-category-name">
                                        <small class="text-muted">Leave empty to auto-generate from name</small>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Brief description of the category"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                        <small class="text-muted">Category description for SEO and display</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Image Upload -->
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Category Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">Upload new image to replace current</small>
                                        
                                        <!-- Current Image Preview -->
                                        <?php if ($category['image']): ?>
                                            <div class="mt-3">
                                                <h6>Current Image:</h6>
                                                <img src="<?php echo htmlspecialchars($category['image']); ?>" 
                                                     class="image-preview" alt="Current category image">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($category['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($category['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Category
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
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            document.getElementById('slug').value = slug;
        });

        // Form validation
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const slug = document.getElementById('slug').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('Please enter category name');
                return false;
            }
            
            if (!slug) {
                e.preventDefault();
                alert('Please enter category slug');
                return false;
            }
            
            return true;
        });

        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'image-preview mt-3';
                    preview.alt = 'New category image';
                    
                    const container = document.getElementById('image').parentNode;
                    const existingPreview = container.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.replaceWith(preview);
                    } else {
                        container.appendChild(preview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

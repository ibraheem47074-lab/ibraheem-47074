<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $slug = slugify($name);
    $description = clean_input($_POST['description']);
    $color = clean_input($_POST['color']);
    $status = clean_input($_POST['status']);
    
    // Validation
    if (empty($name)) {
        $error = 'Tag name is required';
    } elseif (strlen($name) < 2) {
        $error = 'Tag name must be at least 2 characters';
    } elseif (strlen($name) > 50) {
        $error = 'Tag name must not exceed 50 characters';
    } else {
        // Check if tag already exists
        $check_query = "SELECT id FROM tags WHERE name = ? OR slug = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Tag with this name already exists';
        } else {
            // Insert new tag
            $insert_query = "INSERT INTO tags (name, slug, description, color, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssss', $name, $slug, $description, $color, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Tag '$name' created successfully!";
                // Clear form
                $_POST = [];
            } else {
                $error = "Error creating tag: " . mysqli_error($conn);
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
    <title>Add Tag - PK Live News Admin</title>
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
                        <h1 class="h3 mb-0">Add New Tag</h1>
                        <small>Create a new content tag</small>
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

                <!-- Add Tag Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" id="tagForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tag Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                               placeholder="Enter tag name" required>
                                        <div class="form-text">Must be 2-50 characters</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">URL Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo htmlspecialchars(slugify($_POST['name'] ?? '')); ?>" 
                                               placeholder="Auto-generated from name" readonly>
                                        <div class="form-text">URL-friendly version of the name</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="4" placeholder="Describe what this tag is used for"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Color</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="color-preview" id="colorPreview" style="background-color: #007bff;"></div>
                                            <input type="color" class="form-control form-control-color" id="color" name="color" 
                                                   value="<?php echo htmlspecialchars($_POST['color'] ?? '#007bff'); ?>">
                                            <div class="color-options">
                                                <div class="color-option selected" style="background-color: #007bff;" data-color="#007bff"></div>
                                                <div class="color-option" style="background-color: #28a745;" data-color="#28a745"></div>
                                                <div class="color-option" style="background-color: #dc3545;" data-color="#dc3545"></div>
                                                <div class="color-option" style="background-color: #ffc107;" data-color="#ffc107"></div>
                                                <div class="color-option" style="background-color: #17a2b8;" data-color="#17a2b8"></div>
                                                <div class="color-option" style="background-color: #6f42c1;" data-color="#6f42c1"></div>
                                                <div class="color-option" style="background-color: #e83e8c;" data-color="#e83e8c"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                        <div class="form-text">Active tags can be used in content</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Create Tag
                                        </button>
                                        <a href="manage-tags-complete.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Popular Tags -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-fire me-2"></i>Popular Tag Colors</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #007bff;" data-color="#007bff"></div>
                                <small>Primary</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #28a745;" data-color="#28a745"></div>
                                <small>Success</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #dc3545;" data-color="#dc3545"></div>
                                <small>Danger</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #ffc107;" data-color="#ffc107"></div>
                                <small>Warning</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #17a2b8;" data-color="#17a2b8"></div>
                                <small>Info</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #6f42c1;" data-color="#6f42c1"></div>
                                <small>Purple</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #e83e8c;" data-color="#e83e8c"></div>
                                <small>Pink</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="color-option" style="background-color: #6c757d;" data-color="#6c757d"></div>
                                <small>Gray</small>
                            </div>
                        </div>
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

        // Color picker functionality
        const colorInput = document.getElementById('color');
        const colorPreview = document.getElementById('colorPreview');
        
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
        });

        // Color preset selection
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                const color = this.dataset.color;
                colorInput.value = color;
                colorPreview.style.backgroundColor = color;
                
                // Update selected state
                document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
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

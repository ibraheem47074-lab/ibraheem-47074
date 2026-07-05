<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $position = clean_input($_POST['position']);
    $status = clean_input($_POST['status']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $code = clean_input($_POST['code']);
    $image_path = '';
    
    // Validate dates
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($start_date) > strtotime($end_date)) {
            $error = 'End date must be after start date';
        }
    }
    
    if (empty($error)) {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_extensions)) {
                if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                    $file_name = uniqid() . '.' . $file_extension;
                    $upload_path = UPLOAD_PATH . 'ads/' . $file_name;
                    
                    if (!file_exists('../' . UPLOAD_PATH . 'ads/')) {
                        mkdir('../' . UPLOAD_PATH . 'ads/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $upload_path)) {
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
            // Insert advertisement
            $query = "INSERT INTO advertisements (title, image, code, position, status, start_date, end_date) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssssss', $title, $image_path, $code, $position, $status, $start_date, $end_date);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Advertisement created successfully!";
                $_POST = array();
            } else {
                $error = "Error creating advertisement: " . mysqli_error($conn);
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
    <title>Add Advertisement - PK Live News Admin</title>
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
        .ad-preview {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .ad-preview.has-content {
            border-style: solid;
            border-color: #667eea;
            background: white;
        }
        .position-card {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .position-card:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .position-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .position-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 600;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .code-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            max-height: 150px;
            overflow-y: auto;
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
                            <a class="nav-link active" href="manage-ads.php">
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
                        <h1 class="h3 mb-0">Add Advertisement</h1>
                        <small>Create new advertisement for your website</small>
                    </div>
                    <div>
                        <a href="manage-ads.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Ads
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

                <!-- Add Advertisement Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="adForm">
                            <!-- Advertisement Title -->
                            <div class="mb-4">
                                <label for="title" class="form-label">Advertisement Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                       placeholder="Enter advertisement title" 
                                       required maxlength="255">
                                <small class="text-muted">Internal title for tracking purposes</small>
                            </div>

                            <!-- Advertisement Position -->
                            <div class="mb-4">
                                <label class="form-label">Advertisement Position *</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="position-card" onclick="selectPosition('header')">
                                            <div class="position-icon">
                                                <i class="fas fa-window-maximize"></i>
                                            </div>
                                            <h6>Header Banner</h6>
                                            <small>Top of every page</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="position" id="position_header" 
                                                       value="header" <?php echo (isset($_POST['position']) && $_POST['position'] == 'header') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="position_header"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="position-card" onclick="selectPosition('sidebar')">
                                            <div class="position-icon">
                                                <i class="fas fa-columns"></i>
                                            </div>
                                            <h6>Sidebar</h6>
                                            <small>Right sidebar area</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="position" id="position_sidebar" 
                                                       value="sidebar" <?php echo (isset($_POST['position']) && $_POST['position'] == 'sidebar') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="position_sidebar"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="position-card" onclick="selectPosition('footer')">
                                            <div class="position-icon">
                                                <i class="fas fa-window-restore"></i>
                                            </div>
                                            <h6>Footer</h6>
                                            <small>Bottom of pages</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="position" id="position_footer" 
                                                       value="footer" <?php echo (isset($_POST['position']) && $_POST['position'] == 'footer') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="position_footer"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="position-card" onclick="selectPosition('popup')">
                                            <div class="position-icon">
                                                <i class="fas fa-external-link-alt"></i>
                                            </div>
                                            <h6>Popup</h6>
                                            <small>Modal overlay</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="position" id="position_popup" 
                                                       value="popup" <?php echo (isset($_POST['position']) && $_POST['position'] == 'popup') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="position_popup"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advertisement Content -->
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Image Upload -->
                                    <div class="mb-4">
                                        <label for="image" class="form-label">Advertisement Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                        <small class="text-muted">Upload image file (JPG, PNG, GIF, WebP)</small>
                                        
                                        <!-- Image Preview -->
                                        <div id="imagePreview" class="mt-3" style="display: none;">
                                            <h6>Image Preview:</h6>
                                            <img id="previewImg" class="image-preview" alt="Advertisement preview">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Custom Code -->
                                    <div class="mb-4">
                                        <label for="code" class="form-label">Custom HTML Code</label>
                                        <textarea class="form-control" id="code" name="code" rows="6" 
                                                  placeholder="Enter custom HTML/JavaScript code (optional)"><?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : ''; ?></textarea>
                                        <small class="text-muted">Or paste custom ad code (Google Ads, etc.)</small>
                                        
                                        <!-- Code Preview -->
                                        <div id="codePreview" class="mt-3" style="display: none;">
                                            <h6>Code Preview:</h6>
                                            <div class="code-preview" id="codeDisplay"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Preview -->
                            <div class="mb-4">
                                <label class="form-label">Live Preview</label>
                                <div id="livePreview" class="ad-preview">
                                    <div class="text-muted">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <h6>Advertisement Preview</h6>
                                        <p>Upload an image or add custom code to see preview</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <!-- Status -->
                                    <div class="mb-4">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active (Show Immediately)</option>
                                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Draft)</option>
                                        </select>
                                        <small class="text-muted">Choose whether to display this advertisement</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <!-- Start Date -->
                                    <div class="mb-4">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                                        <small class="text-muted">When to start showing this ad</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <!-- End Date -->
                                    <div class="mb-4">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                                        <small class="text-muted">When to stop showing this ad</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Advertisement
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-times me-2"></i>Clear Form
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="saveAsDraft()">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Advertisement Guidelines -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Advertisement Guidelines
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-ruler-combined me-2 text-primary"></i>Recommended Sizes</h6>
                                <ul class="small">
                                    <li><strong>Header Banner:</strong> 728×90px (Leaderboard)</li>
                                    <li><strong>Sidebar:</strong> 300×250px (Medium Rectangle)</li>
                                    <li><strong>Footer:</strong> 970×90px (Large Leaderboard)</li>
                                    <li><strong>Popup:</strong> 600×400px (Modal)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-check-circle me-2 text-success"></i>Best Practices</h6>
                                <ul class="small">
                                    <li>Use high-quality images</li>
                                    <li>Keep file sizes under 500KB</li>
                                    <li>Ensure mobile-friendly designs</li>
                                    <li>Test ads across different browsers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Position selection
        function selectPosition(position) {
            // Remove selected class from all cards
            document.querySelectorAll('.position-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById('position_' + position).checked = true;
        }

        // Initialize selected position
        document.addEventListener('DOMContentLoaded', function() {
            const selectedPosition = document.querySelector('input[name="position"]:checked');
            if (selectedPosition) {
                const positionCard = document.querySelector(`.position-card[onclick*="${selectedPosition.value}"]`);
                if (positionCard) {
                    positionCard.classList.add('selected');
                }
            }
        });

        // Image preview
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    updateLivePreview();
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                updateLivePreview();
            }
        }

        // Code preview
        document.getElementById('code').addEventListener('input', function() {
            const code = this.value;
            const codePreview = document.getElementById('codePreview');
            const codeDisplay = document.getElementById('codeDisplay');
            
            if (code.trim()) {
                codeDisplay.textContent = code;
                codePreview.style.display = 'block';
            } else {
                codePreview.style.display = 'none';
            }
            
            updateLivePreview();
        });

        // Update live preview
        function updateLivePreview() {
            const livePreview = document.getElementById('livePreview');
            const imageFile = document.getElementById('image').files[0];
            const code = document.getElementById('code').value;
            
            if (imageFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    livePreview.innerHTML = `<img src="${e.target.result}" class="img-fluid" alt="Advertisement preview">`;
                    livePreview.classList.add('has-content');
                }
                reader.readAsDataURL(imageFile);
            } else if (code.trim()) {
                livePreview.innerHTML = `<div class="code-preview">${code}</div>`;
                livePreview.classList.add('has-content');
            } else {
                livePreview.innerHTML = `
                    <div class="text-muted">
                        <i class="fas fa-image fa-3x mb-3"></i>
                        <h6>Advertisement Preview</h6>
                        <p>Upload an image or add custom code to see preview</p>
                    </div>
                `;
                livePreview.classList.remove('has-content');
            }
        }

        // Reset form
        function resetForm() {
            document.getElementById('adForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('codePreview').style.display = 'none';
            document.querySelectorAll('.position-card').forEach(card => {
                card.classList.remove('selected');
            });
            updateLivePreview();
        }

        // Save as draft
        function saveAsDraft() {
            document.getElementById('status').value = 'inactive';
            document.getElementById('adForm').submit();
        }

        // Form validation
        document.getElementById('adForm').addEventListener('submit', function(e) {
            const position = document.querySelector('input[name="position"]:checked');
            const image = document.getElementById('image').files[0];
            const code = document.getElementById('code').value;
            
            if (!position) {
                e.preventDefault();
                alert('Please select an advertisement position');
                return false;
            }
            
            if (!image && !code.trim()) {
                e.preventDefault();
                alert('Please upload an image or add custom HTML code');
                return false;
            }
            
            // Validate dates
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            return true;
        });

        // Auto-update preview on form changes
        document.getElementById('title').addEventListener('input', updateLivePreview);
        document.getElementById('status').addEventListener('change', updateLivePreview);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        document.getElementById('adForm').submit();
                        break;
                    case 'd':
                        e.preventDefault();
                        resetForm();
                        break;
                    case 'a':
                        e.preventDefault();
                        saveAsDraft();
                        break;
                }
            }
        });

        // File size validation
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file && file.size > maxSize) {
                alert('File size too large. Maximum size is 5MB');
                this.value = '';
                document.getElementById('imagePreview').style.display = 'none';
                updateLivePreview();
            }
        });

        // Position tooltips
        document.querySelectorAll('.position-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.2)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>

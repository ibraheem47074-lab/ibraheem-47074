<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($ad_id === 0) {
    redirect('manage-ads.php');
}

// Get advertisement
$query = "SELECT * FROM advertisements WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $ad_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ad = mysqli_fetch_assoc($result);

if (!$ad) {
    redirect('manage-ads.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $position = clean_input($_POST['position']);
    $status = clean_input($_POST['status']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $code = clean_input($_POST['code']);
    $image_path = $ad['image']; // Keep existing image by default
    
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
                    // Delete old image if exists
                    if ($ad['image'] && file_exists('../' . $ad['image'])) {
                        unlink('../' . $ad['image']);
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
    
    // Validate dates
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($start_date) > strtotime($end_date)) {
            $error = "End date must be after start date";
        }
    }
    
    if (empty($error)) {
        // Update advertisement
        $query = "UPDATE advertisements SET title = ?, position = ?, status = ?, start_date = ?, 
                  end_date = ?, code = ?, image = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $position, $status, $start_date, 
                               $end_date, $code, $image_path, $ad_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Advertisement updated successfully!";
            // Refresh ad data
            $ad = array_merge($ad, $_POST);
            $ad['image'] = $image_path;
        } else {
            $error = "Error updating advertisement: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Advertisement - PK Live News Admin</title>
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
        .image-preview {
            max-width: 200px;
            max-height: 150px;
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
        .ad-stats {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
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
                        <h1 class="h3 mb-0">Edit Advertisement</h1>
                        <small>Update advertisement content and settings</small>
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

                <!-- Edit Advertisement Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="adForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Title -->
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Advertisement Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($ad['title']); ?>" 
                                               placeholder="Enter advertisement title" 
                                               required maxlength="255">
                                        <small class="text-muted">Internal title for tracking purposes</small>
                                    </div>

                                    <!-- Advertisement Position -->
                                    <div class="mb-3">
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
                                                               value="header" <?php echo ($ad['position'] == 'header') ? 'checked' : ''; ?>>
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
                                                               value="sidebar" <?php echo ($ad['position'] == 'sidebar') ? 'checked' : ''; ?>>
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
                                                               value="footer" <?php echo ($ad['position'] == 'footer') ? 'checked' : ''; ?>>
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
                                                               value="popup" <?php echo ($ad['position'] == 'popup') ? 'checked' : ''; ?>>
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
                                            <div class="mb-3">
                                                <label for="image" class="form-label">Advertisement Image</label>
                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                <small class="text-muted">Upload new image to replace current</small>
                                                
                                                <!-- Current Image Preview -->
                                                <?php if ($ad['image']): ?>
                                                    <div class="mt-3">
                                                        <h6>Current Image:</h6>
                                                        <img src="<?php echo htmlspecialchars($ad['image']); ?>" 
                                                             class="image-preview" alt="Current advertisement image">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <!-- Custom Code -->
                                            <div class="mb-3">
                                                <label for="code" class="form-label">Custom HTML Code</label>
                                                <textarea class="form-control" id="code" name="code" rows="6" 
                                                          placeholder="Enter custom HTML/JavaScript code (optional)"><?php echo htmlspecialchars($ad['code']); ?></textarea>
                                                <small class="text-muted">Or paste custom ad code (Google Ads, etc.)</small>
                                                
                                                <!-- Code Preview -->
                                                <div id="codePreview" class="mt-3" style="display: none;">
                                                    <h6>Code Preview:</h6>
                                                    <div class="code-preview" id="codeDisplay"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo ($ad['status'] == 'active') ? 'selected' : ''; ?>>Active (Show Immediately)</option>
                                            <option value="inactive" <?php echo ($ad['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Draft)</option>
                                        </select>
                                        <small class="text-muted">Choose whether to display this advertisement</small>
                                    </div>
                                    
                                    <!-- Start Date -->
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="<?php echo htmlspecialchars($ad['start_date']); ?>">
                                        <small class="text-muted">When to start showing this ad</small>
                                    </div>
                                    
                                    <!-- End Date -->
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="<?php echo htmlspecialchars($ad['end_date']); ?>">
                                        <small class="text-muted">When to stop showing this ad</small>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Advertisement
                                        </button>
                                        <a href="manage-ads.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Advertisement Statistics -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Advertisement Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary"><?php echo number_format($ad['clicks'] ?? 0); ?></h4>
                                    <small class="text-muted">Total Clicks</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success">
                                        <?php 
                                        $days_active = $ad['start_date'] ? 
                                            (strtotime($ad['end_date']) > time() ? 
                                                floor((time() - strtotime($ad['start_date'])) / 86400) : 
                                                floor((strtotime($ad['end_date']) - strtotime($ad['start_date'])) / 86400)) : 0;
                                        echo number_format($days_active ?? 0);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Days Active</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info"><?php echo ucfirst($ad['position']); ?></h4>
                                    <small class="text-muted">Position</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning"><?php echo ucfirst($ad['status']); ?></h4>
                                    <small class="text-muted">Current Status</small>
                                </div>
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
        });

        // Form validation
        document.getElementById('adForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const position = document.querySelector('input[name="position"]:checked');
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!title) {
                e.preventDefault();
                alert('Please enter advertisement title');
                return false;
            }
            
            if (!position) {
                e.preventDefault();
                alert('Please select an advertisement position');
                return false;
            }
            
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('End date must be after start date');
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
                    preview.alt = 'New advertisement image';
                    
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

        // Auto-save draft
        let autoSaveTimer;
        function autoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = new FormData(document.getElementById('adForm'));
                formData.append('auto_save', '1');
                
                fetch('edit-ad.php?id=<?php echo $ad_id; ?>', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(data => console.log('Auto-saved'))
                  .catch(error => console.error('Auto-save failed:', error));
            }, 30000); // Auto-save after 30 seconds of inactivity
        }

        // Listen for changes
        document.getElementById('title').addEventListener('input', autoSave);
        document.getElementById('code').addEventListener('input', autoSave);
        document.getElementById('start_date').addEventListener('change', autoSave);
        document.getElementById('end_date').addEventListener('change', autoSave);
    </script>
</body>
</html>

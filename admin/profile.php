<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $bio = clean_input($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle image upload
    $image_path = $user['image'] ?? ''; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['image']['size'] <= MAX_FILE_SIZE) {
                $file_name = uniqid() . '.' . $file_extension;
                $upload_path = UPLOAD_PATH . 'users/' . $file_name;
                
                if (!file_exists('../' . UPLOAD_PATH . 'users/')) {
                    mkdir('../' . UPLOAD_PATH . 'users/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $upload_path)) {
                    // Delete old image if exists
                    if (!empty($user['image']) && file_exists('../' . $user['image'])) {
                        unlink('../' . $user['image']);
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
    
    // Validate email uniqueness
    if ($email !== $user['email']) {
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'si', $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email address already exists. Please choose a different email.";
        }
    }
    
    // Handle password change
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $error = "Please enter your current password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect";
        } elseif (empty($new_password)) {
            $error = "Please enter a new password";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        }
    }
    
    if (empty($error)) {
        // Check which columns exist in users table
        $columns_result = mysqli_query($conn, "SHOW COLUMNS FROM users");
        $existing_columns = [];
        while ($column = mysqli_fetch_assoc($columns_result)) {
            $existing_columns[] = $column['Field'];
        }
        
        // Build dynamic update query based on existing columns
        $update_fields = [];
        $update_types = [];
        $update_values = [];
        
        if (in_array('name', $existing_columns)) {
            $update_fields[] = "name = ?";
            $update_types[] = "s";
            $update_values[] = $name;
        }
        
        if (in_array('email', $existing_columns)) {
            $update_fields[] = "email = ?";
            $update_types[] = "s";
            $update_values[] = $email;
        }
        
        if (in_array('phone', $existing_columns)) {
            $update_fields[] = "phone = ?";
            $update_types[] = "s";
            $update_values[] = $phone;
        }
        
        if (in_array('bio', $existing_columns)) {
            $update_fields[] = "bio = ?";
            $update_types[] = "s";
            $update_values[] = $bio;
        }
        
        if (in_array('image', $existing_columns)) {
            $update_fields[] = "image = ?";
            $update_types[] = "s";
            $update_values[] = $image_path;
        }
        
        // Add password to update if provided
        if (!empty($new_password)) {
            $update_fields[] = "password = ?";
            $update_types[] = "s";
            $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $update_types[] = "i";
        $update_values[] = $user_id;
        
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, implode('', $update_types), ...$update_values);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Refresh user data
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['bio'] = $bio;
            $user['image'] = $image_path;
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PK Live News Admin</title>
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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .form-label {
            font-weight: 600;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
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
                            <a class="nav-link active" href="profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-main-content">
                <!-- Profile Header -->
                <div class="profile-header text-center">
                    <div class="container">
                        <?php if (!empty($user['image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['image']); ?>" 
                                 class="profile-image mb-3" alt="Profile Picture">
                        <?php else: ?>
                            <div class="profile-image mb-3 bg-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h2 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="badge bg-light text-dark"><?php echo ucfirst($user['role']); ?></span>
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

                <!-- Profile Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="profileForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-4">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h5>
                                    
                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" 
                                               placeholder="Enter your full name" 
                                               required maxlength="100">
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                                               placeholder="Enter your email" 
                                               required maxlength="255">
                                    </div>

                                    <!-- Phone -->
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                               placeholder="Enter your phone number" 
                                               maxlength="20">
                                    </div>

                                    <!-- Bio -->
                                    <div class="mb-3">
                                        <label for="bio" class="form-label">Bio</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                                  placeholder="Tell us about yourself"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                        <small class="text-muted">Brief description about yourself</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-4">
                                        <i class="fas fa-camera me-2"></i>Profile Picture
                                    </h5>
                                    
                                    <!-- Image Upload -->
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Upload New Picture</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">Upload new profile picture (JPG, PNG, GIF, WebP)</small>
                                        
                                        <!-- Current Image Preview -->
                                        <?php if (!empty($user['image'])): ?>
                                            <div class="mt-3">
                                                <h6>Current Picture:</h6>
                                                <img src="<?php echo htmlspecialchars($user['image']); ?>" 
                                                     class="image-preview" alt="Current profile picture">
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="mb-4 mt-4">
                                        <i class="fas fa-lock me-2"></i>Change Password
                                    </h5>
                                    
                                    <!-- Current Password -->
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" 
                                               placeholder="Enter current password to change">
                                        <small class="text-muted">Leave empty if you don't want to change password</small>
                                    </div>

                                    <!-- New Password -->
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" 
                                               placeholder="Enter new password">
                                        <div id="passwordStrength" class="password-strength"></div>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-times me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Statistics -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Account Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary">
                                        <?php
                                        $news_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id")->fetch_assoc()['count'];
                                        echo number_format($news_count ?? 0);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Articles Published</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success">
                                        <?php
                                        $comments_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id")->fetch_assoc()['count'];
                                        echo number_format($comments_count ?? 0);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Comments Made</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info">
                                        <?php echo date('Y', strtotime($user['created_at'])); ?>
                                    </h4>
                                    <small class="text-muted">Member Since</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning">
                                        <?php echo ucfirst($user['role']); ?>
                                    </h4>
                                    <small class="text-muted">Account Role</small>
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
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!name) {
                e.preventDefault();
                alert('Please enter your full name');
                return false;
            }
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address');
                return false;
            }
            
            // Validate password fields only if current password is entered
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match');
                    return false;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('New password must be at least 6 characters long');
                    return false;
                }
            }
            
            return true;
        });

        // Reset form
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? Any unsaved changes will be lost.')) {
                document.getElementById('profileForm').reset();
                document.getElementById('passwordStrength').className = 'password-strength';
            }
        }

        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'image-preview mt-3';
                    preview.alt = 'New profile picture';
                    
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

        // Auto-save profile picture
        let autoSaveTimer;
        function autoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Implement auto-save functionality if needed
                console.log('Auto-save triggered');
            }, 5000);
        }

        // Listen for changes
        document.getElementById('name').addEventListener('input', autoSave);
        document.getElementById('email').addEventListener('input', autoSave);
        document.getElementById('phone').addEventListener('input', autoSave);
        document.getElementById('bio').addEventListener('input', autoSave);
    </script>
</body>
</html>

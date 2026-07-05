<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = clean_input($_POST['name']);
        $email = clean_input($_POST['email']);
        $bio = clean_input($_POST['bio']);
        $phone = clean_input($_POST['phone']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } else {
            // Check if email is already taken by another user
            $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $email_check);
            mysqli_stmt_bind_param($stmt, 'si', $email, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $error = "Email is already taken by another user!";
            } else {
                // Update profile
                $update_query = "UPDATE users SET name = ?, email = ?, bio = ?, phone = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $bio, $phone, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Profile updated successfully!";
                    // Update session
                    $_SESSION['user_name'] = $name;
                } else {
                    $error = "Failed to update profile!";
                }
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current user data
        $user_query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        
        // Verify current password
        if (!password_verify($current_password, $user_data['password'])) {
            $error = "Current password is incorrect!";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long!";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $password_query);
            mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password!";
            }
        }
    }
    
    // Handle avatar upload
    if (isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
        $avatar = $_FILES['avatar'];
        
        // Validate file
        if ($avatar['error'] !== UPLOAD_ERR_OK) {
            $error = "Error uploading file!";
        } elseif (!in_array(mime_content_type($avatar['tmp_name']), ['image/jpeg', 'image/png', 'image/gif'])) {
            $error = "Only JPG, PNG, and GIF images are allowed!";
        } elseif ($avatar['size'] > 2097152) { // 2MB
            $error = "File size must be less than 2MB!";
        } else {
            // Generate unique filename
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . pathinfo($avatar['name'], PATHINFO_EXTENSION);
            $upload_path = '../uploads/avatars/' . $filename;
            
            // Create directory if it doesn't exist
            if (!is_dir('../uploads/avatars/')) {
                mkdir('../uploads/avatars/', 0755, true);
            }
            
            // Move file
            if (move_uploaded_file($avatar['tmp_name'], $upload_path)) {
                // Update database
                $avatar_query = "UPDATE users SET avatar = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $avatar_query);
                mysqli_stmt_bind_param($stmt, 'si', $filename, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Avatar uploaded successfully!";
                } else {
                    $error = "Failed to update avatar in database!";
                }
            } else {
                $error = "Failed to upload file!";
            }
        }
    }
}

// Get user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get user statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id"))['count'];
$published_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE author_id = $user_id AND status = 'published'"))['count'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM news WHERE author_id = $user_id"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PK Live News Reporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f39c12;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/reporter-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-user me-3"></i>My Profile</h2>
                <p class="text-muted">Manage your personal information and account settings.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Info -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo !empty($user['avatar']) ? '../uploads/avatars/' . htmlspecialchars($user['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&size=120&background=f39c12&color=fff'; ?>" 
                             alt="Profile Avatar" class="profile-avatar mb-3">
                        
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted">Reporter</p>
                        
                        <div class="text-start mt-4">
                            <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                            <?php if (!empty($user['phone'])): ?>
                                <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                            <?php endif; ?>
                            <p><i class="fas fa-calendar me-2"></i> Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                            <?php if (!empty($user['last_login'])): ?>
                                <p><i class="fas fa-clock me-2"></i> Last login: <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Avatar Upload -->
                        <div class="mt-3">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <label class="form-label">Update Avatar</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                </div>
                                <button type="submit" name="upload_avatar" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-upload me-1"></i>Upload Avatar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-lg-8 mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary text-white">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <h3><?php echo $total_articles; ?></h3>
                            <p class="text-muted mb-0">Total Articles</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-success text-white">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3><?php echo $published_articles; ?></h3>
                            <p class="text-muted mb-0">Published</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning text-white">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3><?php echo number_format($total_views); ?></h3>
                            <p class="text-muted mb-0">Total Views</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Edit Profile -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bio</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

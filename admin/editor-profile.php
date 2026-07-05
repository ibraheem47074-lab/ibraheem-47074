<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
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
            $error = "Upload error: Code " . $avatar['error'] . " - Check file size and permissions";
        } elseif (!isset($avatar['tmp_name']) || $avatar['tmp_name'] === '') {
            $error = "No file was uploaded!";
        } elseif (!file_exists($avatar['tmp_name'])) {
            $error = "Temporary file not found!";
        } elseif ($avatar['size'] > 5242880) { // 5MB limit
            $error = "File size must be less than 5MB! Current size: " . number_format($avatar['size'] / 1024 / 1024, 2) . "MB";
        } else {
            // Check file type using multiple methods
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_type = finfo_file($finfo, $avatar['tmp_name']);
            finfo_close($finfo);
            
            // Also check extension
            $extension = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($detected_type, $allowed_types) || !in_array($extension, $allowed_extensions)) {
                $error = "Only JPG, PNG, GIF, and WebP images are allowed! Detected: $detected_type, Extension: $extension";
            } else {
                // Create uploads directory if it doesn't exist
                $uploads_dir = '../uploads/avatars/';
                if (!is_dir($uploads_dir)) {
                    if (!mkdir($uploads_dir, 0755, true)) {
                        $error = "Failed to create uploads directory! Please create it manually.";
                    }
                }
                
                // Check if directory is writable
                if (is_dir($uploads_dir) && !is_writable($uploads_dir)) {
                    $error = "Uploads directory is not writable! Please check permissions.";
                } else {
                    // Generate unique filename
                    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
                    $upload_path = $uploads_dir . $filename;
                    
                    // Delete old avatar if exists
                    $avatar_column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar'");
                    if (mysqli_num_rows($avatar_column_check) > 0) {
                        $current_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT avatar FROM users WHERE id = $user_id"));
                        if ($current_user['avatar'] && file_exists($uploads_dir . $current_user['avatar'])) {
                            unlink($uploads_dir . $current_user['avatar']);
                        }
                    }
                    
                    // Move file
                    if (move_uploaded_file($avatar['tmp_name'], $upload_path)) {
                        // Update database - only if avatar column exists
                        $avatar_column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar'");
                        if (mysqli_num_rows($avatar_column_check) > 0) {
                            $avatar_query = "UPDATE users SET avatar = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $avatar_query);
                            mysqli_stmt_bind_param($stmt, 'si', $filename, $user_id);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                $success = "Avatar uploaded successfully!";
                            } else {
                                $error = "Failed to update avatar in database!";
                            }
                        } else {
                            // Avatar column doesn't exist, but file was uploaded successfully
                            $success = "Avatar uploaded successfully! (Note: Avatar column not in database)";
                        }
                    } else {
                        $error = "Failed to move uploaded file! Check directory permissions.";
                    }
                }
            }
        }
    }
}

// Get user data - handle missing avatar and last_login columns
$avatar_column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar'");
$last_login_column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'last_login'");

if (mysqli_num_rows($avatar_column_check) > 0 && mysqli_num_rows($last_login_column_check) > 0) {
    $user_query = "SELECT * FROM users WHERE id = ?";
} elseif (mysqli_num_rows($avatar_column_check) > 0) {
    $user_query = "SELECT id, name, email, bio, phone, role, status, created_at FROM users WHERE id = ?";
} else {
    $user_query = "SELECT id, name, email, bio, phone, role, status, created_at FROM users WHERE id = ?";
}
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get editor statistics
$articles_reviewed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status = 'published' AND updated_at > created_at"))['count'];
$comments_moderated = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM comments WHERE status IS NOT NULL"))['count'];
$total_articles_managed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$recent_activity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE DATE(updated_at) = CURDATE()"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Profile - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .editor-header {
            background: linear-gradient(135deg, #4834d4 0%, #686de0 100%);
            color: white;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4834d4;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #4834d4;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #4834d4;
        }
        .activity-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -2.3rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4834d4;
            border: 2px solid white;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-user me-3"></i>Editor Profile</h2>
                <p class="text-muted">Manage your profile and track your editorial performance.</p>
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
                        <img src="<?php echo (!empty($user['avatar']) && isset($user['avatar'])) ? '../uploads/avatars/' . htmlspecialchars($user['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&size=120&background=4834d4&color=fff'; ?>" 
                             alt="Profile Avatar" class="profile-avatar mb-3">
                        
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted">Editor</p>
                        
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

            <!-- Editor Statistics -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Editor Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon bg-primary text-white">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <h3><?php echo $total_articles_managed; ?></h3>
                                    <p class="text-muted mb-0">Articles Managed</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon bg-success text-white">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h3><?php echo $articles_reviewed; ?></h3>
                                    <p class="text-muted mb-0">Articles Reviewed</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon bg-info text-white">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <h3><?php echo $comments_moderated; ?></h3>
                                    <p class="text-muted mb-0">Comments Moderated</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon bg-warning text-white">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h3><?php echo $recent_activity; ?></h3>
                                    <p class="text-muted mb-0">Today's Activity</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Activity Timeline -->
                        <div class="mt-4">
                            <h6><i class="fas fa-history me-2"></i>Recent Editorial Activity</h6>
                            <div class="activity-timeline">
                                <?php
                                $activities_query = "SELECT 'article_published' as type, n.title COLLATE utf8mb4_unicode_ci as title, n.updated_at as date, u.name COLLATE utf8mb4_unicode_ci as target_name
                                FROM news n 
                                LEFT JOIN users u ON n.author_id = u.id 
                                WHERE n.status = 'published' AND n.updated_at > n.created_at
                                UNION ALL
                                SELECT 'comment_moderated' as type, cm.comment COLLATE utf8mb4_unicode_ci as title, cm.updated_at as date, cm.name COLLATE utf8mb4_unicode_ci as target_name
                                FROM comments cm 
                                WHERE cm.status IS NOT NULL
                                ORDER BY date DESC LIMIT 5";
                                $activities = mysqli_query($conn, $activities_query);
                                
                                while ($activity = mysqli_fetch_assoc($activities)):
                                ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>
                                                    <?php 
                                                    if ($activity['type'] === 'article_published') {
                                                        echo '📝 Published Article';
                                                    } else {
                                                        echo '💬 Moderated Comment';
                                                    }
                                                    ?>
                                                </strong>
                                                <p class="mb-1 small text-muted">
                                                    <?php echo htmlspecialchars(substr($activity['title'], 0, 60)) . '...'; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($activity['target_name'] ?? 'Unknown'); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo time_ago($activity['date']); ?>
                                        </small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
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

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <a href="editor-dashboard.php" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="manage-news.php" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-newspaper me-1"></i>Manage News
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="editor-comments.php" class="btn btn-info btn-sm w-100">
                                    <i class="fas fa-comments me-1"></i>Manage Comments
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="editor-polls.php" class="btn btn-purple btn-sm w-100" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none;">
                                    <i class="fas fa-poll me-1"></i>Manage Polls
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="editor-statistics.php" class="btn btn-warning btn-sm w-100">
                                    <i class="fas fa-chart-line me-1"></i>View Statistics
                                </a>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="logout.php" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

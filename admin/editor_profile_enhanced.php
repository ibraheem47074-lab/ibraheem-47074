<?php
require_once '../config/database.php';

// Check if user is logged in and is editor
if (!is_logged_in() || !is_editor()) {
    redirect('login.php');
}

$editor_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $bio = clean_input($_POST['bio']);
    
    // Handle image upload
    $image = $_SESSION['user_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/users/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image = $filename;
            }
        }
    }
    
    // Update profile
    $query = "UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, image = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $phone, $bio, $image, $editor_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_image'] = $image;
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile. Please try again.";
    }
}

// Get editor information
$editor_query = "SELECT * FROM users WHERE id = $editor_id";
$editor_result = mysqli_query($conn, $editor_query);
$editor = mysqli_fetch_assoc($editor_result);

// Get editor's statistics
$stats_query = "SELECT 
    COUNT(*) as total_articles_reviewed,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as articles_approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as articles_rejected,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as articles_pending,
    SUM(views) as total_views_managed
    FROM news WHERE updated_by = $editor_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get articles reviewed by editor
$reviewed_articles_query = "SELECT n.*, c.name as category_name, u.name as author_name
                           FROM news n 
                           LEFT JOIN categories c ON n.category_id = c.id 
                           LEFT JOIN users u ON n.author_id = u.id 
                           WHERE n.updated_by = $editor_id 
                           ORDER BY n.updated_at DESC LIMIT 5";
$recent_reviewed = mysqli_query($conn, $reviewed_articles_query);

// Get comments moderated by editor
$comments_moderated_query = "SELECT COUNT(*) as total_comments FROM comments c 
                            WHERE c.moderated_by = $editor_id AND c.status = 'approved'";
$total_comments_moderated = mysqli_fetch_assoc(mysqli_query($conn, $comments_moderated_query))['total_comments'];

// Helper function for time ago
if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('M d, Y', $time);
        }
    }
}
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
        .editor-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .editor-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .editor-sidebar .nav-link:hover,
        .editor-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .editor-main-content {
            background-color: #f7fafc;
            min-height: 100vh;
        }
        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #4834d4;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
            border-left: 4px solid #4834d4;
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
            margin: 0 auto 15px;
        }
        .article-preview {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
            border-left: 4px solid #4834d4;
        }
        .article-preview:hover {
            transform: translateY(-2px);
        }
        .edit-profile-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .upload-avatar-btn {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .upload-avatar-btn input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .badge-role {
            background: linear-gradient(45deg, #4834d4, #686de0);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
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
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block editor-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Editor Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="editor-dashboard-enhanced.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage-news.php">
                                <i class="fas fa-newspaper me-2"></i>Manage News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-comments.php">
                                <i class="fas fa-comments me-2"></i>Manage Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-polls.php">
                                <i class="fas fa-poll me-2"></i>Manage Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="editor-statistics.php">
                                <i class="fas fa-chart-line me-2"></i>Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="editor_profile_enhanced.php">
                                <i class="fas fa-user me-2"></i>My Profile
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 editor-main-content">
                <!-- Header -->
                <div class="editor-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-user me-2"></i>Editor Profile
                        </h1>
                        <small>Manage your professional profile and view your editorial statistics</small>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo $editor['image'] ? '../uploads/users/' . htmlspecialchars($editor['image']) : 'https://picsum.photos/seed/editor/150/150.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($editor['name']); ?>" 
                                 class="profile-avatar mb-3">
                            <div class="upload-avatar-btn">
                                <label for="avatarUpload" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-camera me-1"></i>Change Photo
                                </label>
                                <input type="file" id="avatarUpload" accept="image/*" onchange="document.getElementById('profileForm').submit()">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex align-items-center mb-3">
                                <h2 class="mb-0 me-3"><?php echo htmlspecialchars($editor['name']); ?></h2>
                                <span class="badge-role">
                                    <i class="fas fa-edit me-1"></i><?php echo ucfirst($editor['role']); ?>
                                </span>
                            </div>
                            
                            <?php if ($editor['bio']): ?>
                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($editor['bio'])); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-info-circle me-2"></i>No bio added yet. Tell readers about your editorial expertise!
                                </p>
                            <?php endif; ?>
                            
                            <div class="row text-muted">
                                <div class="col-md-4">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?php echo htmlspecialchars($editor['email']); ?>
                                </div>
                                <?php if ($editor['phone']): ?>
                                    <div class="col-md-4">
                                        <i class="fas fa-phone me-2"></i>
                                        <?php echo htmlspecialchars($editor['phone']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <i class="fas fa-calendar me-2"></i>
                                    Joined <?php echo date('M Y', strtotime($editor['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Editorial Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary text-white">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <h4><?php echo $stats['total_articles_reviewed']; ?></h4>
                            <small>Articles Reviewed</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-success text-white">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4><?php echo $stats['articles_approved']; ?></h4>
                            <small>Approved</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-danger text-white">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h4><?php echo $stats['articles_rejected']; ?></h4>
                            <small>Rejected</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning text-white">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h4><?php echo $stats['articles_pending']; ?></h4>
                            <small>Pending</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-info text-white">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h4><?php echo $total_comments_moderated; ?></h4>
                            <small>Comments Moderated</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-secondary text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4><?php echo $stats['total_articles_reviewed'] > 0 ? round(($stats['articles_approved'] / $stats['total_articles_reviewed']) * 100, 1) : 0; ?>%</h4>
                            <small>Approval Rate</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Edit Profile -->
                    <div class="col-lg-6 mb-4">
                        <div class="edit-profile-form">
                            <h4 class="mb-4">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </h4>
                            
                            <form method="POST" enctype="multipart/form-data" id="profileForm">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($editor['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($editor['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($editor['phone'] ?? ''); ?>"
                                           placeholder="+92 300 1234567">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Bio / Introduction</label>
                                    <textarea name="bio" class="form-control" rows="4" 
                                              placeholder="Tell readers about your editorial expertise and experience..."><?php echo htmlspecialchars($editor['bio'] ?? ''); ?></textarea>
                                    <small class="text-muted">This will be displayed on your public profile</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload a professional headshot (JPG, PNG, max 2MB)</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Editorial Actions -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Editorial Actions
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <?php if (mysqli_num_rows($recent_reviewed) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($recent_reviewed)): ?>
                                        <div class="article-preview">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h6>
                                                <span class="badge bg-<?php echo $article['status'] == 'published' ? 'success' : ($article['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                    <?php echo ucfirst($article['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                Author: <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <span>
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                                <span>
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo number_format($article['views']); ?> views
                                                </span>
                                                <span>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Reviewed: <?php echo time_ago($article['updated_at']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    
                                    <div class="text-center mt-3">
                                        <a href="manage-news.php" class="btn btn-outline-primary">
                                            View All Reviewed Articles
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No editorial actions yet</h5>
                                        <p class="text-muted">Start reviewing articles to see your activity here</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Editorial Performance Metrics -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Editorial Performance Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <strong><?php echo $stats['total_articles_reviewed']; ?></strong><br>
                                <small class="text-muted">Total Articles Reviewed</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <strong><?php echo $stats['articles_approved']; ?></strong><br>
                                <small class="text-muted">Articles Approved</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <strong><?php echo $stats['articles_rejected']; ?></strong><br>
                                <small class="text-muted">Articles Rejected</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <strong><?php echo $total_comments_moderated; ?></strong><br>
                                <small class="text-muted">Comments Moderated</small>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $stats['total_articles_reviewed'] > 0 ? ($stats['articles_approved'] / $stats['total_articles_reviewed']) * 100 : 0; ?>%">
                                    Approved: <?php echo $stats['articles_approved']; ?>
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: <?php echo $stats['total_articles_reviewed'] > 0 ? ($stats['articles_rejected'] / $stats['total_articles_reviewed']) * 100 : 0; ?>%">
                                    Rejected: <?php echo $stats['articles_rejected']; ?>
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" 
                                     style="width: <?php echo $stats['total_articles_reviewed'] > 0 ? ($stats['articles_pending'] / $stats['total_articles_reviewed']) * 100 : 0; ?>%">
                                    Pending: <?php echo $stats['articles_pending']; ?>
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
        // Handle avatar upload
        document.getElementById('avatarUpload').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                // Add a hidden input to indicate this is an avatar upload
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'avatar_upload';
                hiddenInput.value = '1';
                document.getElementById('profileForm').appendChild(hiddenInput);
                
                // Submit the form
                document.getElementById('profileForm').submit();
            }
        });
    </script>
</body>
</html>

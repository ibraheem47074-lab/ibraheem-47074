<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

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
    mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $phone, $bio, $image, $reporter_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_image'] = $image;
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile. Please try again.";
    }
}

// Get reporter information
$reporter_query = "SELECT * FROM users WHERE id = $reporter_id";
$reporter_result = mysqli_query($conn, $reporter_query);
$reporter = mysqli_fetch_assoc($reporter_result);

// Get reporter's articles
$articles_query = "SELECT n.*, c.name as category_name 
                  FROM news n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE n.author_id = $reporter_id 
                  ORDER BY n.created_at DESC LIMIT 5";
$recent_articles = mysqli_query($conn, $articles_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_articles,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
    SUM(views) as total_views,
    SUM(CASE WHEN is_breaking = 1 THEN 1 ELSE 0 END) as breaking_news
    FROM news WHERE author_id = $reporter_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get comments on reporter's articles
$comments_query = "SELECT COUNT(*) as total_comments FROM comments c 
                  JOIN news n ON c.news_id = n.id 
                  WHERE n.author_id = $reporter_id AND c.status = 'approved'";
$total_comments = mysqli_fetch_assoc(mysqli_query($conn, $comments_query))['total_comments'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Profile - PK Live News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .reporter-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .reporter-sidebar {
            background: #2d3748;
            min-height: 100vh;
            color: white;
        }
        .reporter-sidebar .nav-link {
            color: #cbd5e0;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        .reporter-sidebar .nav-link:hover,
        .reporter-sidebar .nav-link.active {
            background-color: #4a5568;
            color: white;
        }
        .reporter-main-content {
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
            border: 5px solid #667eea;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
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
            margin: 0 auto 15px;
        }
        .article-preview {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
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
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block reporter-sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-newspaper me-2"></i>PK-Live News</h4>
                        <small>Reporter Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_news.php">
                                <i class="fas fa-newspaper me-2"></i>My Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link bg-danger text-white" href="breaking_news_reporter.php">
                                <i class="fas fa-bolt me-2"></i>Breaking News
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="live_reporter.php">
                                <i class="fas fa-broadcast-tower me-2"></i>Live Reporting
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reporter_profile.php">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 reporter-main-content">
                <!-- Header -->
                <div class="reporter-header d-flex justify-content-between align-items-center py-3 px-4 mb-4 rounded">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-user me-2"></i>Reporter Profile
                        </h1>
                        <small>Manage your professional profile and view your statistics</small>
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
                            <img src="<?php echo $reporter['image'] ? '../uploads/users/' . htmlspecialchars($reporter['image']) : 'https://picsum.photos/seed/reporter/150/150.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($reporter['name']); ?>" 
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
                                <h2 class="mb-0 me-3"><?php echo htmlspecialchars($reporter['name']); ?></h2>
                                <span class="badge-role">
                                    <i class="fas fa-pen me-1"></i><?php echo ucfirst($reporter['role']); ?>
                                </span>
                            </div>
                            
                            <?php if ($reporter['bio']): ?>
                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($reporter['bio'])); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-info-circle me-2"></i>No bio added yet. Tell readers about yourself!
                                </p>
                            <?php endif; ?>
                            
                            <div class="row text-muted">
                                <div class="col-md-4">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?php echo htmlspecialchars($reporter['email']); ?>
                                </div>
                                <?php if ($reporter['phone']): ?>
                                    <div class="col-md-4">
                                        <i class="fas fa-phone me-2"></i>
                                        <?php echo htmlspecialchars($reporter['phone']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <i class="fas fa-calendar me-2"></i>
                                    Joined <?php echo date('M Y', strtotime($reporter['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary text-white">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <h4><?php echo $stats['total_articles']; ?></h4>
                            <small>Articles</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-success text-white">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4><?php echo $stats['published']; ?></h4>
                            <small>Published</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-info text-white">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h4><?php echo number_format($stats['total_views'] ?: 0); ?></h4>
                            <small>Total Views</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning text-white">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h4><?php echo $total_comments; ?></h4>
                            <small>Comments</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-danger text-white">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h4><?php echo $stats['breaking_news']; ?></h4>
                            <small>Breaking</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="stats-card">
                            <div class="stats-icon bg-secondary text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4><?php echo $stats['total_articles'] > 0 ? round(($stats['published'] / $stats['total_articles']) * 100, 1) : 0; ?>%</h4>
                            <small>Published Rate</small>
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
                                           value="<?php echo htmlspecialchars($reporter['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($reporter['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($reporter['phone'] ?? ''); ?>"
                                           placeholder="+92 300 1234567">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Bio / Introduction</label>
                                    <textarea name="bio" class="form-control" rows="4" 
                                              placeholder="Tell readers about yourself, your expertise, and what you cover..."><?php echo htmlspecialchars($reporter['bio'] ?? ''); ?></textarea>
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

                    <!-- Recent Articles -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-newspaper me-2"></i>Recent Articles
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <?php if (mysqli_num_rows($recent_articles) > 0): ?>
                                    <?php while ($article = mysqli_fetch_assoc($recent_articles)): ?>
                                        <div class="article-preview">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <a href="../news.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h6>
                                                <?php if ($article['is_breaking']): ?>
                                                    <span class="badge bg-danger">BREAKING</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-muted small mb-2">
                                                <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 100)) . '...'; ?>
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
                                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    
                                    <div class="text-center mt-3">
                                        <a href="reporter_news.php" class="btn btn-outline-primary">
                                            View All Articles
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No articles yet</h5>
                                        <p class="text-muted">Start writing to see your articles here</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Public Profile Preview -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Public Profile Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This is how your profile appears to readers who click on your byline in articles.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="<?php echo $reporter['image'] ? '../uploads/users/' . htmlspecialchars($reporter['image']) : 'https://picsum.photos/seed/reporter/150/150.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($reporter['name']); ?>" 
                                     class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <h6><?php echo htmlspecialchars($reporter['name']); ?></h6>
                                <small class="text-muted"><?php echo ucfirst($reporter['role']); ?></small>
                            </div>
                            <div class="col-md-9">
                                <?php if ($reporter['bio']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($reporter['bio'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No bio available.</p>
                                <?php endif; ?>
                                
                                <div class="row mt-3">
                                    <div class="col-md-3 text-center">
                                        <strong><?php echo $stats['total_articles']; ?></strong><br>
                                        <small class="text-muted">Articles</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong><?php echo number_format($stats['total_views'] ?: 0); ?></strong><br>
                                        <small class="text-muted">Total Views</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong><?php echo $total_comments; ?></strong><br>
                                        <small class="text-muted">Comments</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <strong><?php echo date('M Y', strtotime($reporter['created_at'])); ?></strong><br>
                                        <small class="text-muted">Member Since</small>
                                    </div>
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

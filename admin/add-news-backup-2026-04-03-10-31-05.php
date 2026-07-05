<?php
session_start();
require_once '../config/database.php';
require_once '../includes/sentiment_analysis.php';

// Media handling helper functions
function handle_image_upload($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid image type. Allowed: JPG, PNG, GIF, WebP'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Image file too large. Maximum size: 5MB'];
    }
    
    // Validate image content
    if (!getimagesize($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Invalid image file or corrupted image'];
    }
    
    // Generate unique filename
    $file_name = 'img_' . uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = 'uploads/news/images/' . $file_name;
    $full_upload_path = '../' . $upload_path;
    
    // Ensure upload directory exists
    $upload_dir = dirname($full_upload_path);
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $full_upload_path)) {
        return ['success' => true, 'path' => $upload_path];
    } else {
        return ['success' => false, 'error' => 'Failed to upload image. Check directory permissions.'];
    }
}

function handle_video_upload($file) {
    $allowed_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    $max_size = 50 * 1024 * 1024; // 50MB
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid video type. Allowed: MP4, AVI, MOV, WMV, FLV, WebM, MKV'];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Video file too large. Maximum size: 50MB'];
    }
    
    // Check if it's a valid video file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mime_types = [
        'video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv',
        'video/x-flv', 'video/webm', 'video/x-matroska'
    ];
    
    if (!in_array($mime_type, $allowed_mime_types)) {
        return ['success' => false, 'error' => 'Invalid video file format'];
    }
    
    // Generate unique filename
    $file_name = 'vid_' . uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = 'uploads/news/videos/' . $file_name;
    $full_upload_path = '../' . $upload_path;
    
    // Ensure upload directory exists
    $upload_dir = dirname($full_upload_path);
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $full_upload_path)) {
        return ['success' => true, 'path' => $upload_path];
    } else {
        return ['success' => false, 'error' => 'Failed to upload video. Check directory permissions.'];
    }
}

function handle_upload_error($error_code, $file_type) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "File exceeds upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "File exceeds MAX_FILE_SIZE directive specified in HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "File was only partially uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload";
        default:
            return "Unknown upload error code: " . $error_code;
    }
}

function validate_video_url($url) {
    // Clean URL
    $url = trim($url);
    
    // Check if it's a valid URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['valid' => false, 'error' => 'Invalid video URL format'];
    }
    
    // Check for supported video platforms
    $supported_patterns = [
        'youtube.com' => '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
        'youtu.be' => '/youtu\.be\/([a-zA-Z0-9_-]+)/',
        'vimeo.com' => '/vimeo\.com\/(\d+)/',
        'dailymotion.com' => '/dailymotion\.com\/video\/([a-zA-Z0-9_-]+)/'
    ];
    
    $is_supported = false;
    foreach ($supported_patterns as $platform => $pattern) {
        if (preg_match($pattern, $url)) {
            $is_supported = true;
            break;
        }
    }
    
    if (!$is_supported) {
        return ['valid' => false, 'error' => 'Unsupported video platform. Supported: YouTube, Vimeo, Dailymotion'];
    }
    
    return ['valid' => true, 'url' => $url];
}

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    // Debug session info
    error_log("Access denied - Session info: " . print_r($_SESSION, true));
    redirect('login.php');
}

// Edit mode - handle editing existing articles
$edit_mode = false;
$article = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $article_id = (int)$_GET['id'];
    
    // Fetch article data
    $query = "SELECT * FROM news WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $article_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($article = mysqli_fetch_assoc($result)) {
        $edit_mode = true;
        
        // Fix invalid dates
        if (empty($article['published_at']) || $article['published_at'] === '0000-00-00 00:00:00') {
            $article['published_at'] = date('Y-m-d H:i:s');
        }
        
        // Convert to proper datetime-local format for HTML input
        $article['published_at_formatted'] = date('Y-m-d\TH:i', strtotime($article['published_at']));
        
        // Fix other potential issues
        if (empty($article['excerpt'])) {
            $article['excerpt'] = substr(strip_tags($article['content']), 0, 200) . '...';
        }
        
    } else {
        $error = "Article not found.";
    }
}

$error = '';
$success = '';

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $success = "News article added successfully! (ID: " . $article_id . ")";
    error_log("Success message displayed for article ID: " . $article_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log the entire POST request
    error_log("=== FORM SUBMISSION DEBUG ===");
    error_log("POST request received: " . print_r($_POST, true));
    if (isset($_FILES['image'])) {
        error_log("FILES data: " . print_r($_FILES['image'], true));
    }
    if (isset($_FILES['video'])) {
        error_log("VIDEO FILES data: " . print_r($_FILES['video'], true));
    }
    
    // Check PHP upload errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Image upload error code: " . $_FILES['image']['error']);
        error_log("Image upload error message: " . handle_upload_error($_FILES['image']['error'], 'image'));
    }
    if (isset($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
        error_log("Video upload error code: " . $_FILES['video']['error']);
        error_log("Video upload error message: " . handle_upload_error($_FILES['video']['error'], 'video'));
    }
    
    // Check upload directory permissions
    $image_upload_dir = '../uploads/news/images/';
    $video_upload_dir = '../uploads/news/videos/';
    
    if (!is_dir($image_upload_dir)) {
        error_log("Image upload directory does not exist: $image_upload_dir");
    } else {
        error_log("Image upload directory exists: $image_upload_dir");
        error_log("Image upload directory writable: " . (is_writable($image_upload_dir) ? 'Yes' : 'No'));
    }
    
    if (!is_dir($video_upload_dir)) {
        error_log("Video upload directory does not exist: $video_upload_dir");
    } else {
        error_log("Video upload directory exists: $video_upload_dir");
        error_log("Video upload directory writable: " . (is_writable($video_upload_dir) ? 'Yes' : 'No'));
    }
    
    // Process form data
    $title = clean_input($_POST['title']);
    $content = $_POST['content'];
    $excerpt = clean_input($_POST['excerpt']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $urgency = 'medium'; // Default urgency since field is removed
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $published_at = !empty($_POST['published_at']) ? date('Y-m-d H:i:s', strtotime($_POST['published_at'])) : date('Y-m-d H:i:s');
    
    // Fix invalid published_at date
    if ($published_at === '1970-01-01 00:00:00' || $published_at === false) {
        $published_at = date('Y-m-d H:i:s');
    }
    
    error_log("Processed form data: title='$title', category_id=$category_id, status='$status', urgency='$urgency', published_at='$published_at'");
    error_log("Content length: " . strlen($content));
    
    // Add debug for session
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
        
    // Verify user session
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $error = 'User session not found. Please log in again.';
        error_log("Session error: user_id not set");
    } else {
        error_log("User session valid: user_id = " . $_SESSION['user_id']);
        
        // Validation
        if (empty($title) || empty($content)) {
            $error = 'Title and content are required fields';
            error_log("Validation failed: title=" . (empty($title) ? 'empty' : 'set') . ", content=" . (empty($content) ? 'empty' : 'set'));
        } elseif ($category_id <= 0) {
            $error = 'Please select a valid category';
            error_log("Validation failed: invalid category_id = $category_id");
        } else {
            // Enhanced media handling for images and videos
            $image_path = '';
            $video_path = '';
            $video_url = clean_input($_POST['video_url'] ?? '');
            $media_type = $_POST['media_type'] ?? 'text';
            
            error_log("Media type selected: $media_type");
            
            // Handle image upload if no error
            if (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image_result = handle_image_upload($_FILES['image']);
                if ($image_result['success']) {
                    $image_path = $image_result['path'];
                    error_log("Image uploaded successfully: $image_path");
                } else {
                    $error = $image_result['error'];
                    error_log("Image upload failed: " . $image_result['error']);
                }
            } elseif (empty($error) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = handle_upload_error($_FILES['image']['error'], 'image');
            }
            
            // Handle video upload if no error from image upload
            if (empty($error) && isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
                $video_result = handle_video_upload($_FILES['video']);
                if ($video_result['success']) {
                    $video_path = $video_result['path'];
                    error_log("Video uploaded successfully: $video_path");
                } else {
                    $error = $video_result['error'];
                    error_log("Video upload failed: " . $video_result['error']);
                }
            } elseif (empty($error) && isset($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
                $error = handle_upload_error($_FILES['video']['error'], 'video');
            }
            
            // Validate video URL if provided
            if (empty($error) && !empty($video_url)) {
                $url_validation = validate_video_url($video_url);
                if (!$url_validation['valid']) {
                    $error = $url_validation['error'];
                } else {
                    // Store the validated URL
                    $video_url = $url_validation['url'];
                }
            }
            
            // Validate media requirements based on selected media type
            if (empty($error)) {
                switch($media_type) {
                    case 'image':
                        if (empty($image_path)) {
                            $error = 'Please select an image for Image media type';
                        }
                        break;
                    case 'video':
                        if (empty($video_path) && empty($video_url)) {
                            $error = 'Please upload a video or provide a video URL for Video media type';
                        }
                        break;
                    case 'both':
                        if (empty($image_path)) {
                            $error = 'Please select an image for Both media type';
                        }
                        if (empty($video_path) && empty($video_url)) {
                            $error = 'Please upload a video or provide a video URL for Both media type';
                        }
                        break;
                    case 'text':
                        // No media required for text-only
                        break;
                }
            }
            
            error_log("Final media validation - Image: " . (!empty($image_path) ? 'Yes' : 'No') . ", Video Path: " . (!empty($video_path) ? 'Yes' : 'No') . ", Video URL: " . (!empty($video_url) ? 'Yes' : 'No'));
        
        if (empty($error)) {
                error_log("Validation passed - proceeding with database insertion");
                
                // Generate slug
                $slug = slugify($title);
                
                // Check if slug already exists and make it unique (exclude current article in edit mode)
                $original_slug = $slug;
                $counter = 1;
                while (true) {
                    $slug_check_query = "SELECT id FROM news WHERE slug = ?" . ($edit_mode ? " AND id != ?" : "");
                    $stmt = mysqli_prepare($conn, $slug_check_query);
                    if ($edit_mode) {
                        mysqli_stmt_bind_param($stmt, 'si', $slug, $article_id);
                    } else {
                        mysqli_stmt_bind_param($stmt, 's', $slug);
                    }
                    mysqli_stmt_execute($stmt);
                    $slug_check = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($slug_check) == 0) {
                        break; // Slug is unique
                    }
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                }
                
                // Perform sentiment analysis with fallback
                try {
                    $analysis_text = $title . ' ' . $content . ' ' . $excerpt;
                    $sentiment = analyze_sentiment($analysis_text);
                    $sentiment_score = $sentiment['score'];
                    $sentiment_label = $sentiment['label'];
                    error_log("Sentiment analysis successful: score=$sentiment_score, label=$sentiment_label");
                } catch (Exception $e) {
                    error_log("Sentiment analysis failed: " . $e->getMessage());
                    // Fallback to neutral sentiment
                    $sentiment_score = 0;
                    $sentiment_label = 'neutral';
                }
                
                if ($edit_mode) {
                    // Update existing article
                    $news_type = $article['news_type'] ?? 'pk_live';
                    $source_url = $article['source_url'];
                    
                    // Keep existing media if no new media uploaded
                    if (empty($image_path)) {
                        $image_path = $article['image'] ?? '';
                    }
                    if (empty($video_path)) {
                        $video_path = $article['video_path'] ?? '';
                    }
                    if (empty($video_url)) {
                        $video_url = $article['video_url'] ?? '';
                    }
                    
                    $query = "UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, image = ?, video_url = ?, video_path = ?, category_id = ?, status = ?, is_breaking = ?, published_at = ?, sentiment_score = ?, sentiment_label = ? WHERE id = ?";
                    
                    error_log("UPDATE Query: " . $query);
                    error_log("Parameters: title='$title', slug='$slug', content_length=" . strlen($content) . ", excerpt='$excerpt', image='$image_path', video_url='$video_url', video_path='$video_path', category_id=$category_id, status='$status', is_breaking=$is_breaking, published_at='$published_at', sentiment_score=$sentiment_score, sentiment_label='$sentiment_label', article_id=$article_id");
                    
                    $stmt = mysqli_prepare($conn, $query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'sssssssisissi', 
                            $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path,
                            $category_id, $status, $is_breaking, $published_at, $sentiment_score, $sentiment_label, $article_id
                        );
                        error_log("UPDATE Parameters bound successfully");
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $success = "News article updated successfully! <a href='manage-news.php' class='alert-link'>Manage Articles</a> or <a href='../news.php?slug=$slug' class='alert-link' target='_blank'>View Article</a>";
                            error_log("SUCCESS: News article updated with ID: $article_id");
                            
                            // Redirect to prevent form resubmission and clear state
                            header("Location: add-news.php?id=$article_id&success=1");
                            exit();
                        } else {
                            $error_msg = mysqli_error($conn);
                            error_log("UPDATE EXECUTION FAILED: " . $error_msg);
                            $error = "Error updating news article: " . $error_msg;
                        }
                    } else {
                        $error = "Error preparing update query: " . mysqli_error($conn);
                        error_log("UPDATE PREPARE FAILED: " . mysqli_error($conn));
                    }
                } else {
                    // Insert new article
                    error_log("=== ATTEMPTING NEW ARTICLE INSERT ===");
                    $news_type = 'pk_live'; // Mark as PK Live News original article
                    $source_url = null; // No source URL for original content
                    $query = "INSERT INTO news (title, slug, content, excerpt, image, video_url, video_path, category_id, author_id, status, is_breaking, published_at, sentiment_score, sentiment_label, news_type, source_url) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    error_log("SQL Query: " . $query);
                    error_log("Parameters: title='$title', slug='$slug', content_length=" . strlen($content) . ", excerpt='$excerpt', image='$image_path', video_url='$video_url', video_path='$video_path', category_id=$category_id, author_id=" . $_SESSION['user_id'] . ", status='$status', is_breaking=$is_breaking, published_at='$published_at', sentiment_score=$sentiment_score, sentiment_label='$sentiment_label', news_type='$news_type', source_url='$source_url'");
                    
                    // Validate required fields before insert
                    if (empty($title) || empty($content) || empty($category_id) || empty($_SESSION['user_id'])) {
                        error_log("VALIDATION FAILED: Missing required fields");
                        $error = "Missing required fields for article creation";
                    } else {
                        $stmt = mysqli_prepare($conn, $query);
                        if ($stmt) {
                            error_log("Statement prepared successfully");
                            mysqli_stmt_bind_param($stmt, 'sssssssisississs', 
                                $title, $slug, $content, $excerpt, $image_path, $video_url, $video_path,
                                $category_id, $_SESSION['user_id'], $status, $is_breaking, $published_at, $sentiment_score, $sentiment_label, $news_type, $source_url
                            );
                            error_log("Parameters bound successfully");
                            
                            if (mysqli_stmt_execute($stmt)) {
                                $insert_id = mysqli_insert_id($conn);
                                error_log("SUCCESS: News article inserted with ID: $insert_id");
                                
                                // Set success message and continue without redirect to allow debugging
                                $success = "News article added successfully! (ID: " . $insert_id . ")";
                                
                                // Clear form data after successful submission
                                $_POST = array();
                                $_FILES = array();
                            } else {
                                $error_msg = mysqli_stmt_error($stmt);
                                $mysql_error = mysqli_error($conn);
                                error_log("EXECUTION FAILED: " . $error_msg);
                                error_log("MYSQL ERROR: " . $mysql_error);
                                $error = "Error adding news article: " . $error_msg;
                            }
                        } else {
                            $error = "Error preparing query: " . mysqli_error($conn);
                            error_log("PREPARE FAILED: " . mysqli_error($conn));
                        }
                    }
                }
            }
        }
    }
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit News' : 'Add News'; ?> - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/dmo8p48m5mmp3grrp8sig5nn4e044nvf0uq2rghq6y70f4iq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                        <h1 class="h3 mb-0">Add News</h1>
                        <small>Create a new news article</small>
                    </div>
                    <div>
                        <a href="manage-news.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to News
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

                <!-- Add News Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus'; ?> me-2"></i>
                            <?php echo $edit_mode ? 'Edit PK Live News Article' : 'Create PK Live News Article'; ?>
                        </h4>
                        <small class="opacity-75">
                            <?php echo $edit_mode ? 'Modify and update existing article' : 'Share your story with our readers'; ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="newsForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Title -->
                                    <div class="mb-3">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading me-1"></i>News Title *
                                            <small class="text-muted">(Make it catchy and informative)</small>
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                               value="<?php echo $edit_mode ? htmlspecialchars($article['title']) : (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>" 
                                               placeholder="Enter a compelling headline..."
                                               required>
                                        <div class="form-text">
                                            <span id="titleCount">0</span>/255 characters
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="mb-3">
                                        <label for="content" class="form-label">
                                            <i class="fas fa-file-alt me-1"></i>Article Content *
                                            <small class="text-muted">(Tell the full story)</small>
                                        </label>
                                        <textarea class="form-control" id="content" name="content" rows="12" required><?php echo $edit_mode ? htmlspecialchars($article['content']) : (isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''); ?></textarea>
                                        <div class="form-text">
                                            <span id="contentCount">0</span> words • <span id="readTime">1</span> min read
                                        </div>
                                    </div>

                                    <!-- Excerpt -->
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt/Summary</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="Brief summary of the news article"><?php echo $edit_mode ? htmlspecialchars($article['excerpt']) : (isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''); ?></textarea>
                                    </div>

                                    
                                    <!-- Video URL -->
                                    <div class="mb-3">
                                        <label for="video_url" class="form-label">Video URL (Optional)</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" 
                                               value="<?php echo isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : ''; ?>" 
                                               placeholder="YouTube or video URL">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Category -->
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($edit_mode && $article['category_id'] == $cat['id']) || (!$edit_mode && isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="published" 
                                                <?php echo ($edit_mode && $article['status'] == 'published') || (!$edit_mode && isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                            <option value="draft" 
                                                <?php echo ($edit_mode && $article['status'] == 'draft') || (!$edit_mode && isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>

                                    
                                    <!-- Published Date -->
                                    <div class="mb-3">
                                        <label for="published_at" class="form-label">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Publish Date & Time
                                        </label>
                                        <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                               value="<?php echo $edit_mode ? $article['published_at_formatted'] : (isset($_POST['published_at']) ? date('Y-m-d\TH:i', strtotime($_POST['published_at'])) : date('Y-m-d\TH:i')); ?>">
                                        <?php if ($edit_mode && $article['published_at'] !== $article['published_at_formatted']): ?>
                                            <div class="alert alert-warning mt-2">
                                                <small><i class="fas fa-exclamation-triangle me-1"></i>Original date was fixed from: <?php echo htmlspecialchars($article['published_at']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Featured Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                                        <img id="imagePreview" class="image-preview" alt="Image preview">
                                        <small class="text-muted">Allowed formats: JPG, PNG, GIF, WebP. Max size: 5MB</small>
                                    </div>

                                    <!-- Video Upload -->
                                    <div class="mb-3">
                                        <label for="video" class="form-label">Video Upload (Optional)</label>
                                        <input type="file" class="form-control" id="video" name="video" accept="video/*" onchange="previewVideo(event)">
                                        <video id="videoPreview" class="video-preview" controls style="display: none; max-width: 100%; max-height: 200px; margin-top: 10px;"></video>
                                        <small class="text-muted">Allowed formats: MP4, AVI, MOV, WMV, FLV, WebM, MKV. Max size: 50MB</small>
                                    </div>

                                    <!-- Media Type Selection -->
                                    <div class="mb-3">
                                        <label class="form-label">Media Type</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="media_type" id="media_text" value="text" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="media_text">
                                                <i class="fas fa-file-alt me-1"></i>Text Only
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="media_type" id="media_image" value="image" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="media_image">
                                                <i class="fas fa-image me-1"></i>Image
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="media_type" id="media_video" value="video" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="media_video">
                                                <i class="fas fa-video me-1"></i>Video
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="media_type" id="media_both" value="both" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="media_both">
                                                <i class="fas fa-images me-1"></i>Both
                                            </label>
                                        </div>
                                        <small class="text-muted">Select the primary media type for this article</small>
                                    </div>

                                    <!-- Breaking News -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                                   <?php echo ($edit_mode && $article['is_breaking']) || (!$edit_mode && isset($_POST['is_breaking'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_breaking">
                                                Mark as Breaking News
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-<?php echo $edit_mode ? 'save' : 'plus'; ?> me-2"></i><?php echo $edit_mode ? 'Update Article' : 'Save News Article'; ?>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='manage-news.php'">
                                            <i class="fas fa-arrow-left me-2"></i>Back to News
                                        </button>
                                        <?php if ($edit_mode): ?>
                                            <button type="button" class="btn btn-outline-info" onclick="window.open('../news.php?slug=<?php echo urlencode($article['slug']); ?>', '_blank')">
                                                <i class="fas fa-eye me-2"></i>View Article
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- User Roles Information Section -->
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users-cog me-2"></i>
                            User Roles & Permissions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-shield-alt me-2"></i>Management Roles
                                </h6>
                                <div class="small">
                                    <div class="mb-2">
                                        <strong>Super Administrator:</strong> Full system access with all privileges
                                    </div>
                                    <div class="mb-2">
                                        <strong>Administrator:</strong> System administration and user management
                                    </div>
                                    <div class="mb-2">
                                        <strong>Technical Editor:</strong> Technical content and system maintenance
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-edit me-2"></i>Editorial Roles
                                </h6>
                                <div class="small">
                                    <div class="mb-2">
                                        <strong>Senior Editor:</strong> Senior editorial oversight and content management
                                    </div>
                                    <div class="mb-2">
                                        <strong>Editor:</strong> Content editing and publishing
                                    </div>
                                    <div class="mb-2">
                                        <strong>Associate Editor:</strong> Assisting with content management
                                    </div>
                                    <div class="mb-2">
                                        <strong>Content Analyst:</strong> Analytics and performance analysis
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="text-info mb-3">
                                    <i class="fas fa-microphone me-2"></i>Reporting Roles
                                </h6>
                                <div class="small">
                                    <div class="mb-2">
                                        <strong>Senior Reporter:</strong> Experienced news reporting and mentoring
                                    </div>
                                    <div class="mb-2">
                                        <strong>Reporter:</strong> News content creation and reporting
                                    </div>
                                    <div class="mb-2">
                                        <strong>Junior Reporter:</strong> Entry-level reporting and content creation
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-warning mb-3">
                                    <i class="fas fa-briefcase me-2"></i>Specialized Roles
                                </h6>
                                <div class="small">
                                    <div class="mb-2">
                                        <strong>Multimedia Producer:</strong> Video and multimedia content production
                                    </div>
                                    <div class="mb-2">
                                        <strong>Social Media Manager:</strong> Social media content and engagement
                                    </div>
                                    <div class="mb-2">
                                        <strong>Freelance Contributor:</strong> Part-time content contribution
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info small mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Departments:</strong> Editorial | Reporting | Technical | Management | Marketing | Multimedia
                                    <br>
                                    <strong>Experience Levels:</strong> Junior | Intermediate | Senior | Expert | Lead
                                    <br>
                                    <strong>Verification Status:</strong> Unverified | Verified | Premium
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Video preview
        function previewVideo(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('videoPreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // Media type handling
        function handleMediaTypeChange() {
            const mediaType = document.querySelector('input[name="media_type"]:checked').value;
            const imageUpload = document.getElementById('image').parentElement;
            const videoUpload = document.getElementById('video').parentElement;
            const videoUrl = document.getElementById('video_url').parentElement;
            
            // Reset all fields
            imageUpload.style.display = 'none';
            videoUpload.style.display = 'none';
            videoUrl.style.display = 'none';
            
            switch(mediaType) {
                case 'text':
                    // Hide all media fields
                    break;
                case 'image':
                    imageUpload.style.display = 'block';
                    break;
                case 'video':
                    videoUpload.style.display = 'block';
                    videoUrl.style.display = 'block';
                    break;
                case 'both':
                    imageUpload.style.display = 'block';
                    videoUpload.style.display = 'block';
                    videoUrl.style.display = 'block';
                    break;
            }
        }

        // Add event listeners for media type
        document.querySelectorAll('input[name="media_type"]').forEach(radio => {
            radio.addEventListener('change', handleMediaTypeChange);
        });

        // Initialize media type display
        handleMediaTypeChange();

        // Auto-generate excerpt from content if empty
        document.getElementById('content').addEventListener('blur', function() {
            const excerpt = document.getElementById('excerpt');
            if (excerpt.value.trim() === '') {
                const content = this.value.replace(/<[^>]*>/g, ''); // Remove HTML tags
                excerpt.value = content.substring(0, 200) + (content.length > 200 ? '...' : '');
            }
        });

        // Auto-update media type when files are selected
        function autoUpdateMediaType() {
            const imageFile = document.getElementById('image').files[0];
            const videoFile = document.getElementById('video').files[0];
            const videoUrl = document.getElementById('video_url').value.trim();
            
            let suggestedType = 'text';
            
            if (imageFile && (videoFile || videoUrl)) {
                suggestedType = 'both';
            } else if (imageFile) {
                suggestedType = 'image';
            } else if (videoFile || videoUrl) {
                suggestedType = 'video';
            }
            
            // Only auto-update if current selection is still 'text'
            const currentType = document.querySelector('input[name="media_type"]:checked').value;
            if (currentType === 'text' && suggestedType !== 'text') {
                document.getElementById('media_' + suggestedType).checked = true;
                handleMediaTypeChange();
                console.log('Auto-updated media type to:', suggestedType);
            }
        }

        // Add event listeners for file inputs
        document.getElementById('image').addEventListener('change', autoUpdateMediaType);
        document.getElementById('video').addEventListener('change', autoUpdateMediaType);
        document.getElementById('video_url').addEventListener('input', autoUpdateMediaType);

        // Form validation
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            const title = document.getElementById('title').value.trim();
            const content = tinymce.get('content') ? tinymce.get('content').getContent() : '';
            const category = document.getElementById('category_id').value;
            const mediaType = document.querySelector('input[name="media_type"]:checked').value;
            const imageFile = document.getElementById('image').files[0];
            const videoFile = document.getElementById('video').files[0];
            const videoUrl = document.getElementById('video_url').value.trim();
            
            console.log('Form validation data:', {
                title: title,
                content: content.substring(0, 100) + '...',
                category: category,
                mediaType: mediaType,
                hasImage: !!imageFile,
                hasVideo: !!videoFile,
                hasVideoUrl: !!videoUrl,
                contentLength: content.length
            });
            
            if (!title || title.length === 0) {
                e.preventDefault();
                alert('Please fill in the news title');
                console.log('Title validation failed');
                return false;
            }
            
            if (!content || content.trim() === '' || content.length < 10) {
                e.preventDefault();
                alert('Please fill in the news content (minimum 10 characters)');
                console.log('Content validation failed - length:', content.length);
                return false;
            }
            
            if (!category || category === '') {
                e.preventDefault();
                alert('Please select a category');
                console.log('Category validation failed');
                return false;
            }
            
            // Media validation based on selected media type
            switch(mediaType) {
                case 'image':
                    if (!imageFile) {
                        e.preventDefault();
                        alert('Please select an image for Image media type, or change media type to "Text Only"');
                        console.log('Image validation failed');
                        return false;
                    }
                    break;
                    
                case 'video':
                    if (!videoFile && !videoUrl) {
                        e.preventDefault();
                        alert('Please upload a video or provide a video URL for Video media type, or change media type to "Text Only"');
                        console.log('Video validation failed');
                        return false;
                    }
                    break;
                    
                case 'both':
                    if (!imageFile) {
                        e.preventDefault();
                        alert('Please select an image for Both media type, or change media type to "Video" if you only want to upload video');
                        console.log('Both - image validation failed');
                        return false;
                    }
                    if (!videoFile && !videoUrl) {
                        e.preventDefault();
                        alert('Please upload a video or provide a video URL for Both media type, or change media type to "Image" if you only want to upload image');
                        console.log('Both - video validation failed');
                        return false;
                    }
                    break;
                    
                case 'text':
                    // No media validation required for text-only
                    break;
            }
            
            console.log('Form validation passed - submitting form');
            return true;
        });
        
        // Real-time date and time update function
        function updateLiveDateTime() {
            // Function kept for potential future use but elements removed from UI
        }
        
        // Character and word counting
        function updateCharacterCounts() {
            const titleField = document.getElementById('title');
            const contentField = tinymce.get('content');
            
            // Title character count
            const titleCount = titleField.value.length;
            const titleCountElement = document.getElementById('titleCount');
            if (titleCountElement) {
                titleCountElement.textContent = titleCount;
                if (titleCount > 255) {
                    titleCountElement.style.color = 'red';
                } else if (titleCount > 200) {
                    titleCountElement.style.color = 'orange';
                } else {
                    titleCountElement.style.color = 'green';
                }
            }
            
            // Content word count and read time
            const content = contentField ? contentField.getContent() : '';
            const plainText = content.replace(/<[^>]*>/g, ''); // Remove HTML tags
            const words = plainText.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCount = words.length;
            const readTime = Math.max(1, Math.ceil(wordCount / 200)); // Assume 200 words per minute
            
            const contentCountElement = document.getElementById('contentCount');
            const readTimeElement = document.getElementById('readTime');
            
            if (contentCountElement) {
                contentCountElement.textContent = wordCount;
            }
            if (readTimeElement) {
                readTimeElement.textContent = readTime;
            }
        }
        
        // Add event listeners for character counting
        document.addEventListener('DOMContentLoaded', function() {
            // Title field
            const titleField = document.getElementById('title');
            if (titleField) {
                titleField.addEventListener('input', updateCharacterCounts);
            }
            
            // Content field (TinyMCE)
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#content',
                    height: 400,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                              alignleft aligncenter alignright alignjustify | \
                              bullist numlist outdent indent | removeformat | help',
                    content_css: [
                        '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                        '//www.tiny.cloud/css/codepen.min.css'
                    ],
                    setup: function(editor) {
                        editor.on('change', function() {
                            updateCharacterCounts();
                        });
                        editor.on('keyup', function() {
                            updateCharacterCounts();
                        });
                    },
                    init_instance_callback: function(editor) {
                        console.log('TinyMCE initialized successfully');
                        updateCharacterCounts(); // Initialize counts
                    }
                });
            }
        });

        // Add debug info when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, form validation ready');
            console.log('TinyMCE available:', typeof tinymce !== 'undefined');
            
            // Set current date/time in form field
            const now = new Date();
            const dateTimeLocal = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
            const dateTimeString = dateTimeLocal.toISOString().slice(0, 16);
            
            const publishedAtField = document.getElementById('published_at');
            if (publishedAtField && !publishedAtField.value) {
                publishedAtField.value = dateTimeString;
                console.log('Set current date/time:', dateTimeString);
            }
            
            // Add fallback for form submission
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    console.log('Submit button clicked');
                    // Let the form submit normally, but add extra logging
                    setTimeout(function() {
                        console.log('Form should have submitted by now');
                    }, 100);
                });
            }
        });
    </script>
</body>
</html>

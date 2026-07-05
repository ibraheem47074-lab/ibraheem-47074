<?php
require_once '../config/database.php';

// Check if user is logged in and is reporter or editor
if (!is_logged_in() || !is_reporter()) {
    redirect('login.php');
}

$reporter_id = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $media_title = clean_input($_POST['media_title']);
    $media_description = clean_input($_POST['media_description']);
    $media_type = clean_input($_POST['media_type']);
    $category = clean_input($_POST['category']);
    
    $upload_dir = '../uploads/news/';
    $file = $_FILES['media_file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'wmv'];
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $video_extensions = ['mp4', 'avi', 'mov', 'wmv'];
    
    if (in_array($file_extension, $allowed_extensions)) {
        // Check if it's actually the correct type
        if ($media_type === 'image' && !in_array($file_extension, $image_extensions)) {
            $error = "Please upload an image file (JPG, PNG, GIF, WebP)";
        } elseif ($media_type === 'video' && !in_array($file_extension, $video_extensions)) {
            $error = "Please upload a video file (MP4, AVI, MOV, WMV)";
        } else {
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Create media record (using news table for now, could create separate media table)
                $query = "INSERT INTO news (title, slug, content, image, video_url, category_id, author_id, status, is_breaking) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', 0)";
                
                $slug = create_slug($media_title);
                $content = $media_description;
                $video_url = $media_type === 'video' ? $filename : '';
                $image = $media_type === 'image' ? $filename : '';
                $category_id = 1; // Default category
                
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssssssi', $media_title, $slug, $content, $image, $video_url, $category_id, $reporter_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Media uploaded successfully!";
                } else {
                    $error = "Error saving media information.";
                }
            } else {
                $error = "Error uploading file. Please try again.";
            }
        }
    } else {
        $error = "Invalid file type. Allowed formats: Images (JPG, PNG, GIF, WebP), Videos (MP4, AVI, MOV, WMV)";
    }
}

// Get my media files
$media_query = "SELECT * FROM news WHERE author_id = $reporter_id AND (image IS NOT NULL OR video_url IS NOT NULL) 
                ORDER BY created_at DESC";
$media_files = mysqli_query($conn, $media_query);

// Get file size and format
function get_media_info($filename, $type) {
    $filepath = "../uploads/news/" . $filename;
    if (file_exists($filepath)) {
        $size = format_file_size(filesize($filepath));
        $extension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION));
        return "$type ($extension) • $size";
    }
    return "$type • File not found";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Gallery - PK Live News</title>
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
        .upload-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #17a2b8;
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .media-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .media-card:hover {
            transform: translateY(-5px);
        }
        .media-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f8f9fa;
        }
        .media-info {
            padding: 15px;
        }
        .media-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .image-badge {
            background: rgba(0,123,255,0.9);
            color: white;
        }
        .video-badge {
            background: rgba(220,53,69,0.9);
            color: white;
        }
        .drop-zone {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: #17a2b8;
            background-color: #f8f9fa;
        }
        .file-input {
            display: none;
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
                            <a class="nav-link active" href="media_gallery.php">
                                <i class="fas fa-images me-2"></i>Media Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_comments.php">
                                <i class="fas fa-comments me-2"></i>Comments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reporter_profile.php">
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
                            <i class="fas fa-images me-2"></i>Media Gallery
                        </h1>
                        <small>Upload and manage photos and videos</small>
                    </div>
                    <div>
                        <span class="badge bg-info">
                            <?php echo mysqli_num_rows($media_files); ?> files
                        </span>
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

                <!-- Upload Form -->
                <div class="upload-form mb-4">
                    <h4 class="mb-4">
                        <i class="fas fa-cloud-upload-alt text-info me-2"></i>Upload Media
                    </h4>
                    
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Media Title</label>
                                    <input type="text" name="media_title" class="form-control" 
                                           placeholder="Enter a descriptive title..." required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Media Type</label>
                                    <select name="media_type" class="form-select" id="mediaType" required>
                                        <option value="">Select Type</option>
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="general">General</option>
                                        <option value="politics">Politics</option>
                                        <option value="sports">Sports</option>
                                        <option value="entertainment">Entertainment</option>
                                        <option value="business">Business</option>
                                        <option value="technology">Technology</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="media_description" class="form-control" rows="3" 
                                              placeholder="Describe the media content..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select File</label>
                                    <div class="drop-zone" id="dropZone">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="mb-2">Drag and drop your file here or click to browse</p>
                                        <small class="text-muted">
                                            Images: JPG, PNG, GIF, WebP (max 5MB)<br>
                                            Videos: MP4, AVI, MOV, WMV (max 50MB)
                                        </small>
                                        <input type="file" name="media_file" class="file-input" id="fileInput" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-upload me-2"></i>Upload Media
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Media Gallery -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">My Media Files</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($media_files) > 0): ?>
                            <div class="media-grid">
                                <?php while ($media = mysqli_fetch_assoc($media_files)): ?>
                                    <div class="media-card">
                                        <div class="position-relative">
                                            <?php if ($media['image']): ?>
                                                <img src="../uploads/news/<?php echo htmlspecialchars($media['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($media['title']); ?>" 
                                                     class="media-preview">
                                                <span class="media-type-badge image-badge">
                                                    <i class="fas fa-image me-1"></i>IMAGE
                                                </span>
                                            <?php elseif ($media['video_url']): ?>
                                                <div class="media-preview d-flex align-items-center justify-content-center bg-dark">
                                                    <i class="fas fa-video fa-4x text-white"></i>
                                                </div>
                                                <span class="media-type-badge video-badge">
                                                    <i class="fas fa-video me-1"></i>VIDEO
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="media-info">
                                            <h6 class="mb-2">
                                                <?php echo htmlspecialchars($media['title']); ?>
                                            </h6>
                                            <p class="text-muted small mb-2">
                                                <?php echo htmlspecialchars(substr($media['content'], 0, 80)) . '...'; ?>
                                            </p>
                                            <div class="text-muted small mb-3">
                                                <?php
                                                if ($media['image']) {
                                                    echo get_media_info($media['image'], 'Image');
                                                } elseif ($media['video_url']) {
                                                    echo get_media_info($media['video_url'], 'Video');
                                                }
                                                ?>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($media['created_at'])); ?>
                                                </small>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($media['image']): ?>
                                                        <a href="../uploads/news/<?php echo htmlspecialchars($media['image']); ?>" 
                                                           target="_blank" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button class="btn btn-outline-info" onclick="copyMediaUrl(<?php echo $media['id']; ?>)">
                                                        <i class="fas fa-link"></i>
                                                    </button>
                                                    <a href="edit-news.php?id=<?php echo $media['id']; ?>" class="btn btn-outline-secondary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No media files yet</h4>
                                <p class="text-muted">Upload your first photo or video using the form above</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Usage Tips -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Media Usage Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-image text-primary me-2"></i>Image Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Use high-resolution images (minimum 1200x800)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include descriptive alt text</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Optimize file size for web (under 2MB)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Use relevant, timely images</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-video text-danger me-2"></i>Video Guidelines:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Keep videos under 5 minutes</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Use stable camera work</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Include clear audio</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Add captions if possible</li>
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
        // File upload handling
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const mediaType = document.getElementById('mediaType');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                updateFileInfo(e.target.files[0]);
            }
        });

        function updateFileInfo(file) {
            // Auto-detect media type
            const imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const videoTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv'];
            
            if (imageTypes.includes(file.type)) {
                mediaType.value = 'image';
            } else if (videoTypes.includes(file.type)) {
                mediaType.value = 'video';
            }
            
            // Update drop zone text
            dropZone.innerHTML = `
                <i class="fas fa-file fa-3x text-success mb-3"></i>
                <p class="mb-2 text-success">File selected: ${file.name}</p>
                <small class="text-muted">Size: ${formatFileSize(file.size)}</small>
                <input type="file" name="media_file" class="file-input" id="fileInput" required>
            `;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function copyMediaUrl(mediaId) {
            // This would copy the media URL to clipboard
            // Implementation depends on how you want to structure media URLs
            alert('Media URL copied to clipboard!');
        }
    </script>
</body>
</html>

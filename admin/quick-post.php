<?php
session_start();
require_once '../config/database.php';
require_once '../includes/sentiment_analysis.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
}

// Image upload function
function handle_image_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large. Maximum size is 5MB.'];
    }
    
    $upload_dir = '../uploads/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'file_path' => 'uploads/images/' . $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }
}

// Video upload function
function handle_video_upload($file) {
    $allowed_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'];
    $max_size = 50 * 1024 * 1024; // 50MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only MP4, AVI, MOV, WMV, and WebM are allowed.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large. Maximum size is 50MB.'];
    }
    
    $upload_dir = '../uploads/videos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'file_path' => 'uploads/videos/' . $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $content = $_POST['content'];
    $excerpt = clean_input($_POST['excerpt']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
    
    // Handle file uploads
    $image_path = '';
    $video_path = '';
    $video_url = clean_input($_POST['video_url'] ?? '');
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_result = handle_image_upload($_FILES['image']);
        if ($image_result['success']) {
            $image_path = $image_result['file_path'];
        } else {
            $error = "Image upload failed: " . $image_result['error'];
        }
    }
    
    // Handle video upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video_result = handle_video_upload($_FILES['video']);
        if ($video_result['success']) {
            $video_path = $video_result['file_path'];
        } else {
            $error = "Video upload failed: " . $video_result['error'];
        }
    }
    
    // Validation
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required fields';
    } elseif ($category_id <= 0) {
        $error = 'Please select a valid category';
    } else {
        // Generate slug
        $slug = slugify($title);
        
        // Check if slug already exists
        $slug_check = mysqli_query($conn, "SELECT id FROM news WHERE slug = '$slug'");
        if (mysqli_num_rows($slug_check) > 0) {
            $slug .= '-' . time();
        }
        
        // Perform sentiment analysis
        $analysis_text = $title . ' ' . $content . ' ' . $excerpt;
        $sentiment = analyze_sentiment($analysis_text);
        $sentiment_score = $sentiment['score'];
        $sentiment_label = $sentiment['label'];
        
        // Insert news using proven working approach - first insert essentials only
        $query = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, is_breaking, published_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssisiiis', 
                $title, $slug, $content, $excerpt, $category_id, $_SESSION['user_id'], 
                $status, $is_breaking, $published_at
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $insert_id = mysqli_insert_id($conn);
                
                // Update with media files if they exist
                if (!empty($image_path) || !empty($video_url) || !empty($video_path)) {
                    $media_query = "UPDATE news SET image = ?, video_url = ?, video_path = ? WHERE id = ?";
                    $media_stmt = mysqli_prepare($conn, $media_query);
                    mysqli_stmt_bind_param($media_stmt, 'sssi', $image_path, $video_url, $video_path, $insert_id);
                    mysqli_stmt_execute($media_stmt);
                }
                
                // Update with PK Live News specific fields
                $update_query = "UPDATE news SET news_type = 'pk_live', source_url = NULL, sentiment_score = ?, sentiment_label = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, 'dsi', $sentiment_score, $sentiment_label, $insert_id);
                mysqli_stmt_execute($update_stmt);
                
                $success = "Quick post published successfully! <a href='../news.php?slug=$slug' class='alert-link' target='_blank'>View Article</a>";
                
                // Clear form
                $_POST = [];
            } else {
                $error = "Error publishing article: " . mysqli_error($conn);
            }
        } else {
            $error = "Error preparing query: " . mysqli_error($conn);
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
    <title>Quick Post - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/dmo8p48m5mmp3grrp8sig5nn4e044nvf0uq2rghq6y70f4iq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .quick-post-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .template-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 2px solid transparent;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        .template-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }
        .quick-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .char-counter {
            font-size: 0.875rem;
            font-weight: 500;
        }
        .sentiment-preview {
            min-height: 60px;
            border-radius: 10px;
            background: #f8f9fa;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="quick-post-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-bolt me-3"></i>Quick Post
                    </h1>
                    <p class="mb-0 opacity-75">Publish articles instantly with our quick posting interface</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="manage-news.php" class="btn btn-light me-2">
                        <i class="fas fa-list me-2"></i>Manage Articles
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Templates -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-magic me-2"></i>Quick Templates
                </h4>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card template-card" onclick="useTemplate('breaking')">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h6 class="card-title">Breaking News</h6>
                                <small class="text-muted">Urgent updates</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card template-card" onclick="useTemplate('update')">
                            <div class="card-body text-center">
                                <i class="fas fa-sync fa-2x text-warning mb-2"></i>
                                <h6 class="card-title">Story Update</h6>
                                <small class="text-muted">Follow-up news</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card template-card" onclick="useTemplate('analysis')">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                <h6 class="card-title">Analysis</h6>
                                <small class="text-muted">In-depth coverage</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card template-card" onclick="useTemplate('developing')">
                            <div class="card-body text-center">
                                <i class="fas fa-spinner fa-2x text-success mb-2"></i>
                                <h6 class="card-title">Developing</h6>
                                <small class="text-muted">Ongoing story</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Post Form -->
        <div class="quick-form">
            <form method="POST" id="quickPostForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Headline *
                                <span class="char-counter text-muted float-end">
                                    <span id="titleCount">0</span>/255
                                </span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   placeholder="Enter a compelling headline..."
                                   required>
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="fas fa-file-alt me-1"></i>Article Content *
                                <span class="char-counter text-muted float-end">
                                    <span id="wordCount">0</span> words • <span id="readTime">1</span> min read
                                </span>
                            </label>
                            <textarea class="form-control" id="content" name="content" rows="8" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                        </div>

                        <!-- Excerpt -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Summary
                            </label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="2" 
                                      placeholder="Brief summary for social media and previews"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                        </div>

                        <!-- Media Upload -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-photo-video me-1"></i>Media Files
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="image" class="form-label small">Image (Optional)</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <small class="text-muted">JPEG, PNG, GIF, WebP (Max 5MB)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="video" class="form-label small">Video File (Optional)</label>
                                        <input type="file" class="form-control" id="video" name="video" accept="video/*">
                                        <small class="text-muted">MP4, AVI, MOV, WMV, WebM (Max 50MB)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="video_url" class="form-label small">Video URL (Optional)</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" 
                                       placeholder="https://youtube.com/watch?v=..." 
                                       value="<?php echo isset($_POST['video_url']) ? htmlspecialchars($_POST['video_url']) : ''; ?>">
                                <small class="text-muted">YouTube, Vimeo, or other video URLs</small>
                            </div>
                        </div>

                        <!-- Sentiment Preview -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-brain me-1"></i>Sentiment Analysis
                            </label>
                            <div id="sentimentPreview" class="sentiment-preview">
                                <small class="text-muted">Start typing to see sentiment analysis...</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                <i class="fas fa-tag me-1"></i>Category *
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-eye me-1"></i>Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="featured" <?php echo isset($_POST['status']) && $_POST['status'] == 'featured' ? 'selected' : ''; ?>>Featured</option>
                            </select>
                        </div>

                        <!-- Publish Time -->
                        <div class="mb-3">
                            <label for="published_at" class="form-label">
                                <i class="fas fa-clock me-1"></i>Publish Time
                            </label>
                            <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                   value="<?php echo isset($_POST['published_at']) ? htmlspecialchars($_POST['published_at']) : date('Y-m-d\TH:i'); ?>">
                        </div>

                        <!-- Breaking News -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                       <?php echo isset($_POST['is_breaking']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_breaking">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Mark as Breaking News
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Publish Article
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-redo me-2"></i>Reset Form
                            </button>
                            <a href="manage-news.php" class="btn btn-outline-info">
                                <i class="fas fa-list me-2"></i>View All Articles
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Templates
        const templates = {
            breaking: {
                title: 'BREAKING: ',
                content: 'This is a breaking news story. Our reporters are gathering more information and will provide updates as they become available.',
                excerpt: 'Breaking news update - story developing rapidly.'
            },
            update: {
                title: 'UPDATE: ',
                content: 'Updated information regarding this ongoing story. More details are emerging as the situation develops.',
                excerpt: 'Latest updates on this developing story.'
            },
            analysis: {
                title: 'ANALYSIS: ',
                content: 'In this analysis, we examine the implications of recent events and what they mean for our readers. Expert commentary follows.',
                excerpt: 'Expert analysis and commentary on current events.'
            },
            developing: {
                title: 'DEVELOPING: ',
                content: 'This story is still developing. Our team is on the ground gathering information. Stay tuned for more updates.',
                excerpt: 'Developing story - updates to follow as more information becomes available.'
            }
        };

        function useTemplate(type) {
            const template = templates[type];
            if (template) {
                document.getElementById('title').value = template.title;
                document.getElementById('content').value = template.content;
                document.getElementById('excerpt').value = template.excerpt;
                
                // Update counts and sentiment
                updateCounts();
                updateSentimentPreview();
                
                // Visual feedback
                document.querySelectorAll('.template-card').forEach(card => card.classList.remove('active'));
                event.currentTarget.classList.add('active');
            }
        }

        function resetForm() {
            document.getElementById('quickPostForm').reset();
            document.querySelectorAll('.template-card').forEach(card => card.classList.remove('active'));
            updateCounts();
            updateSentimentPreview();
        }

        function updateCounts() {
            const title = document.getElementById('title').value;
            const content = tinymce.get('content') ? tinymce.get('content').getContent() : '';
            
            // Title count
            document.getElementById('titleCount').textContent = title.length;
            
            // Word count and read time
            const plainText = content.replace(/<[^>]*>/g, '');
            const words = plainText.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCount = words.length;
            const readTime = Math.max(1, Math.ceil(wordCount / 200));
            
            document.getElementById('wordCount').textContent = wordCount;
            document.getElementById('readTime').textContent = readTime;
        }

        function updateSentimentPreview() {
            const title = document.getElementById('title').value;
            const content = tinymce.get('content') ? tinymce.get('content').getContent() : '';
            const excerpt = document.getElementById('excerpt').value;
            
            const analysisText = title + ' ' + content.replace(/<[^>]*>/g, '') + ' ' + excerpt;
            
            if (analysisText.trim().length > 10) {
                // Simple sentiment analysis
                const positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'positive', 'happy', 'joy', 'love', 'success', 'beautiful', 'brilliant', 'hope', 'peace', 'prosperity'];
                const negativeWords = ['bad', 'terrible', 'awful', 'horrible', 'sad', 'angry', 'hate', 'fail', 'failure', 'war', 'crime', 'crisis', 'danger', 'fear'];
                
                const words = analysisText.toLowerCase().split(/\s+/);
                let score = 0;
                let positiveCount = 0;
                let negativeCount = 0;
                
                words.forEach(word => {
                    if (positiveWords.includes(word)) {
                        score += 1;
                        positiveCount++;
                    } else if (negativeWords.includes(word)) {
                        score -= 1;
                        negativeCount++;
                    }
                });
                
                const normalizedScore = words.length > 0 ? score / Math.sqrt(words.length) : 0;
                const clampedScore = Math.max(-1, Math.min(1, normalizedScore));
                
                let label = 'neutral';
                let badge = 'secondary';
                let icon = '😐';
                
                if (clampedScore > 0.1) {
                    label = 'positive';
                    badge = 'success';
                    icon = '😊';
                } else if (clampedScore < -0.1) {
                    label = 'negative';
                    badge = 'danger';
                    icon = '😔';
                }
                
                document.getElementById('sentimentPreview').innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-${badge} me-2">${label.toUpperCase()}</span>
                            <span class="fw-bold">Score: ${clampedScore.toFixed(2)}</span>
                            <span class="ms-2">${icon}</span>
                        </div>
                        <small class="text-muted">
                            <span class="text-success">+${positiveCount}</span> / 
                            <span class="text-danger">${negativeCount}</span> words
                        </small>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: ${Math.max(0, -clampedScore * 100)}%"></div>
                        <div class="progress-bar bg-secondary" style="width: ${(1 - Math.abs(clampedScore)) * 100}%"></div>
                        <div class="progress-bar bg-success" style="width: ${Math.max(0, clampedScore * 100)}%"></div>
                    </div>
                `;
            } else {
                document.getElementById('sentimentPreview').innerHTML = '<small class="text-muted">Start typing to see sentiment analysis...</small>';
            }
        }

        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 300,
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime media table paste code help wordcount'],
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist outdent indent | removeformat | help',
            setup: function(editor) {
                editor.on('change keyup', function() {
                    updateCounts();
                    updateSentimentPreview();
                });
            }
        });

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('title').addEventListener('input', function() {
                updateCounts();
                updateSentimentPreview();
            });
            
            document.getElementById('excerpt').addEventListener('input', updateSentimentPreview);
            
            // Auto-generate excerpt if empty
            document.getElementById('content').addEventListener('blur', function() {
                const excerpt = document.getElementById('excerpt');
                if (excerpt.value.trim() === '') {
                    const content = tinymce.get('content') ? tinymce.get('content').getContent() : '';
                    const plainText = content.replace(/<[^>]*>/g, '');
                    excerpt.value = plainText.substring(0, 150) + (plainText.length > 150 ? '...' : '');
                }
            });
            
            // Initialize
            updateCounts();
        });
    </script>
</body>
</html>

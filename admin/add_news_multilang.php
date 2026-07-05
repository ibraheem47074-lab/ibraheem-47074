<?php
require_once '../config/database.php';
require_once '../includes/language_functions.php';

// Check if user is admin or editor
if (!is_admin() && !is_editor()) {
    redirect('../login.php');
}

$message = '';
$error = '';

// Get active languages
$languages = get_active_languages();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic news data
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $summary = clean_input($_POST['summary']);
    $category_id = (int)$_POST['category_id'];
    $author_id = is_admin() ? (int)$_POST['author_id'] : $_SESSION['user_id'];
    $tags = clean_input($_POST['tags']);
    $status = clean_input($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
    $language_code = clean_input($_POST['language_code']);
    
    // Validation
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/news/' . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image = 'uploads/news/' . $new_filename;
                } else {
                    $error = 'Error uploading image';
                }
            } else {
                $error = 'Invalid file type. Allowed: ' . implode(', ', $allowed);
            }
        }
        
        if (empty($error)) {
            // Create slug
            $slug = create_slug($title);
            
            // Check if slug exists and make unique
            $slug_check_query = "SELECT id FROM news WHERE slug = ?";
            $slug_stmt = mysqli_prepare($conn, $slug_check_query);
            mysqli_stmt_bind_param($slug_stmt, 's', $slug);
            mysqli_stmt_execute($slug_stmt);
            $slug_result = mysqli_stmt_get_result($slug_stmt);
            
            if (mysqli_num_rows($slug_result) > 0) {
                $slug .= '-' . time();
            }
            
            // Insert main news article
            $insert_query = "INSERT INTO news (title, content, summary, slug, category_id, author_id, tags, 
                           status, is_featured, is_breaking, published_at, image, language_code) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssssissssssss', $title, $content, $summary, $slug, 
                                  $category_id, $author_id, $tags, $status, $is_featured, $is_breaking, 
                                  $published_at, $image, $language_code);
            
            if (mysqli_stmt_execute($stmt)) {
                $news_id = mysqli_insert_id($conn);
                
                // Insert multilingual content
                foreach ($languages as $lang) {
                    if ($lang['code'] === $language_code) continue; // Skip the main language
                    
                    $lang_title = clean_input($_POST['title_' . $lang['code']] ?? '');
                    $lang_content = clean_input($_POST['content_' . $lang['code']] ?? '');
                    $lang_summary = clean_input($_POST['summary_' . $lang['code']] ?? '');
                    
                    if (!empty($lang_title) || !empty($lang_content)) {
                        $update_query = "UPDATE news SET title_{$lang['code']} = ?, content_{$lang['code']} = ?, 
                                       summary_{$lang['code']} = ? WHERE id = ?";
                        $update_stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($update_stmt, 'sssi', $lang_title, $lang_content, 
                                             $lang_summary, $news_id);
                        mysqli_stmt_execute($update_stmt);
                    }
                }
                
                // Handle video URL if provided
                if (!empty($_POST['video_url'])) {
                    $video_url = clean_input($_POST['video_url']);
                    $video_query = "UPDATE news SET video_url = ? WHERE id = ?";
                    $video_stmt = mysqli_prepare($conn, $video_query);
                    mysqli_stmt_bind_param($video_stmt, 'si', $video_url, $news_id);
                    mysqli_stmt_execute($video_stmt);
                }
                
                $message = 'News article added successfully!';
                
                // Clear form
                $_POST = array();
            } else {
                $error = 'Error adding news article: ' . mysqli_error($conn);
            }
        }
    }
}

// Get categories
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Get users (for admin to select author)
$users_query = "SELECT id, name, role FROM users ORDER BY name ASC";
$users_result = mysqli_query($conn, $users_query);
?>

<?php include 'includes/admin-header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2>Add Multilingual News Article</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Language Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Primary Language</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="language_code" class="form-label">Primary Language</label>
                                    <select class="form-select" id="language_code" name="language_code" required>
                                        <?php foreach ($languages as $lang): ?>
                                            <option value="<?php echo $lang['code']; ?>">
                                                <?php echo $lang['flag_icon'] . ' ' . $lang['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">This will be the main language of the article</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content (Primary Language) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Main Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="summary" class="form-label">Summary</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="3"><?php echo htmlspecialchars($_POST['summary'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control editor" id="content" name="content" rows="10" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                        <?php mysqli_data_seek($categories_result, 0); ?>
                                    </select>
                                </div>
                                
                                <?php if (is_admin()): ?>
                                    <div class="mb-3">
                                        <label for="author_id" class="form-label">Author</label>
                                        <select class="form-select" id="author_id" name="author_id">
                                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                                <option value="<?php echo $user['id']; ?>" 
                                                    <?php echo (isset($_POST['author_id']) && $_POST['author_id'] == $user['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo $user['role']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                            <?php mysqli_data_seek($users_result, 0); ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>" 
                                           placeholder="tag1, tag2, tag3">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="video_url" class="form-label">Video URL</label>
                                    <input type="url" class="form-control" id="video_url" name="video_url" 
                                           value="<?php echo htmlspecialchars($_POST['video_url'] ?? ''); ?>" 
                                           placeholder="YouTube or other video URL">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="form-text">Max size: 5MB, Formats: jpg, png, gif, webp</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="published_at" class="form-label">Publish Date</label>
                                    <input type="datetime-local" class="form-control" id="published_at" name="published_at" 
                                           value="<?php echo htmlspecialchars($_POST['published_at'] ?? date('Y-m-d\TH:i')); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                        <option value="featured" <?php echo (isset($_POST['status']) && $_POST['status'] === 'featured') ? 'selected' : ''; ?>>Featured</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                               <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Featured Article
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                               <?php echo (isset($_POST['is_breaking']) && $_POST['is_breaking']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_breaking">
                                            Breaking News
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Multilingual Content -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Translations</h5>
                        <small class="text-muted">Optional - Add translations for other languages</small>
                    </div>
                    <div class="card-body">
                        <?php foreach ($languages as $lang): ?>
                            <?php if ($lang['code'] === 'en') continue; // Skip English as it's the main language ?>
                            
                            <div class="mb-4 p-3 border rounded">
                                <h6 class="mb-3">
                                    <?php echo $lang['flag_icon'] . ' ' . $lang['native_name']; ?>
                                    <small class="text-muted">(<?php echo $lang['name']; ?>)</small>
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="title_<?php echo $lang['code']; ?>" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title_<?php echo $lang['code']; ?>" 
                                                   name="title_<?php echo $lang['code']; ?>" 
                                                   value="<?php echo htmlspecialchars($_POST['title_' . $lang['code']] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="summary_<?php echo $lang['code']; ?>" class="form-label">Summary</label>
                                            <textarea class="form-control" id="summary_<?php echo $lang['code']; ?>" 
                                                      name="summary_<?php echo $lang['code']; ?>" rows="3"><?php echo htmlspecialchars($_POST['summary_' . $lang['code']] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="content_<?php echo $lang['code']; ?>" class="form-label">Content</label>
                                            <textarea class="form-control editor" id="content_<?php echo $lang['code']; ?>" 
                                                      name="content_<?php echo $lang['code']; ?>" rows="8"><?php echo htmlspecialchars($_POST['content_' . $lang['code']] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Article
                    </button>
                    <a href="manage-news.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rich Text Editor -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
<script>
// Initialize CKEditor for all content textareas
document.querySelectorAll('.editor').forEach(function(textarea) {
    ClassicEditor
        .create(textarea)
        .catch(error => {
            console.error(error);
        });
});
</script>

<?php include 'includes/admin-footer.php'; ?>

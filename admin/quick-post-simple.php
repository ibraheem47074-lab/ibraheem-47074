<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin or editor
if (!is_logged_in() || (!is_admin() && !is_editor())) {
    redirect('login.php');
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
        
        // Simple insert with just essential columns
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
                
                // Update with additional info after insert
                $update_query = "UPDATE news SET news_type = 'pk_live', source_url = NULL WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, 'i', $insert_id);
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
    <title>Quick Post (Simple) - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-bolt me-2"></i>Quick Post (Simple Version)</h4>
                    </div>
                    <div class="card-body">
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

                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="6" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Summary</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="published" <?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="draft" <?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_breaking" name="is_breaking" 
                                           <?php echo isset($_POST['is_breaking']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_breaking">
                                        Mark as Breaking News
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Publish Article
                                </button>
                                <a href="manage-news.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to News
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

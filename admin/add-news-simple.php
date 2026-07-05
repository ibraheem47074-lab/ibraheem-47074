<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $excerpt = substr(strip_tags($content), 0, 200) . '...';
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
    
    // Check for duplicate slug
    $counter = 1;
    $original_slug = $slug;
    while (true) {
        $check_query = "SELECT id FROM news WHERE slug = '$slug'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) == 0) {
            break;
        }
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    // Insert article
    $query = "INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, created_at, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssssiss', $title, $slug, $content, $excerpt, $category_id, $_SESSION['user_id'], $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Article published successfully!";
    } else {
        $error = "Failed to publish article: " . mysqli_error($conn);
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
    <title>Add News - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add News Article
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Content *</label>
                                <textarea name="content" class="form-control" rows="10" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Article
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

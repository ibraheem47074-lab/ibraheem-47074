<?php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new category
    if (isset($_POST['add_category'])) {
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $slug = create_slug($name);
        $meta_title = clean_input($_POST['meta_title']);
        $meta_description = clean_input($_POST['meta_description']);
        
        // Check if category already exists
        $check_query = "SELECT id FROM categories WHERE name = ? OR slug = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $error = "Category with this name or slug already exists!";
        } else {
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_filename = 'category_' . time() . '.' . $ext;
                    $upload_path = '../uploads/categories/' . $new_filename;
                    
                    if (!is_dir('../uploads/categories/')) {
                        mkdir('../uploads/categories/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = $new_filename;
                    }
                }
            }
            
            $query = "INSERT INTO categories (name, slug, description, image, meta_title, meta_description, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssss', $name, $slug, $description, $image_path, $meta_title, $meta_description);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Category added successfully!";
            } else {
                $error = "Failed to add category!";
            }
        }
    }
    
    // Update category
    if (isset($_POST['update_category'])) {
        $id = (int)$_POST['category_id'];
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $slug = create_slug($name);
        $meta_title = clean_input($_POST['meta_title']);
        $meta_description = clean_input($_POST['meta_description']);
        
        // Check if category already exists (excluding current)
        $check_query = "SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $error = "Category with this name or slug already exists!";
        } else {
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_filename = 'category_' . time() . '.' . $ext;
                    $upload_path = '../uploads/categories/' . $new_filename;
                    
                    if (!is_dir('../uploads/categories/')) {
                        mkdir('../uploads/categories/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        // Delete old image if exists
                        $old_image = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM categories WHERE id = $id"))['image'];
                        if ($old_image && file_exists('../uploads/categories/' . $old_image)) {
                            unlink('../uploads/categories/' . $old_image);
                        }
                        
                        $image_update = ", image = '$new_filename'";
                    }
                }
            } else {
                $image_update = "";
            }
            
            $query = "UPDATE categories SET name = ?, slug = ?, description = ?, meta_title = ?, meta_description = ? $image_update WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            if ($image_update) {
                mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $slug, $description, $meta_title, $meta_description, $new_filename, $id);
            } else {
                mysqli_stmt_bind_param($stmt, 'sssssi', $name, $slug, $description, $meta_title, $meta_description, $id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Category updated successfully!";
            } else {
                $error = "Failed to update category!";
            }
        }
    }
    
    // Delete category
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Check if category has articles
        $articles_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE category_id = $id"))['count'];
        
        if ($articles_count > 0) {
            $error = "Cannot delete category! It contains $articles_count articles. Move or delete the articles first.";
        } else {
            // Delete category image if exists
            $image = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM categories WHERE id = $id"))['image'];
            if ($image && file_exists('../uploads/categories/' . $image)) {
                unlink('../uploads/categories/' . $image);
            }
            
            $query = "DELETE FROM categories WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Category deleted successfully!";
            } else {
                $error = "Failed to delete category!";
            }
        }
    }
    
    // Toggle category status
    if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $current_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM categories WHERE id = $id"))['status'];
        $new_status = $current_status === 'active' ? 'inactive' : 'active';
        
        $query = "UPDATE categories SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Category status updated!";
        }
    }
}

// Get categories with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Filtering
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total categories count
$count_query = "SELECT COUNT(*) as total FROM categories $where_clause";
$stmt = mysqli_prepare($conn, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_categories = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

// Get categories
$categories_query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM news WHERE category_id = c.id) as articles_count
                    FROM categories c 
                    $where_clause 
                    ORDER BY c.created_at DESC 
                    LIMIT $per_page OFFSET $offset";
$stmt = mysqli_prepare($conn, $categories_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$categories = mysqli_stmt_get_result($stmt);

// Calculate pagination
$total_pages = ceil($total_categories / $per_page);

// Get statistics
$active_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM categories WHERE status = 'active'"))['count'];
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$uncategorized_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE category_id IS NULL OR category_id = 0"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - PK Live News Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .category-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .category-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }
        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="fas fa-tags me-3"></i>Manage Categories</h2>
                <p class="text-muted">Organize your news content with categories.</p>
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <h3><?php echo $total_categories; ?></h3>
                    <p class="mb-0">Total Categories</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <h3><?php echo $active_categories; ?></h3>
                    <p class="mb-0">Active Categories</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <h3><?php echo $total_articles; ?></h3>
                    <p class="mb-0">Total Articles</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <h3><?php echo $uncategorized_articles; ?></h3>
                    <p class="mb-0">Uncategorized</p>
                </div>
            </div>
        </div>

        <!-- Add Category Button & Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus me-2"></i>Add New Category
                                </button>
                            </div>
                            <div class="col-md-9">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search categories...">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-search me-1"></i>Filter
                                        </button>
                                        <a href="manage-categories.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Clear
                                        </a>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <small class="text-muted"><?php echo $total_categories; ?> categories</small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Categories List</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($categories) > 0): ?>
                            <div class="row">
                                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <div class="category-card">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($category['image']): ?>
                                                        <img src="../uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                                             class="category-image me-3">
                                                    <?php else: ?>
                                                        <div class="category-image bg-light d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-tag fa-2x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                        <small class="text-muted">/<?php echo htmlspecialchars($category['slug']); ?></small>
                                                    </div>
                                                </div>
                                                <span class="status-badge status-<?php echo $category['status']; ?>">
                                                    <?php echo ucfirst($category['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <?php if ($category['description']): ?>
                                                <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($category['description'], 0, 100)) . '...'; ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge bg-primary"><?php echo $category['articles_count']; ?> articles</span>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-<?php echo $category['status'] === 'active' ? 'warning' : 'success'; ?>" 
                                                            onclick="toggleStatus(<?php echo $category['id']; ?>)">
                                                        <i class="fas fa-<?php echo $category['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                    <?php if ($category['articles_count'] == 0): ?>
                                                        <button class="btn btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Created: <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Categories pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <h5>No categories found</h5>
                                <p class="text-muted">Start by creating your first category!</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus me-2"></i>Create Your First Category
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF (Max 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control" placeholder="SEO title">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3" placeholder="SEO description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editCategoryForm" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Name *</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                    <div id="current_image_preview" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" id="edit_meta_title" class="form-control" placeholder="SEO title">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" id="edit_meta_description" class="form-control" rows="3" placeholder="SEO description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id) {
            // Fetch category data via AJAX (for simplicity, we'll use a basic approach)
            fetch('get-category.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_category_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_description').value = data.description || '';
                    document.getElementById('edit_meta_title').value = data.meta_title || '';
                    document.getElementById('edit_meta_description').value = data.meta_description || '';
                    
                    if (data.image) {
                        document.getElementById('current_image_preview').innerHTML = 
                            '<img src="../uploads/categories/' + data.image + '" style="max-width: 100px; height: auto;" class="img-thumbnail">';
                    }
                    
                    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback: show modal without data
                    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
                });
        }
        
        function toggleStatus(id) {
            if (confirm('Toggle category status?')) {
                window.location.href = 'manage-categories.php?action=toggle_status&id=' + id;
            }
        }
        
        function deleteCategory(id) {
            if (confirm('Delete this category? This action cannot be undone!')) {
                window.location.href = 'manage-categories.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>

<?php
require_once '../config/database.php';
require_once 'includes/admin-header.php';

// Check if user is logged in and is admin
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Manage Affiliate Categories';
$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category has products
    $check_products = "SELECT COUNT(*) as count FROM affiliate_products WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $check_products);
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product_count = mysqli_fetch_assoc($result)['count'];
    
    if ($product_count > 0) {
        $error_message = "Cannot delete category - it has products assigned to it.";
    } else {
        $delete_query = "DELETE FROM affiliate_categories WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Category deleted successfully.";
        } else {
            $error_message = "Error deleting category: " . mysqli_error($conn);
        }
    }
}

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $slug = create_slug($name);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $sort_order = !empty($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
    $status = clean_input($_POST['status']);
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    
    if (empty($name)) {
        $error_message = "Category name is required.";
    } else {
        if ($category_id > 0) {
            // Update existing category
            $update_query = "UPDATE affiliate_categories SET 
                name = ?, slug = ?, description = ?, icon = ?, parent_id = ?, 
                sort_order = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssisisi", $name, $slug, $description, $icon, $parent_id, $sort_order, $status, $category_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Category updated successfully.";
            } else {
                $error_message = "Error updating category: " . mysqli_error($conn);
            }
        } else {
            // Check if slug already exists
            $check_slug = "SELECT id FROM affiliate_categories WHERE slug = ?";
            $stmt = mysqli_prepare($conn, $check_slug);
            mysqli_stmt_bind_param($stmt, 's', $slug);
            mysqli_stmt_execute($stmt);
            $slug_exists = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($slug_exists) > 0) {
                $slug .= '-' . time();
            }
            
            // Add new category
            $insert_query = "INSERT INTO affiliate_categories 
                (name, slug, description, icon, parent_id, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssisis", $name, $slug, $description, $icon, $parent_id, $sort_order, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Category added successfully.";
            } else {
                $error_message = "Error adding category: " . mysqli_error($conn);
            }
        }
    }
}

// Get all categories
$categories_query = "SELECT c1.*, c2.name as parent_name 
                    FROM affiliate_categories c1 
                    LEFT JOIN affiliate_categories c2 ON c1.parent_id = c2.id 
                    ORDER BY c1.sort_order, c1.name";
$categories_result = mysqli_query($conn, $categories_query);

// Get parent categories for dropdown
$parent_categories_query = "SELECT * FROM affiliate_categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order, name";
$parent_categories_result = mysqli_query($conn, $parent_categories_query);

// Get category for editing
$edit_category = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $category_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM affiliate_categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $edit_query);
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);
    $edit_result = mysqli_stmt_get_result($stmt);
    $edit_category = mysqli_fetch_assoc($edit_result);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags"></i> Manage Affiliate Categories
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 15%;">Slug</th>
                                    <th style="width: 15%;">Parent</th>
                                    <th style="width: 10%;">Icon</th>
                                    <th style="width: 8%;">Sort Order</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 7%;">Products</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($categories_result) > 0): ?>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <?php
                                        // Get product count for this category
                                        $product_count_query = "SELECT COUNT(*) as count FROM affiliate_products WHERE category_id = ?";
                                        $stmt = mysqli_prepare($conn, $product_count_query);
                                        mysqli_stmt_bind_param($stmt, 'i', $category['id']);
                                        mysqli_stmt_execute($stmt);
                                        $product_count_result = mysqli_stmt_get_result($stmt);
                                        $product_count = mysqli_fetch_assoc($product_count_result)['count'];
                                        ?>
                                        <tr>
                                            <td style="font-weight: bold; color: #2c3e50;">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                                <?php if ($category['parent_id']): ?>
                                                    <br><small style="color: #6c757d;">(Subcategory)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;"><?php echo htmlspecialchars($category['slug']); ?></code>
                                            </td>
                                            <td>
                                                <?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '<span style="color: #6c757d;">None</span>'; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($category['icon'])): ?>
                                                    <i class="<?php echo htmlspecialchars($category['icon']); ?>" style="color: #007bff; margin-right: 5px;"></i>
                                                    <small><?php echo htmlspecialchars($category['icon']); ?></small>
                                                <?php else: ?>
                                                    <span style="color: #6c757d;">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white"><?php echo $category['sort_order']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo ($category['status'] === 'active') ? 'success' : 'danger'; ?> text-white">
                                                    <?php echo ucfirst($category['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary text-white"><?php echo number_format($product_count); ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            onclick="editCategory(<?php echo $category['id']; ?>)"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?delete=<?php echo $category['id']; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Are you sure you want to delete this category?')"
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center" style="padding: 30px;">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                            <br>
                                            <strong>No categories found.</strong>
                                            <br>
                                            <small class="text-muted">Start by adding your first product category.</small>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tag"></i> 
                    <span id="modalTitle">Add Category</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="categoryId" value="0">
                    
                    <div class="form-group mb-3">
                        <label for="name">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="icon">Icon Class</label>
                                <input type="text" class="form-control" id="icon" name="icon" 
                                       placeholder="fas fa-laptop">
                                <small class="form-text text-muted">Font Awesome icon class (e.g., fas fa-laptop)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="0" min="0">
                                <small class="form-text text-muted">Lower numbers appear first</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="parent_id">Parent Category</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">None (Root Category)</option>
                                    <?php 
                                    mysqli_data_seek($parent_categories_result, 0);
                                    while ($parent = mysqli_fetch_assoc($parent_categories_result)): 
                                    ?>
                                        <option value="<?php echo $parent['id']; ?>">
                                            <?php echo htmlspecialchars($parent['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(categoryId) {
    // Fetch category data via AJAX or use PHP data
    <?php if ($edit_category): ?>
    // Pre-fill form with existing data
    document.getElementById('categoryId').value = '<?php echo $edit_category['id']; ?>';
    document.getElementById('name').value = '<?php echo htmlspecialchars($edit_category['name']); ?>';
    document.getElementById('description').value = '<?php echo htmlspecialchars($edit_category['description']); ?>';
    document.getElementById('icon').value = '<?php echo htmlspecialchars($edit_category['icon']); ?>';
    document.getElementById('sort_order').value = '<?php echo $edit_category['sort_order']; ?>';
    document.getElementById('parent_id').value = '<?php echo $edit_category['parent_id']; ?>';
    document.getElementById('status').value = '<?php echo $edit_category['status']; ?>';
    document.getElementById('modalTitle').textContent = 'Edit Category';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
    <?php else: ?>
    // For dynamic loading, you would make an AJAX call here
    window.location.href = '?edit=' + categoryId;
    <?php endif; ?>
}

// Reset form when modal is hidden
document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '0';
    document.getElementById('modalTitle').textContent = 'Add Category';
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>

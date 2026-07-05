<?php
require_once '../config/database.php';
require_once 'includes/admin-header.php';

// Check if user is logged in and is admin
if (!is_admin() && !is_editor()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Manage Affiliate Products';
$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    
    // Check if product is linked to any news
    $check_news = "SELECT COUNT(*) as count FROM news_product_relations WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $check_news);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $news_count = mysqli_fetch_assoc($result)['count'];
    
    if ($news_count > 0) {
        $error_message = "Cannot delete product - it is linked to news articles.";
    } else {
        $delete_query = "DELETE FROM affiliate_products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'i', $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Product deleted successfully.";
        } else {
            $error_message = "Error deleting product: " . mysqli_error($conn);
        }
    }
}

// Handle toggle featured action
if (isset($_GET['toggle_featured']) && !empty($_GET['toggle_featured'])) {
    $product_id = intval($_GET['toggle_featured']);
    
    $toggle_query = "UPDATE affiliate_products SET featured = NOT featured WHERE id = ?";
    $stmt = mysqli_prepare($conn, $toggle_query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Product featured status updated.";
    } else {
        $error_message = "Error updating product: " . mysqli_error($conn);
    }
}

// Handle toggle status action
if (isset($_GET['toggle_status']) && !empty($_GET['toggle_status'])) {
    $product_id = intval($_GET['toggle_status']);
    
    $toggle_query = "UPDATE affiliate_products SET status = CASE 
                     WHEN status = 'active' THEN 'inactive' 
                     WHEN status = 'inactive' THEN 'active' 
                     ELSE status 
                     END WHERE id = ?";
    $stmt = mysqli_prepare($conn, $toggle_query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Product status updated.";
    } else {
        $error_message = "Error updating product: " . mysqli_error($conn);
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search and filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$featured_filter = isset($_GET['featured']) ? clean_input($_GET['featured']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.brand LIKE ? OR p.model LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

if (!empty($category_filter)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if (!empty($status_filter)) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($featured_filter)) {
    $where_conditions[] = "p.featured = ?";
    $params[] = ($featured_filter === 'yes') ? 1 : 0;
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get categories for filter
$categories_result = null;
$categories_query = "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY name";
// Check if table exists first
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_categories'");
if (mysqli_num_rows($table_check) > 0) {
    $categories_result = mysqli_query($conn, $categories_query);
}

// Check if affiliate_products table exists
$products_table_exists = false;
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($table_check) > 0) {
    $products_table_exists = true;
}

// Count total products
$total_products = 0;
$total_pages = 0;
if ($products_table_exists) {
    $count_query = "SELECT COUNT(*) as total FROM affiliate_products p $where_clause";
    $stmt = mysqli_prepare($conn, $count_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
    $total_products = mysqli_fetch_assoc($total_result)['total'];
    $total_pages = ceil($total_products / $per_page);
}

// Get products
$products_result = null;
if ($products_table_exists) {
    $products_query = "SELECT p.*, c.name as category_name 
                      FROM affiliate_products p 
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id 
                      $where_clause 
                      ORDER BY p.created_at DESC 
                      LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $products_query);
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types = $types . 'ii';
    if (!empty($all_params)) {
        mysqli_stmt_bind_param($stmt, $all_types, ...$all_params);
    }
    mysqli_stmt_execute($stmt);
    $products_result = mysqli_stmt_get_result($stmt);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Manage Affiliate Products
                    </h3>
                    <div class="card-tools">
                        <a href="add-affiliate-product.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($status_filter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="featured">
                                    <option value="">All Products</option>
                                    <option value="yes" <?php echo ($featured_filter === 'yes') ? 'selected' : ''; ?>>Featured</option>
                                    <option value="no" <?php echo ($featured_filter === 'no') ? 'selected' : ''; ?>>Not Featured</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="manage-affiliate-products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Products Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Rating</th>
                                    <th>Network</th>
                                    <th>Clicks</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($products_result) > 0): ?>
                                    <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($product['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                                <?php if (!empty($product['brand'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                                                <?php endif; ?>
                                                <?php if ($product['featured']): ?>
                                                    <br><span class="badge badge-warning">Featured</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?></td>
                                            <td>
                                                <?php if ($product['price']): ?>
                                                    <?php echo htmlspecialchars($product['currency']); ?> <?php echo number_format($product['price'], 2); ?>
                                                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                                        <br><small class="text-muted"><del><?php echo number_format($product['original_price'], 2); ?></del></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['rating'] > 0): ?>
                                                    <div class="text-warning">
                                                        <?php echo str_repeat('★', round($product['rating'])); ?>
                                                        <?php echo str_repeat('☆', 5 - round($product['rating'])); ?>
                                                        <br><small><?php echo $product['review_count']; ?> reviews</small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No rating</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo ($product['affiliate_network'] === 'amazon') ? 'warning' : 'info'; ?>">
                                                    <?php echo ucfirst($product['affiliate_network']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo number_format($product['click_count']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo ($product['status'] === 'active') ? 'success' : (($product['status'] === 'pending') ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($product['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit-affiliate-product.php?id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?toggle_featured=<?php echo $product['id']; ?>" 
                                                       class="btn btn-warning" title="Toggle Featured">
                                                        <i class="fas fa-star"></i>
                                                    </a>
                                                    <a href="?toggle_status=<?php echo $product['id']; ?>" 
                                                       class="btn btn-secondary" title="Toggle Status">
                                                        <i class="fas fa-power-off"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                                       class="btn btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this product?')"
                                                       title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No products found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center">
                            <nav>
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo urlencode($status_filter); ?>&featured=<?php echo urlencode($featured_filter); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

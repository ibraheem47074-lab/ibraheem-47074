<?php
require_once '../config/database.php';
require_once 'includes/admin-header.php';

// Check if user is logged in and is admin
if (!is_admin() && !is_editor()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Add Affiliate Product';
$success_message = '';
$error_message = '';

// Get categories
$categories_result = null;
$categories_query = "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order, name";
// Check if table exists first
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_categories'");
if (mysqli_num_rows($table_check) > 0) {
    $categories_result = mysqli_query($conn, $categories_query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $slug = create_slug($title);
    $description = clean_input($_POST['description']);
    $short_description = clean_input($_POST['short_description']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $currency = clean_input($_POST['currency']);
    $image_url = clean_input($_POST['image_url']);
    $affiliate_url = clean_input($_POST['affiliate_url']);
    $affiliate_network = clean_input($_POST['affiliate_network']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $rating = !empty($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $review_count = !empty($_POST['review_count']) ? intval($_POST['review_count']) : 0;
    $availability = clean_input($_POST['availability']);
    $brand = clean_input($_POST['brand']);
    $model = clean_input($_POST['model']);
    $tags = clean_input($_POST['tags']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = clean_input($_POST['status']);

    // Validate required fields
    if (empty($title) || empty($affiliate_url)) {
        $error_message = "Title and Affiliate URL are required fields.";
    } else {
        // Check if slug already exists
        $check_slug = "SELECT id FROM affiliate_products WHERE slug = ?";
        $stmt = mysqli_prepare($conn, $check_slug);
        mysqli_stmt_bind_param($stmt, 's', $slug);
        mysqli_stmt_execute($stmt);
        $slug_exists = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($slug_exists) > 0) {
            $slug .= '-' . time();
        }

        // Insert product
        $insert_query = "INSERT INTO affiliate_products (
            title, slug, description, short_description, price, original_price, 
            currency, image_url, affiliate_url, affiliate_network, category_id, 
            rating, review_count, availability, brand, model, tags, featured, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssddsssssiissssiss", 
            $title, $slug, $description, $short_description, $price, $original_price,
            $currency, $image_url, $affiliate_url, $affiliate_network, $category_id,
            $rating, $review_count, $availability, $brand, $model, $tags, $featured, $status
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Product added successfully! <a href='manage-affiliate-products.php'>Manage Products</a>";
        } else {
            $error_message = "Error adding product: " . mysqli_error($conn);
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Add Affiliate Product
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Product Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>

                                <div class="form-group">
                                    <label for="short_description">Short Description</label>
                                    <input type="text" class="form-control" id="short_description" name="short_description" 
                                           placeholder="Brief description (max 500 chars)" maxlength="500">
                                </div>

                                <div class="form-group">
                                    <label for="description">Full Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Sale Price</label>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   step="0.01" min="0" placeholder="299.99">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="original_price">Original Price</label>
                                            <input type="number" class="form-control" id="original_price" name="original_price" 
                                                   step="0.01" min="0" placeholder="399.99">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="currency">Currency</label>
                                            <select class="form-control" id="currency" name="currency">
                                                <option value="USD">USD ($)</option>
                                                <option value="EUR">EUR (€)</option>
                                                <option value="GBP">GBP (£)</option>
                                                <option value="PKR">PKR (₨)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="brand">Brand</label>
                                            <input type="text" class="form-control" id="brand" name="brand" 
                                                   placeholder="Apple, Samsung, etc.">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="model">Model</label>
                                            <input type="text" class="form-control" id="model" name="model" 
                                                   placeholder="iPhone 15 Pro, etc.">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="affiliate_url">Affiliate URL *</label>
                                    <input type="url" class="form-control" id="affiliate_url" name="affiliate_url" required
                                           placeholder="https://amazon.com/dp/...">
                                    <small class="form-text text-muted">Your affiliate link from Amazon, AliExpress, etc.</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="affiliate_network">Affiliate Network</label>
                                            <select class="form-control" id="affiliate_network" name="affiliate_network">
                                                <option value="amazon">Amazon</option>
                                                <option value="aliexpress">AliExpress</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="availability">Availability</label>
                                            <select class="form-control" id="availability" name="availability">
                                                <option value="in_stock">In Stock</option>
                                                <option value="limited">Limited Stock</option>
                                                <option value="out_of_stock">Out of Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tags">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           placeholder="smartphone, 5g, camera, gaming">
                                    <small class="form-text text-muted">Comma-separated tags</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="image_url">Product Image URL</label>
                                    <input type="url" class="form-control" id="image_url" name="image_url" 
                                           placeholder="https://example.com/image.jpg">
                                    <small class="form-text text-muted">Direct image URL</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="rating">Rating</label>
                                            <input type="number" class="form-control" id="rating" name="rating" 
                                                   step="0.1" min="0" max="5" placeholder="4.5">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="review_count">Review Count</label>
                                            <input type="number" class="form-control" id="review_count" name="review_count" 
                                                   min="0" placeholder="1234">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featured" name="featured">
                                        <label class="custom-control-label" for="featured">Featured Product</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    <strong>Affiliate Disclosure:</strong> This product will be displayed with 
                                    affiliate links. Ensure you comply with Amazon/AliExpress policies.
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Product
                            </button>
                            <a href="manage-affiliate-products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

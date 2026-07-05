<?php
require_once 'config/database.php';
require_once 'includes/language_functions.php';

// Check if affiliate tables exist
$affiliate_tables_exist = false;
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($tables_check) > 0) {
    $affiliate_tables_exist = true;
    require_once 'includes/affiliate-functions.php';
}

$page_title = 'Products - ' . SITE_NAME;
$current_lang = get_current_language();
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get filters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$sort = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'latest';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// Build query - only if tables exist
$where_conditions = ["p.status = 'active'"];
$params = [];
$types = '';
$where_clause = '';

if ($affiliate_tables_exist) {
    if (!empty($category_id)) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_id;
        $types .= 'i';
    }

    if (!empty($search)) {
        $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.brand LIKE ? OR p.tags LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        $types .= 'ssss';
    }

    if ($min_price > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
        $types .= 'd';
    }

    if ($max_price > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
        $types .= 'd';
    }

    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Sorting
$order_by = 'ORDER BY p.created_at DESC';
switch ($sort) {
    case 'price-low':
        $order_by = 'ORDER BY p.price ASC';
        break;
    case 'price-high':
        $order_by = 'ORDER BY p.price DESC';
        break;
    case 'rating':
        $order_by = 'ORDER BY p.rating DESC, p.review_count DESC';
        break;
    case 'popular':
        $order_by = 'ORDER BY p.click_count DESC';
        break;
    case 'featured':
        $order_by = 'ORDER BY p.featured DESC, p.created_at DESC';
        break;
}

// Count total products
if ($affiliate_tables_exist) {
    $count_query = "SELECT COUNT(*) as total FROM affiliate_products p $where_clause";
    $stmt = mysqli_prepare($conn, $count_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
    $total_products = mysqli_fetch_assoc($total_result)['total'];
    $total_pages = ceil($total_products / $per_page);
} else {
    $total_products = 0;
    $total_pages = 0;
    $where_clause = '';
}

// Get products
if ($affiliate_tables_exist) {
    $products_query = "SELECT p.*, c.name as category_name 
                      FROM affiliate_products p 
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id 
                      $where_clause 
                      $order_by 
                      LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $products_query);
    $all_params = array_merge($params, [$per_page, $offset]);
    $all_types = $types . 'ii';
    if (!empty($all_params)) {
        mysqli_stmt_bind_param($stmt, $all_types, ...$all_params);
    }
    mysqli_stmt_execute($stmt);
    $products_result = mysqli_stmt_get_result($stmt);
} else {
    $products_result = false;
}

// Get categories for filter
if ($affiliate_tables_exist) {
    $categories = get_product_categories();
} else {
    $categories = [];
}

// Get featured products for sidebar
if ($affiliate_tables_exist) {
    $featured_products = get_featured_products(3);
} else {
    $featured_products = [];
}
?>

<div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-2">Products & Deals</h1>
                        <p class="text-muted">Discover amazing products and exclusive deals</p>
                    </div>
                    <?php 
if ($affiliate_tables_exist && function_exists('display_affiliate_disclosure')) {
    echo display_affiliate_disclosure();
} 
?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="Min Price" value="<?php echo $min_price; ?>" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="Max Price" value="<?php echo $max_price; ?>" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="sort">
                                    <option value="latest" <?php echo ($sort === 'latest') ? 'selected' : ''; ?>>Latest</option>
                                    <option value="price-low" <?php echo ($sort === 'price-low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price-high" <?php echo ($sort === 'price-high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="rating" <?php echo ($sort === 'rating') ? 'selected' : ''; ?>>Highest Rated</option>
                                    <option value="popular" <?php echo ($sort === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                                    <option value="featured" <?php echo ($sort === 'featured') ? 'selected' : ''; ?>>Featured</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Category Pills -->
                <div class="product-categories mb-4">
                    <a href="products.php" class="category-pill <?php echo empty($category_id) ? 'active' : ''; ?>">
                        All Products
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?>" 
                           class="category-pill <?php echo ($category_id == $category['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Products Grid -->
                <?php if ($affiliate_tables_exist && $products_result && mysqli_num_rows($products_result) > 0): ?>
                    <div class="products-grid">
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <?php 
                            if (function_exists('display_product_card')) {
                                echo display_product_card($product);
                            } else {
                                // Fallback product display
                                echo '<div class="col-md-6 col-lg-4 mb-4">';
                                echo '<div class="card h-100">';
                                if (!empty($product['image_url'])) {
                                    echo '<img src="' . htmlspecialchars($product['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($product['name']) . '">';
                                }
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>';
                                if (!empty($product['description'])) {
                                    echo '<p class="card-text">' . substr(htmlspecialchars($product['description']), 0, 100) . '...</p>';
                                }
                                echo '<h6 class="text-primary">$' . number_format($product['price'], 2) . '</h6>';
                                echo '<a href="' . htmlspecialchars($product['affiliate_url']) . '" class="btn btn-primary" target="_blank">View Product</a>';
                                echo '</div></div></div>';
                            }
                            ?>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Products pagination" class="mt-5">
                            <div class="d-flex justify-content-center">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    if ($start_page > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                        if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        $active_class = ($page === $i) ? 'active' : '';
                                        echo "<li class='page-item $active_class'>
                                                <a class='page-link' href='?page=$i&search=" . urlencode($search) . "&category=$category_id&sort=$sort&min_price=$min_price&max_price=$max_price'>$i</a>
                                              </li>";
                                    }
                                    
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        echo "<li class='page-item'><a class='page-link' href='?page=$total_pages&search=" . urlencode($search) . "&category=$category_id&sort=$sort&min_price=$min_price&max_price=$max_price'>$total_pages</a></li>";
                                    }
                                    ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </nav>
                    <?php endif; ?>

                <?php elseif (!$affiliate_tables_exist): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-database fa-3x text-muted mb-3"></i>
                        <h4>Products System Not Ready</h4>
                        <p class="text-muted">The affiliate products system needs to be set up first.</p>
                        <div class="alert alert-info d-inline-block">
                            <strong>Admin:</strong> Please run <code>create_affiliate_tables.php</code> to set up the products system.
                        </div>
                        <a href="index.php" class="btn btn-primary">Back to Home</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms.</p>
                        <a href="products.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Featured Products -->
                <?php if ($affiliate_tables_exist && !empty($featured_products)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-star text-warning"></i> Featured Deals
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($featured_products as $product): ?>
                                <div class="sidebar-product">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                             class="product-image">
                                    <?php endif; ?>
                                    <div class="product-body">
                                        <h6 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h6>
                                        <div class="product-price">
                                            <?php 
                                            if (function_exists('format_product_price')) {
                                                echo format_product_price($product['price'], $product['currency']);
                                            } else {
                                                echo '$' . number_format($product['price'], 2);
                                            }
                                            ?>
                                        </div>
                                        <a href="<?php 
                                        if (function_exists('generate_affiliate_link')) {
                                            echo generate_affiliate_link($product['id'], $product['affiliate_url']);
                                        } else {
                                            echo htmlspecialchars($product['affiliate_url']);
                                        }
                                        ?>" 
                                           target="_blank" class="btn btn-primary btn-sm">
                                            Buy Now
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Categories -->
                <?php if ($affiliate_tables_exist && !empty($categories)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-th-large"></i> Categories
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="products.php" class="list-group-item list-group-item-action <?php echo empty($category_id) ? 'active' : ''; ?>">
                                    All Products
                                </a>
                                <?php foreach ($categories as $category): ?>
                                    <a href="products.php?category=<?php echo $category['id']; ?>" 
                                       class="list-group-item list-group-item-action <?php echo ($category_id == $category['id']) ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <?php if (!empty($category['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($category['icon']); ?> float-end"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Affiliate Networks -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shopping-bag"></i> Shop From
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="products.php?network=amazon" class="btn btn-warning">
                                <i class="fab fa-amazon me-2"></i> Amazon
                            </a>
                            <a href="products.php?network=aliexpress" class="btn btn-danger">
                                <i class="fas fa-shopping-cart me-2"></i> AliExpress
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Only load affiliate products JS if tables exist -->
    <?php if ($affiliate_tables_exist): ?>
    <script src="assets/js/affiliate-products.js"></script>
    <?php endif; ?>
</body>
</html>

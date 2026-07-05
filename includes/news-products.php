<?php
// News Products Integration Component

require_once 'affiliate-functions.php';

/**
 * Display products related to a news article
 */
function display_news_products($news_id, $news_title = '', $news_content = '', $position = 'sidebar') {
    // Try to get manually linked products first
    $linked_products = get_news_related_products($news_id, 4);
    
    // If no linked products, get smart recommendations
    if (empty($linked_products)) {
        $linked_products = get_smart_product_recommendations($news_title, $news_content, 4);
    }
    
    if (empty($linked_products)) {
        return '';
    }
    
    $html = '';
    
    if ($position === 'sidebar') {
        $html = generate_sidebar_products($linked_products);
    } elseif ($position === 'bottom') {
        $html = generate_bottom_products($linked_products);
    } elseif ($position === 'inline') {
        $html = generate_inline_products($linked_products);
    }
    
    return $html;
}

/**
 * Generate sidebar products HTML
 */
function generate_sidebar_products($products) {
    $html = '
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-bag text-primary"></i> Recommended Products
                </h5>
            </div>
            <div class="card-body">';
    
    foreach ($products as $product) {
        $image_url = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/images/product-placeholder.jpg';
        $title = htmlspecialchars($product['title']);
        $price = format_product_price($product['price'], $product['currency']);
        $affiliate_link = generate_affiliate_link($product['id'], $product['affiliate_url']);
        
        $html .= '
            <div class="sidebar-product">
                <img src="' . $image_url . '" alt="' . $title . '" class="product-image">
                <div class="product-body">
                    <h6 class="product-title">' . $title . '</h6>
                    <div class="product-price">' . $price . '</div>
                    <a href="' . $affiliate_link . '" target="_blank" class="affiliate-btn ' . $product['affiliate_network'] . '">
                        Buy Now
                    </a>
                </div>
            </div>';
    }
    
    $html .= '
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Affiliate links - we may earn a commission
                </small>
            </div>
        </div>';
    
    return $html;
}

/**
 * Generate bottom products HTML (full width)
 */
function generate_bottom_products($products) {
    $html = '
        <div class="news-products">
            <h3 class="products-title">
                <i class="fas fa-shopping-cart"></i> Related Products & Deals
            </h3>
            ' . display_affiliate_disclosure() . '
            <div class="products-grid products-3">';
    
    foreach ($products as $product) {
        $html .= display_product_card($product);
    }
    
    $html .= '
            </div>
            <div class="text-center mt-3">
                <a href="products.php" class="btn btn-outline-primary">
                    View All Products <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>';
    
    return $html;
}

/**
 * Generate inline products HTML (compact list)
 */
function generate_inline_products($products) {
    $html = '
        <div class="news-products">
            <h4 class="products-title">
                <i class="fas fa-tag"></i> Featured Deals
            </h4>
            <div class="product-list">';
    
    foreach ($products as $product) {
        $image_url = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/images/product-placeholder.jpg';
        $title = htmlspecialchars($product['title']);
        $price = format_product_price($product['price'], $product['currency']);
        $affiliate_link = generate_affiliate_link($product['id'], $product['affiliate_url']);
        
        $html .= '
            <div class="product-item">
                <img src="' . $image_url . '" alt="' . $title . '" class="product-image">
                <div class="product-details">
                    <h5 class="product-title">' . $title . '</h5>
                    <div class="product-price">' . $price . '</div>
                    <a href="' . $affiliate_link . '" target="_blank" class="affiliate-btn ' . $product['affiliate_network'] . '">
                        Buy Now
                    </a>
                </div>
            </div>';
    }
    
    $html .= '
            </div>
        </div>';
    
    return $html;
}

/**
 * Get trending products for homepage
 */
function get_trending_products($limit = 8) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              ORDER BY p.click_count DESC, p.created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
    
    return $products;
}

/**
 * Display products section for homepage
 */
function display_homepage_products_section() {
    $featured_products = get_featured_products(4);
    $trending_products = get_trending_products(4);
    
    $html = '';
    
    // Featured Products Section
    if (!empty($featured_products)) {
        $html .= '
            <section class="products-section">
                <div class="container">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <i class="fas fa-star text-warning"></i> Featured Deals
                            </h2>
                            <p class="section-subtitle">Hand-picked products with exclusive offers</p>
                        </div>
                        <a href="products.php?sort=featured" class="view-all-btn">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="products-grid products-4">';
        
        foreach ($featured_products as $product) {
            $html .= display_product_card($product);
        }
        
        $html .= '
                    </div>
                </div>
            </section>';
    }
    
    // Trending Products Section
    if (!empty($trending_products)) {
        $html .= '
            <section class="products-section bg-light">
                <div class="container">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <i class="fas fa-fire text-danger"></i> Trending Now
                            </h2>
                            <p class="section-subtitle">Most popular products this week</p>
                        </div>
                        <a href="products.php?sort=popular" class="view-all-btn">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="products-grid products-4">';
        
        foreach ($trending_products as $product) {
            $html .= display_product_card($product);
        }
        
        $html .= '
                    </div>
                </div>
            </section>';
    }
    
    return $html;
}

/**
 * Add product management to news edit form
 */
function add_product_selection_to_news_form($news_id = 0) {
    global $conn;
    
    // Get all active products
    $products_query = "SELECT p.*, c.name as category_name 
                      FROM affiliate_products p 
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id 
                      WHERE p.status = 'active' 
                      ORDER BY p.title";
    $products_result = mysqli_query($conn, $products_query);
    
    // Get already linked products if editing
    $linked_products = [];
    if ($news_id > 0) {
        $linked_query = "SELECT product_id FROM news_product_relations WHERE news_id = ?";
        $stmt = mysqli_prepare($conn, $linked_query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $linked_result = mysqli_stmt_get_result($stmt);
        
        while ($linked = mysqli_fetch_assoc($linked_result)) {
            $linked_products[] = $linked['product_id'];
        }
    }
    
    ob_start();
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-shopping-cart"></i> Related Products
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Select products to display with this news article:</p>
            
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Available Products</label>
                    <select class="form-select" id="availableProducts" size="8" multiple>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <?php if (!in_array($product['id'], $linked_products)): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['title']); ?> 
                                    (<?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?>)
                                </option>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Selected Products</label>
                    <select class="form-select" id="selectedProducts" name="selected_products[]" size="8" multiple>
                        <?php 
                        // Reset result pointer and show selected products
                        mysqli_data_seek($products_result, 0);
                        while ($product = mysqli_fetch_assoc($products_result)): 
                            if (in_array($product['id'], $linked_products)):
                        ?>
                            <option value="<?php echo $product['id']; ?>" selected>
                                <?php echo htmlspecialchars($product['title']); ?> 
                                (<?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?>)
                            </option>
                        <?php 
                            endif;
                        endwhile; 
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="button" class="btn btn-sm btn-primary" onclick="addProductToSelection()">
                    <i class="fas fa-plus"></i> Add to Selection
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeProductFromSelection()">
                    <i class="fas fa-minus"></i> Remove from Selection
                </button>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Display Position</label>
                    <select class="form-select" name="product_display_position">
                        <option value="sidebar">Sidebar</option>
                        <option value="bottom">Bottom of Article</option>
                        <option value="inline">Inline in Content</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Auto-Recommend Products</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="auto_recommend" id="autoRecommend" checked>
                        <label class="form-check-label" for="autoRecommend">
                            Automatically suggest related products based on content
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function addProductToSelection() {
        const available = document.getElementById('availableProducts');
        const selected = document.getElementById('selectedProducts');
        
        for (let i = 0; i < available.options.length; i++) {
            if (available.options[i].selected) {
                const option = available.options[i];
                selected.appendChild(option.cloneNode(true));
                option.remove();
                i--; // Adjust index after removal
            }
        }
    }
    
    function removeProductFromSelection() {
        const available = document.getElementById('availableProducts');
        const selected = document.getElementById('selectedProducts');
        
        for (let i = 0; i < selected.options.length; i++) {
            if (selected.options[i].selected) {
                const option = selected.options[i];
                available.appendChild(option.cloneNode(true));
                option.remove();
                i--; // Adjust index after removal
            }
        }
    }
    
    // Select all options before form submission
    document.querySelector('form').addEventListener('submit', function() {
        const selected = document.getElementById('selectedProducts');
        for (let i = 0; i < selected.options.length; i++) {
            selected.options[i].selected = true;
        }
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Save news product relations
 */
function save_news_product_relations($news_id, $selected_products, $display_position = 'sidebar') {
    global $conn;
    
    // Delete existing relations
    $delete_query = "DELETE FROM news_product_relations WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    
    // Insert new relations
    if (!empty($selected_products) && is_array($selected_products)) {
        $insert_query = "INSERT INTO news_product_relations (news_id, product_id, display_position, sort_order) 
                         VALUES (?, ?, ?, ?)";
        
        foreach ($selected_products as $index => $product_id) {
            $sort_order = $index + 1;
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iisi', $news_id, $product_id, $display_position, $sort_order);
            mysqli_stmt_execute($stmt);
        }
    }
}
?>

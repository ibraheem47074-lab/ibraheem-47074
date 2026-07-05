<?php
// Affiliate Marketing Functions

require_once __DIR__ . '/../config/database.php';

/**
 * Get featured products for display
 */
function get_featured_products($limit = 6) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE p.status = 'active' AND p.featured = 1 
              ORDER BY p.created_at DESC 
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
 * Get products by category
 */
function get_products_by_category($category_id, $limit = 12) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE p.status = 'active' AND p.category_id = ? 
              ORDER BY p.created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $category_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
    
    return $products;
}

/**
 * Get latest products
 */
function get_latest_products($limit = 8) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              ORDER BY p.created_at DESC 
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
 * Get products related to news article
 */
function get_news_related_products($news_id, $limit = 4) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name, npr.display_position 
              FROM news_product_relations npr 
              JOIN affiliate_products p ON npr.product_id = p.id 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE npr.news_id = ? AND p.status = 'active' 
              ORDER BY npr.sort_order, p.created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $news_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
    
    return $products;
}

/**
 * Get smart product recommendations based on news content
 */
function get_smart_product_recommendations($news_title, $news_content, $limit = 4) {
    global $conn;
    
    // Extract keywords from news
    $keywords = extract_product_keywords($news_title . ' ' . $news_content);
    
    if (empty($keywords)) {
        return get_featured_products($limit);
    }
    
    // Build search query
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    $types = '';
    
    foreach ($keywords as $keyword) {
        $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ? OR p.brand LIKE ?)";
        $keyword_param = "%$keyword%";
        $params = array_merge($params, [$keyword_param, $keyword_param, $keyword_param, $keyword_param]);
        $types .= 'ssss';
    }
    
    $where_clause = implode(' OR ', $where_conditions);
    
    $query = "SELECT p.*, c.name as category_name,
                     MATCH(p.title, p.description) AGAINST(?) as relevance_score
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE $where_clause
              ORDER BY relevance_score DESC, p.featured DESC, p.created_at DESC 
              LIMIT ?";
    
    $search_term = implode(' ', $keywords);
    $all_params = array_merge([$search_term], $params, [$limit]);
    $all_types = 's' . $types . 'i';
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $all_types, ...$all_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
    
    // If no products found, return featured products
    if (empty($products)) {
        return get_featured_products($limit);
    }
    
    return $products;
}

/**
 * Extract product-related keywords from text
 */
function extract_product_keywords($text) {
    // Common product-related keywords
    $product_keywords = [
        'phone', 'mobile', 'smartphone', 'iphone', 'samsung', 'xiaomi', 'oppo', 'vivo',
        'laptop', 'computer', 'macbook', 'dell', 'hp', 'lenovo',
        'tablet', 'ipad', 'samsung tablet',
        'camera', 'canon', 'nikon', 'sony',
        'headphone', 'earphone', 'airpod', 'speaker',
        'watch', 'smartwatch', 'apple watch',
        'tv', 'television', 'smart tv',
        'gaming', 'playstation', 'xbox', 'nintendo',
        'shoes', 'nike', 'adidas',
        'clothing', 'fashion',
        'book', 'kindle'
    ];
    
    $text = strtolower($text);
    $found_keywords = [];
    
    foreach ($product_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            $found_keywords[] = $keyword;
        }
    }
    
    return array_unique($found_keywords);
}

/**
 * Track affiliate click
 */
function track_affiliate_click($product_id) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Insert click record
    $insert_query = "INSERT INTO affiliate_clicks (product_id, ip_address, user_agent, referrer) 
                     VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, 'isss', $product_id, $ip_address, $user_agent, $referrer);
    mysqli_stmt_execute($stmt);
    
    // Update product click count
    $update_query = "UPDATE affiliate_products SET click_count = click_count + 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    
    return true;
}

/**
 * Get product categories
 */
function get_product_categories() {
    global $conn;
    
    $query = "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order, name";
    $result = mysqli_query($conn, $query);
    
    $categories = [];
    while ($category = mysqli_fetch_assoc($result)) {
        $categories[] = $category;
    }
    
    return $categories;
}

/**
 * Get single product by ID
 */
function get_product($product_id) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM affiliate_products p 
              LEFT JOIN affiliate_categories c ON p.category_id = c.id 
              WHERE p.id = ? AND p.status = 'active'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Get products for sidebar
 */
function get_sidebar_products($limit = 3) {
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
 * Format product price with currency
 */
function format_product_price($price, $currency = 'USD') {
    if (empty($price)) return 'Price not available';
    
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'PKR' => '₨'
    ];
    
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($price, 2);
}

/**
 * Generate affiliate link with tracking
 */
function generate_affiliate_link($product_id, $affiliate_url) {
    // Create tracking URL
    $tracking_url = SITE_URL . "affiliate-click.php?product_id=$product_id&redirect=" . urlencode($affiliate_url);
    return $tracking_url;
}

/**
 * Display product card HTML
 */
function display_product_card($product, $size = 'normal') {
    $image_url = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/images/product-placeholder.jpg';
    $title = htmlspecialchars($product['title']);
    $description = htmlspecialchars($product['short_description'] ?? substr($product['description'], 0, 100) . '...');
    $price = format_product_price($product['price'], $product['currency']);
    $original_price = !empty($product['original_price']) ? format_product_price($product['original_price'], $product['currency']) : '';
    $affiliate_link = generate_affiliate_link($product['id'], $product['affiliate_url']);
    $network_class = $product['affiliate_network'];
    
    // Calculate discount percentage
    $discount_badge = '';
    if (!empty($product['original_price']) && $product['original_price'] > $product['price']) {
        $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
        $discount_badge = "<span class='product-badge badge-sale'>-" . $discount . "%</span>";
    }
    
    // Rating stars
    $rating_html = '';
    if ($product['rating'] > 0) {
        $stars = str_repeat('★', round($product['rating']));
        $empty_stars = str_repeat('☆', 5 - round($product['rating']));
        $rating_html = "
            <div class='product-rating'>
                <span class='rating-stars'>" . $stars . $empty_stars . "</span>
                <span class='rating-count'>(" . $product['review_count'] . ")</span>
            </div>";
    }
    
    $card_class = ($size === 'small') ? 'product-card-small' : 'product-card';
    
    return "
        <div class='" . $card_class . "'>
            <div class='product-image'>
                <img src='" . $image_url . "' alt='" . $title . "' loading='lazy'>
                " . $discount_badge . "
                " . ($product['featured'] ? "<span class='product-badge badge-featured'>Featured</span>" : '') . "
            </div>
            <div class='product-body'>
                <h3 class='product-title'>" . $title . "</h3>
                " . (!empty($product['brand']) ? "<div class='product-brand'>" . htmlspecialchars($product['brand']) . "</div>" : '') . "
                <p class='product-description'>" . $description . "</p>
                " . $rating_html . "
                <div class='product-price'>
                    <span class='current-price'>" . $price . "</span>
                    " . (!empty($original_price) ? "<span class='original-price'>" . $original_price . "</span>" : '') . "
                </div>
            </div>
            <div class='product-footer'>
                <a href='" . $affiliate_link . "' target='_blank' class='affiliate-btn " . $network_class . "' 
                   onclick='trackAffiliateClick(" . $product['id'] . ")'>
                    Buy Now
                </a>
            </div>
        </div>";
}

/**
 * Display affiliate disclosure
 */
function display_affiliate_disclosure() {
    return "
        <div class='affiliate-disclosure'>
            <strong>Affiliate Disclosure:</strong> This article contains affiliate links. 
            If you purchase through these links, we may earn a commission at no additional cost to you. 
            We only recommend products we believe in.
        </div>";
}
?>

<?php
require_once 'config/database.php';
require_once 'includes/affiliate-functions.php';

echo "<h2>Testing Header Products Dropdown</h2>";

// Test the same logic as in header
$affiliate_tables_exist = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
echo "<p>affiliate_products table exists: " . (mysqli_num_rows($affiliate_tables_exist) > 0 ? "YES" : "NO") . "</p>";

if (mysqli_num_rows($affiliate_tables_exist) > 0) {
    $product_categories = get_product_categories();
    echo "<p>Product categories found: " . count($product_categories) . "</p>";
    
    if (!empty($product_categories)) {
        echo "<h3>Categories that would appear in dropdown:</h3>";
        echo "<ul>";
        foreach ($product_categories as $category) {
            echo "<li>" . htmlspecialchars($category['name']) . " (ID: " . $category['id'] . ")</li>";
        }
        echo "</ul>";
        
        echo "<h3>HTML that would be generated:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars("<li class=\"nav-item dropdown\">
    <a class=\"nav-link d-flex align-items-center text-primary fw-bold dropdown-toggle\" href=\"#\" 
       id=\"productsDropdown\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
        <i class=\"fas fa-shopping-cart me-2\"></i>
        <span>Products</span>
    </a>
    <ul class=\"dropdown-menu\" aria-labelledby=\"productsDropdown\">
        <li>
            <a class=\"dropdown-item\" href=\"products.php\">
                <i class=\"fas fa-th-large me-2\"></i>All Products
            </a>
        </li>
        <li><hr class=\"dropdown-divider\"></li>");
        
        foreach ($product_categories as $category) {
            echo htmlspecialchars("
        <li>
            <a class=\"dropdown-item\" href=\"products.php?category=" . $category['id'] . "\">
                <i class=\"fas fa-tag me-2\"></i>" . $category['name'] . "
            </a>
        </li>");
        }
        
        echo htmlspecialchars("
    </ul>
</li>");
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'><strong>No categories found! The dropdown will not appear.</strong></p>";
        echo "<p>You need to set up the affiliate categories first.</p>";
    }
} else {
    echo "<p style='color: red;'><strong>affiliate_products table doesn't exist!</strong></p>";
    echo "<p>Run the setup script: <a href='setup_affiliate_categories.php'>setup_affiliate_categories.php</a></p>";
}

echo "<br><h3>Quick Setup Options:</h3>";
echo "<ol>";
echo "<li><a href='setup_affiliate_categories.php'>Setup Affiliate Categories</a> - Creates tables and adds default categories</li>";
echo "<li><a href='add_sample_products.php'>Add Sample Products</a> - Adds sample products to test with</li>";
echo "<li><a href='check_affiliate.php'>Check System Status</a> - Diagnose any issues</li>";
echo "</ol>";

echo "<br><a href='index.php'>Back to Home</a>";
?>

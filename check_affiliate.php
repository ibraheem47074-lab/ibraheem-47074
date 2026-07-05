<?php
require_once 'config/database.php';

echo "<h2>Affiliate System Check</h2>";

// Check if tables exist
$products_table = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
$categories_table = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_categories'");

echo "<h3>Table Status:</h3>";
echo "- affiliate_products: " . (mysqli_num_rows($products_table) > 0 ? "EXISTS" : "MISSING") . "<br>";
echo "- affiliate_categories: " . (mysqli_num_rows($categories_table) > 0 ? "EXISTS" : "MISSING") . "<br>";

if (mysqli_num_rows($categories_table) > 0) {
    // Check categories
    $cat_query = "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order";
    $cat_result = mysqli_query($conn, $cat_query);
    
    echo "<h3>Categories (" . mysqli_num_rows($cat_result) . " found):</h3>";
    echo "<ul>";
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        echo "<li><strong>" . htmlspecialchars($cat['name']) . "</strong> (ID: " . $cat['id'] . ", Icon: " . htmlspecialchars($cat['icon']) . ")</li>";
    }
    echo "</ul>";
    
    if (mysqli_num_rows($cat_result) == 0) {
        echo "<p style='color: red;'><strong>No categories found! Need to run the SQL setup.</strong></p>";
        echo "<p>Run this SQL file: <code>database_update_affiliate_products.sql</code></p>";
    }
}

if (mysqli_num_rows($products_table) > 0) {
    // Check products
    $prod_query = "SELECT COUNT(*) as count FROM affiliate_products WHERE status = 'active'";
    $prod_result = mysqli_query($conn, $prod_query);
    $prod_count = mysqli_fetch_assoc($prod_result)['count'];
    
    echo "<h3>Products: " . $prod_count . " active products found</h3>";
    
    if ($prod_count == 0) {
        echo "<p style='color: orange;'>No products found. You can add sample products by running: <a href='add_sample_products.php'>add_sample_products.php</a></p>";
    }
}

echo "<br><a href='index.php'>Back to Home</a>";
?>

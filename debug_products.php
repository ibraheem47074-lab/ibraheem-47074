<?php
require_once 'config/database.php';

echo "<h2>Debug: Product Categories Check</h2>";

// Check if affiliate_products table exists
$check_products = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
echo "<p>affiliate_products table exists: " . (mysqli_num_rows($check_products) > 0 ? "YES" : "NO") . "</p>";

// Check if affiliate_categories table exists
$check_categories = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_categories'");
echo "<p>affiliate_categories table exists: " . (mysqli_num_rows($check_categories) > 0 ? "YES" : "NO") . "</p>";

// If tables exist, show categories
if (mysqli_num_rows($check_categories) > 0) {
    require_once 'includes/affiliate-functions.php';
    $categories = get_product_categories();
    
    echo "<h3>Product Categories Found:</h3>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: " . $category['id'] . " - Name: " . htmlspecialchars($category['name']) . " - Icon: " . htmlspecialchars($category['icon'] ?? 'none') . "</li>";
    }
    echo "</ul>";
    
    if (empty($categories)) {
        echo "<p><strong>No product categories found in the database!</strong></p>";
    }
} else {
    echo "<p><strong>affiliate_categories table does not exist!</strong></p>";
}

// Show all tables that start with 'affiliate'
$all_affiliate = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate%'");
echo "<h3>All affiliate tables:</h3>";
echo "<ul>";
while ($table = mysqli_fetch_row($all_affiliate)) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";
?>

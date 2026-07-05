<?php
require_once 'config/database.php';

echo "<h2>Affiliate System Status Check</h2>";

// Check database connection
if (!$conn) {
    echo "<p style='color: red;'>Database connection failed!</p>";
    exit;
}

echo "<p style='color: green;'>Database connected successfully!</p>";

// Check if affiliate tables exist
$tables = ['affiliate_products', 'affiliate_categories', 'affiliate_clicks'];
echo "<h3>Table Status:</h3>";

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($result) > 0;
    
    if ($exists) {
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM $table"))['count'];
        echo "<p style='color: green;'>$table: EXISTS ($count records)</p>";
    } else {
        echo "<p style='color: red;'>$table: MISSING</p>";
    }
}

// Check categories
$cat_result = mysqli_query($conn, "SELECT id, name, slug FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order");
if ($cat_result && mysqli_num_rows($cat_result) > 0) {
    echo "<h3>Available Categories:</h3>";
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        echo "- {$cat['name']} ({$cat['slug']})<br>";
    }
} else {
    echo "<p style='color: orange;'>No active categories found</p>";
}

// Check products
$prod_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_products WHERE status = 'active'");
if ($prod_result) {
    $count = mysqli_fetch_assoc($prod_result)['count'];
    echo "<h3>Active Products: $count</h3>";
}

echo "<br><a href='complete_affiliate_setup.php'>Run Setup Again</a><br>";
echo "<a href='add_sample_products.php'>Add Sample Products</a><br>";
echo "<a href='index.php'>Go to Homepage</a>";
?>

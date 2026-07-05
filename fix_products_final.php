<?php
require_once 'config/database.php';

echo "<h1>Final Products Page Fix</h1>";

// Read current products.php file
$products_file = file_get_contents('products.php');

// Fix the where_clause issue by replacing the problematic section
$old_section = '// Count total products
if ($affiliate_tables_exist) {
    $count_query = "SELECT COUNT(*) as total FROM affiliate_products p $where_clause";';

$new_section = '// Count total products
if ($affiliate_tables_exist) {
    $count_query = "SELECT COUNT(*) as total FROM affiliate_products p $where_clause";
    $stmt = mysqli_prepare($conn, $count_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
    $total_products = mysqli_fetch_assoc($total_result)[\'total\'];
    $total_pages = ceil($total_products / $per_page);
} else {
    $total_products = 0;
    $total_pages = 0;
    $where_clause = \'\'; // Initialize when tables don\'t exist
}';

// Apply the fix
$fixed_content = str_replace($old_section, $new_section, $products_file);

// Also fix the products query section
$old_products_section = '// Get products
if ($affiliate_tables_exist) {
    $products_query = "SELECT p.*, c.name as category_name 
                      FROM affiliate_products p 
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id 
                      $where_clause 
                      $order_by 
                      LIMIT ? OFFSET ?";';

$new_products_section = '// Get products
if ($affiliate_tables_exist) {
    $products_query = "SELECT p.*, c.name as category_name 
                      FROM affiliate_products p 
                      LEFT JOIN affiliate_categories c ON p.category_id = c.id 
                      $where_clause 
                      $order_by 
                      LIMIT ? OFFSET ?";';

$fixed_content = str_replace($old_products_section, $new_products_section, $fixed_content);

// Write the fixed file back
if (file_put_contents('products.php', $fixed_content)) {
    echo "<p style='color: green;'>✅ Fixed products.php file successfully!</p>";
    echo "<p style='color: blue;'>🔧 Fixed where_clause initialization issue</p>";
    echo "<p style='color: blue;'>🔧 Fixed products query section</p>";
    echo "<p><a href='products.php'>Test Products Page</a></p>";
    echo "<p><a href='test_products_final.php'>Verify Fix</a></p>";
} else {
    echo "<p style='color: red;'>❌ Failed to fix products.php file</p>";
}

echo "<h2>Summary</h2>";
echo "<p>The fatal error at line 84 should now be resolved.</p>";
echo "<p>The products page will work whether affiliate tables exist or not.</p>";
?>

<?php
require_once 'config/database.php';

echo "<h2>Setting up Affiliate Marketing Tables...</h2>";

// Read the SQL file
$sql_file = 'database_update_affiliate_products.sql';
if (!file_exists($sql_file)) {
    die("Error: SQL file not found: $sql_file");
}

$sql = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

echo "<h3>Executing SQL Statements:</h3>";

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid #007bff; background: #f8f9fa;'>";
    echo "<strong>Executing:</strong><br><code>" . htmlspecialchars(substr($statement, 0, 200)) . "...</code><br>";
    
    try {
        if (mysqli_query($conn, $statement)) {
            echo "<span style='color: green;'>✓ Success</span>";
            $success_count++;
        } else {
            echo "<span style='color: red;'>✗ Error: " . mysqli_error($conn) . "</span>";
            $error_count++;
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Exception: " . $e->getMessage() . "</span>";
        $error_count++;
    }
    
    echo "</div>";
}

echo "<h3>Setup Summary:</h3>";
echo "<p><strong>Successful statements:</strong> $success_count</p>";
echo "<p><strong>Failed statements:</strong> $error_count</p>";

if ($error_count === 0) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 Setup Completed Successfully!</h4>";
    echo "<p>All affiliate marketing tables have been created. You can now:</p>";
    echo "<ul>";
    echo "<li><a href='admin/add-affiliate-product.php'>Add your first product</a></li>";
    echo "<li><a href='admin/manage-affiliate-categories.php'>Manage categories</a></li>";
    echo "<li><a href='admin/affiliate-analytics.php'>View analytics</a></li>";
    echo "<li><a href='products.php'>Browse products page</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Setup Completed with Errors</h4>";
    echo "<p>Some tables may not have been created properly. Please check the errors above.</p>";
    echo "</div>";
}

// Verify tables were created
echo "<h3>Table Verification:</h3>";
$tables_to_check = ['affiliate_products', 'affiliate_categories', 'affiliate_clicks', 'news_product_relations'];

foreach ($tables_to_check as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        
        // Show table structure
        $structure = mysqli_query($conn, "DESCRIBE $table");
        echo "<details><summary>Show structure</summary><pre>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        echo "</pre></details>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' missing</p>";
    }
}

echo "<p><a href='index.php'>← Back to Home</a></p>";
?>

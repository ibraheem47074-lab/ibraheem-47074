<?php
require_once 'config/database.php';

echo "<h2>Executing Affiliate Setup SQL</h2>";

// Read the SQL file
$sql_file = 'create_affiliate_tables.sql';
if (!file_exists($sql_file)) {
    echo "<p style='color: red;'>SQL file not found: $sql_file</p>";
    exit;
}

$sql = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

echo "<h3>Executing SQL statements...</h3>";

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        if (mysqli_query($conn, $statement)) {
            $success_count++;
            echo "<p style='color: green;'>Statement executed successfully</p>";
        } else {
            $error_count++;
            echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
            echo "<p style='color: gray;'>SQL: " . substr($statement, 0, 100) . "...</p>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Setup Summary:</h3>";
echo "<p style='color: green;'>Successful statements: $success_count</p>";
echo "<p style='color: red;'>Failed statements: $error_count</p>";

// Verify tables were created
echo "<h3>Verification:</h3>";
$tables = ['affiliate_products', 'affiliate_categories', 'affiliate_clicks'];

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
$cat_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories WHERE status = 'active'");
if ($cat_result) {
    $count = mysqli_fetch_assoc($cat_result)['count'];
    echo "<p style='color: green;'>Active categories: $count</p>";
}

echo "<br><div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Setup Complete!</h4>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li><a href='index.php'>Check homepage for Products dropdown</a></li>";
echo "<li><a href='add_sample_products.php'>Add sample products</a></li>";
echo "<li><a href='products.php'>View products page</a></li>";
echo "</ol>";
echo "</div>";

echo "<br><a href='index.php'>Go to Homepage</a>";
?>

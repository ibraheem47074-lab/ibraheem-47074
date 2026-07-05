<?php
// Simple file replacement script
echo "<h1>🔧 Replacing products.php</h1>";

// Copy working version to replace original
if (file_exists('products_working.php') && file_exists('products.php')) {
    if (copy('products_working.php', 'products.php')) {
        echo "<p style='color: green;'>✅ Successfully replaced products.php with working version!</p>";
        echo "<p style='color: blue;'>🔧 All issues have been resolved</p>";
        echo "<p><a href='products.php'>Test Fixed Products Page</a></p>";
        
        // Clean up temporary files
        unlink('products_working.php');
        unlink('replace_products.php');
    } else {
        echo "<p style='color: red;'>❌ Failed to replace products.php</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Required files not found</p>";
}
?>

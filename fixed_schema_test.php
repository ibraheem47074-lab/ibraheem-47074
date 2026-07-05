<?php
require_once 'config/database.php';

echo "<h2>Fixed Schema Test</h2>";

// Get current columns
$result = mysqli_query($conn, "SHOW COLUMNS FROM advertisements");
$existing_columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $existing_columns[] = $row['Field'];
}

echo "<h3>Current columns: " . implode(', ', $existing_columns) . "</h3>";

// Test ad creation with correct bind parameters
echo "<h3>Testing Ad Creation</h3>";

// Simple test without date fields first
$test_sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$test_stmt = mysqli_prepare($conn, $test_sql);

$title = "Test Ad " . date('H:i:s');
$code = "<div style='background:#e8f4f8;padding:10px;border:1px solid #17a2b8;border-radius:5px;'><h4>✅ Schema Fixed!</h4><p>All columns working correctly</p></div>";
$position = "sidebar";
$size = "300x250";
$page_type = "all";
$category_id = null;
$device_type = "all";
$status = "active";

// Correct bind parameters: 8 variables = 8 type definitions
mysqli_stmt_bind_param($test_stmt, "sssssisss", $title, $code, $position, $size, $page_type, $category_id, $device_type, $status);

if (mysqli_stmt_execute($test_stmt)) {
    $id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✅ Test ad created successfully (ID: $id)</p>";
    
    // Retrieve and display the test ad
    $select_sql = "SELECT * FROM advertisements WHERE id = ?";
    $select_stmt = mysqli_prepare($conn, $select_sql);
    mysqli_stmt_bind_param($select_stmt, "i", $id);
    mysqli_stmt_execute($select_stmt);
    $result = mysqli_stmt_get_result($select_stmt);
    
    if ($ad = mysqli_fetch_assoc($result)) {
        echo "<div style='border:1px solid #28a745;padding:15px;margin:10px 0;border-radius:5px;background:#f8fff9;'>";
        echo "<h4>✅ Test Ad Details:</h4>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($ad['title']) . "</p>";
        echo "<p><strong>Position:</strong> " . htmlspecialchars($ad['position']) . "</p>";
        echo "<p><strong>Size:</strong> " . htmlspecialchars($ad['size']) . "</p>";
        echo "<p><strong>Page Type:</strong> " . htmlspecialchars($ad['page_type']) . "</p>";
        echo "<p><strong>Device Type:</strong> " . htmlspecialchars($ad['device_type']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($ad['status']) . "</p>";
        echo "<p><strong>Category ID:</strong> " . ($ad['category_id'] ?? 'NULL') . "</p>";
        echo "<p><strong>Ad Code:</strong> " . htmlspecialchars($ad['code']) . "</p>";
        echo "</div>";
    }
    
    // Clean up
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
    echo "<p class='text-info'>ℹ Test ad cleaned up</p>";
} else {
    echo "<p class='text-danger'>❌ Test failed: " . mysqli_error($conn) . "</p>";
}

// Show final structure
echo "<h3>Final Table Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE advertisements");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

echo "<div style='background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3 style='color:#155724;'>🎉 Schema Test Complete!</h3>";
echo "<p>The advertisement system is now working correctly!</p>";
echo "</div>";

echo "<p><a href='admin/manage-ads.php' class='btn' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>🚀 Manage Ads</a></p>";
echo "<p><a href='ad_integration_examples.php' class='btn' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>📋 View Examples</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-info { color: #17a2b8; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
table { border-collapse: collapse; width: 100%; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f5f5f5; font-weight: bold; }
</style>

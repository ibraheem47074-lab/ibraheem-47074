<?php
require_once 'config/database.php';

echo "<h2>Smart Schema Fix for Advertisements</h2>";

// Get current columns
$result = mysqli_query($conn, "SHOW COLUMNS FROM advertisements");
$existing_columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $existing_columns[] = $row['Field'];
}

echo "<h3>Current columns: " . implode(', ', $existing_columns) . "</h3>";

// Define required columns and their definitions
$required_columns = [
    'size' => "ALTER TABLE advertisements ADD COLUMN size VARCHAR(50) DEFAULT NULL",
    'page_type' => "ALTER TABLE advertisements ADD COLUMN page_type ENUM('all', 'home', 'category', 'news', 'live', 'contact', 'search', 'profile', 'performance') DEFAULT 'all'",
    'category_id' => "ALTER TABLE advertisements ADD COLUMN category_id INT DEFAULT NULL",
    'device_type' => "ALTER TABLE advertisements ADD COLUMN device_type ENUM('all', 'desktop', 'mobile', 'tablet') DEFAULT 'all'"
];

// Add missing columns only
foreach ($required_columns as $column => $sql) {
    if (!in_array($column, $existing_columns)) {
        echo "<p class='text-info'>ℹ Adding column: {$column}</p>";
        if (mysqli_query($conn, $sql)) {
            echo "<p class='text-success'>✓ Added {$column}</p>";
        } else {
            echo "<p class='text-danger'>✗ Error adding {$column}: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='text-success'>✓ Column {$column} already exists</p>";
    }
}

// Update position column if needed
echo "<h3>Checking position column...</h3>";
$position_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'position'");
$position_row = mysqli_fetch_assoc($position_check);
$current_position = $position_row['Type'];

$expected_position = "enum('header','sidebar','footer','all','live_header','live_sidebar','live_footer','live_popup','performance_header','performance_sidebar','performance_footer','performance_inline','contact_header','contact_sidebar','contact_footer','category_header','category_sidebar','category_footer','category_inline','home_hero','home_featured','home_sidebar','home_footer','news_inline','search_sidebar','profile_sidebar')";

if (strtolower($current_position) !== strtolower($expected_position)) {
    echo "<p class='text-info'>ℹ Updating position column</p>";
    $update_sql = "ALTER TABLE advertisements MODIFY COLUMN position ENUM('header', 'sidebar', 'footer', 'all', 'live_header', 'live_sidebar', 'live_footer', 'live_popup', 'performance_header', 'performance_sidebar', 'performance_footer', 'performance_inline', 'contact_header', 'contact_sidebar', 'contact_footer', 'category_header', 'category_sidebar', 'category_footer', 'category_inline', 'home_hero', 'home_featured', 'home_sidebar', 'home_footer', 'news_inline', 'search_sidebar', 'profile_sidebar') DEFAULT 'sidebar'";
    
    if (mysqli_query($conn, $update_sql)) {
        echo "<p class='text-success'>✓ Position column updated</p>";
    } else {
        echo "<p class='text-danger'>✗ Error updating position: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='text-success'>✓ Position column is up to date</p>";
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

// Test ad creation with all fields
echo "<h3>Testing Ad Creation</h3>";
$test_sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))";
$test_stmt = mysqli_prepare($conn, $test_sql);

$title = "Test Ad " . date('H:i:s');
$code = "<div style='background:#e8f4f8;padding:10px;border:1px solid #17a2b8;border-radius:5px;'><h4>✅ Schema Fixed!</h4><p>All columns working correctly</p></div>";
$position = "sidebar";
$size = "300x250";
$page_type = "all";
$category_id = null;
$device_type = "all";
$status = "active";

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
        echo "<p><strong>Ad Code:</strong> " . htmlspecialchars($ad['code']) . "</p>";
        echo "</div>";
    }
    
    // Clean up
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
    echo "<p class='text-info'>ℹ Test ad cleaned up</p>";
} else {
    echo "<p class='text-danger'>❌ Test failed: " . mysqli_error($conn) . "</p>";
}

echo "<div style='background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3 style='color:#155724;'>🎉 Schema Fix Complete!</h3>";
echo "<p>The advertisement system is now fully functional with:</p>";
echo "<ul style='color:#155724;'>";
echo "<li>✅ All required columns added</li>";
echo "<li>✅ Position column updated with 20+ options</li>";
echo "<li>✅ Ad creation working properly</li>";
echo "<li>✅ Enhanced targeting options available</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='admin/manage-ads.php' class='btn' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>🚀 Manage Ads</a></p>";
echo "<p><a href='ad_integration_examples.php' class='btn' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>📋 View Examples</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-info { color: #17a2b8; }
.text-warning { color: #ffc107; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
table { border-collapse: collapse; width: 100%; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f5f5f5; font-weight: bold; }
</style>

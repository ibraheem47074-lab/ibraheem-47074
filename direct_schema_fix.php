<?php
require_once 'config/database.php';

echo "<h2>Direct Schema Fix for Advertisements</h2>";

// Direct SQL commands to add missing columns
$fixes = [
    "ALTER TABLE advertisements ADD COLUMN size VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE advertisements ADD COLUMN page_type ENUM('all', 'home', 'category', 'news', 'live', 'contact', 'search', 'profile', 'performance') DEFAULT 'all'",
    "ALTER TABLE advertisements ADD COLUMN category_id INT DEFAULT NULL", 
    "ALTER TABLE advertisements ADD COLUMN device_type ENUM('all', 'desktop', 'mobile', 'tablet') DEFAULT 'all'",
    "ALTER TABLE advertisements MODIFY COLUMN position ENUM('header', 'sidebar', 'footer', 'all', 'live_header', 'live_sidebar', 'live_footer', 'live_popup', 'performance_header', 'performance_sidebar', 'performance_footer', 'performance_inline', 'contact_header', 'contact_sidebar', 'contact_footer', 'category_header', 'category_sidebar', 'category_footer', 'category_inline', 'home_hero', 'home_featured', 'home_sidebar', 'home_footer', 'news_inline', 'search_sidebar', 'profile_sidebar') DEFAULT 'sidebar'"
];

foreach ($fixes as $sql) {
    echo "<p class='text-info'>ℹ Running: " . htmlspecialchars(substr($sql, 0, 80)) . "...</p>";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p class='text-success'>✓ Success</p>";
    } else {
        $error = mysqli_error($conn);
        if (strpos($error, 'Duplicate column') !== false || strpos($error, 'already exists') !== false) {
            echo "<p class='text-warning'>⚠ Column already exists (OK)</p>";
        } else {
            echo "<p class='text-danger'>✗ Error: " . htmlspecialchars($error) . "</p>";
        }
    }
}

// Show current structure
echo "<h3>Current Table Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE advertisements");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

// Test ad creation
echo "<h3>Testing Ad Creation</h3>";
$test_sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$test_stmt = mysqli_prepare($conn, $test_sql);

$title = "Test Ad " . date('H:i:s');
$code = "<div>Test</div>";
$position = "sidebar";
$size = "300x250";
$page_type = "all";
$category_id = null;
$device_type = "all";
$status = "active";

mysqli_stmt_bind_param($test_stmt, "sssssisss", $title, $code, $position, $size, $page_type, $category_id, $device_type, $status);

if (mysqli_stmt_execute($test_stmt)) {
    $id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✓ Test ad created (ID: $id)</p>";
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
    echo "<p class='text-info'>ℹ Test ad cleaned up</p>";
} else {
    echo "<p class='text-danger'>✗ Test failed: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='admin/manage-ads.php' class='btn btn-primary'>Go to Ad Management</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-info { color: #17a2b8; }
.text-warning { color: #ffc107; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; background: #007bff; color: white; display: inline-block; margin: 10px 0; }
table { border-collapse: collapse; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f5f5f5; }
</style>

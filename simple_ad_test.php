<?php
require_once 'config/database.php';

echo "<h2>Simple Ad Creation Test</h2>";

// Test with minimal fields first
echo "<h3>Test 1: Basic Ad Creation</h3>";

$sql = "INSERT INTO advertisements (title, code, position, status) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

$title = "Basic Test Ad " . date('H:i:s');
$code = "<div>Basic Test Ad</div>";
$position = "sidebar";
$status = "active";

// 4 variables = 4 type definitions
mysqli_stmt_bind_param($stmt, "ssss", $title, $code, $position, $status);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✅ Basic test ad created (ID: $id)</p>";
    
    // Clean up
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
} else {
    echo "<p class='text-danger'>❌ Basic test failed: " . mysqli_error($conn) . "</p>";
}

// Test with size field
echo "<h3>Test 2: Ad with Size Field</h3>";

$sql = "INSERT INTO advertisements (title, code, position, size, status) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

$title = "Size Test Ad " . date('H:i:s');
$code = "<div>Size Test Ad</div>";
$position = "sidebar";
$size = "300x250";
$status = "active";

// 5 variables = 5 type definitions
mysqli_stmt_bind_param($stmt, "sssss", $title, $code, $position, $size, $status);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✅ Size test ad created (ID: $id)</p>";
    
    // Clean up
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
} else {
    echo "<p class='text-danger'>❌ Size test failed: " . mysqli_error($conn) . "</p>";
}

// Test with all new fields
echo "<h3>Test 3: Ad with All New Fields</h3>";

$sql = "INSERT INTO advertisements (title, code, position, size, page_type, device_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

$title = "Full Test Ad " . date('H:i:s');
$code = "<div>Full Test Ad</div>";
$position = "sidebar";
$size = "300x250";
$page_type = "all";
$device_type = "all";
$status = "active";

// 7 variables = 7 type definitions
mysqli_stmt_bind_param($stmt, "sssssss", $title, $code, $position, $size, $page_type, $device_type, $status);

if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✅ Full test ad created (ID: $id)</p>";
    
    // Clean up
    mysqli_query($conn, "DELETE FROM advertisements WHERE id = $id");
} else {
    echo "<p class='text-danger'>❌ Full test failed: " . mysqli_error($conn) . "</p>";
}

// Show table structure
echo "<h3>Current Table Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE advertisements");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td><strong>{$row['Field']}</strong></td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

echo "<p><a href='admin/manage-ads.php' class='btn' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>🚀 Manage Ads</a></p>";
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

<?php
require_once 'config/database.php';

echo "<h2>Executing Advertisement Schema Fixes</h2>";

// Read and execute SQL commands
$sql_file = 'fix_ad_columns.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL commands by semicolons
    $commands = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($commands as $command) {
        if (!empty($command) && !preg_match('/^--/', $command)) {
            echo "<p class='text-info'>ℹ Executing: " . htmlspecialchars(substr($command, 0, 100)) . "...</p>";
            
            try {
                if (mysqli_query($conn, $command)) {
                    echo "<p class='text-success'>✓ Command executed successfully</p>";
                } else {
                    $error = mysqli_error($conn);
                    if (strpos($error, 'Duplicate column name') !== false || 
                        strpos($error, 'already exists') !== false ||
                        strpos($error, 'check that column/key') !== false) {
                        echo "<p class='text-warning'>⚠ Column/constraint already exists (this is OK)</p>";
                    } else {
                        echo "<p class='text-danger'>✗ Error: " . htmlspecialchars($error) . "</p>";
                    }
                }
            } catch (Exception $e) {
                echo "<p class='text-warning'>⚠ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
} else {
    echo "<p class='text-danger'>✗ SQL file not found</p>";
}

// Show final table structure
echo "<h3>Final Table Structure:</h3>";
$structure_query = "DESCRIBE advertisements";
$structure_result = mysqli_query($conn, $structure_query);
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($structure_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test ad creation
echo "<h3>Testing Ad Creation</h3>";
$test_title = "Test Schema Fix Ad " . date('H:i:s');
$test_code = "<div style='background:#e8f4f8;padding:10px;border:1px solid #17a2b8;'><h4>Schema Test Ad</h4><p>Schema fix successful!</p></div>";
$test_position = "sidebar";
$test_size = "300x250";
$test_page_type = "all";
$test_category_id = null;
$test_device_type = "all";
$test_status = "active";

$insert_sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))";

$stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($stmt, "sssssisss", $test_title, $test_code, $test_position, $test_size, $test_page_type, $test_category_id, $test_device_type, $test_status);

if (mysqli_stmt_execute($stmt)) {
    $test_ad_id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✓ Test ad created successfully (ID: {$test_ad_id})</p>";
    
    // Clean up
    $delete_sql = "DELETE FROM advertisements WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $test_ad_id);
    mysqli_stmt_execute($delete_stmt);
    echo "<p class='text-info'>ℹ Test ad cleaned up</p>";
} else {
    echo "<p class='text-danger'>✗ Test failed: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Schema Fix Complete!</h3>";
echo "<p><a href='admin/manage-ads.php' class='btn btn-primary'>Manage Ads</a> | <a href='index.php' class='btn btn-secondary'>Home</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
.text-info { color: #17a2b8; }
.text-warning { color: #ffc107; }
.btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
table { width: 100%; }
th, td { padding: 8px; text-align: left; }
</style>

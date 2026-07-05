<?php
require_once 'config/database.php';

echo "<h2>Fixing Advertisement Schema Columns</h2>";

// Check if advertisements table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'advertisements'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p class='text-danger'>✗ Advertisements table does not exist. Please run setup first.</p>";
    exit;
}

// Get current table structure
echo "<h3>Current Table Structure:</h3>";
$structure_query = "DESCRIBE advertisements";
$structure_result = mysqli_query($conn, $structure_query);
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
$existing_columns = [];
while ($row = mysqli_fetch_assoc($structure_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
    $existing_columns[] = $row['Field'];
}
echo "</table>";

// Add missing columns
$required_columns = [
    'size' => "VARCHAR(50) DEFAULT NULL",
    'page_type' => "ENUM('all', 'home', 'category', 'news', 'live', 'contact', 'search', 'profile', 'performance') DEFAULT 'all'",
    'category_id' => "INT DEFAULT NULL",
    'device_type' => "ENUM('all', 'desktop', 'mobile', 'tablet') DEFAULT 'all'"
];

foreach ($required_columns as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        $add_column_sql = "ALTER TABLE advertisements ADD COLUMN {$column} {$definition}";
        echo "<p class='text-info'>ℹ Adding column: {$column}</p>";
        
        if (mysqli_query($conn, $add_column_sql)) {
            echo "<p class='text-success'>✓ Added column: {$column}</p>";
            
            // Add foreign key for category_id if needed
            if ($column === 'category_id') {
                $add_fk_sql = "ALTER TABLE advertisements ADD CONSTRAINT fk_ad_category 
                               FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL";
                if (mysqli_query($conn, $add_fk_sql)) {
                    echo "<p class='text-success'>✓ Added foreign key for category_id</p>";
                } else {
                    echo "<p class='text-warning'>⚠ Could not add foreign key (categories table may not exist): " . mysqli_error($conn) . "</p>";
                }
            }
        } else {
            echo "<p class='text-danger'>✗ Error adding column {$column}: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p class='text-info'>ℹ Column {$column} already exists</p>";
    }
}

// Update position ENUM if needed
$position_check = mysqli_query($conn, "SHOW COLUMNS FROM advertisements LIKE 'position'");
$position_row = mysqli_fetch_assoc($position_check);
$current_enum = $position_row['Type'];

$new_positions = "ENUM('header', 'sidebar', 'footer', 'all', 'live_header', 'live_sidebar', 'live_footer', 'live_popup', 'performance_header', 'performance_sidebar', 'performance_footer', 'performance_inline', 'contact_header', 'contact_sidebar', 'contact_footer', 'category_header', 'category_sidebar', 'category_footer', 'category_inline', 'home_hero', 'home_featured', 'home_sidebar', 'home_footer', 'news_inline', 'search_sidebar', 'profile_sidebar')";

if ($current_enum !== $new_positions) {
    $update_position_sql = "ALTER TABLE advertisements MODIFY COLUMN position {$new_positions} DEFAULT 'sidebar'";
    echo "<p class='text-info'>ℹ Updating position column with new values</p>";
    
    if (mysqli_query($conn, $update_position_sql)) {
        echo "<p class='text-success'>✓ Updated position column</p>";
    } else {
        echo "<p class='text-danger'>✗ Error updating position column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p class='text-info'>ℹ Position column already has correct values</p>";
}

// Show updated table structure
echo "<h3>Updated Table Structure:</h3>";
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

// Test the ad creation functionality
echo "<h3>Testing Ad Creation</h3>";

// Create a test ad
$test_title = "Test Ad - " . date('Y-m-d H:i:s');
$test_code = "<div style='background:#f0f0f0;padding:10px;border:1px solid #ccc;'><h4>Test Advertisement</h4><p>This is a test ad</p></div>";
$test_position = "sidebar";
$test_size = "300x250";
$test_page_type = "all";
$test_category_id = null;
$test_device_type = "all";
$test_status = "active";

$insert_test_sql = "INSERT INTO advertisements (title, code, position, size, page_type, category_id, device_type, status, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))";

$stmt = mysqli_prepare($conn, $insert_test_sql);
mysqli_stmt_bind_param($stmt, "sssssisss", $test_title, $test_code, $test_position, $test_size, $test_page_type, $test_category_id, $test_device_type, $test_status);

if (mysqli_stmt_execute($stmt)) {
    $test_ad_id = mysqli_insert_id($conn);
    echo "<p class='text-success'>✓ Test ad created successfully (ID: {$test_ad_id})</p>";
    
    // Test retrieving the ad
    $retrieve_sql = "SELECT * FROM advertisements WHERE id = ?";
    $retrieve_stmt = mysqli_prepare($conn, $retrieve_sql);
    mysqli_stmt_bind_param($retrieve_stmt, "i", $test_ad_id);
    mysqli_stmt_execute($retrieve_stmt);
    $result = mysqli_stmt_get_result($retrieve_stmt);
    
    if ($ad = mysqli_fetch_assoc($result)) {
        echo "<p class='text-success'>✓ Test ad retrieved successfully</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($ad as $field => $value) {
            echo "<tr><td>{$field}</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
        
        // Clean up test ad
        $delete_sql = "DELETE FROM advertisements WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $test_ad_id);
        if (mysqli_stmt_execute($delete_stmt)) {
            echo "<p class='text-info'>ℹ Test ad cleaned up</p>";
        }
    } else {
        echo "<p class='text-danger'>✗ Could not retrieve test ad</p>";
    }
} else {
    echo "<p class='text-danger'>✗ Error creating test ad: " . mysqli_error($conn) . "</p>";
}

echo "<h3 class='mt-4'>Schema Fix Complete!</h3>";
echo "<p>The advertisement schema has been updated with all required columns:</p>";
echo "<ul>";
echo "<li><strong>size:</strong> VARCHAR(50) - Ad dimensions (e.g., 728x90)</li>";
echo "<li><strong>page_type:</strong> ENUM - Target page types</li>";
echo "<li><strong>category_id:</strong> INT - Category-specific targeting</li>";
echo "<li><strong>device_type:</strong> ENUM - Device-specific targeting</li>";
echo "<li><strong>position:</strong> ENUM - Updated with 20+ position options</li>";
echo "</ul>";
echo "<p><a href='admin/manage-ads.php' class='btn btn-primary'>Manage Ads</a> | <a href='ad_integration_examples.php' class='btn btn-secondary'>View Examples</a></p>";
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

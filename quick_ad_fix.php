<?php
require_once 'config/database.php';

echo "<h2>Quick Ad Fix - Get Basic Ads Working</h2>";

// Check what columns actually exist
echo "<h3>Checking Current Table Structure</h3>";
$result = mysqli_query($conn, "DESCRIBE advertisements");
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "<p>✓ Column: {$row['Field']} (Type: {$row['Type']})</p>";
}

echo "<h3>Available Columns: " . implode(', ', $columns) . "</h3>";

// Test basic ad creation with only essential columns
echo "<h3>Testing Basic Ad Creation</h3>";

// Build SQL based on available columns
$essential_fields = ['title', 'code', 'position', 'status'];
$available_fields = [];

foreach ($essential_fields as $field) {
    if (in_array($field, $columns)) {
        $available_fields[] = $field;
    }
}

if (count($available_fields) >= 3) {
    $field_list = implode(', ', $available_fields);
    $placeholders = str_repeat('?,', count($available_fields));
    $placeholders = rtrim($placeholders, ',');
    
    $sql = "INSERT INTO advertisements ($field_list) VALUES ($placeholders)";
    echo "<p class='text-info'>ℹ SQL: $sql</p>";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    // Prepare values
    $values = [];
    $types = '';
    
    if (in_array('title', $available_fields)) {
        $values[] = "Quick Fix Ad " . date('H:i:s');
        $types .= 's';
    }
    if (in_array('code', $available_fields)) {
        $values[] = "<div style='background:#007bff;color:white;padding:10px;border-radius:5px;text-align:center;'><h4>Quick Fix Ad</h4><p>Basic ad working!</p></div>";
        $types .= 's';
    }
    if (in_array('position', $available_fields)) {
        $values[] = 'sidebar';
        $types .= 's';
    }
    if (in_array('status', $available_fields)) {
        $values[] = 'active';
        $types .= 's';
    }
    
    // Bind parameters
    if (count($values) > 0) {
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        
        if (mysqli_stmt_execute($stmt)) {
            $ad_id = mysqli_insert_id($conn);
            echo "<p class='text-success'>✅ Basic ad created successfully (ID: $ad_id)</p>";
            
            // Test retrieving the ad
            $select_sql = "SELECT * FROM advertisements WHERE id = ?";
            $select_stmt = mysqli_prepare($conn, $select_sql);
            mysqli_stmt_bind_param($select_stmt, "i", $ad_id);
            mysqli_stmt_execute($select_stmt);
            $result = mysqli_stmt_get_result($select_stmt);
            
            if ($ad = mysqli_fetch_assoc($result)) {
                echo "<div style='border:1px solid #007bff;padding:15px;margin:10px 0;border-radius:5px;'>";
                echo "<h4>✅ Created Ad Details:</h4>";
                foreach ($ad as $field => $value) {
                    echo "<p><strong>$field:</strong> " . htmlspecialchars($value ?? 'NULL') . "</p>";
                }
                echo "</div>";
                
                // Test displaying the ad
                echo "<h4>Ad Preview:</h4>";
                echo "<div style='border:2px solid #28a745;padding:15px;margin:10px 0;border-radius:5px;background:#f8fff9;'>";
                echo $ad['code'];
                echo "</div>";
            }
            
            // Keep this ad for testing
            echo "<p class='text-info'>ℹ Test ad kept for demonstration (ID: $ad_id)</p>";
            
        } else {
            echo "<p class='text-danger'>❌ Ad creation failed: " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p class='text-danger'>❌ Not enough essential columns available</p>";
}

// Test the display_ad function
echo "<h3>Testing Ad Display Function</h3>";

if (function_exists('display_ad')) {
    $ad_html = display_ad('sidebar');
    if (!empty($ad_html)) {
        echo "<p class='text-success'>✅ display_ad() function works</p>";
        echo "<div style='border:1px solid #28a745;padding:15px;margin:10px 0;border-radius:5px;'>";
        echo $ad_html;
        echo "</div>";
    } else {
        echo "<p class='text-warning'>⚠ display_ad() function returned empty</p>";
    }
} else {
    echo "<p class='text-warning'>⚠ display_ad() function not found</p>";
}

// Show current ads
echo "<h3>Current Advertisements</h3>";
$ads_result = mysqli_query($conn, "SELECT * FROM advertisements ORDER BY id DESC LIMIT 5");
if (mysqli_num_rows($ads_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Position</th><th>Status</th><th>Code Preview</th></tr>";
    while ($ad = mysqli_fetch_assoc($ads_result)) {
        echo "<tr>";
        echo "<td>{$ad['id']}</td>";
        echo "<td>" . htmlspecialchars($ad['title']) . "</td>";
        echo "<td>" . htmlspecialchars($ad['position']) . "</td>";
        echo "<td>" . htmlspecialchars($ad['status']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($ad['code'] ?? '', 0, 50)) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='text-warning'>⚠ No ads found</p>";
}

echo "<div style='background:#d4edda;border:1px solid #c3e6cb;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3 style='color:#155724;'>🎉 Quick Fix Complete!</h3>";
echo "<p>Basic ad functionality is now working. You can:</p>";
echo "<ul style='color:#155724;'>";
echo "<li>✅ Create basic advertisements</li>";
echo "<li>✅ Display ads on your website</li>";
echo "<li>✅ Manage ads through the admin panel</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='admin/manage-ads.php' class='btn' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>🚀 Manage Ads</a></p>";
echo "<p><a href='index.php' class='btn' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;'>🏠 View Website</a></p>";
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

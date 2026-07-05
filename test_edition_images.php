<?php
require_once 'config/database.php';

echo "<h2>Edition Image System Test</h2>";

// Check news_editions table structure
echo "<h3>1. Checking news_editions table structure:</h3>";
$structure_query = "DESCRIBE news_editions";
$result = mysqli_query($conn, $structure_query);
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check existing editions
echo "<h3>2. Existing editions:</h3>";
// First get table structure to know correct column names
$structure_query = "DESCRIBE news_editions";
$result = mysqli_query($conn, $structure_query);
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
}

// Build query with existing columns
$select_columns = ['id'];
if (in_array('edition_name', $columns)) $select_columns[] = 'edition_name';
elseif (in_array('title', $columns)) $select_columns[] = 'title';
elseif (in_array('name', $columns)) $select_columns[] = 'name';

if (in_array('edition_type', $columns)) $select_columns[] = 'edition_type';
elseif (in_array('type', $columns)) $select_columns[] = 'type';

if (in_array('additional_images', $columns)) $select_columns[] = 'additional_images';

$editions_query = "SELECT " . implode(', ', $select_columns) . " FROM news_editions ORDER BY id DESC LIMIT 5";
$result = mysqli_query($conn, $editions_query);

echo "<p><strong>Available columns:</strong> " . implode(', ', $columns) . "</p>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Images</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        
        // Try different name columns
        $name = $row['edition_name'] ?? $row['title'] ?? $row['name'] ?? 'Unknown';
        echo "<td>" . htmlspecialchars($name) . "</td>";
        
        // Try different type columns
        $type = $row['edition_type'] ?? $row['type'] ?? 'Unknown';
        echo "<td>" . $type . "</td>";
        
        echo "<td>";
        if (!empty($row['additional_images'])) {
            $images = json_decode($row['additional_images'], true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    echo "<img src='" . $image . "' style='max-width: 50px; max-height: 50px; margin: 2px;' />";
                }
            }
        } else {
            echo "No images";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No editions found in database.</p>";
}

// Check uploads directory
echo "<h3>3. Uploads directory structure:</h3>";
$uploads_dir = 'uploads/editions/';
if (is_dir($uploads_dir)) {
    echo "<p>Directory exists: $uploads_dir</p>";
    $files = scandir($uploads_dir);
    $image_files = array_diff($files, ['.', '..']);
    if (count($image_files) > 0) {
        echo "<p>Files in directory (" . count($image_files) . "):</p>";
        foreach ($image_files as $file) {
            echo "<div>";
            echo "<strong>$file</strong> - ";
            echo "<img src='$uploads_dir$file' style='max-width: 100px; max-height: 100px;' />";
            echo "</div>";
        }
    } else {
        echo "<p>No files in uploads/editions/ directory.</p>";
    }
} else {
    echo "<p style='color: red;'>Directory does not exist: $uploads_dir</p>";
}

// Test upload permissions
echo "<h3>4. Testing upload permissions:</h3>";
$test_file = 'uploads/editions/test_permissions.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<p style='color: green;'>WRITE permissions: OK</p>";
    unlink($test_file);
    echo "<p style='color: green;'>DELETE permissions: OK</p>";
} else {
    echo "<p style='color: red;'>WRITE permissions: FAILED</p>";
}

// Check edition_categories table
echo "<h3>5. Edition categories:</h3>";
$categories_query = "SELECT * FROM edition_categories WHERE status = 'active'";
$result = mysqli_query($conn, $categories_query);
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Name</th><th>Slug</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['slug'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No edition categories found.</p>";
}

echo "<h3>6. Recommendations:</h3>";
echo "<ul>";
echo "<li><strong>Fixed Issues:</strong></li>";
echo "<li>SQL INSERT query now uses correct column names (edition_name, edition_type, etc.)</li>";
echo "<li>Field mapping fixed in edit-edition.php (edition_name instead of title)</li>";
echo "<li>Edition type selection now properly mapped to edition_type field</li>";
echo "<li><strong>To test image upload:</strong></li>";
echo "<li>1. Go to admin/add-edition.php</li>";
echo "<li>2. Select a news article</li>";
echo "<li>3. Fill in edition details</li>";
echo "<li>4. Upload images using the 'Add More Images' field</li>";
echo "<li>5. Submit the form</li>";
echo "<li>6. Check if images appear in admin/edit-edition.php</li>";
echo "</ul>";
?>

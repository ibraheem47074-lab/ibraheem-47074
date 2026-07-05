<?php
require_once 'config/database.php';

echo "<h2>Fix Edition Column Names</h2>";

// Check what columns actually exist
$structure_query = "DESCRIBE news_editions";
$result = mysqli_query($conn, $structure_query);
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
}

echo "<h3>Actual columns in news_editions:</h3>";
echo "<ul>";
foreach ($columns as $col) {
    echo "<li><strong>$col</strong></li>";
}
echo "</ul>";

// Determine correct column names
$name_column = in_array('edition_name', $columns) ? 'edition_name' : (in_array('title', $columns) ? 'title' : 'name');
$type_column = in_array('edition_type', $columns) ? 'edition_type' : 'type';

echo "<h3>Using columns:</h3>";
echo "<p>Name column: <strong>$name_column</strong></p>";
echo "<p>Type column: <strong>$type_column</strong></p>";

// Fix edit-edition.php
$file_path = 'admin/edit-edition.php';
$content = file_get_contents($file_path);

// Replace form field names
$content = preg_replace('/name="title"/', "name=\"$name_column\"", $content);
$content = preg_replace('/name="edition_type"/', "name=\"$type_column\"", $content);

// Replace POST variables
$content = preg_replace('/\$_POST\[\'title\'\]/', "\$_POST['$name_column']", $content);
$content = preg_replace('/\$_POST\[\'edition_type\'\]/', "\$_POST['$type_column']", $content);

// Replace array access
$content = preg_replace('/\$edition\[\'title\'\]/', "\$edition['$name_column']", $content);
$content = preg_replace('/\$edition\[\'edition_type\'\]/', "\$edition['$type_column']", $content);

// Replace UPDATE query
$update_pattern = '/UPDATE news_editions SET (title|edition_name|name) = \?, (edition_type|type) = \?,/';
$replacement = "UPDATE news_editions SET $name_column = ?, $type_column = ?,";
$content = preg_replace($update_pattern, $replacement, $content);

file_put_contents($file_path, $content);
echo "<p style='color: green;'>Fixed edit-edition.php</p>";

// Fix add-edition.php
$file_path = 'admin/add-edition.php';
$content = file_get_contents($file_path);

// Replace INSERT query
$insert_pattern = '/INSERT INTO news_editions \([^)]+\) VALUES \([^)]+\)/';
if (preg_match($insert_pattern, $content)) {
    $new_insert = "INSERT INTO news_editions (news_id, $name_column, $type_column, content, additional_images, status, published_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
    $content = preg_replace($insert_pattern, $new_insert, $content);
    
    // Fix bind_param
    $content = preg_replace('/mysqli_stmt_bind_param\(\$stmt, \'[^\']+\',/', "mysqli_stmt_bind_param(\$stmt, 'issssss',", $content);
    
    // Fix variables in bind_param
    $content = preg_replace('/\$edition_name, \$edition_type/', "\$$name_column, \$$type_column", $content);
}

file_put_contents($file_path, $content);
echo "<p style='color: green;'>Fixed add-edition.php</p>";

echo "<h3>Next Steps:</h3>";
echo "<p>1. Try accessing <a href='admin/edit-edition.php'>edit-edition.php</a> again</p>";
echo "<p>2. Test creating a new edition at <a href='admin/add-edition.php'>add-edition.php</a></p>";
echo "<p>3. Upload images to test the image functionality</p>";
?>

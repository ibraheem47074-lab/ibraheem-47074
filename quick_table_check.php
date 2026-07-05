<?php
require_once 'config/database.php';

echo "<h2>Quick Table Check</h2>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    echo "<p style='color: red;'>Table 'news_editions' does not exist!</p>";
    exit;
}

echo "<p style='color: green;'>Table 'news_editions' exists!</p>";

// Get actual table structure
$structure_query = "DESCRIBE news_editions";
$result = mysqli_query($conn, $structure_query);

echo "<h3>Actual columns in news_editions table:</h3>";
echo "<ul>";
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "<li><strong>" . $row['Field'] . "</strong> - " . $row['Type'] . "</li>";
}
echo "</ul>";

// Now let's fix the edit-edition.php file with correct column names
echo "<h3>Fixing edit-edition.php...</h3>";

// Read the file
$file_content = file_get_contents('admin/edit-edition.php');

// Replace column names based on what actually exists
if (in_array('title', $columns) && !in_array('edition_name', $columns)) {
    $file_content = str_replace('edition_name', 'title', $file_content);
    echo "<p>Replaced 'edition_name' with 'title'</p>";
}

if (in_array('type', $columns) && !in_array('edition_type', $columns)) {
    $file_content = str_replace('edition_type', 'type', $file_content);
    echo "<p>Replaced 'edition_type' with 'type'</p>";
}

// Write back the fixed file
file_put_contents('admin/edit-edition.php', $file_content);
echo "<p style='color: green;'>Fixed edit-edition.php with correct column names!</p>";

echo "<p><a href='admin/edit-edition.php'>Test the fix</a></p>";
?>

<?php
require_once 'config/database.php';

echo "<h2>Checking news_editions Table Structure</h2>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    echo "<p style='color: red;'>Error: Table 'news_editions' does not exist!</p>";
    exit;
}

// Get current columns
$result = mysqli_query($conn, "DESCRIBE news_editions");
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
}

echo "<p><strong>Current columns:</strong> " . implode(', ', $columns) . "</p>";

// Required columns based on edit-edition.php
$required_columns = [
    'id' => "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY",
    'news_id' => "int(11) NOT NULL",
    'title' => "varchar(255) NOT NULL",
    'edition_type' => "enum('morning','evening','breaking','special','weekend','regional') NOT NULL DEFAULT 'morning'",
    'content' => "text",
    'additional_images' => "longtext",
    'priority' => "int(11) DEFAULT 0",
    'status' => "enum('draft','published','archived') DEFAULT 'draft'",
    'published_at' => "timestamp NULL",
    'created_at' => "timestamp DEFAULT CURRENT_TIMESTAMP",
    'updated_at' => "timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

$missing_columns = [];
foreach ($required_columns as $col => $def) {
    if (!in_array($col, $columns)) {
        $missing_columns[$col] = $def;
    }
}

if (empty($missing_columns)) {
    echo "<p style='color: green;'>All required columns exist!</p>";
    exit;
}

echo "<h3>Missing columns found:</h3>";
echo "<ul>";
foreach ($missing_columns as $col => $def) {
    echo "<li><strong>$col</strong>: $def</li>";
}
echo "</ul>";

// Build ALTER TABLE statements
echo "<h3>Adding missing columns...</h3>";

foreach ($missing_columns as $col => $def) {
    // Determine position
    $after_col = '';
    $col_keys = array_keys($required_columns);
    $col_pos = array_search($col, $col_keys);
    if ($col_pos > 0) {
        $prev_col = $col_keys[$col_pos - 1];
        if (in_array($prev_col, $columns)) {
            $after_col = " AFTER `$prev_col`";
        }
    }
    
    $sql = "ALTER TABLE news_editions ADD COLUMN `$col` $def$after_col";
    echo "<p>Executing: $sql</p>";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Added column '$col'</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding '$col': " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Done!</h3>";
echo "<p><a href='admin/edit-edition.php?id=1' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Go to Edit Edition Page</a></p>";
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
</style>

<?php
require_once 'config/database.php';

echo "<h2>Actual News Editions Table Structure</h2>";

// Check if table exists first
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    echo "<p style='color: red;'>Table 'news_editions' does not exist!</p>";
} else {
    echo "<p style='color: green;'>Table 'news_editions' exists!</p>";
    
    // Get actual table structure
    echo "<h3>Current table structure:</h3>";
    $structure_query = "DESCRIBE news_editions";
    $result = mysqli_query($conn, $structure_query);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $has_edition_type = false;
    $has_title = false;
    $has_edition_name = false;
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'edition_type') $has_edition_type = true;
        if ($row['Field'] === 'title') $has_title = true;
        if ($row['Field'] === 'edition_name') $has_edition_name = true;
    }
    echo "</table>";
    
    echo "<h3>Column Analysis:</h3>";
    echo "<p>- Has 'edition_type' column: " . ($has_edition_type ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "</p>";
    echo "<p>- Has 'title' column: " . ($has_title ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "</p>";
    echo "<p>- Has 'edition_name' column: " . ($has_edition_name ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "</p>";
    
    // If missing edition_type, provide SQL to add it
    if (!$has_edition_type) {
        echo "<h3>SQL to add missing edition_type column:</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo "ALTER TABLE news_editions ADD COLUMN edition_type ENUM('morning','evening','breaking','special','weekend','regional') NOT NULL DEFAULT 'morning' AFTER edition_name;";
        echo "</pre>";
    }
    
    // If using edition_name instead of title, show the fix
    if ($has_edition_name && !$has_title) {
        echo "<h3>Code Fix Needed:</h3>";
        echo "<p>The table uses 'edition_name' column but the code tries to update 'title'. Change line 92-94 in edit-edition.php from:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo "UPDATE news_editions SET title = ?, edition_type = ?, content = ?,";
        echo "</pre>";
        echo "<p>To:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo "UPDATE news_editions SET edition_name = ?, edition_type = ?, content = ?,";
        echo "</pre>";
    }
}
?>

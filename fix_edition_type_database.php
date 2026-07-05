<?php
require_once 'config/database.php';

echo "<h2>Fixing news_editions Table Structure</h2>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    echo "<p style='color: red;'>Error: Table 'news_editions' does not exist!</p>";
    exit;
}

// Check if edition_type column exists
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM news_editions LIKE 'edition_type'");
if (mysqli_num_rows($column_check) > 0) {
    echo "<p style='color: green;'>Column 'edition_type' already exists. No fix needed.</p>";
    exit;
}

// Add the missing column
$alter_sql = "ALTER TABLE news_editions 
              ADD COLUMN edition_type ENUM('morning','evening','breaking','special','weekend','regional') 
              NOT NULL DEFAULT 'morning' 
              AFTER edition_name";

echo "<p>Adding missing 'edition_type' column...</p>";

if (mysqli_query($conn, $alter_sql)) {
    echo "<p style='color: green;'>SUCCESS: Column 'edition_type' added successfully!</p>";
    echo "<p>You can now use the edit-edition.php page without errors.</p>";
    echo "<p><a href='admin/edit-edition.php?id=1' class='btn btn-primary'>Go to Edit Edition Page</a></p>";
} else {
    echo "<p style='color: red;'>ERROR: " . mysqli_error($conn) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
</style>

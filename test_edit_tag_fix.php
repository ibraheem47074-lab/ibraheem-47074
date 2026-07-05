<?php
// Test file to verify the edit-tag.php fix
require_once 'config/database.php';

echo "Testing edit-tag.php fix...\n";

// Read the current content of edit-tag.php
$file_content = file_get_contents('admin/edit-tag.php');

// Check if the problematic SQL query still exists
if (strpos($file_content, 'UPDATE tags SET name = ?, slug = ?, description = ?, color = ?, status = ?') !== false) {
    echo "ERROR: The old SQL query with description, color, and status still exists in the file!\n";
    die("The file was not properly updated.");
} else {
    echo "SUCCESS: The old SQL query has been removed.\n";
}

// Check if the correct SQL query exists
if (strpos($file_content, 'UPDATE tags SET name = ?, slug = ? WHERE id = ?') !== false) {
    echo "SUCCESS: The correct SQL query is present.\n";
} else {
    echo "ERROR: The correct SQL query is not found!\n";
}

// Check if description field still exists in HTML
if (strpos($file_content, 'name="description"') !== false) {
    echo "ERROR: Description field still exists in HTML!\n";
} else {
    echo "SUCCESS: Description field has been removed from HTML.\n";
}

// Check if color field still exists in HTML
if (strpos($file_content, 'name="color"') !== false) {
    echo "ERROR: Color field still exists in HTML!\n";
} else {
    echo "SUCCESS: Color field has been removed from HTML.\n";
}

// Check if status field still exists in HTML
if (strpos($file_content, 'name="status"') !== false) {
    echo "ERROR: Status field still exists in HTML!\n";
} else {
    echo "SUCCESS: Status field has been removed from HTML.\n";
}

echo "\nFile verification complete.\n";
?>

<?php
require_once 'config/database.php';
echo 'Checking categories table structure...\n';
$result = mysqli_query($conn, 'DESCRIBE categories');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}

echo "\n\nChecking if parent_id column exists...\n";
$check_parent = mysqli_query($conn, "SHOW COLUMNS FROM categories LIKE 'parent_id'");
if (mysqli_num_rows($check_parent) > 0) {
    echo "parent_id column exists\n";
} else {
    echo "parent_id column does NOT exist - need to add it\n";
}

echo "\n\nCurrent categories:\n";
$categories = mysqli_query($conn, "SELECT id, name, slug FROM categories ORDER BY name");
while ($cat = mysqli_fetch_assoc($categories)) {
    echo $cat['id'] . ' - ' . $cat['name'] . ' (' . $cat['slug'] . ")\n";
}
?>

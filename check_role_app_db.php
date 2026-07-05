<?php
require_once 'config/database.php';

echo "Checking role_applications table...\n";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'role_applications'");
if (mysqli_num_rows($result) > 0) {
    echo "role_applications table exists\n";
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM role_applications");
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    echo "role_applications table does not exist\n";
}

echo "\nChecking users table for application columns...\n";
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'application_%'");
if (mysqli_num_rows($result) > 0) {
    while ($col = mysqli_fetch_assoc($result)) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    echo "application columns not found in users table\n";
}

echo "\nChecking uploads directory...\n";
if (is_dir('uploads/cv/')) {
    echo "uploads/cv/ directory exists\n";
} else {
    echo "uploads/cv/ directory does not exist\n";
}

echo "\nDone.\n";
?>

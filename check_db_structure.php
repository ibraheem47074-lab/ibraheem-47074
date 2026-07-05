<?php
require_once 'config/database.php';

echo "=== Database Structure Check ===\n\n";

// Check users table
echo "USERS table structure:\n";
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n";

// Check articles table
echo "ARTICLES table structure:\n";
$result = mysqli_query($conn, "DESCRIBE articles");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n";

// Check if affiliate_products table exists
echo "AFFILIATE_PRODUCTS table check:\n";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($result) > 0) {
    echo "Table exists. Structure:\n";
    $result = mysqli_query($conn, "DESCRIBE affiliate_products");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Table does NOT exist.\n";
}

echo "\n";

// Check polls table for user_id column
echo "POLLS table structure:\n";
$result = mysqli_query($conn, "DESCRIBE polls");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n=== Check Complete ===\n";
?>

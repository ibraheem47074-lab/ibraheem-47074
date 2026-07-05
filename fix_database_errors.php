<?php
require_once 'config/database.php';

echo "=== Fixing Database Errors ===\n\n";

// Fix 1: Add missing 'image' column to users table if it doesn't exist
echo "1. Checking users table for 'image' column...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'image'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding 'image' column to users table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER email");
    if ($add_column) {
        echo "✓ 'image' column added successfully.\n";
    } else {
        echo "✗ Error adding 'image' column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ 'image' column already exists.\n";
}

// Fix 2: Add missing 'image_type' column to articles table if it doesn't exist
echo "\n2. Checking articles table for 'image_type' column...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE 'image_type'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding 'image_type' column to articles table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE articles ADD COLUMN image_type VARCHAR(50) DEFAULT 'standard' AFTER image");
    if ($add_column) {
        echo "✓ 'image_type' column added successfully.\n";
    } else {
        echo "✗ Error adding 'image_type' column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ 'image_type' column already exists.\n";
}

// Fix 3: Create affiliate_products table if it doesn't exist
echo "\n3. Checking affiliate_products table...\n";
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($check_table) == 0) {
    echo "Creating affiliate_products table...\n";
    $create_table = mysqli_query($conn, "CREATE TABLE affiliate_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        affiliate_url VARCHAR(500),
        image_url VARCHAR(500),
        category VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    if ($create_table) {
        echo "✓ affiliate_products table created successfully.\n";
    } else {
        echo "✗ Error creating affiliate_products table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ affiliate_products table already exists.\n";
}

// Fix 4: Add missing 'user_id' column to polls table if it doesn't exist
echo "\n4. Checking polls table for 'user_id' column...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM polls LIKE 'user_id'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding 'user_id' column to polls table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE polls ADD COLUMN user_id INT NULL AFTER id");
    if ($add_column) {
        echo "✓ 'user_id' column added successfully.\n";
    } else {
        echo "✗ Error adding 'user_id' column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ 'user_id' column already exists.\n";
}

// Fix 5: Add missing 'source_name' column to articles table if it doesn't exist
echo "\n5. Checking articles table for 'source_name' column...\n";
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE 'source_name'");
if (mysqli_num_rows($check_column) == 0) {
    echo "Adding 'source_name' column to articles table...\n";
    $add_column = mysqli_query($conn, "ALTER TABLE articles ADD COLUMN source_name VARCHAR(255) NULL AFTER source");
    if ($add_column) {
        echo "✓ 'source_name' column added successfully.\n";
    } else {
        echo "✗ Error adding 'source_name' column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ 'source_name' column already exists.\n";
}

echo "\n=== Database Fixes Complete ===\n";
?>

<?php
// Simple script to run database fixes
require_once 'config/database.php';

echo "Starting database fixes...\n";

// Fix 1: Add image column to users
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'image'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER email";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added image column to users\n";
    } else {
        echo "✗ Failed to add image column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ Image column already exists in users\n";
}

// Fix 2: Add image_type column to articles
$check = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE 'image_type'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE articles ADD COLUMN image_type VARCHAR(50) DEFAULT 'standard' AFTER image";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added image_type column to articles\n";
    } else {
        echo "✗ Failed to add image_type column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ Image_type column already exists in articles\n";
}

// Fix 3: Create affiliate_products table
$check = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_products'");
if (mysqli_num_rows($check) == 0) {
    $sql = "CREATE TABLE affiliate_products (
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
    )";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Created affiliate_products table\n";
    } else {
        echo "✗ Failed to create affiliate_products table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ Affiliate_products table already exists\n";
}

// Fix 4: Add user_id column to polls
$check = mysqli_query($conn, "SHOW COLUMNS FROM polls LIKE 'user_id'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE polls ADD COLUMN user_id INT NULL AFTER id";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added user_id column to polls\n";
    } else {
        echo "✗ Failed to add user_id column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ User_id column already exists in polls\n";
}

// Fix 5: Add source_name column to articles
$check = mysqli_query($conn, "SHOW COLUMNS FROM articles LIKE 'source_name'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE articles ADD COLUMN source_name VARCHAR(255) NULL AFTER source";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Added source_name column to articles\n";
    } else {
        echo "✗ Failed to add source_name column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ Source_name column already exists in articles\n";
}

echo "Database fixes completed!\n";
?>

<?php
// Quick database fix - run this directly
$mysqli = new mysqli('localhost', 'root', '', 'pk_live_news');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database\n";

// Fix 1: Add image column to users
$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'image'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN image VARCHAR(255) NULL AFTER email";
    if ($mysqli->query($sql)) {
        echo "✓ Added image column to users\n";
    } else {
        echo "✗ Error adding image column: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Image column exists in users\n";
}

// Fix 2: Add image_type to articles
$result = $mysqli->query("SHOW COLUMNS FROM articles LIKE 'image_type'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE articles ADD COLUMN image_type VARCHAR(50) DEFAULT 'standard' AFTER image";
    if ($mysqli->query($sql)) {
        echo "✓ Added image_type column to articles\n";
    } else {
        echo "✗ Error adding image_type column: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Image_type column exists in articles\n";
}

// Fix 3: Add source_name to articles
$result = $mysqli->query("SHOW COLUMNS FROM articles LIKE 'source_name'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE articles ADD COLUMN source_name VARCHAR(255) NULL AFTER source";
    if ($mysqli->query($sql)) {
        echo "✓ Added source_name column to articles\n";
    } else {
        echo "✗ Error adding source_name column: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Source_name column exists in articles\n";
}

// Fix 4: Create affiliate_products table
$result = $mysqli->query("SHOW TABLES LIKE 'affiliate_products'");
if ($result->num_rows == 0) {
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
    if ($mysqli->query($sql)) {
        echo "✓ Created affiliate_products table\n";
    } else {
        echo "✗ Error creating affiliate_products table: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Affiliate_products table exists\n";
}

// Fix 5: Add user_id to polls
$result = $mysqli->query("SHOW COLUMNS FROM polls LIKE 'user_id'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE polls ADD COLUMN user_id INT NULL AFTER id";
    if ($mysqli->query($sql)) {
        echo "✓ Added user_id column to polls\n";
    } else {
        echo "✗ Error adding user_id column: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ User_id column exists in polls\n";
}

echo "Database fixes completed!\n";
$mysqli->close();
?>

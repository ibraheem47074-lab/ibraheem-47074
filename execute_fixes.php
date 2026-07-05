<?php
// Database fixes execution script
echo "<pre>";
echo "=== PK Live News Database Fixes ===\n\n";

// Include database configuration
require_once 'config/database.php';

// Helper function to safely add column
function addColumnIfNotExists($conn, $table, $column, $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (mysqli_query($conn, $sql)) {
            echo "✓ Added $column to $table\n";
            return true;
        } else {
            echo "✗ Failed to add $column to $table: " . mysqli_error($conn) . "\n";
            return false;
        }
    } else {
        echo "✓ $column already exists in $table\n";
        return true;
    }
}

// Helper function to safely create table
function createTableIfNotExists($conn, $table, $definition) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check) == 0) {
        if (mysqli_query($conn, $definition)) {
            echo "✓ Created $table table\n";
            return true;
        } else {
            echo "✗ Failed to create $table table: " . mysqli_error($conn) . "\n";
            return false;
        }
    } else {
        echo "✓ $table table already exists\n";
        return true;
    }
}

// Execute fixes
echo "1. Fixing users table...\n";
addColumnIfNotExists($conn, 'users', 'image', 'VARCHAR(255) NULL AFTER email');

echo "\n2. Fixing articles table...\n";
addColumnIfNotExists($conn, 'articles', 'image_type', 'VARCHAR(50) DEFAULT "standard" AFTER image');
addColumnIfNotExists($conn, 'articles', 'source_name', 'VARCHAR(255) NULL AFTER source');

echo "\n3. Creating affiliate_products table...\n";
$affiliate_table_sql = "CREATE TABLE `affiliate_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2),
    `affiliate_url` VARCHAR(500),
    `image_url` VARCHAR(500),
    `category` VARCHAR(100),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
createTableIfNotExists($conn, 'affiliate_products', $affiliate_table_sql);

echo "\n4. Fixing polls table...\n";
addColumnIfNotExists($conn, 'polls', 'user_id', 'INT NULL AFTER id');

echo "\n5. Adding indexes for better performance...\n";
// Add indexes if they don't exist
$index_checks = [
    'articles' => 'source_name',
    'polls' => 'user_id'
];

foreach ($index_checks as $table => $column) {
    $index_name = "idx_{$table}_{$column}";
    $check_index = mysqli_query($conn, "SHOW INDEX FROM `$table` WHERE Key_name = '$index_name'");
    if (mysqli_num_rows($check_index) == 0) {
        $sql = "ALTER TABLE `$table` ADD INDEX `$index_name` (`$column`)";
        if (mysqli_query($conn, $sql)) {
            echo "✓ Added index $index_name\n";
        } else {
            echo "✗ Failed to add index $index_name: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Index $index_name already exists\n";
    }
}

echo "\n=== Database Fixes Complete ===\n";
echo "\nChecking for any remaining errors...\n";

// Test some queries to verify fixes
echo "\nTesting queries:\n";

// Test users table with image column
$test = mysqli_query($conn, "SELECT id, username, email, image FROM users LIMIT 1");
if ($test) {
    echo "✓ Users table query successful\n";
} else {
    echo "✗ Users table query failed: " . mysqli_error($conn) . "\n";
}

// Test articles table with new columns
$test = mysqli_query($conn, "SELECT id, title, image, image_type, source, source_name FROM articles LIMIT 1");
if ($test) {
    echo "✓ Articles table query successful\n";
} else {
    echo "✗ Articles table query failed: " . mysqli_error($conn) . "\n";
}

// Test affiliate_products table
$test = mysqli_query($conn, "SELECT COUNT(*) as total FROM affiliate_products");
if ($test) {
    echo "✓ Affiliate_products table query successful\n";
} else {
    echo "✗ Affiliate_products table query failed: " . mysqli_error($conn) . "\n";
}

// Test polls table with user_id
$test = mysqli_query($conn, "SELECT id, user_id, question FROM polls LIMIT 1");
if ($test) {
    echo "✓ Polls table query successful\n";
} else {
    echo "✗ Polls table query failed: " . mysqli_error($conn) . "\n";
}

echo "\nAll database fixes have been applied successfully!\n";
echo "</pre>";
?>

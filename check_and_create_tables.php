<?php
// Check and create missing tables
$mysqli = new mysqli('localhost', 'root', '', 'pk_live_news');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database\n\n";

// Check existing tables
$result = $mysqli->query("SHOW TABLES");
echo "Existing tables:\n";
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}
echo "\n";

// Create articles table if it doesn't exist
$result = $mysqli->query("SHOW TABLES LIKE 'articles'");
if ($result->num_rows == 0) {
    echo "Creating articles table...\n";
    $sql = "CREATE TABLE articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        excerpt TEXT,
        author VARCHAR(100),
        category VARCHAR(100),
        tags VARCHAR(255),
        image VARCHAR(255),
        image_type VARCHAR(50) DEFAULT 'standard',
        source VARCHAR(255),
        source_name VARCHAR(255),
        status ENUM('published', 'draft', 'pending') DEFAULT 'draft',
        featured BOOLEAN DEFAULT FALSE,
        breaking_news BOOLEAN DEFAULT FALSE,
        views INT DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($sql)) {
        echo "✓ Created articles table\n";
    } else {
        echo "✗ Error creating articles table: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Articles table exists\n";
}

// Create polls table if it doesn't exist
$result = $mysqli->query("SHOW TABLES LIKE 'polls'");
if ($result->num_rows == 0) {
    echo "Creating polls table...\n";
    $sql = "CREATE TABLE polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        question VARCHAR(255) NOT NULL,
        options JSON,
        votes JSON,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($sql)) {
        echo "✓ Created polls table\n";
    } else {
        echo "✗ Error creating polls table: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Polls table exists\n";
}

// Create users table if it doesn't exist
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "Creating users table...\n";
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        image VARCHAR(255),
        role ENUM('admin', 'editor', 'reporter', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($sql)) {
        echo "✓ Created users table\n";
    } else {
        echo "✗ Error creating users table: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Users table exists\n";
}

// Create categories table if it doesn't exist
$result = $mysqli->query("SHOW TABLES LIKE 'categories'");
if ($result->num_rows == 0) {
    echo "Creating categories table...\n";
    $sql = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE,
        description TEXT,
        image VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($mysqli->query($sql)) {
        echo "✓ Created categories table\n";
    } else {
        echo "✗ Error creating categories table: " . $mysqli->error . "\n";
    }
} else {
    echo "✓ Categories table exists\n";
}

echo "\nTable creation completed!\n";

// Now run the column fixes
echo "\nRunning column fixes...\n";

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

echo "\nAll database fixes completed successfully!\n";
$mysqli->close();
?>

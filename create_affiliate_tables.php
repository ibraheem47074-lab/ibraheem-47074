<?php
require_once 'config/database.php';

echo "<h1>Creating Affiliate System Tables</h1>";

$tables_to_create = [
    'affiliate_products' => "
        CREATE TABLE IF NOT EXISTS `affiliate_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `price` decimal(10,2) NOT NULL DEFAULT 0.00,
            `original_price` decimal(10,2) DEFAULT NULL,
            `discount_percentage` decimal(5,2) DEFAULT 0.00,
            `affiliate_url` varchar(500) NOT NULL,
            `image_url` varchar(500) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `brand` varchar(100) DEFAULT NULL,
            `rating` decimal(3,2) DEFAULT 0.00,
            `reviews_count` int(11) DEFAULT 0,
            `availability` enum('in_stock','out_of_stock','limited') DEFAULT 'in_stock',
            `featured` tinyint(1) DEFAULT 0,
            `status` enum('active','inactive','pending') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            `created_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `category_id` (`category_id`),
            KEY `status` (`status`),
            KEY `featured` (`featured`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL,
            FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'affiliate_categories' => "
        CREATE TABLE IF NOT EXISTS `affiliate_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(100) NOT NULL,
            `description` text DEFAULT NULL,
            `icon` varchar(50) DEFAULT NULL,
            `image` varchar(500) DEFAULT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `sort_order` int(11) DEFAULT 0,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `parent_id` (`parent_id`),
            KEY `status` (`status`),
            KEY `sort_order` (`sort_order`),
            FOREIGN KEY (`parent_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'affiliate_clicks' => "
        CREATE TABLE IF NOT EXISTS `affiliate_clicks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `referrer` varchar(500) DEFAULT NULL,
            `clicked_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `converted` tinyint(1) DEFAULT 0,
            `conversion_amount` decimal(10,2) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `product_id` (`product_id`),
            KEY `user_id` (`user_id`),
            KEY `clicked_at` (`clicked_at`),
            KEY `converted` (`converted`),
            FOREIGN KEY (`product_id`) REFERENCES `affiliate_products` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'affiliate_commissions' => "
        CREATE TABLE IF NOT EXISTS `affiliate_commissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `click_id` int(11) DEFAULT NULL,
            `product_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `order_id` varchar(100) DEFAULT NULL,
            `amount` decimal(10,2) NOT NULL,
            `commission_rate` decimal(5,2) DEFAULT 0.00,
            `commission_amount` decimal(10,2) NOT NULL,
            `status` enum('pending','approved','rejected') DEFAULT 'pending',
            `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `approved_at` timestamp NULL DEFAULT NULL,
            `approved_by` int(11) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `click_id` (`click_id`),
            KEY `product_id` (`product_id`),
            KEY `user_id` (`user_id`),
            KEY `status` (`status`),
            KEY `earned_at` (`earned_at`),
            FOREIGN KEY (`click_id`) REFERENCES `affiliate_clicks` (`id`) ON DELETE SET NULL,
            FOREIGN KEY (`product_id`) REFERENCES `affiliate_products` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

$success_count = 0;
$error_count = 0;

foreach ($tables_to_create as $table_name => $create_sql) {
    echo "<h3>Creating table: $table_name</h3>";
    
    // Check if table exists first
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
    if (mysqli_num_rows($table_check) == 0) {
        echo "<p style='color: orange;'>⚠ Table '$table_name' does not exist. Creating...</p>";
        
        if (mysqli_query($conn, $create_sql)) {
            echo "<p style='color: green;'>✓ Table '$table_name' created successfully</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Error creating table '$table_name': " . mysqli_error($conn) . "</p>";
            $error_count++;
        }
    } else {
        echo "<p style='color: blue;'>ℹ Table '$table_name' already exists</p>";
    }
}

// Insert default categories if none exist
echo "<h3>Checking Default Categories</h3>";
$category_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories");
$category_count = mysqli_fetch_assoc($category_check)['count'];

if ($category_count == 0) {
    echo "<p style='color: orange;'>⚠ No categories found. Inserting default categories...</p>";
    
    $default_categories = [
        ['Electronics', 'electronics', 'fas fa-laptop', 'Latest gadgets and electronic devices'],
        ['Fashion', 'fashion', 'fas fa-tshirt', 'Trending clothing and accessories'],
        ['Home & Garden', 'home-garden', 'fas fa-home', 'Everything for your home and garden'],
        ['Sports & Outdoors', 'sports-outdoors', 'fas fa-football-ball', 'Sports equipment and outdoor gear'],
        ['Books & Media', 'books-media', 'fas fa-book', 'Books, movies, and digital media'],
        ['Health & Beauty', 'health-beauty', 'fas fa-heart', 'Health supplements and beauty products'],
        ['Toys & Games', 'toys-games', 'fas fa-gamepad', 'Toys and games for all ages'],
        ['Automotive', 'automotive', 'fas fa-car', 'Car parts and accessories']
    ];
    
    foreach ($default_categories as $category) {
        $insert_sql = "INSERT INTO affiliate_categories (name, slug, icon, description, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $category[0], $category[1], $category[2], $category[3]);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✓ Added category: {$category[0]}</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to add category: {$category[0]}</p>";
        }
    }
} else {
    echo "<p style='color: blue;'>ℹ Categories already exist ($category_count found)</p>";
}

// Insert sample products if none exist
echo "<h3>Checking Sample Products</h3>";
$product_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_products");
$product_count = mysqli_fetch_assoc($product_check)['count'];

if ($product_count == 0) {
    echo "<p style='color: orange;'>⚠ No products found. Inserting sample products...</p>";
    
    $sample_products = [
        [
            'Wireless Headphones Pro',
            'Premium noise-cancelling wireless headphones with 30-hour battery life',
            299.99,
            'https://example.com/headphones-pro',
            'https://via.placeholder.com/300x300',
            1,
            'TechBrand',
            4.5,
            234
        ],
        [
            'Smart Watch Ultra',
            'Advanced fitness tracking and health monitoring smartwatch',
            449.99,
            'https://example.com/smartwatch-ultra',
            'https://via.placeholder.com/300x300',
            1,
            'TechBrand',
            4.7,
            189
        ],
        [
            'Organic Yoga Mat',
            'Eco-friendly non-slip yoga mat with carrying strap',
            39.99,
            'https://example.com/yoga-mat',
            'https://via.placeholder.com/300x300',
            5,
            'EcoFit',
            4.8,
            567
        ]
    ];
    
    foreach ($sample_products as $product) {
        $insert_sql = "INSERT INTO affiliate_products (name, description, price, affiliate_url, image_url, category_id, brand, rating, reviews_count, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, 'ssdsisdis', 
            $product[0], $product[1], $product[2], $product[3], 
            $product[4], $product[5], $product[6], $product[7], $product[8]
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✓ Added product: {$product[0]}</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to add product: {$product[0]}</p>";
        }
    }
} else {
    echo "<p style='color: blue;'>ℹ Products already exist ($product_count found)</p>";
}

echo "<h2>Summary</h2>";
echo "<p style='color: green;'>✓ Successfully created: $success_count tables</p>";
echo "<p style='color: red;'>✗ Failed to create: $error_count tables</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All affiliate system tables created successfully!</p>";
    echo "<p><a href='products.php'>Test Products Page</a> | <a href='index.php'>Go to Home</a></p>";
} else {
    echo "<p style='color: red;'>⚠ Some tables failed to create. Please check errors above.</p>";
}

// Verify final table structure
echo "<h2>Final Table Structure</h2>";
$tables_to_check = ['affiliate_products', 'affiliate_categories', 'affiliate_clicks', 'affiliate_commissions'];

foreach ($tables_to_check as $table_name) {
    echo "<h4>$table_name</h4>";
    $columns_query = "SHOW COLUMNS FROM $table_name";
    $columns_result = mysqli_query($conn, $columns_query);
    
    if ($columns_result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($column = mysqli_fetch_assoc($columns_result)) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Error checking table structure for $table_name</p>";
    }
}
?>

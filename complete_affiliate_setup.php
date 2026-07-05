<?php
require_once 'config/database.php';

echo "<h2>Complete Affiliate System Setup</h2>";

// Create affiliate_products table
echo "<h3>Creating affiliate_products table...</h3>";
$create_products = "CREATE TABLE IF NOT EXISTS `affiliate_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `short_description` varchar(500),
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `image_url` varchar(500),
  `affiliate_url` varchar(500) NOT NULL,
  `affiliate_network` enum('amazon','aliexpress','other') DEFAULT 'amazon',
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `review_count` int(11) DEFAULT '0',
  `availability` enum('in_stock','out_of_stock','limited') DEFAULT 'in_stock',
  `brand` varchar(100),
  `model` varchar(100),
  `tags` varchar(255),
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `click_count` int(11) DEFAULT '0',
  `conversion_count` int(11) DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_products)) {
    echo "<p style='color: green;'>affiliate_products table created/exists</p>";
} else {
    echo "<p style='color: red;'>Error creating affiliate_products table: " . mysqli_error($conn) . "</p>";
}

// Create affiliate_categories table
echo "<h3>Creating affiliate_categories table...</h3>";
$create_categories = "CREATE TABLE IF NOT EXISTS `affiliate_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50),
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_categories)) {
    echo "<p style='color: green;'>affiliate_categories table created/exists</p>";
} else {
    echo "<p style='color: red;'>Error creating affiliate_categories table: " . mysqli_error($conn) . "</p>";
}

// Create affiliate_clicks table
echo "<h3>Creating affiliate_clicks table...</h3>";
$create_clicks = "CREATE TABLE IF NOT EXISTS `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `referrer` varchar(500),
  `click_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `converted` tinyint(1) DEFAULT '0',
  `conversion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `click_date` (`click_date`),
  KEY `converted` (`converted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $create_clicks)) {
    echo "<p style='color: green;'>affiliate_clicks table created/exists</p>";
} else {
    echo "<p style='color: red;'>Error creating affiliate_clicks table: " . mysqli_error($conn) . "</p>";
}

// Add foreign key if not exists
echo "<h3>Adding foreign key constraints...</h3>";
mysqli_query($conn, "ALTER TABLE `affiliate_products` ADD CONSTRAINT `fk_affiliate_products_category` FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL");

// Check if categories exist and add them if needed
$cat_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories"))['count'];

if ($cat_count == 0) {
    echo "<h3>Adding default categories...</h3>";
    $categories = [
        ['Electronics', 'electronics', 'Mobile phones, laptops, gadgets', 'fa-laptop', 1],
        ['Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', 2],
        ['Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', 3],
        ['Gaming', 'gaming', 'Gaming consoles and accessories', 'fa-gamepad', 4],
        ['Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', 5],
        ['Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', 6],
        ['Smart Home', 'smart-home', 'Smart home devices and IoT', 'fa-home', 7],
        ['Fashion', 'fashion', 'Clothing and accessories', 'fa-tshirt', 8],
        ['Sports', 'sports', 'Sports equipment and gear', 'fa-football-ball', 9],
        ['Books', 'books', 'Books and educational materials', 'fa-book', 10]
    ];
    
    foreach ($categories as $cat) {
        mysqli_query($conn, "INSERT INTO affiliate_categories (name, slug, description, icon, sort_order) VALUES ('$cat[0]', '$cat[1]', '$cat[2]', '$cat[3]', $cat[4])");
    }
    echo "<p style='color: green;'>10 default categories added</p>";
} else {
    echo "<p style='color: green;'>Categories already exist ($cat_count found)</p>";
}

// Show final status
echo "<h3>Setup Complete! Status:</h3>";
$tables = ['affiliate_products', 'affiliate_categories', 'affiliate_clicks'];
foreach ($tables as $table) {
    $exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE '$table'")) > 0;
    echo "- $table: " . ($exists ? "EXISTS" : "MISSING") . "<br>";
}

$cat_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories WHERE status = 'active'"))['count'];
$prod_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_products WHERE status = 'active'"))['count'];

echo "<br>Active categories: $cat_count<br>";
echo "Active products: $prod_count<br>";

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Setup Complete! Your Products dropdown should now appear in the header.</h4>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li><a href='index.php'>Refresh your homepage</a> - Products dropdown should be visible</li>";
echo "<li><a href='add_sample_products.php'>Add sample products</a> (optional)</li>";
echo "<li><a href='products.php'>View products page</a></li>";
echo "</ol>";
echo "</div>";

echo "<br><a href='index.php'>Go to Homepage</a>";
?>

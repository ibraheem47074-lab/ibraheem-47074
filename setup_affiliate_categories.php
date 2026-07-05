<?php
require_once 'config/database.php';

echo "<h2>Setting Up Affiliate Categories</h2>";

// Check if categories table exists
$categories_table = mysqli_query($conn, "SHOW TABLES LIKE 'affiliate_categories'");

if (mysqli_num_rows($categories_table) == 0) {
    echo "<p style='color: red;'>affiliate_categories table does not exist. Creating table...</p>";
    
    // Create the table
    $create_table = "CREATE TABLE `affiliate_categories` (
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
    
    if (mysqli_query($conn, $create_table)) {
        echo "<p style='color: green;'>affiliate_categories table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>affiliate_categories table exists.</p>";
}

// Check if categories exist
$cat_count_query = "SELECT COUNT(*) as count FROM affiliate_categories";
$cat_count_result = mysqli_query($conn, $cat_count_query);
$cat_count = mysqli_fetch_assoc($cat_count_result)['count'];

echo "<p>Current categories: $cat_count</p>";

if ($cat_count == 0) {
    echo "<p>Inserting default categories...</p>";
    
    $insert_categories = "INSERT INTO `affiliate_categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
    ('Electronics', 'electronics', 'Mobile phones, laptops, gadgets', 'fa-laptop', 1),
    ('Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', 2),
    ('Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', 3),
    ('Gaming', 'gaming', 'Gaming consoles and accessories', 'fa-gamepad', 4),
    ('Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', 5),
    ('Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', 6),
    ('Smart Home', 'smart-home', 'Smart home devices and IoT', 'fa-home', 7),
    ('Fashion', 'fashion', 'Clothing and accessories', 'fa-tshirt', 8),
    ('Sports', 'sports', 'Sports equipment and gear', 'fa-football-ball', 9),
    ('Books', 'books', 'Books and educational materials', 'fa-book', 10)";
    
    if (mysqli_query($conn, $insert_categories)) {
        echo "<p style='color: green;'>Default categories inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error inserting categories: " . mysqli_error($conn) . "</p>";
    }
}

// Show all categories
$show_categories = "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order";
$categories_result = mysqli_query($conn, $show_categories);

echo "<h3>Available Categories:</h3>";
echo "<ul>";
while ($cat = mysqli_fetch_assoc($categories_result)) {
    echo "<li><strong>" . htmlspecialchars($cat['name']) . "</strong> (ID: " . $cat['id'] . ", Icon: " . htmlspecialchars($cat['icon']) . ")</li>";
}
echo "</ul>";

echo "<br><a href='index.php'>Back to Home</a> | <a href='add_sample_products.php'>Add Sample Products</a>";
?>

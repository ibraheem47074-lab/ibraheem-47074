<?php
require_once 'config/database.php';

echo "<h2>Quick Fix for Product Categories</h2>";

// Create categories table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `affiliate_categories` (
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
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Check if categories exist
$count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM affiliate_categories"))['count'];

if ($count == 0) {
    // Insert basic categories
    $categories = [
        ['Electronics', 'electronics', 'Mobile phones, laptops, gadgets', 'fa-laptop', 1],
        ['Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', 2],
        ['Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', 3],
        ['Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', 4],
        ['Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', 5]
    ];
    
    foreach ($categories as $cat) {
        mysqli_query($conn, "INSERT INTO affiliate_categories (name, slug, description, icon, sort_order) VALUES ('$cat[0]', '$cat[1]', '$cat[2]', '$cat[3]', $cat[4])");
    }
    echo "<p style='color: green;'>Categories added successfully!</p>";
} else {
    echo "<p style='color: green;'>Categories already exist.</p>";
}

// Show result
$result = mysqli_query($conn, "SELECT * FROM affiliate_categories WHERE status = 'active' ORDER BY sort_order");
echo "<h3>Categories now available:</h3>";
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>" . htmlspecialchars($row['name']) . "</li>";
}
echo "</ul>";

echo "<p style='color: blue;'><strong>Now refresh your homepage - the Products dropdown should appear!</strong></p>";
echo "<a href='index.php'>Go to Homepage</a>";
?>

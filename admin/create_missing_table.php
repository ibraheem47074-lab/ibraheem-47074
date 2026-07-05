<?php
require_once '../config/database.php';

echo "<h2>Creating Missing Edition Categories Table</h2>";

// Check if table exists first
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'edition_categories'");
if (mysqli_num_rows($check_table) > 0) {
    echo "<p>✅ Table 'edition_categories' already exists!</p>";
} else {
    echo "<p>⚠️ Table 'edition_categories' does not exist. Creating now...</p>";
    
    // Create table
    $sql = "CREATE TABLE `edition_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(50) NOT NULL,
      `slug` varchar(50) NOT NULL,
      `description` text DEFAULT NULL,
      `icon` varchar(50) DEFAULT 'fa-layer-group',
      `color` varchar(7) DEFAULT '#007bff',
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p>✅ Table 'edition_categories' created successfully!</p>";
        
        // Insert default data
        $insert_sql = "INSERT INTO `edition_categories` (`name`, `slug`, `description`, `icon`, `color`) VALUES
        ('Breaking News', 'breaking', 'Urgent breaking news editions', 'fa-exclamation-triangle', '#dc3545'),
        ('Morning Edition', 'morning', 'Daily morning news roundup', 'fa-sun', '#28a745'),
        ('Evening Edition', 'evening', 'Daily evening news summary', 'fa-moon', '#343a40'),
        ('Special Report', 'special', 'In-depth special reports', 'fa-star', '#ffc107'),
        ('Weekend Edition', 'weekend', 'Weekend news highlights', 'fa-calendar-week', '#6f42c1'),
        ('Regional News', 'regional', 'Regional news coverage', 'fa-map-marker-alt', '#17a2b8')";
        
        if (mysqli_query($conn, $insert_sql)) {
            echo "<p>✅ Default edition categories inserted successfully!</p>";
        } else {
            echo "<p>⚠️ Error inserting data: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>❌ Error creating table: " . mysqli_error($conn) . "</p>";
    }
}

// Test the query that was failing
echo "<h3>Testing the Query</h3>";
$test_result = mysqli_query($conn, "SELECT * FROM edition_categories WHERE status = 'active' ORDER BY name ASC");

if ($test_result) {
    echo "<p>✅ Query executed successfully!</p>";
    echo "<p>Found " . mysqli_num_rows($test_result) . " active edition categories:</p>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($test_result)) {
        echo "<li>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['slug']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ Query failed: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='edit-edition.php'>Go to Edit Edition</a></p>";
?>

<?php
require_once 'config/database.php';

echo "<h2>Creating Edition Categories Table</h2>";

// Create the table
$sql = "CREATE TABLE IF NOT EXISTS `edition_categories` (
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
    echo "<p style='color: green;'>✓ Table 'edition_categories' created successfully!</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
}

// Insert default data
$insert_sql = "INSERT INTO `edition_categories` (`name`, `slug`, `description`, `icon`, `color`) VALUES
('Breaking News', 'breaking', 'Urgent breaking news editions', 'fa-exclamation-triangle', '#dc3545'),
('Morning Edition', 'morning', 'Daily morning news roundup', 'fa-sun', '#28a745'),
('Evening Edition', 'evening', 'Daily evening news summary', 'fa-moon', '#343a40'),
('Special Report', 'special', 'In-depth special reports', 'fa-star', '#ffc107'),
('Weekend Edition', 'weekend', 'Weekend news highlights', 'fa-calendar-week', '#6f42c1'),
('Regional News', 'regional', 'Regional news coverage', 'fa-map-marker-alt', '#17a2b8')";

if (mysqli_query($conn, $insert_sql)) {
    echo "<p style='color: green;'>✓ Default edition categories inserted successfully!</p>";
} else {
    echo "<p style='color: orange;'>⚠ Categories may already exist or error inserting data: " . mysqli_error($conn) . "</p>";
}

// Verify table exists
$check_sql = "SHOW TABLES LIKE 'edition_categories'";
$result = mysqli_query($conn, $check_sql);
if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: blue;'>✓ Table 'edition_categories' exists and is ready to use!</p>";
    
    // Show the data
    $data_sql = "SELECT * FROM edition_categories";
    $data_result = mysqli_query($conn, $data_sql);
    echo "<h3>Current Edition Categories:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Slug</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($data_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['slug'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Table still does not exist!</p>";
}

echo "<p><a href='admin/edit-edition.php'>Test the fix by going to edit-edition.php</a></p>";
?>

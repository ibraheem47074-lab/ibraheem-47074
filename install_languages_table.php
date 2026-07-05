<?php
/**
 * Language Table Installer
 * Run this script via browser to create the missing languages table
 */

require_once 'config/database.php';

echo "<h2>Installing Languages Table...</h2>";

// Check if table already exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'languages'");
if (mysqli_num_rows($check_table) > 0) {
    echo "<p style='color: orange;'>⚠ Languages table already exists!</p>";
    exit;
}

// Create languages table
$sql = "CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `native_name` varchar(100) NOT NULL,
  `flag_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✓ Languages table created successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
    exit;
}

// Insert default languages
$languages = [
    ['en', 'English', 'English', '🇺🇸', 1, 1],
    ['ur', 'Urdu', 'اردو', '🇵🇰', 1, 2],
    ['hi', 'Hindi', 'हिन्दी', '🇮🇳', 1, 3],
    ['zh', 'Chinese', '中文', '🇨🇳', 1, 4],
    ['ps', 'Pashto', 'پښتو', '🇦🇫', 1, 5]
];

$stmt = mysqli_prepare($conn, "INSERT INTO languages (code, name, native_name, flag_icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($languages as $lang) {
    mysqli_stmt_bind_param($stmt, 'ssssii', $lang[0], $lang[1], $lang[2], $lang[3], $lang[4], $lang[5]);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Added language: {$lang[1]} ({$lang[0]})</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding {$lang[1]}: " . mysqli_stmt_error($stmt) . "</p>";
    }
}

echo "<hr>";
echo "<h3 style='color: green;'>✓ Installation Complete!</h3>";
echo "<p>You can now <a href='admin/manage_languages.php'>go to Language Management</a>.</p>";
echo "<p><strong>Remember to delete this file after installation for security.</strong></p>";

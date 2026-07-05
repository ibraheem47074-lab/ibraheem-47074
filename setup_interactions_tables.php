<?php
require_once 'config/database.php';

echo "Setting up news interactions tables...\n";

// Create news_likes table
$sql = "CREATE TABLE IF NOT EXISTS `news_likes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_user` (`news_id`, `user_id`),
    KEY `idx_news_ip` (`news_id`, `ip_address`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'news_likes' created successfully\n";
} else {
    echo "Error creating 'news_likes' table: " . mysqli_error($conn) . "\n";
}

// Create news_shares table
$sql = "CREATE TABLE IF NOT EXISTS `news_shares` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `news_id` int(11) NOT NULL,
    `platform` varchar(50) NOT NULL DEFAULT 'unknown',
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_news_platform` (`news_id`, `platform`),
    KEY `idx_news_user` (`news_id`, `user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "Table 'news_shares' created successfully\n";
} else {
    echo "Error creating 'news_shares' table: " . mysqli_error($conn) . "\n";
}

// Add missing columns to news table
$columns_to_add = [
    'likes_count' => "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `likes_count` int(11) NOT NULL DEFAULT 0",
    'share_count' => "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `share_count` int(11) NOT NULL DEFAULT 0", 
    'comment_count' => "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `comment_count` int(11) NOT NULL DEFAULT 0"
];

foreach ($columns_to_add as $column => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Column '$column' added successfully\n";
    } else {
        echo "Error adding column '$column': " . mysqli_error($conn) . "\n";
    }
}

// Update existing records
$update_queries = [
    "UPDATE `news` SET `likes_count` = 0 WHERE `likes_count` IS NULL",
    "UPDATE `news` SET `share_count` = 0 WHERE `share_count` IS NULL", 
    "UPDATE `news` SET `comment_count` = 0 WHERE `comment_count` IS NULL"
];

foreach ($update_queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Updated existing records successfully\n";
    } else {
        echo "Error updating records: " . mysqli_error($conn) . "\n";
    }
}

echo "Setup complete!\n";
?>

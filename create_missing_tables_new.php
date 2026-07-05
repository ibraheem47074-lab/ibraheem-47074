<?php
require_once 'config/database.php';

// Create alert_categories table
$create_alert_categories = "
CREATE TABLE IF NOT EXISTS `alert_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `alert_type` varchar(50) NOT NULL DEFAULT 'general',
    `alert_message` text DEFAULT NULL,
    `alert_frequency` varchar(20) DEFAULT 'daily',
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    KEY `alert_type` (`alert_type`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Create news_sources table
$create_news_sources = "
CREATE TABLE IF NOT EXISTS `news_sources` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) DEFAULT NULL,
    `source_name` varchar(255) NOT NULL,
    `source_url` varchar(500) DEFAULT NULL,
    `source_type` enum('rss','api','manual','scraped') DEFAULT 'manual',
    `is_active` tinyint(1) DEFAULT 1,
    `last_fetched` datetime DEFAULT NULL,
    `fetch_frequency` int(11) DEFAULT 3600,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    KEY `source_name` (`source_name`),
    KEY `is_active` (`is_active`),
    KEY `source_type` (`source_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Create category_analytics table
$create_category_analytics = "
CREATE TABLE IF NOT EXISTS `category_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `view_count` int(11) DEFAULT 0,
    `click_count` int(11) DEFAULT 0,
    `article_count` int(11) DEFAULT 0,
    `date_recorded` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `category_date` (`category_id`, `date_recorded`),
    KEY `category_id` (`category_id`),
    KEY `date_recorded` (`date_recorded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Execute table creation
$tables = [
    'alert_categories' => $create_alert_categories,
    'news_sources' => $create_news_sources,
    'category_analytics' => $create_category_analytics
];

foreach ($tables as $table_name => $sql) {
    echo "Creating table: $table_name\n";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Table '$table_name' created successfully\n";
    } else {
        echo "✗ Error creating table '$table_name': " . mysqli_error($conn) . "\n";
    }
}

// Verify tables were created
echo "\nVerifying table creation:\n";
foreach (array_keys($tables) as $table_name) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table_name'");
    $exists = mysqli_num_rows($result) > 0;
    echo "$table_name: " . ($exists ? "EXISTS ✓" : "MISSING ✗") . "\n";
}

mysqli_close($conn);
echo "\nTable creation process completed.\n";
?>

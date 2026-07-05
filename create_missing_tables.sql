-- Create alert_categories table
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

-- Create news_sources table
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

-- Create category_analytics table
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

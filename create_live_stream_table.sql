-- Create live_stream table
CREATE TABLE `live_stream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `stream_url` varchar(500) NOT NULL,
  `stream_key` varchar(255) DEFAULT NULL,
  `embed_code` text DEFAULT NULL,
  `status` enum('offline','online','scheduled') DEFAULT 'offline',
  `schedule_time` timestamp NULL DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `multi_camera_config` longtext DEFAULT NULL COMMENT 'Configuration for multiple cameras',
  `overlay_config` longtext DEFAULT NULL COMMENT 'Overlay configuration and settings',
  `active_camera` int(11) DEFAULT 1 COMMENT 'Currently active camera (1-based index)',
  `camera_count` int(11) DEFAULT 1 COMMENT 'Total number of cameras configured',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create news_sources table for RSS import functionality
CREATE TABLE `news_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of news source',
  `url` varchar(500) NOT NULL COMMENT 'Main URL of news source',
  `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL',
  `type` enum('rss','scrape') NOT NULL DEFAULT 'rss' COMMENT 'Import type',
  `category_id` int(11) DEFAULT NULL COMMENT 'Default category ID',
  `scrape_frequency` int(11) NOT NULL DEFAULT '60' COMMENT 'Scraping frequency in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Source status',
  `last_scraped` timestamp NULL DEFAULT NULL COMMENT 'Last successful scrape',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_last_scraped` (`last_scraped`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

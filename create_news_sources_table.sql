-- Create news_sources table for RSS import functionality
CREATE TABLE IF NOT EXISTS `news_sources` (
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

-- Insert default RSS sources
INSERT IGNORE INTO `news_sources` (`name`, `url`, `rss_url`, `type`, `category_id`, `status`) VALUES
('BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml', 'rss', 1, 'active'),
('CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss', 'rss', 1, 'active'),
('Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews', 'rss', 1, 'active'),
('Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml', 'rss', 1, 'active'),
('Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews', 'rss', 1, 'active'),
('Fox News', 'https://www.foxnews.com', 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest', 'rss', 1, 'active'),
('The Guardian', 'https://www.theguardian.com', 'https://www.theguardian.com/world/rss', 'rss', 1, 'active'),
('NBC News', 'https://www.nbcnews.com', 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml', 'rss', 1, 'active'),
('CBS News', 'https://www.cbsnews.com', 'https://www.cbsnews.com/rss/live/rss.rss', 'rss', 1, 'active'),
('NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml', 'rss', 1, 'active');

-- Create user_analytics table for tracking user activity
CREATE TABLE IF NOT EXISTS `user_analytics` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `action` varchar(50) NOT NULL DEFAULT 'page_view',
    `page_url` varchar(500) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `referrer` varchar(500) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_user_action_date` (`user_id`, `action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing
INSERT IGNORE INTO `user_analytics` (`user_id`, `action`, `page_url`, `ip_address`, `created_at`) VALUES
(NULL, 'page_view', '/index.php', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(NULL, 'page_view', '/search.php', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(NULL, 'search', '/search.php?q=news', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'page_view', '/index.php', '192.168.1.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'login', '/login.php', '192.168.1.1', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 'page_view', '/categories.php', '10.0.0.1', NOW()),
(2, 'page_view', '/article.php?id=123', '10.0.0.1', NOW()),
(NULL, 'page_view', '/rss.php', '203.0.113.1', NOW());

-- Create page_views table for tracking page visits
CREATE TABLE IF NOT EXISTS `page_views` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_url` varchar(500) NOT NULL,
    `page_type` varchar(50) DEFAULT 'page',
    `page_title` varchar(255) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `referrer` varchar(500) DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_page_type` (`page_type`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_page_type_created` (`page_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample page_views data for testing
INSERT IGNORE INTO `page_views` (`page_url`, `page_type`, `page_title`, `ip_address`, `created_at`) VALUES
('/index.php', 'home', 'PK Live News - Home', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('/search.php?q=pakistan', 'search', 'Search Results', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('/search.php?q=politics', 'search', 'Search Results', '192.168.1.1', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('/categories.php?id=1', 'category', 'Breaking News', '10.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('/article.php?id=123', 'article', 'Latest News Article', '203.0.113.1', NOW()),
('/search.php?q=sports', 'search', 'Search Results', '172.16.0.1', NOW()),
('/index.php', 'home', 'PK Live News - Home', '192.168.1.100', NOW()),
('/rss.php', 'rss', 'RSS Feed', '10.0.0.50', NOW());

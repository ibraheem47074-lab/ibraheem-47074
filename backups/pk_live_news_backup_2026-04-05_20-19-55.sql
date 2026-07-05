-- PK Live News Database Backup
-- Generated on: 2026-04-05 20:19:55
-- MySQL Version: 10.4.32-MariaDB

-- Table structure for `ad_clicks`
CREATE TABLE `ad_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `click_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `ad_clicks_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `ad_impressions`
CREATE TABLE `ad_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `impression_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `ad_impressions_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `admins`
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` VALUES ('1', 'admin', '$2y$10$hh8yiBdGHKlr2nxezOg.luwhURGBxU4e1trSDVt8lmB8wBDFwxK5G', 'admin@pklivenews.com', 'System Administrator', 'super_admin', '2026-03-30 00:21:28', NULL, '1';


-- Table structure for `adsense_analytics`
CREATE TABLE `adsense_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `page_views` int(11) DEFAULT 0,
  `ad_impressions` int(11) DEFAULT 0,
  `ad_clicks` int(11) DEFAULT 0,
  `earnings` decimal(10,2) DEFAULT 0.00,
  `ctr` decimal(5,2) DEFAULT 0.00,
  `cpc` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `advertisements`
CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `ad_code` text NOT NULL,
  `position` enum('header','sidebar','footer','popup') NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `max_impressions` int(11) DEFAULT NULL,
  `current_impressions` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `advertising_rates`
CREATE TABLE `advertising_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_type` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'PKR',
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `advertising_rates` VALUES ('25', 'Sidebar Banner', 'Right Sidebar', '300x250px', 'Monthly', '15000.00', 'PKR', 'Medium rectangle banner in right sidebar, visible on all pages', 'Unlimited impressions,Click tracking,Monthly performance report,Free banner design', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('26', 'Header Banner', 'Top Header', '728x90px', 'Monthly', '25000.00', 'PKR', 'Leaderboard banner at top of website, maximum visibility', 'Premium placement,Unlimited impressions,Advanced analytics,Priority support', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('27', 'Footer Banner', 'Bottom Footer', '970x90px', 'Monthly', '20000.00', 'PKR', 'Wide banner at bottom of all pages', 'Full width display,High visibility,Monthly analytics,Free design service', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('28', 'In-Article Ad', 'Within Content', 'Responsive', 'Monthly', '30000.00', 'PKR', 'Responsive ad within article content, high engagement', 'Native integration,High CTR,Content targeting,A/B testing', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('29', 'Sponsored Article', 'News Feed', 'Article Format', 'Per Article', '35000.00', 'PKR', 'Branded article promoting your business/product', 'Professional writing,SEO optimization,Social media promotion,Permanent placement', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('30', 'Business Spotlight', 'Homepage Featured', 'Featured Section', 'Monthly', '40000.00', 'PKR', 'Featured business section on homepage', 'Homepage placement,Logo display,Company profile,Direct link to website', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('31', 'Live Stream Sponsor', 'Video Content', 'Video Overlay', 'Per Stream', '50000.00', 'PKR', 'Sponsorship of live streaming sessions', 'Brand mentions,Logo placement,Product integration,Audience engagement', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';
INSERT INTO `advertising_rates` VALUES ('32', 'Newsletter Ad', 'Email Newsletter', 'Email Format', 'Per Campaign', '12000.00', 'PKR', 'Advertisement in weekly newsletter to subscribers', 'Direct email,High open rates,Click tracking,Audience targeting', 'active', '2026-03-20 08:41:31', '2026-03-20 08:41:31';


-- Table structure for `affiliate_categories`
CREATE TABLE `affiliate_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affiliate_categories` VALUES ('1', 'Electronics', 'electronics', 'Mobile phones, laptops, gadgets', '0', '3', '1', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('2', 'Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', NULL, '2', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('3', 'Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', NULL, '3', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('4', 'Gaming', 'gaming', 'Gaming consoles and accessories', 'fa-gamepad', NULL, '4', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('5', 'Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', NULL, '5', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('6', 'Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', NULL, '6', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('7', 'Smart Home', 'smart-home', 'Smart home devices and IoT', 'fa-home', NULL, '7', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('8', 'Fashion', 'fashion', 'Clothing and accessories', 'fa-tshirt', NULL, '8', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('9', 'Sports', 'sports', 'Sports equipment and gear', 'fa-football-ball', NULL, '9', 'active', '2026-03-26 20:58:46';
INSERT INTO `affiliate_categories` VALUES ('10', 'Books', 'books', 'Books and educational materials', 'fa-book', NULL, '10', 'active', '2026-03-26 20:58:46';


-- Table structure for `affiliate_clicks`
CREATE TABLE `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `click_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted` tinyint(1) DEFAULT 0,
  `conversion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `click_date` (`click_date`),
  KEY `converted` (`converted`),
  CONSTRAINT `fk_affiliate_clicks_product` FOREIGN KEY (`product_id`) REFERENCES `affiliate_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `affiliate_products`
CREATE TABLE `affiliate_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `image_url` varchar(500) DEFAULT NULL,
  `affiliate_url` varchar(500) NOT NULL,
  `affiliate_network` enum('amazon','aliexpress','other') DEFAULT 'amazon',
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `availability` enum('in_stock','out_of_stock','limited') DEFAULT 'in_stock',
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `click_count` int(11) DEFAULT 0,
  `conversion_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`),
  KEY `affiliate_network` (`affiliate_network`),
  CONSTRAINT `fk_affiliate_products_category` FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affiliate_products` VALUES ('7', 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'The latest iPhone with A17 Pro chip, titanium design, and advanced camera system. Features include 6.7-inch Super Retina XDR display, 5G connectivity, and all-day battery life.', '0', '1199.00', '1299.00', 'USD', 'https://via.placeholder.com/400x400/000000/FFFFFF?text=iPhone+15+Pro+Max', 'https://amazon.com/dp/B0CHX2TJQX', 'amazon', '2', '4.00', '15420', 'in_stock', 'Apple', 'iPhone 15 Pro Max', '0', '1', 'active', '0', '0', '2026-04-02 14:40:59', '2026-04-02 14:40:59';
INSERT INTO `affiliate_products` VALUES ('8', 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Samsung\'s flagship smartphone with S Pen, 200MP camera, and AI features. Includes 6.8-inch Dynamic AMOLED 2X display, Snapdragon 8 Gen 3 processor, and 5000mAh battery.', '0', '1099.00', '1299.00', 'USD', 'https://via.placeholder.com/400x400/1f77b4/FFFFFF?text=Galaxy+S24+Ultra', 'https://amazon.com/dp/B0BPXVQ44W', 'amazon', '2', '4.00', '12350', 'in_stock', 'Samsung', 'Galaxy S24 Ultra', '0', '1', 'active', '0', '0', '2026-04-02 14:40:59', '2026-04-02 14:40:59';
INSERT INTO `affiliate_products` VALUES ('9', 'MacBook Pro 14-inch', 'macbook-pro-14-inch', 'Apple MacBook Pro with M3 Pro chip, 14.2-inch Liquid Retina XDR display, and 18-hour battery life. Perfect for professionals and content creators.', '0', '1999.00', '2199.00', 'USD', 'https://via.placeholder.com/400x400/666666/FFFFFF?text=MacBook+Pro+14', 'https://amazon.com/dp/B0CHX1Q1JX', 'amazon', '3', '4.00', '8765', 'in_stock', 'Apple', 'MacBook Pro 14\"', '0', '1', 'active', '0', '0', '2026-04-02 14:40:59', '2026-04-02 14:40:59';
INSERT INTO `affiliate_products` VALUES ('10', 'Sony WH-1000XM5 Headphones', 'sony-wh-1000xm5-headphones', 'Industry-leading noise canceling headphones with exceptional sound quality, 30-hour battery life, and multipoint connectivity.', '0', '349.00', '399.00', 'USD', 'https://via.placeholder.com/400x400/000000/FFFFFF?text=Sony+WH-1000XM5', 'https://amazon.com/dp/B097TYWD5K', 'amazon', '6', '4.00', '23450', 'in_stock', 'Sony', 'WH-1000XM5', '0', '0', 'active', '0', '0', '2026-04-02 14:40:59', '2026-04-02 14:40:59';


-- Table structure for `alert_categories`
CREATE TABLE `alert_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_alert_category` (`alert_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `alert_categories_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `breaking_news_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `bookmarks`
CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) NOT NULL,
  `folder` varchar(50) DEFAULT 'default',
  `notes` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_news` (`user_id`,`news_id`),
  KEY `idx_bookmarks_user` (`user_id`),
  KEY `idx_bookmarks_news` (`news_id`),
  KEY `idx_bookmarks_folder` (`folder`),
  KEY `idx_bookmarks_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `breaking_news_alerts`
CREATE TABLE `breaking_news_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `alert_type` enum('breaking','urgent','update') DEFAULT 'breaking',
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_alerts_news` (`news_id`),
  KEY `idx_alerts_status` (`status`),
  KEY `idx_alerts_type` (`alert_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `broadcast_logs`
CREATE TABLE `broadcast_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `action` enum('start','stop') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `timestamp` (`timestamp`),
  CONSTRAINT `broadcast_logs_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_ur` varchar(255) DEFAULT NULL,
  `name_hi` varchar(255) DEFAULT NULL,
  `name_zh` varchar(255) DEFAULT NULL,
  `name_ps` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT 'fas fa-newspaper',
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ur` text DEFAULT NULL,
  `description_hi` text DEFAULT NULL,
  `description_zh` text DEFAULT NULL,
  `description_ps` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categories_status` (`status`),
  KEY `idx_status_slug` (`status`,`slug`),
  KEY `fk_category_parent` (`parent_id`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` VALUES ('1', 'Politics', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'politics', 'Political news and updates', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-03-19 19:53:56', '2026-04-03 20:29:27', NULL;
INSERT INTO `categories` VALUES ('2', 'Sports', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'sports', 'Sports news and coverage', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-04-03 14:04:31', NULL;
INSERT INTO `categories` VALUES ('3', 'Technology', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'technology', 'Technology and tech news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-26 07:29:37', NULL;
INSERT INTO `categories` VALUES ('4', 'Business', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'business', 'Business and economy news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-26 07:29:27', NULL;
INSERT INTO `categories` VALUES ('5', 'Entertainment', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'entertainment', 'Entertainment news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-19 21:26:43', NULL;
INSERT INTO `categories` VALUES ('6', 'Health', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'health', 'Health and medical news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-19 21:26:39', NULL;
INSERT INTO `categories` VALUES ('7', 'Education', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'education', 'Education news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-19 21:26:40', NULL;
INSERT INTO `categories` VALUES ('8', 'International', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'international', 'International news', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-03-19 19:53:56', '2026-03-19 21:26:47', NULL;
INSERT INTO `categories` VALUES ('10', 'Jobs', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'jobs', 'All job opportunities and career updates', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-04-03 23:18:38', '2026-04-04 00:01:05', NULL;
INSERT INTO `categories` VALUES ('11', 'Govt Jobs', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'govt-jobs', 'Government job opportunities and vacancies', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-04-03 23:18:38', '2026-04-03 23:59:33', '10';
INSERT INTO `categories` VALUES ('12', 'Private Jobs', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'private-jobs', 'Private sector job opportunities', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-04-03 23:18:38', '2026-04-03 23:59:45', '10';
INSERT INTO `categories` VALUES ('13', 'Overseas Jobs', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'overseas-jobs', 'International job opportunities', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-04-03 23:18:38', '2026-04-03 23:51:50', '10';
INSERT INTO `categories` VALUES ('14', 'Freelance Jobs', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'freelance-jobs', 'Freelance and remote work opportunities', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-04-03 23:18:38', '2026-04-03 23:59:36', '10';


-- Table structure for `category_analytics`
CREATE TABLE `category_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `views` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `engagement_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_date` (`category_id`,`date`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `channel_bans`
CREATE TABLE `channel_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `ban_time` datetime NOT NULL,
  `unban_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `user_id` (`user_id`),
  KEY `ban_time` (`ban_time`),
  CONSTRAINT `channel_bans_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `channel_schedule`
CREATE TABLE `channel_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `program_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurring_days` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `channel_schedule` VALUES ('1', '1', 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', '1', '1', '2026-03-19 19:48:06';
INSERT INTO `channel_schedule` VALUES ('2', '1', 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', '1', '1', '2026-03-19 19:48:06';
INSERT INTO `channel_schedule` VALUES ('3', '1', 'Evening News Wrap', 'Complete roundup of the day\'s events', '2026-03-19 20:00:00', '2026-03-19 21:00:00', '1', '1', '2026-03-19 19:48:06';
INSERT INTO `channel_schedule` VALUES ('4', '2', 'Live Cricket Match', 'Coverage of today\'s cricket match', '2026-03-19 15:00:00', '2026-03-19 19:00:00', '0', NULL, '2026-03-19 19:48:06';
INSERT INTO `channel_schedule` VALUES ('5', '2', 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', '1', '1', '2026-03-19 19:48:06';
INSERT INTO `channel_schedule` VALUES ('6', '1', 'Morning News Bulletin', 'Start your day with comprehensive news coverage', '2026-03-19 07:00:00', '2026-03-19 09:00:00', '1', '1', '2026-03-19 19:52:46';
INSERT INTO `channel_schedule` VALUES ('7', '1', 'Breaking News Live', 'Real-time coverage of developing stories', '2026-03-19 14:00:00', '2026-03-19 16:00:00', '1', '1', '2026-03-19 19:52:46';
INSERT INTO `channel_schedule` VALUES ('8', '1', 'Evening News Wrap', 'Complete roundup of the day\'s events', '2026-03-19 20:00:00', '2026-03-19 21:00:00', '1', '1', '2026-03-19 19:52:46';
INSERT INTO `channel_schedule` VALUES ('9', '2', 'Live Cricket Match', 'Coverage of today\'s cricket match', '2026-03-19 15:00:00', '2026-03-19 19:00:00', '0', NULL, '2026-03-19 19:52:46';
INSERT INTO `channel_schedule` VALUES ('10', '2', 'Sports Highlights', 'Daily sports recap and analysis', '2026-03-19 22:00:00', '2026-03-19 23:00:00', '1', '1', '2026-03-19 19:52:46';


-- Table structure for `channels`
CREATE TABLE `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` enum('news','sports','entertainment','business','technology','international') NOT NULL DEFAULT 'news',
  `stream_url` text DEFAULT NULL,
  `stream_type` enum('youtube','hls','rtmp','iframe') NOT NULL DEFAULT 'youtube',
  `thumbnail` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('live','offline','scheduled') NOT NULL DEFAULT 'offline',
  `viewer_count` int(11) DEFAULT 0,
  `language` varchar(10) DEFAULT 'en',
  `country` varchar(50) DEFAULT 'PK',
  `sort_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `schedule_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stream_key` varchar(255) DEFAULT NULL,
  `stream_title` varchar(255) DEFAULT NULL,
  `stream_description` text DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `max_viewers` int(11) DEFAULT 1000,
  `quality` enum('480p','720p','1080p') DEFAULT '720p',
  `allow_chat` tinyint(1) DEFAULT 1,
  `record_stream` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `status` (`status`),
  KEY `is_featured` (`is_featured`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `channels` VALUES ('1', 'Geo News Live', 'news', 'https://www.youtube.com/embed/JpGhoXzh7DY', 'youtube', 'https://i.imgur.com/8QJGYZb.jpg', 'Pakistan\'s leading news channel providing 24/7 coverage of breaking news, politics, and current affairs.', 'live', '24185', '0', '0', '60', '1', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('2', 'ARY News Live', 'news', 'https://www.youtube.com/embed/hHqkGOE3XwY', 'youtube', 'https://i.imgur.com/9QJH8KJ.jpg', 'Breaking news and current affairs from ARY News - Pakistan\'s most trusted news source.', 'live', '13282', '0', '0', '50', '1', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('3', 'Dunya News Live', 'news', 'https://www.youtube.com/embed/wvBgyD5_3tI', 'youtube', 'https://i.imgur.com/7QJG7QJ.jpg', 'Latest news and political talk shows from Dunya News with in-depth analysis.', 'live', '9470', '0', '0', '92', '0', NULL, '2026-03-28 23:57:54', '2026-03-31 01:41:57', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('4', 'Samaa News Live', 'news', 'https://www.youtube.com/embed/21X5lGlDOfg', 'youtube', 'https://i.imgur.com/5QJG5QJ.jpg', 'Samaa News - Fastest growing news channel in Pakistan', '', '11234', '0', 'PK', '4', '0', NULL, '2026-03-28 23:57:54', '2026-03-28 23:58:09', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('5', 'Tamasha Live', 'entertainment', 'https://www.youtube.com/embed/0zh4WQ4k7Sw', 'youtube', 'https://i.imgur.com/3QJG3QJ.jpg', 'Tamasha - Pakistan\'s leading entertainment channel with dramas and shows', 'live', '18551', '0', 'PK', '5', '1', NULL, '2026-03-28 23:57:54', '2026-03-30 00:26:49', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('6', 'Hum TV Live', 'entertainment', 'https://www.youtube.com/embed/gF4tH7qN8rP', 'youtube', 'https://i.imgur.com/1QJG1QJ.jpg', 'Popular Pakistani entertainment channel with dramas, shows and reality programs.', 'live', '17051', '0', '0', '90', '0', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('7', 'ARY Digital Live', 'news', 'https://www.youtube.com/embed/mN3pK8dR4tS', 'youtube', 'https://i.imgur.com/8QJG8QJ.jpg', 'Leading entertainment channel with popular dramas, sitcoms and family shows.', 'live', '17190', '0', '0', '25', '0', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('8', 'PTV Sports Live', 'sports', 'https://www.youtube.com/embed/kL7xJ9c3M2Q', 'youtube', 'https://i.imgur.com/9QJG9QJ.jpg', 'Pakistan\'s state sports channel - Live cricket, football, hockey and sports coverage.', 'live', '14635', '0', '0', '76', '1', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('9', 'Ten Sports Live', 'sports', 'https://www.youtube.com/embed/2vP5jL7qA1y', 'youtube', 'https://i.imgur.com/6QJG6QJ.jpg', 'International sports channel with live cricket, football, tennis and more.', 'live', '14288', '0', '0', '39', '0', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('10', 'BBC World News', 'international', 'https://www.youtube.com/embed/5qG5tG9xY7w', 'youtube', 'https://i.imgur.com/7QJG7QJ.jpg', 'International news and analysis from BBC - Global perspective on world events.', 'live', '21647', '0', '0', '69', '1', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('11', 'CNN International', 'international', 'https://www.youtube.com/embed/6rH8tJ7kZ9x', 'youtube', 'https://i.imgur.com/4QJG4QJ.jpg', 'Global news coverage from CNN International - 24/7 breaking news worldwide.', 'live', '13058', '0', '0', '34', '0', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('12', 'Al Jazeera English', 'international', 'https://www.youtube.com/embed/8sL2mK4nX8y', 'youtube', 'https://i.imgur.com/2QJG2QJ.jpg', 'Middle East perspective and global news from Al Jazeera English.', 'live', '20606', '0', '0', '46', '0', NULL, '2026-03-28 23:57:54', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('13', 'Bloomberg TV', 'entertainment', 'https://www.youtube.com/embed/7tN3jL5oY9z', 'youtube', NULL, 'Business news, market analysis and financial coverage from Bloomberg.', 'live', '19010', '0', '0', '8', '1', NULL, '2026-03-30 01:29:16', '2026-03-31 01:41:41', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('14', 'CNBC Pakistan', 'business', 'https://www.youtube.com/embed/9uO4mK6pZ0x', 'youtube', NULL, 'Business news and market updates from Pakistan and international markets.', 'live', '5619', '0', '0', '68', '0', NULL, '2026-03-30 01:29:16', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('15', 'Tech Republic', 'technology', 'https://www.youtube.com/embed/2vP5jL7qA1y', 'youtube', NULL, 'Latest technology news, gadget reviews and innovation stories.', 'live', '5620', '0', '0', '11', '1', NULL, '2026-03-30 01:29:16', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('16', 'Discovery Science', 'technology', 'https://www.youtube.com/embed/3wQ6kM8rB2z', 'youtube', NULL, 'Science, technology and innovation documentaries and educational content.', 'live', '23700', '0', '0', '43', '0', NULL, '2026-03-30 01:29:16', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('17', 'Samaa TV Live', 'entertainment', 'https://www.youtube.com/embed/91LGH6X9x4U', 'youtube', NULL, 'Live streaming channel', 'live', '15080', 'en', 'US', '54', '0', NULL, '2026-03-30 01:37:18', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('18', '92 News Live', 'news', 'https://www.youtube.com/embed/8Qn5dLg9LsM', 'youtube', NULL, 'Live streaming channel', 'live', '15220', 'en', 'US', '2', '0', NULL, '2026-03-30 01:37:18', '2026-03-30 01:37:18', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('19', 'NASA TV Live', 'entertainment', 'https://www.youtube.com/embed/21X5lGlDOfg', 'youtube', NULL, 'Live streaming channel', 'live', '9802', 'en', 'US', '62', '0', NULL, '2026-03-30 01:37:18', '2026-03-31 01:41:49', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('20', 'Fox News Live', 'news', 'https://www.youtube.com/embed/4wQ6kM8rB2z', 'youtube', NULL, 'Live streaming channel', 'live', '23600', 'en', 'US', '3', '0', NULL, '2026-03-30 01:37:19', '2026-03-30 01:37:19', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('21', 'ESPN Live', 'news', 'https://www.youtube.com/embed/5wQ6kM8rB2z', 'youtube', NULL, 'Live streaming channel', 'live', '7970', 'en', 'US', '15', '0', NULL, '2026-03-30 01:37:19', '2026-03-30 01:37:19', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';
INSERT INTO `channels` VALUES ('22', 'MTV Live', 'entertainment', 'https://www.youtube.com/embed/6wQ6kM8rB2z', 'youtube', NULL, 'Live streaming channel', 'live', '16816', 'en', 'US', '89', '0', NULL, '2026-03-30 01:37:19', '2026-03-30 01:37:19', NULL, NULL, NULL, NULL, NULL, '1000', '720p', '1', '0';


-- Table structure for `comments`
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('approved','pending','rejected') DEFAULT 'pending',
  `parent_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_news` (`news_id`),
  KEY `idx_comments_user` (`user_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_news_status` (`news_id`,`status`),
  KEY `idx_status_created` (`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` VALUES ('5', '570', '5', 'Salman', 'salman47074@gmail.com', 'hi gause', 'approved', NULL, NULL, '2026-04-03 13:58:26', '2026-04-04 19:43:38';
INSERT INTO `comments` VALUES ('8', '609', '1', 'Admin', 'admin@pklivenews.com', 'hi', 'approved', NULL, NULL, '2026-04-04 21:12:51', '2026-04-04 21:13:35';
INSERT INTO `comments` VALUES ('9', '609', '1', 'Admin', 'admin@pklivenews.com', 'huu', 'approved', NULL, NULL, '2026-04-04 22:50:45', '2026-04-04 22:51:05';


-- Table structure for `content_patterns`
CREATE TABLE `content_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(100) NOT NULL,
  `pattern_type` enum('clickbait','sensationalism','propaganda','misinformation','satire','opinion') NOT NULL,
  `pattern_regex` text DEFAULT NULL,
  `pattern_keywords` text DEFAULT NULL,
  `confidence_weight` decimal(3,2) DEFAULT 1.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `content_patterns` VALUES ('1', 'Clickbait Headlines', 'clickbait', '/\\b(you won\'t believe|shocking|unbelievable|incredible|amazing|stunning|mind-blowing|jaw-dropping)\\b/i', '0', '0.80', '1', '2026-03-20 00:20:40', '2026-03-20 00:20:40';
INSERT INTO `content_patterns` VALUES ('2', 'Sensational Language', 'sensationalism', '/\\b(disaster|catastrophe|devastating|horrifying|terrifying|nightmare|emergency|crisis)\\b/i', '0', '0.70', '1', '2026-03-20 00:20:40', '2026-03-20 00:20:40';
INSERT INTO `content_patterns` VALUES ('3', 'Conspiracy Indicators', 'misinformation', '/\\b(conspiracy|cover-up|hidden truth|they don\'t want you to know|secret|exposed)\\b/i', '0', '0.90', '1', '2026-03-20 00:20:40', '2026-03-20 00:20:40';
INSERT INTO `content_patterns` VALUES ('4', 'Opinion Markers', 'opinion', '/\\b(i think|in my opinion|believe|feel|seems like|perhaps|maybe)\\b/i', '0', '0.60', '1', '2026-03-20 00:20:40', '2026-03-20 00:20:40';
INSERT INTO `content_patterns` VALUES ('5', 'Unverified Claims', 'misinformation', '/\\b(sources say|according to sources|reports claim|allegedly|rumor has it)\\b/i', '0', '0.70', '1', '2026-03-20 00:20:40', '2026-03-20 00:20:40';


-- Table structure for `job_applications`
CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `resume_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','reviewed','interview','rejected','hired') DEFAULT 'pending',
  `experience_level` varchar(100) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `expected_salary` varchar(255) DEFAULT NULL,
  `location_preference` varchar(255) DEFAULT NULL,
  `linkedin_profile` varchar(500) DEFAULT NULL,
  `portfolio_website` varchar(500) DEFAULT NULL,
  `skills_qualifications` text DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_job_id` (`job_id`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `languages`
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `native_name` varchar(100) NOT NULL,
  `flag_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `languages` VALUES ('1', 'en', 'English', 'English', '🇺🇸', '1', '1', '2026-03-19 19:58:02';
INSERT INTO `languages` VALUES ('2', 'ur', 'Urdu', 'اردو', '🇵🇰', '1', '2', '2026-03-19 19:58:02';
INSERT INTO `languages` VALUES ('3', 'hi', 'Hindi', 'हिन्दी', '🇮🇳', '1', '3', '2026-03-19 19:58:02';
INSERT INTO `languages` VALUES ('4', 'zh', 'Chinese', '中文', '🇨🇳', '1', '4', '2026-03-19 19:58:02';
INSERT INTO `languages` VALUES ('5', 'ps', 'Pashto', 'پښتو', '🇦🇫', '1', '5', '2026-03-19 19:58:02';


-- Table structure for `live_chat`
CREATE TABLE `live_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `live_chat` VALUES ('1', '5', 'Guest', 'hi', '2026-03-29 09:15:01', '0';
INSERT INTO `live_chat` VALUES ('2', '5', 'Guest', 'hhhh', '2026-03-29 09:15:08', '0';
INSERT INTO `live_chat` VALUES ('3', '5', 'Guest', 'bbbbbbbbbbbbb', '2026-03-29 09:17:12', '0';
INSERT INTO `live_chat` VALUES ('4', '5', 'Guest', 'nnnnnnnnnnn', '2026-03-29 09:17:17', '0';


-- Table structure for `live_deployments`
CREATE TABLE `live_deployments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment_name` varchar(255) NOT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `deployment_type` enum('single','multi_camera','scheduled','emergency') DEFAULT 'single',
  `status` enum('preparing','testing','ready','live','ended','failed') DEFAULT 'preparing',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `primary_stream_url` varchar(500) DEFAULT NULL,
  `backup_stream_url` varchar(500) DEFAULT NULL,
  `stream_key` varchar(255) DEFAULT NULL,
  `rtmp_url` varchar(500) DEFAULT NULL,
  `hls_url` varchar(500) DEFAULT NULL,
  `embed_code` text DEFAULT NULL,
  `auto_start` tinyint(1) DEFAULT 0,
  `auto_failover` tinyint(1) DEFAULT 1,
  `recording_enabled` tinyint(1) DEFAULT 1,
  `chat_enabled` tinyint(1) DEFAULT 1,
  `analytics_enabled` tinyint(1) DEFAULT 1,
  `stream_quality` enum('auto','240p','360p','480p','720p','1080p','4K') DEFAULT 'auto',
  `bitrate` int(11) DEFAULT 2500,
  `fps` int(11) DEFAULT 30,
  `scheduled_start` datetime DEFAULT NULL,
  `scheduled_end` datetime DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_end` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `health_check_url` varchar(500) DEFAULT NULL,
  `monitoring_enabled` tinyint(1) DEFAULT 1,
  `alert_threshold` int(11) DEFAULT 5,
  `peak_viewers` int(11) DEFAULT 0,
  `total_views` int(11) DEFAULT 0,
  `avg_watch_time` int(11) DEFAULT 0,
  `bandwidth_used` bigint(20) DEFAULT 0,
  `server_region` varchar(100) DEFAULT NULL,
  `cdn_provider` varchar(100) DEFAULT NULL,
  `stream_protocol` enum('rtmp','hls','dash','webrtc') DEFAULT 'rtmp',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deployments_status` (`status`),
  KEY `idx_deployments_scheduled` (`scheduled_start`),
  KEY `idx_deployments_priority` (`priority`),
  KEY `idx_deployments_type` (`deployment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `live_stream`
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_type` varchar(20) DEFAULT 'manual',
  `video_url` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','featured','archived') DEFAULT 'draft',
  `news_type` varchar(50) DEFAULT 'manual',
  `is_breaking` tinyint(1) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `breaking_news` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `share_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `source_url` varchar(500) DEFAULT NULL COMMENT 'Original source URL for scraped articles',
  `source_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sentiment_score` decimal(3,2) DEFAULT 0.00,
  `sentiment_label` varchar(20) DEFAULT 'neutral',
  `urgency` varchar(20) DEFAULT 'medium',
  `credibility_status` enum('PENDING','ANALYZED','REVIEWED') DEFAULT 'PENDING',
  `auto_flagged` tinyint(1) DEFAULT 0,
  `time_status` enum('new','recent','older') DEFAULT 'older',
  `company_name` varchar(255) DEFAULT NULL,
  `job_location` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `last_date_to_apply` date DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Freelance','Internship') DEFAULT NULL,
  `apply_url` varchar(500) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `is_job_posting` tinyint(1) DEFAULT 0,
  `is_channel_job` tinyint(1) DEFAULT 0 COMMENT 'Flag for channel-specific job postings',
  `channel_name` varchar(50) DEFAULT NULL COMMENT 'Channel name for specialized jobs',
  `apply_criteria` text DEFAULT NULL COMMENT 'Application criteria for channel jobs',
  `show_as_soon` tinyint(1) DEFAULT 0 COMMENT 'Show job as coming soon instead of apply now',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_news_category` (`category_id`),
  KEY `idx_news_author` (`author_id`),
  KEY `idx_news_status` (`status`),
  KEY `idx_news_featured` (`featured`),
  KEY `idx_news_breaking` (`breaking_news`),
  KEY `idx_news_published` (`published_at`),
  KEY `idx_status_published` (`status`,`published_at`),
  KEY `idx_category_status` (`category_id`,`status`),
  KEY `idx_views_date` (`views`,`created_at`),
  KEY `idx_featured` (`status`,`published_at`),
  KEY `idx_slug` (`slug`),
  KEY `idx_author_status` (`author_id`,`status`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_author_id` (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=611 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news` VALUES ('539', 'Create and publish new news content with images and videos', 'reate-and-publish-new-news-content-with-images-and-videos', 'Create and publish new news content with images and videos', 'Create and publish new news content with images and videos...', 'uploads/news/img_69cf69d6ddf78_1775200726.png', 'manual', '', 'uploads/news/videos/vid_69cf69d6de295_1775200726.mp4', '1', '1', NULL, 'published', 'manual', '0', '0', '0', '1', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 12:18:46', NULL, NULL, '2026-04-03 12:18:46', '2026-04-03 14:04:53', '0.00', 'neutral', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('540', 'How Israel is destroying healthcare infrastructure in southern Lebanon', 'how-israel-is-destroying-healthcare-infrastructure-in-southern-lebanon', '<p>Israel\'s attacks on hospitals, medical centres and healthcare workers are fuelling displacement and a health crisis.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/features/2026/4/3/how-israel-is-destroying-healthcare-infrastructure-in-southern-lebanon?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/features/2026/4/3/how-israel-is-destroying-healthcare-infrastructure-in-southern-lebanon?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Israel\'s attacks on hospitals, medical centres and healthcare workers are fuelling displacement and a health crisis.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.aljazeera.com/features/2026/4/3/how-israel-is-destroying-healthcare-infrastructure-in-southern-lebanon?traffic_source=rss', NULL, '2026-04-03 12:53:17', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('541', 'Iranian women footballers reveal ‘enormous pressure’ of the Asian Cup saga', 'iranian-women-footballers-reveal-enormous-pressure-of-the-asian-cup-saga', '<p>Two Iranian players who sought asylum in Australia before changing their minds tell their story to Al Jazeera.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/sports/2026/4/3/iranian-women-footballers-reveal-enormous-pressure-of-the-asian-cup-saga?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/sports/2026/4/3/iranian-women-footballers-reveal-enormous-pressure-of-the-asian-cup-saga?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Two Iranian players who sought asylum in Australia before changing their minds tell their story to Al Jazeera.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.aljazeera.com/sports/2026/4/3/iranian-women-footballers-reveal-enormous-pressure-of-the-asian-cup-saga?traffic_source=rss', NULL, '2026-04-03 12:53:17', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('542', 'Hegseth fires US Army chief of staff in reported string of dismissals', 'hegseth-fires-us-army-chief-of-staff-in-reported-string-of-dismissals', '<p>Retirement of Randy George is latest high-profile dismissal since the US defence secretary took office last January.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/3/hegseth-fires-us-army-chief-of-staff-in-reported-string-of-dismissals?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/3/hegseth-fires-us-army-chief-of-staff-in-reported-string-of-dismissals?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Retirement of Randy George is latest high-profile dismissal since the US defence secretary took office last January.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.aljazeera.com/news/2026/4/3/hegseth-fires-us-army-chief-of-staff-in-reported-string-of-dismissals?traffic_source=rss', NULL, '2026-04-03 12:53:17', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('543', 'Iran war: What is happening on day 35 of US-Israeli attacks?', 'iran-war-what-is-happening-on-day-35-of-us-israeli-attacks', '<p>The US and Israel have hit a century-old medical research centre and a bridge near Tehran.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/3/iran-war-what-is-happening-on-day-35-of-us-israeli-attacks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/3/iran-war-what-is-happening-on-day-35-of-us-israeli-attacks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'The US and Israel have hit a century-old medical research centre and a bridge near Tehran.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.aljazeera.com/news/2026/4/3/iran-war-what-is-happening-on-day-35-of-us-israeli-attacks?traffic_source=rss', NULL, '2026-04-03 12:53:17', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('544', 'Myanmar’s coup leader elected president by pro-military parliament', 'myanmar-s-coup-leader-elected-president-by-pro-military-parliament', '<p>Min Aung Hlaing wins 429 out of the 584 votes cast by MPs to become the country\'s president.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/3/myanmars-coup-leader-elected-president-by-pro-military-parliament?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/3/myanmars-coup-leader-elected-president-by-pro-military-parliament?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Min Aung Hlaing wins 429 out of the 584 votes cast by MPs to become the country\'s president.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '1', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.aljazeera.com/news/2026/4/3/myanmars-coup-leader-elected-president-by-pro-military-parliament?traffic_source=rss', NULL, '2026-04-03 12:53:17', '2026-04-05 19:46:26', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('545', 'Artemis II blasts ever closer to the far side of the Moon', 'artemis-ii-blasts-ever-closer-to-the-far-side-of-the-moon', '<p>The mission\'s last, big push on its lunar journey takes humans out of the Earth\'s orbit for the first time since 1972.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c937g7nd5x4o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c937g7nd5x4o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The mission\'s last, big push on its lunar journey takes humans out of the Earth\'s orbit for the first time since 1972.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.bbc.com/news/articles/c937g7nd5x4o?at_medium=RSS&at_campaign=rss', NULL, '2026-04-03 12:53:18', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('546', 'M&S boss calls for more action on crime and abuse of staff', 'm-s-boss-calls-for-more-action-on-crime-and-abuse-of-staff', '<p>Thinus Keeve\'s comments come days after an MS store was targeted during disorder in south London.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/crk16j2j1ygo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/crk16j2j1ygo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Thinus Keeve\'s comments come days after an MS store was targeted during disorder in south London.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.bbc.com/news/articles/crk16j2j1ygo?at_medium=RSS&at_campaign=rss', NULL, '2026-04-03 12:53:18', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('547', 'Marmalade to be rebranded in post-Brexit food deal', 'marmalade-to-be-rebranded-in-post-brexit-food-deal', '<p>The breakfast favourite will be legally renamed when Britain aligns with new EU labelling rules.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c0e53x475qjo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/c0e53x475qjo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The breakfast favourite will be legally renamed when Britain aligns with new EU labelling rules.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.bbc.com/news/articles/c0e53x475qjo?at_medium=RSS&at_campaign=rss', NULL, '2026-04-03 12:53:18', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('548', 'Trump removes US Attorney General Pam Bondi ', 'trump-removes-us-attorney-general-pam-bondi', '<p>Bondi\'s time as top US law enforcement officer was overshadowed by her handling of the Epstein files.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/ce843ge47z4o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/ce843ge47z4o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Bondi\'s time as top US law enforcement officer was overshadowed by her handling of the Epstein files.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '1', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.bbc.com/news/articles/ce843ge47z4o?at_medium=RSS&at_campaign=rss', NULL, '2026-04-03 12:53:18', '2026-04-04 17:05:25', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('549', 'Crisis staff found \'unforgivable scene\' at convicted undertaker\'s funeral home', 'crisis-staff-found-unforgivable-scene-at-convicted-undertaker-s-funeral-home', '<p>An international crisis team was drafted in after police raided Hull\'s Legacy funeral home.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cvg33l4g88lo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cvg33l4g88lo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'An international crisis team was drafted in after police raided Hull\'s Legacy funeral home.', '', 'manual', '', NULL, NULL, '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.bbc.com/news/articles/cvg33l4g88lo?at_medium=RSS&at_campaign=rss', NULL, '2026-04-03 12:53:18', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('550', 'Legal analyst on Attorney General William Barr\'s testimony at House hearing', 'legal-analyst-on-attorney-general-william-barr-s-testimony-at-house-hearing', '<p>Attorney General William Bar appeared before the House Judiciary Committee for the first time Tuesday as protests continue to play out across the country. CBS News legal analyst Kim Wehle joined CBSN to discuss the impact of Barr\'s statements.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'Attorney General William Bar appeared before the House Judiciary Committee for the first time Tuesday as protests continue to play out across the country. CBS News legal analyst Kim Wehle joined CBSN to discuss the impact of Barr\'s statements.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.cbsnews.com/video/attorney-general-william-barr-testimony-at-house-hearing/', NULL, '2026-04-03 12:53:20', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('551', 'As COVID-19 deaths rise, new controversy over hydroxychloroquine', 'as-covid-19-deaths-rise-new-controversy-over-hydroxychloroquine', '<p>Coronavirus cases and deaths keep rising in many states. CBS News\' Laura Podesta reports on the latest, and Dr. Dara Kass, an ER doctor and Yahoo News medical contributor, joined CBSN to discuss the latest figures, concerns about COVID-19\'s impact on the heart, and the controversy surrounding the drug hydroxychloroquine.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'Coronavirus cases and deaths keep rising in many states. CBS News\' Laura Podesta reports on the latest, and Dr. Dara Kass, an ER doctor and Yahoo News medical contributor, joined CBSN to discuss the latest figures, concerns about COVID-19\'s impact...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.cbsnews.com/video/us-death-toll-from-coronavirus-nears-150000-hydroxychloroquine-heart/', NULL, '2026-04-03 12:53:20', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('552', 'Hollywood faces financial crisis as studios delay release of summer blockbusters', 'hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters', '<p>The pandemic is wreaking havoc in Hollywood as most movie studios have paused production indefinitely. With studios repeatedly postponing the release of summer blockbusters, the entertainment industry is facing its biggest financial crisis yet. Axios media reporter Sara Fischer joins CBSN\'s Elaine Quijano with the details.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The pandemic is wreaking havoc in Hollywood as most movie studios have paused production indefinitely. With studios repeatedly postponing the release of summer blockbusters, the entertainment industry is facing its biggest financial crisis yet....', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.cbsnews.com/video/hollywood-faces-financial-crisis-as-studios-delay-release-of-summer-blockbusters/', NULL, '2026-04-03 12:53:20', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('553', 'Lawmakers bid final farewell to late Congressman John Lewis', 'lawmakers-bid-final-farewell-to-late-congressman-john-lewis', '<p>The late civil rights icon and Congressman John Lewis left the U.S. Capitol for the final time. He will be buried in Atlanta after a funeral at the historic Ebenezer Baptist Church. CBS News political contributor Antjuan Seawright joins CBSN to discuss Lewsi\' legacy and how to continue in the fight for equality.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The late civil rights icon and Congressman John Lewis left the U.S. Capitol for the final time. He will be buried in Atlanta after a funeral at the historic Ebenezer Baptist Church. CBS News political contributor Antjuan Seawright joins CBSN to...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.cbsnews.com/video/lawmakers-bid-final-farewell-to-late-congressman-john-lewis/', NULL, '2026-04-03 12:53:20', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('554', 'Tech CEOs to testify at congressional antitrust hearing', 'tech-ceos-to-testify-at-congressional-antitrust-hearing', '<p>The CEOs of Facebook, Amazon, Google, and Apple are testifying on Capitol Hill today as lawmakers conduct an antitrust probe into the companies. CNET executive editor Roger Cheng joins CBSN to discuss what\'s at stake.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/\" target=\"_blank\" rel=\"noopener\">CBS News</a></em></p>\n\n<p><strong><a href=\"https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/\" target=\"_blank\" rel=\"noopener\">Read full story on CBS News</a></strong></p>', 'The CEOs of Facebook, Amazon, Google, and Apple are testifying on Capitol Hill today as lawmakers conduct an antitrust probe into the companies. CNET executive editor Roger Cheng joins CBSN to discuss what\'s at stake.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:12:23', 'https://www.cbsnews.com/video/tech-ceos-to-testify-congress-antitrust-hearing/', NULL, '2026-04-03 12:53:20', '2026-04-03 14:12:23', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('555', 'Why Trump’s war speech failed: Declaring victory but still bombing Iran back to the ‘Stone Ages’', 'why-trump-s-war-speech-failed-declaring-victory-but-still-bombing-iran-back-to-the-stone-ages', '<p>Trump\'s address on Iran declared victory but promised weeks more bombing, drawing criticism from pundits, European allies, and voters in a recent poll.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/media/why-trumps-war-speech-failed-declaring-victory-still-bombing-iran-back-stone-ages\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/media/why-trumps-war-speech-failed-declaring-victory-still-bombing-iran-back-stone-ages\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Trump\'s address on Iran declared victory but promised weeks more bombing, drawing criticism from pundits, European allies, and voters in a recent poll.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.foxnews.com/media/why-trumps-war-speech-failed-declaring-victory-still-bombing-iran-back-stone-ages', NULL, '2026-04-03 12:53:22', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('556', 'Colorado House advances conversion therapy lawsuit bill; GOP lawmaker calls it ‘slap in the face’ to SCOTUS', 'colorado-house-advances-conversion-therapy-lawsuit-bill-gop-lawmaker-calls-it-slap-in-the-face-to-scotus', '<p>Colorado Democrats advance a bill creating civil lawsuit pathways against conversion therapy providers just days after the Supreme Court blocked the ban.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/politics/colorado-house-advances-conversion-therapy-lawsuit-bill-gop-lawmaker-calls-slap-face-scotus\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/politics/colorado-house-advances-conversion-therapy-lawsuit-bill-gop-lawmaker-calls-slap-face-scotus\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Colorado Democrats advance a bill creating civil lawsuit pathways against conversion therapy providers just days after the Supreme Court blocked the ban.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.foxnews.com/politics/colorado-house-advances-conversion-therapy-lawsuit-bill-gop-lawmaker-calls-slap-face-scotus', NULL, '2026-04-03 12:53:22', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('557', 'Artemis II crew describes life aboard Orion spacecraft on historic journey to the moon and back', 'artemis-ii-crew-describes-life-aboard-orion-spacecraft-on-historic-journey-to-the-moon-and-back', '<p>The Artemis II crew launched from Kennedy Space Center aboard Orion, embarking on NASA\'s first crewed moon mission since the Apollo era of the 1970s.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/us/artemis-ii-crew-describes-life-aboard-orion-spacecraft-historic-journey-moon-back\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/us/artemis-ii-crew-describes-life-aboard-orion-spacecraft-historic-journey-moon-back\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'The Artemis II crew launched from Kennedy Space Center aboard Orion, embarking on NASA\'s first crewed moon mission since the Apollo era of the 1970s.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.foxnews.com/us/artemis-ii-crew-describes-life-aboard-orion-spacecraft-historic-journey-moon-back', NULL, '2026-04-03 12:53:22', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('558', 'Karoline Leavitt reveals \'anti-climatic\' way Trump told her she’d be press secretary: ‘Oh, by the way’', 'karoline-leavitt-reveals-anti-climatic-way-trump-told-her-she-d-be-press-secretary-oh-by-the-way', '<p>Karoline Leavitt reveals President Trump offered her the White House press secretary role in a casual phone call with no formal process after the 2024 election.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/politics/karoline-leavitt-reveals-anti-climatic-way-trump-told-her-shed-press-secretary-oh-way\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/politics/karoline-leavitt-reveals-anti-climatic-way-trump-told-her-shed-press-secretary-oh-way\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Karoline Leavitt reveals President Trump offered her the White House press secretary role in a casual phone call with no formal process after the 2024 election.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.foxnews.com/politics/karoline-leavitt-reveals-anti-climatic-way-trump-told-her-shed-press-secretary-oh-way', NULL, '2026-04-03 12:53:22', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('559', 'Trump slaps up to 100% tariff on some brand-name drug imports in major America First push', 'trump-slaps-up-to-100-tariff-on-some-brand-name-drug-imports-in-major-america-first-push', '<p>President Trump unveiled a tiered tariff plan hitting some imported drugs with rates up to 100, aiming to boost U.S. manufacturing and reduce reliance on foreign supply chains.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/politics/trump-slaps-up-100-tariff-some-brand-name-drug-imports-major-america-first-push\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/politics/trump-slaps-up-100-tariff-some-brand-name-drug-imports-major-america-first-push\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'President Trump unveiled a tiered tariff plan hitting some imported drugs with rates up to 100, aiming to boost U.S. manufacturing and reduce reliance on foreign supply chains.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.foxnews.com/politics/trump-slaps-up-100-tariff-some-brand-name-drug-imports-major-america-first-push', NULL, '2026-04-03 12:53:22', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('560', 'Fuel rates explode as govt withdraws blanket subsidy - Dawn', 'fuel-rates-explode-as-govt-withdraws-blanket-subsidy-dawn', '<p>Fuel rates explode as govt withdraws blanket subsidyDawnGovt increases petrol tax to Rs161 per litre, sets new price at Rs458 per litreThe Express TribuneLeaders condemn POL price hike as height of injustice\' to citizensGeo NewsPakistan to seek IMF flexibility amid regional tensions and oil price...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTE1oODZvNnc2Mzl1UHpicVZ3ZXhmU3ZiSDdkZWNVOVF1dkZSQlRqTnBRcEo1RENCSm5NeFJ3eW95LWk3elVRVmhkLdIBTkFVX3lxTE5YOWlnV1BaNkxJQXpVaVdyZ25LSm5vWFlPdzlkMXk5dDk0Y20xcm05NjNWOWNqOFd4RWt4Skl6TWVjelN2ejZYSFYzRnNRUQ?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTE1oODZvNnc2Mzl1UHpicVZ3ZXhmU3ZiSDdkZWNVOVF1dkZSQlRqTnBRcEo1RENCSm5NeFJ3eW95LWk3elVRVmhkLdIBTkFVX3lxTE5YOWlnV1BaNkxJQXpVaVdyZ25LSm5vWFlPdzlkMXk5dDk0Y20xcm05NjNWOWNqOFd4RWt4Skl6TWVjelN2ejZYSFYzRnNRUQ?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Fuel rates explode as govt withdraws blanket subsidyDawnGovt increases petrol tax to Rs161 per litre, sets new price at Rs458 per litreThe Express TribuneLeaders condemn POL price hike as height of injustice\' to citizensGeo NewsPakistan to seek IMF...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://news.google.com/rss/articles/CBMiSEFVX3lxTE1oODZvNnc2Mzl1UHpicVZ3ZXhmU3ZiSDdkZWNVOVF1dkZSQlRqTnBRcEo1RENCSm5NeFJ3eW95LWk3elVRVmhkLdIBTkFVX3lxTE5YOWlnV1BaNkxJQXpVaVdyZ25LSm5vWFlPdzlkMXk5dDk0Y20xcm05NjNWOWNqOFd4RWt4Skl6TWVjelN2ejZYSFYzRnNRUQ?oc=5', NULL, '2026-04-03 12:53:24', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('561', 'Trump threatens to strike Iran\'s bridges and electric power plants - Reuters', 'trump-threatens-to-strike-iran-s-bridges-and-electric-power-plants-reuters', '<p>Trump threatens to strike Iran\'s bridges and electric power plantsReutersTrump vows to hit more Iranian infrastructure as nations seek to open HormuzReutersTrump warns of strikes on Iran power plants, bridges in new postInvesting.comUS-Israel war on Iran: Whats happening on day 28 of attacks?Al...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMisgFBVV95cUxOUS1DNHpZcG9LNUdON3hHamVsbmU0bVZEMnpFQUc2QjN2SnZ6YVRuZ1RScWJDMUxyNVB5WUp3aUtYbTVNVHM1d0xSUFBCd2JmZjBTVUpZWW1YMmtld1lYYTMwOEliQkpIWTJxdnN5YXViWjFrazY5ZjNhTmtrOU9peGtpNzRmWkI4RDI0WHMtT19kOFgtUTdIeERNN1RMRmIwQ2NxWkkydm5nQmNWZDRoRmR3?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMisgFBVV95cUxOUS1DNHpZcG9LNUdON3hHamVsbmU0bVZEMnpFQUc2QjN2SnZ6YVRuZ1RScWJDMUxyNVB5WUp3aUtYbTVNVHM1d0xSUFBCd2JmZjBTVUpZWW1YMmtld1lYYTMwOEliQkpIWTJxdnN5YXViWjFrazY5ZjNhTmtrOU9peGtpNzRmWkI4RDI0WHMtT19kOFgtUTdIeERNN1RMRmIwQ2NxWkkydm5nQmNWZDRoRmR3?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Trump threatens to strike Iran\'s bridges and electric power plantsReutersTrump vows to hit more Iranian infrastructure as nations seek to open HormuzReutersTrump warns of strikes on Iran power plants, bridges in new postInvesting.comUS-Israel war on...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://news.google.com/rss/articles/CBMisgFBVV95cUxOUS1DNHpZcG9LNUdON3hHamVsbmU0bVZEMnpFQUc2QjN2SnZ6YVRuZ1RScWJDMUxyNVB5WUp3aUtYbTVNVHM1d0xSUFBCd2JmZjBTVUpZWW1YMmtld1lYYTMwOEliQkpIWTJxdnN5YXViWjFrazY5ZjNhTmtrOU9peGtpNzRmWkI4RDI0WHMtT19kOFgtUTdIeERNN1RMRmIwQ2NxWkkydm5nQmNWZDRoRmR3?oc=5', NULL, '2026-04-03 12:53:24', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('562', 'US Army Chief Randy George asked to step down by Pete Hegseth in major shakeup - The News International', 'us-army-chief-randy-george-asked-to-step-down-by-pete-hegseth-in-major-shakeup-the-news-international', '<p>US Army Chief Randy George asked to step down by Pete Hegseth in major shakeupThe News InternationalPentagon shake-up as Hegseth ousts army chief amid Iran conflictDawnHegseth asks US Army Chief of Staff Gen Randy George to step downBBCHegseth ousts US Army chief of staff and two other generals...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMivAFBVV95cUxQWmVHQTFTWXIweGs4b1ZhYzItT3lvZ3JienlYdmVEa0J5MVEtaVgtME5qN0IxOE50RU95X3hfSzk2WlJGTnVGV2huU0hXMFoxNkMzS3NIcTZZb0I2Z0pLT1FSSHQ2U3NiMS1jX0U5Q2lLcDNKQ0lTZ3oxcmpCa1R3aF83XzlsbUJVTWdUNXBiU00wTlVGbmxXa0J4M013eDZPejRCbDlSXzJoUFJkcEpFWDlEY25ockVfYU1UNNIBuAFBVV95cUxNLWhIc0Ewb1FlZ2lvbmpSMUIwOFdVanNVZFdkQ2ExQVFiQU1ZVy12d1ZHVGJtVTFhOVpyLW1uQWFNM0llekJQRVZmb19Tc1hYV0pmZjVqa2UwdVl0cnFacC1ybm81dXZjM2hLMHU2dXRYRWRIc19aVnRhbGRFUm1oUDBkSWxLMHdfRFlHVjNQU0N3X1RHQ1NIUzNOSWJZN2RMVkljbXV1UXEtSnBrWnpDQWNSLVdKVGNh?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMivAFBVV95cUxQWmVHQTFTWXIweGs4b1ZhYzItT3lvZ3JienlYdmVEa0J5MVEtaVgtME5qN0IxOE50RU95X3hfSzk2WlJGTnVGV2huU0hXMFoxNkMzS3NIcTZZb0I2Z0pLT1FSSHQ2U3NiMS1jX0U5Q2lLcDNKQ0lTZ3oxcmpCa1R3aF83XzlsbUJVTWdUNXBiU00wTlVGbmxXa0J4M013eDZPejRCbDlSXzJoUFJkcEpFWDlEY25ockVfYU1UNNIBuAFBVV95cUxNLWhIc0Ewb1FlZ2lvbmpSMUIwOFdVanNVZFdkQ2ExQVFiQU1ZVy12d1ZHVGJtVTFhOVpyLW1uQWFNM0llekJQRVZmb19Tc1hYV0pmZjVqa2UwdVl0cnFacC1ybm81dXZjM2hLMHU2dXRYRWRIc19aVnRhbGRFUm1oUDBkSWxLMHdfRFlHVjNQU0N3X1RHQ1NIUzNOSWJZN2RMVkljbXV1UXEtSnBrWnpDQWNSLVdKVGNh?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'US Army Chief Randy George asked to step down by Pete Hegseth in major shakeupThe News InternationalPentagon shake-up as Hegseth ousts army chief amid Iran conflictDawnHegseth asks US Army Chief of Staff Gen Randy George to step downBBCHegseth ousts...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://news.google.com/rss/articles/CBMivAFBVV95cUxQWmVHQTFTWXIweGs4b1ZhYzItT3lvZ3JienlYdmVEa0J5MVEtaVgtME5qN0IxOE50RU95X3hfSzk2WlJGTnVGV2huU0hXMFoxNkMzS3NIcTZZb0I2Z0pLT1FSSHQ2U3NiMS1jX0U5Q2lLcDNKQ0lTZ3oxcmpCa1R3aF83XzlsbUJVTWdUNXBiU00wTlVGbmxXa0J4M013eDZPejRCbDlSXzJoUFJkcEpFWDlEY25ockVfYU1UNNIBuAFBVV95cUxNLWhIc0Ewb1FlZ2lvbmpSMUIwOFdVanNVZFdkQ2ExQVFiQU1ZVy12d1ZHVGJtVTFhOVpyLW1uQWFNM0llekJQRVZmb19Tc1hYV0pmZjVqa2UwdVl0cnFacC1ybm81dXZjM2hLMHU2dXRYRWRIc19aVnRhbGRFUm1oUDBkSWxLMHdfRFlHVjNQU0N3X1RHQ1N', NULL, '2026-04-03 12:53:24', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('563', 'World anxious to open Hormuz Strait while Trump and Iran trade threats - Dunya News', 'world-anxious-to-open-hormuz-strait-while-trump-and-iran-trade-threats-dunya-news', '<p>World anxious to open Hormuz Strait while Trump and Iran trade threatsDunya NewsView Full coverage on Google News</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiqwFBVV95cUxPa2g4WlNtTTh3N19kZlE1Zi1uRXJYd1F5ajRVajlnOFdaeWZ4QkM3bzZCUExXcEdjRFp6Z3BJUUZaaVFGQjhHeWJOaU80dVRURXNvcEtla0h4Tm8xcHplWTEtYmdmcHZQb2ZyZW5LakVSSGVnZWV1OEh3aEdaM2RmcXY3bUFCRVZzaTNZSHpPT2JSVjdJbVV0V2lUMnFzVHM3MXdVemlIWVl1aVXSAVZBVV95cUxNUmFUblVwMHZqaWN3MU83ZUZZd0JTMl92bXhrbWlGUmFPdlM0LXRRaTNnazdRRE0wSmdSNjZjaTQ1Sk56ZlJ3M3RoNjlsaDBiT2JvajdRdw?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiqwFBVV95cUxPa2g4WlNtTTh3N19kZlE1Zi1uRXJYd1F5ajRVajlnOFdaeWZ4QkM3bzZCUExXcEdjRFp6Z3BJUUZaaVFGQjhHeWJOaU80dVRURXNvcEtla0h4Tm8xcHplWTEtYmdmcHZQb2ZyZW5LakVSSGVnZWV1OEh3aEdaM2RmcXY3bUFCRVZzaTNZSHpPT2JSVjdJbVV0V2lUMnFzVHM3MXdVemlIWVl1aVXSAVZBVV95cUxNUmFUblVwMHZqaWN3MU83ZUZZd0JTMl92bXhrbWlGUmFPdlM0LXRRaTNnazdRRE0wSmdSNjZjaTQ1Sk56ZlJ3M3RoNjlsaDBiT2JvajdRdw?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'World anxious to open Hormuz Strait while Trump and Iran trade threatsDunya NewsView Full coverage on Google News', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://news.google.com/rss/articles/CBMiqwFBVV95cUxPa2g4WlNtTTh3N19kZlE1Zi1uRXJYd1F5ajRVajlnOFdaeWZ4QkM3bzZCUExXcEdjRFp6Z3BJUUZaaVFGQjhHeWJOaU80dVRURXNvcEtla0h4Tm8xcHplWTEtYmdmcHZQb2ZyZW5LakVSSGVnZWV1OEh3aEdaM2RmcXY3bUFCRVZzaTNZSHpPT2JSVjdJbVV0V2lUMnFzVHM3MXdVemlIWVl1aVXSAVZBVV95cUxNUmFUblVwMHZqaWN3MU83ZUZZd0JTMl92bXhrbWlGUmFPdlM0LXRRaTNnazdRRE0wSmdSNjZjaTQ1Sk56ZlJ3M3RoNjlsaDBiT2JvajdRdw?oc=5', NULL, '2026-04-03 12:53:24', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('564', 'Pakistan-China’s five-point initiative: Dar steps up peace efforts - Business Recorder', 'pakistan-china-s-five-point-initiative-dar-steps-up-peace-efforts-business-recorder', '<p>Pakistan-Chinas five-point initiative: Dar steps up peace effortsBusiness RecorderPakistan, China call for cessation of hostilities, starting peace talks as soon as possibleDawnWill China join Pakistan-led efforts to mediate US-Iran peace?Al JazeeraFive-point plan: the way to goThe Express...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiUEFVX3lxTE1BWW9Sc0NsdEt0anBLVk1oU2plSmZRN0VoeURaTVdzdTRybEEyNTZpRHFVUUE0MHFGVUxEb0RQXzJlRF9MVTNHb3A1X1EtbE5T0gFWQVVfeXFMT09WcE80eGwwRTZaZnREaTZzS3ZidkhwOXhmVkdzMXNmZTlmd19uVm9LTjYwTVM3RUxUU3BwM19ZX2puMDdFd3MtRVJYTkNjelFMOGJXdWc?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiUEFVX3lxTE1BWW9Sc0NsdEt0anBLVk1oU2plSmZRN0VoeURaTVdzdTRybEEyNTZpRHFVUUE0MHFGVUxEb0RQXzJlRF9MVTNHb3A1X1EtbE5T0gFWQVVfeXFMT09WcE80eGwwRTZaZnREaTZzS3ZidkhwOXhmVkdzMXNmZTlmd19uVm9LTjYwTVM3RUxUU3BwM19ZX2puMDdFd3MtRVJYTkNjelFMOGJXdWc?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Pakistan-Chinas five-point initiative: Dar steps up peace effortsBusiness RecorderPakistan, China call for cessation of hostilities, starting peace talks as soon as possibleDawnWill China join Pakistan-led efforts to mediate US-Iran peace?Al...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://news.google.com/rss/articles/CBMiUEFVX3lxTE1BWW9Sc0NsdEt0anBLVk1oU2plSmZRN0VoeURaTVdzdTRybEEyNTZpRHFVUUE0MHFGVUxEb0RQXzJlRF9MVTNHb3A1X1EtbE5T0gFWQVVfeXFMT09WcE80eGwwRTZaZnREaTZzS3ZidkhwOXhmVkdzMXNmZTlmd19uVm9LTjYwTVM3RUxUU3BwM19ZX2puMDdFd3MtRVJYTkNjelFMOGJXdWc?oc=5', NULL, '2026-04-03 12:53:24', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('565', 'Blake Lively\'s sexual harassment claims against Justin Baldoni tossed out but robust case remains', 'blake-lively-s-sexual-harassment-claims-against-justin-baldoni-tossed-out-but-robust-case-remains', '<p>Blake Lively\'s sexual harassment claims against Justin Baldoni over the movie \"It Ends With Us\" were dismissed Thursday by a federal judge who left intact three claims, including retaliation, that will let a jury hear many of the allegations anyway.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/03/nx-s1-5772611/blake-lively-sexual-harassment-justin-baldoni-tossed-out\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/03/nx-s1-5772611/blake-lively-sexual-harassment-justin-baldoni-tossed-out\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'Blake Lively\'s sexual harassment claims against Justin Baldoni over the movie \"It Ends With Us\" were dismissed Thursday by a federal judge who left intact three claims, including retaliation, that will let a jury hear many of the allegations anyway.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.npr.org/2026/04/03/nx-s1-5772611/blake-lively-sexual-harassment-justin-baldoni-tossed-out', NULL, '2026-04-03 12:53:25', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('566', 'Pakistan says a new round of peace talks with Afghanistan is underway in China', 'pakistan-says-a-new-round-of-peace-talks-with-afghanistan-is-underway-in-china', '<p>Pakistan confirmed it was holding peace talks with Afghanistan\'s Taliban government in China, where Beijing is trying to broker a lasting ceasefire after weeks of fighting.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/03/g-s1-116299/pakistan-says-a-new-round-of-peace-talks-with-afghanistan-is-underway-in-china\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/03/g-s1-116299/pakistan-says-a-new-round-of-peace-talks-with-afghanistan-is-underway-in-china\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'Pakistan confirmed it was holding peace talks with Afghanistan\'s Taliban government in China, where Beijing is trying to broker a lasting ceasefire after weeks of fighting.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.npr.org/2026/04/03/g-s1-116299/pakistan-says-a-new-round-of-peace-talks-with-afghanistan-is-underway-in-china', NULL, '2026-04-03 12:53:25', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('567', 'Cuba releasing 2,010 prisoners as the US pressures the island\'s government', 'cuba-releasing-2-010-prisoners-as-the-us-pressures-the-island-s-government', '<p>The Cuban government said the pardons were a \"humanitarian gesture\" in connection with Holy Week and didn\'t mention mounting pressures with the U.S.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/03/g-s1-116297/cuba-releasing-2-010-prisoners-as-the-us-pressures-the-islands-government\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/03/g-s1-116297/cuba-releasing-2-010-prisoners-as-the-us-pressures-the-islands-government\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'The Cuban government said the pardons were a \"humanitarian gesture\" in connection with Holy Week and didn\'t mention mounting pressures with the U.S.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.npr.org/2026/04/03/g-s1-116297/cuba-releasing-2-010-prisoners-as-the-us-pressures-the-islands-government', NULL, '2026-04-03 12:53:25', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('568', 'ICE detention deaths are on a record pace. One Texas facility bears the brunt', 'ice-detention-deaths-are-on-a-record-pace-one-texas-facility-bears-the-brunt', '<p>ICE inspectors in February found 49 violations to detention standards at Camp East Montana, including failure from staff to\"accurately document required checks to prevent significant self-harm and suicide.\"</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/03/nx-s1-5754749/ice-detention-deaths-are-on-a-record-pace-one-texas-facility-bears-the-brunt\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/03/nx-s1-5754749/ice-detention-deaths-are-on-a-record-pace-one-texas-facility-bears-the-brunt\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'ICE inspectors in February found 49 violations to detention standards at Camp East Montana, including failure from staff to\"accurately document required checks to prevent significant self-harm and suicide.\"', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.npr.org/2026/04/03/nx-s1-5754749/ice-detention-deaths-are-on-a-record-pace-one-texas-facility-bears-the-brunt', NULL, '2026-04-03 12:53:25', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('569', 'A botanist searches for the seeds of the rare Death Valley Sage', 'a-botanist-searches-for-the-seeds-of-the-rare-death-valley-sage', '<p>For more than 15 years, botanist Naomi Fraga has been trying to collect seeds from the rare Death Valley sage, for safekeeping in a vault of native California seeds.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/sections/the-picture-show/2026/04/01/nx-s1-5749446/botanist-search-seeds-rare-death-valley-sage\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/sections/the-picture-show/2026/04/01/nx-s1-5749446/botanist-search-seeds-rare-death-valley-sage\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'For more than 15 years, botanist Naomi Fraga has been trying to collect seeds from the rare Death Valley sage, for safekeeping in a vault of native California seeds.', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.npr.org/sections/the-picture-show/2026/04/01/nx-s1-5749446/botanist-search-seeds-rare-death-valley-sage', NULL, '2026-04-03 12:53:25', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('570', 'Uganda receives first US deportation flight under third-country agreement', 'uganda-receives-first-us-deportation-flight-under-third-country-agreement', '<p>Dozen people arrive under new deal but legal challenges expected with scheme criticised as dehumanising processA flight carrying people being deported from the US has landed in Uganda, as Donald Trumps administration pushes on with its strategy of expelling migrants to countries they have no ties to.The deported people would stay in the east African country as a transition phase for potential onward transmission to other countries, an unnamed senior Ugandan government official told Reuters.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/us-news/2026/apr/02/uganda-receives-first-us-deportation-flight-under-third-country-agreement\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\r\n<p><strong><a href=\"https://www.theguardian.com/us-news/2026/apr/02/uganda-receives-first-us-deportation-flight-under-third-country-agreement\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'Dozen people arrive under new deal but legal challenges expected with scheme criticised as dehumanising processA flight carrying people being deported from the US has landed in Uganda, as Donald Trumps administration pushes on with its strategy of...', 'uploads/news/69cf721921784.png', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '1', '0', '0', '10', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 12:54:36', 'https://www.theguardian.com/us-news/2026/apr/02/uganda-receives-first-us-deportation-flight-under-third-country-agreement', NULL, '2026-04-03 12:53:26', '2026-04-05 20:08:39', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('571', 'News outlets falsely report Somaliland called for extradition of Ilhan Omar', 'news-outlets-falsely-report-somaliland-called-for-extradition-of-ilhan-omar', '<p>Reports, based on X post from unofficial account, follow JD Vances accusations and threats of finding legal remediesSign up for the Breaking News US email to get newsletter alerts in your inboxSeveral news outlets have falsely reported that Somalilands government called for the extradition of Ilhan Omar, basing their stories on a post from an X account that does not represent the state despite its claims to the contrary.Fox News, the New York Post, Sinclair Broadcast Groups the National News Desk and the Independent ran stories on the US representative. The reports centred on a post by RepOfSomaliland in reaction to claims by JD Vance that Omar had committed immigration fraud, which echoed prior allegations against the Somali-born Minnesota Democrat that she has vehemently denied.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/us-news/2026/mar/30/ilhan-omar-false-reports-somaliland\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/us-news/2026/mar/30/ilhan-omar-false-reports-somaliland\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'Reports, based on X post from unofficial account, follow JD Vances accusations and threats of finding legal remediesSign up for the Breaking News US email to get newsletter alerts in your inboxSeveral news outlets have falsely reported that...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '1', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.theguardian.com/us-news/2026/mar/30/ilhan-omar-false-reports-somaliland', NULL, '2026-04-03 12:53:26', '2026-04-03 14:17:19', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('572', 'Interpol arrest warrant requested in Congo-Brazzaville for Jean-Guy Blaise Mayolas', 'interpol-arrest-warrant-requested-in-congo-brazzaville-for-jean-guy-blaise-mayolas', '<p>Football federation president on the run with wife and sonConviction in absentia of wide-ranging corruption chargesAuthorities in Congo-Brazzaville have applied to Interpol for an international arrest warrant against Jean-Guy Blaise Mayolas, the president of the countrys football federation, Fecofoot, after he was convicted of embezzling 1.1m in Fifa funds.Mayolas is on the run with his wife and son after they were all sentenced to life imprisonment this month for embezzling funds provided by world footballs governing body as part of its Covid-19 relief plan in February 2021. As the Guardian revealed last year, that included almost 500,000 earmarked for the Congo womens team.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/football/2026/mar/30/congo-brazzaville-jean-guy-blaise-mayolas-fifa-interpol-arrest-warrant\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/football/2026/mar/30/congo-brazzaville-jean-guy-blaise-mayolas-fifa-interpol-arrest-warrant\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'Football federation president on the run with wife and sonConviction in absentia of wide-ranging corruption chargesAuthorities in Congo-Brazzaville have applied to Interpol for an international arrest warrant against Jean-Guy Blaise Mayolas, the...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.theguardian.com/football/2026/mar/30/congo-brazzaville-jean-guy-blaise-mayolas-fifa-interpol-arrest-warrant', NULL, '2026-04-03 12:53:26', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('573', 'Weather tracker: Thunderstorms drench UAE and Saudi Arabia', 'weather-tracker-thunderstorms-drench-uae-and-saudi-arabia', '<p>Abnormally strong jet stream triggers deluge in Middle East, while north Africa braces for 60-80mph gustsAn unusual weather pattern unleashed severe thunderstorms across parts of the Middle East last week, battering countries including the United Arab Emirates and Saudi Arabia. The Arabian peninsula typically dominated by arid desert climates received up to 150mm of rain in just a few days.The deluge was caused by an abnormally strong jet stream, which helped a deep area of low pressure to develop north of Saudi Arabia. This, in turn, drew moist tropical air from the Indian Ocean and triggered intense storms.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/environment/2026/mar/30/weather-tracker-thunderstorms-uae-united-arab-emirates-saudi-arabia\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/environment/2026/mar/30/weather-tracker-thunderstorms-uae-united-arab-emirates-saudi-arabia\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'Abnormally strong jet stream triggers deluge in Middle East, while north Africa braces for 60-80mph gustsAn unusual weather pattern unleashed severe thunderstorms across parts of the Middle East last week, battering countries including the United...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.theguardian.com/environment/2026/mar/30/weather-tracker-thunderstorms-uae-united-arab-emirates-saudi-arabia', NULL, '2026-04-03 12:53:26', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('574', 'Urgent action needed to prevent surge in digital violence in Africa, experts say', 'urgent-action-needed-to-prevent-surge-in-digital-violence-in-africa-experts-say', '<p>A huge rise in internet users under the age of 30 has fuelled an increase in online violence against women and girls with devastating real-life effects, activists sayActivists and lawyers in Africa are calling for urgent action to protect women, girls and boys as digital violence surges across the continent.A massive rise in internet users, coupled with huge numbers of people aged under 30, has fuelled an increase in gendered online violence across the continent, according to experts, by giving perpetrators new tools to control and silence women and girls, and influence boys.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/global-development/2026/mar/30/urgent-action-needed-to-prevent-surge-in-digital-violence-in-africa-experts-say\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/global-development/2026/mar/30/urgent-action-needed-to-prevent-surge-in-digital-violence-in-africa-experts-say\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'A huge rise in internet users under the age of 30 has fuelled an increase in online violence against women and girls with devastating real-life effects, activists sayActivists and lawyers in Africa are calling for urgent action to protect women,...', '', 'manual', '', NULL, '1', '1', NULL, 'published', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 14:10:57', 'https://www.theguardian.com/global-development/2026/mar/30/urgent-action-needed-to-prevent-surge-in-digital-violence-in-africa-experts-say', NULL, '2026-04-03 12:53:26', '2026-04-03 14:10:57', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('582', 'Junior Graphic Designer - Entry Level', 'junior-graphic-designer-entry-2024', 'Great opportunity for fresh talent! We are looking for a passionate Junior Graphic Designer to start their career with us. This is an entry-level position perfect for recent graduates or those looking to build their professional portfolio.', 'Entry level Graphic Designer position - perfect for fresh talent!', NULL, 'manual', NULL, NULL, '1', '1', NULL, 'published', 'manual', '0', '0', '0', '5', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 19:16:19', NULL, NULL, '2026-04-04 19:16:19', '2026-04-04 21:00:43', '0.00', 'neutral', 'medium', 'PENDING', '0', 'older', 'Design Studio Pakistan', 'Islamabad, Pakistan', 'PKR 40,000 - 60,000', '2024-07-01', 'Full-time', 'https://example.com/apply-junior-designer', 'Proficiency in Adobe Creative Suite, 0-1 year experience, creative portfolio, basic understanding of design principles, willingness to learn and grow', '1', '1', 'graphic-designer', '1. Portfolio review, 2. Basic design test, 3. Creative interview, 4. Training session attendance', '1';
INSERT INTO `news` VALUES ('583', 'Three suspects ordered to stay in UK custody over Jewish charity attack', 'three-suspects-ordered-to-stay-in-uk-custody-over-jewish-charity-attack', '<p>The Metropolitan Police said the three men were charged with arson after setting ambulances on fire.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/4/three-suspects-ordered-to-stay-in-uk-custody-over-jewish-charity-attack?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/4/three-suspects-ordered-to-stay-in-uk-custody-over-jewish-charity-attack?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'The Metropolitan Police said the three men were charged with arson after setting ambulances on fire.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 15:19:01', 'https://www.aljazeera.com/news/2026/4/4/three-suspects-ordered-to-stay-in-uk-custody-over-jewish-charity-attack?traffic_source=rss', NULL, '2026-04-04 20:49:56', '2026-04-04 20:49:56', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('584', 'Witness records Israeli strike on building in Lebanon’s Tyre', 'witness-records-israeli-strike-on-building-in-lebanon-s-tyre', '<p>Video captured the moment an Israeli strike targeted a building in Burj Shamali in Lebanons Tyre district.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/4/witness-records-israeli-strike-on-building-in-lebanons-tyre?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/4/witness-records-israeli-strike-on-building-in-lebanons-tyre?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Video captured the moment an Israeli strike targeted a building in Burj Shamali in Lebanons Tyre district.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 15:15:39', 'https://www.aljazeera.com/video/newsfeed/2026/4/4/witness-records-israeli-strike-on-building-in-lebanons-tyre?traffic_source=rss', NULL, '2026-04-04 20:49:56', '2026-04-04 20:49:56', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('585', 'Vehicles burn in Israel after being hit with Iranian projectile debris', 'vehicles-burn-in-israel-after-being-hit-with-iranian-projectile-debris', '<p>Video shows vehicles on fire in central Israel after debris from an intercepted Iranian projectile struck Ramat Gan.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/4/vehicles-burn-in-israel-after-being-hit-with-iranian-projectile-debris?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/4/vehicles-burn-in-israel-after-being-hit-with-iranian-projectile-debris?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Video shows vehicles on fire in central Israel after debris from an intercepted Iranian projectile struck Ramat Gan.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 14:40:04', 'https://www.aljazeera.com/video/newsfeed/2026/4/4/vehicles-burn-in-israel-after-being-hit-with-iranian-projectile-debris?traffic_source=rss', NULL, '2026-04-04 20:49:56', '2026-04-04 20:49:56', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('586', 'Haaland grabs hat-trick as Man City thrash Liverpool to reach FA Cup semis', 'haaland-grabs-hat-trick-as-man-city-thrash-liverpool-to-reach-fa-cup-semis', '<p>Erling Haaland treble and goal by Antoine Semenyo give City 4-0 win, as Liverpool\'s Mohamed Salah misses a penalty.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/sports/2026/4/4/haaland-grabs-hat-trick-as-man-city-thrash-liverpool-to-reach-fa-cup-semis?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/sports/2026/4/4/haaland-grabs-hat-trick-as-man-city-thrash-liverpool-to-reach-fa-cup-semis?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Erling Haaland treble and goal by Antoine Semenyo give City 4-0 win, as Liverpool\'s Mohamed Salah misses a penalty.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 13:57:15', 'https://www.aljazeera.com/sports/2026/4/4/haaland-grabs-hat-trick-as-man-city-thrash-liverpool-to-reach-fa-cup-semis?traffic_source=rss', NULL, '2026-04-04 20:49:56', '2026-04-04 20:49:56', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('587', 'Israeli strikes damage hospital in Lebanon’s Tyre; ground invasion advances', 'israeli-strikes-damage-hospital-in-lebanon-s-tyre-ground-invasion-advances', '<p>At least 11 people are injured as Israel strikes near the Lebanese Italian Hospital in Tyre, damaging the facility.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/4/israeli-strikes-damage-hospital-in-lebanons-tyre?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/4/israeli-strikes-damage-hospital-in-lebanons-tyre?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'At least 11 people are injured as Israel strikes near the Lebanese Italian Hospital in Tyre, damaging the facility.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 13:07:25', 'https://www.aljazeera.com/news/2026/4/4/israeli-strikes-damage-hospital-in-lebanons-tyre?traffic_source=rss', NULL, '2026-04-04 20:49:56', '2026-04-04 20:49:56', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('588', 'Amber wind warning issued as Storm Dave set to hit parts of UK', 'amber-wind-warning-issued-as-storm-dave-set-to-hit-parts-of-uk', '<p>The Met Office said injuries or danger to life could occur as a result of flying debris.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cg40xx110peo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cg40xx110peo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The Met Office said injuries or danger to life could occur as a result of flying debris.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 13:39:54', 'https://www.bbc.com/news/articles/cg40xx110peo?at_medium=RSS&at_campaign=rss', NULL, '2026-04-04 20:49:58', '2026-04-04 20:49:58', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('589', 'Fourth suspect arrested over Jewish charity ambulance arson attack', 'fourth-suspect-arrested-over-jewish-charity-ambulance-arson-attack', '<p>Three men charged following the attack appeared at court this morning, and have since been remanded in custody.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/clyjrrl17j1o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/clyjrrl17j1o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Three men charged following the attack appeared at court this morning, and have since been remanded in custody.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 15:10:04', 'https://www.bbc.com/news/articles/clyjrrl17j1o?at_medium=RSS&at_campaign=rss', NULL, '2026-04-04 20:49:58', '2026-04-04 20:49:58', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('590', 'Artemis II crew now halfway to Moon as they take \'spectacular\' image of Earth ', 'artemis-ii-crew-now-halfway-to-moon-as-they-take-spectacular-image-of-earth', '<p>The snap was taken aboard the Orion capsule by its commander, Reid Wiseman, as the crew head towards the Moon.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/ce8jzr423p9o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/ce8jzr423p9o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The snap was taken aboard the Orion capsule by its commander, Reid Wiseman, as the crew head towards the Moon.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 06:42:48', 'https://www.bbc.com/news/articles/ce8jzr423p9o?at_medium=RSS&at_campaign=rss', NULL, '2026-04-04 20:49:58', '2026-04-04 20:49:58', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('591', 'Russian attack on Ukraine market kills five', 'russian-attack-on-ukraine-market-kills-five', '<p>A Russian drone hit a busy spot in the southern Ukrainian town on Saturday morning, injuring another 21 people.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cp869m2gzr0o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cp869m2gzr0o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'A Russian drone hit a busy spot in the southern Ukrainian town on Saturday morning, injuring another 21 people.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 14:00:31', 'https://www.bbc.com/news/articles/cp869m2gzr0o?at_medium=RSS&at_campaign=rss', NULL, '2026-04-04 20:49:58', '2026-04-04 20:49:58', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('592', 'State pension age starts rising to 67 - here\'s how much you get and when', 'state-pension-age-starts-rising-to-67-here-s-how-much-you-get-and-when', '<p>The age at which people can start receiving the state pension is going up in stages over the next two years.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cx2e7e90kneo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cx2e7e90kneo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The age at which people can start receiving the state pension is going up in stages over the next two years.', '', 'manual', '', NULL, NULL, '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 23:27:52', 'https://www.bbc.com/news/articles/cx2e7e90kneo?at_medium=RSS&at_campaign=rss', NULL, '2026-04-04 20:49:58', '2026-04-04 20:49:58', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('593', 'From Hollywood headlines to Heartland buzz: Are you on the pulse?', 'from-hollywood-headlines-to-heartland-buzz-are-you-on-the-pulse', '<p>Test your knowledge of entertainment with this quiz on celebrities, trends and headline-making moments. Featured this week: celebrity scandals and guess the celebrity photo.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/entertainment/from-hollywood-headlines-heartland-buzz-are-you-on-the-pulse-2\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/entertainment/from-hollywood-headlines-heartland-buzz-are-you-on-the-pulse-2\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Test your knowledge of entertainment with this quiz on celebrities, trends and headline-making moments. Featured this week: celebrity scandals and guess the celebrity photo.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:30:44', 'https://www.foxnews.com/entertainment/from-hollywood-headlines-heartland-buzz-are-you-on-the-pulse-2', NULL, '2026-04-04 20:50:04', '2026-04-04 20:50:04', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('594', 'Trump unveils $1.5T defense surge, deep domestic cuts — what’s on the budget chopping block', 'trump-unveils-1-5t-defense-surge-deep-domestic-cuts-what-s-on-the-budget-chopping-block', '<p>The White House proposes a fiscal year 2027 budget with roughly 1.5 trillion in defense spending and sweeping cuts to key domestic programs.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/politics/trump-unveils-1-5t-defense-surge-deep-domestic-cuts-whats-chopping-block\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/politics/trump-unveils-1-5t-defense-surge-deep-domestic-cuts-whats-chopping-block\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'The White House proposes a fiscal year 2027 budget with roughly 1.5 trillion in defense spending and sweeping cuts to key domestic programs.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:28:37', 'https://www.foxnews.com/politics/trump-unveils-1-5t-defense-surge-deep-domestic-cuts-whats-chopping-block', NULL, '2026-04-04 20:50:04', '2026-04-04 20:50:04', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('595', 'Mavericks rookie Cooper Flagg becomes first teen in NBA history to score 50 points in a game', 'mavericks-rookie-cooper-flagg-becomes-first-teen-in-nba-history-to-score-50-points-in-a-game', '<p>Cooper Flagg, 19, made NBA history by becoming the first teenager to score over 50 points in a game, dropping 51 in the Dallas Mavericks\' loss to the Orlando Magic.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/sports/mavericks-rookie-cooper-flagg-becomes-first-teen-nba-history-score-50-points-game\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/sports/mavericks-rookie-cooper-flagg-becomes-first-teen-nba-history-score-50-points-game\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Cooper Flagg, 19, made NBA history by becoming the first teenager to score over 50 points in a game, dropping 51 in the Dallas Mavericks\' loss to the Orlando Magic.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:21:54', 'https://www.foxnews.com/sports/mavericks-rookie-cooper-flagg-becomes-first-teen-nba-history-score-50-points-game', NULL, '2026-04-04 20:50:04', '2026-04-04 20:50:04', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('596', 'Zach Bryan \'forced\' to cancel Oklahoma show over safety concerns', 'zach-bryan-forced-to-cancel-oklahoma-show-over-safety-concerns', '<p>Zach Bryan says he was forced by his team to cancel his Tulsa concert due to dangerous weather, sparking mixed reactions from fans and fellow artists.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/entertainment/zach-bryan-forced-cancel-oklahoma-show-over-safety-concerns\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/entertainment/zach-bryan-forced-cancel-oklahoma-show-over-safety-concerns\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'Zach Bryan says he was forced by his team to cancel his Tulsa concert due to dangerous weather, sparking mixed reactions from fans and fellow artists.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:13:35', 'https://www.foxnews.com/entertainment/zach-bryan-forced-cancel-oklahoma-show-over-safety-concerns', NULL, '2026-04-04 20:50:04', '2026-04-04 20:50:04', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('597', 'WWE NXT champ Jacy Jayne ready for call-up, puts women\'s division on notice', 'wwe-nxt-champ-jacy-jayne-ready-for-call-up-puts-women-s-division-on-notice', '<p>NXT women\'s champion Jacy Jayne says years ago she would have floundered on the main roster, but now she feels ready to take a spot from the best in WWE.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.foxnews.com/sports/wwe-nxt-champ-jacy-jayne-ready-call-puts-womens-division-notice\" target=\"_blank\" rel=\"noopener\">Fox News</a></em></p>\n\n<p><strong><a href=\"https://www.foxnews.com/sports/wwe-nxt-champ-jacy-jayne-ready-call-puts-womens-division-notice\" target=\"_blank\" rel=\"noopener\">Read full story on Fox News</a></strong></p>', 'NXT women\'s champion Jacy Jayne says years ago she would have floundered on the main roster, but now she feels ready to take a spot from the best in WWE.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:04:50', 'https://www.foxnews.com/sports/wwe-nxt-champ-jacy-jayne-ready-call-puts-womens-division-notice', NULL, '2026-04-04 20:50:04', '2026-04-04 20:50:04', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('598', 'Ahsan directs provinces to curb inflation after fuel price hike - RADIO PAKISTAN', 'ahsan-directs-provinces-to-curb-inflation-after-fuel-price-hike-radio-pakistan', '<p>Ahsan directs provinces to curb inflation after fuel price hikeRADIO PAKISTANPetrol price reduces to Rs378 per litre as govt cuts levy by Rs80DawnPakistan PM cuts petrol price a day after hike as Iran war drives oil shockArab News PKAbbasi criticises govt over inconsistent policies, calls for fuel...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMioAFBVV95cUxQNVBsZVdKUktsYlQzQmhjU09jdnFybWFtR3ItZ3dqS20wZHN2WTQ3bmZEaGdyV2JCZFRFVUcxX0FxZDBPOVJSVTNkbjNPc1dRQ3Z4RWVGckRPVU1kalZEVUQxM0tkdmZoemJ4M1NMVl96LW4yZ3M1cXAxU2wwR25jNmRwVnFGZlJDaW5FdEpTYkJZZlZpWERMQWR0ZW5xNzFS?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMioAFBVV95cUxQNVBsZVdKUktsYlQzQmhjU09jdnFybWFtR3ItZ3dqS20wZHN2WTQ3bmZEaGdyV2JCZFRFVUcxX0FxZDBPOVJSVTNkbjNPc1dRQ3Z4RWVGckRPVU1kalZEVUQxM0tkdmZoemJ4M1NMVl96LW4yZ3M1cXAxU2wwR25jNmRwVnFGZlJDaW5FdEpTYkJZZlZpWERMQWR0ZW5xNzFS?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Ahsan directs provinces to curb inflation after fuel price hikeRADIO PAKISTANPetrol price reduces to Rs378 per litre as govt cuts levy by Rs80DawnPakistan PM cuts petrol price a day after hike as Iran war drives oil shockArab News PKAbbasi...', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 15:11:11', 'https://news.google.com/rss/articles/CBMioAFBVV95cUxQNVBsZVdKUktsYlQzQmhjU09jdnFybWFtR3ItZ3dqS20wZHN2WTQ3bmZEaGdyV2JCZFRFVUcxX0FxZDBPOVJSVTNkbjNPc1dRQ3Z4RWVGckRPVU1kalZEVUQxM0tkdmZoemJ4M1NMVl96LW4yZ3M1cXAxU2wwR25jNmRwVnFGZlJDaW5FdEpTYkJZZlZpWERMQWR0ZW5xNzFS?oc=5', NULL, '2026-04-04 20:50:09', '2026-04-04 20:50:09', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('599', '‘Never refused to go to Islamabad’: Iran says US media misrepresenting its position on peace talks - Dawn', 'never-refused-to-go-to-islamabad-iran-says-us-media-misrepresenting-its-position-on-peace-talks-dawn', '<p>Never refused to go to Islamabad: Iran says US media misrepresenting its position on peace talksDawn\'Deeply grateful to Pakistan\' for mediation efforts, \'never refused to go to Islamabad\': Iranian FMThe Express TribuneIran praises Pakistan\'s mediation as backchannel talks with US edge toward...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTFBvS2VUaTJFSnFsalA4LVZHNW5VWFJNSmNVNDAwN09lU09pV0RLOHgza3VHYnYxSGo0UnBhY3ZBR2xqX1pGZWdfVNIBTkFVX3lxTE95aHFRM19wMVQ0eloteFNjTUszLVdMeFZuUHMyZWQxVFN4NWh1R0N4ZEpkVWNXSmxaOTVTcDNPUll2TVZ0U0NGd3dBMEw1UQ?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiSEFVX3lxTFBvS2VUaTJFSnFsalA4LVZHNW5VWFJNSmNVNDAwN09lU09pV0RLOHgza3VHYnYxSGo0UnBhY3ZBR2xqX1pGZWdfVNIBTkFVX3lxTE95aHFRM19wMVQ0eloteFNjTUszLVdMeFZuUHMyZWQxVFN4NWh1R0N4ZEpkVWNXSmxaOTVTcDNPUll2TVZ0U0NGd3dBMEw1UQ?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Never refused to go to Islamabad: Iran says US media misrepresenting its position on peace talksDawn\'Deeply grateful to Pakistan\' for mediation efforts, \'never refused to go to Islamabad\': Iranian FMThe Express TribuneIran praises Pakistan\'s...', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:55:13', 'https://news.google.com/rss/articles/CBMiSEFVX3lxTFBvS2VUaTJFSnFsalA4LVZHNW5VWFJNSmNVNDAwN09lU09pV0RLOHgza3VHYnYxSGo0UnBhY3ZBR2xqX1pGZWdfVNIBTkFVX3lxTE95aHFRM19wMVQ0eloteFNjTUszLVdMeFZuUHMyZWQxVFN4NWh1R0N4ZEpkVWNXSmxaOTVTcDNPUll2TVZ0U0NGd3dBMEw1UQ?oc=5', NULL, '2026-04-04 20:50:09', '2026-04-04 20:50:09', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('600', 'Trump weighs broader cabinet shake-up as Iran war pressure grows - The Express Tribune', 'trump-weighs-broader-cabinet-shake-up-as-iran-war-pressure-grows-the-express-tribune', '<p>Trump weighs broader cabinet shake-up as Iran war pressure growsThe Express TribuneView Full coverage on Google News</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiowFBVV95cUxPbUJSMy10TDVialY3MktYU04zTzVxNXBYMlR2cFRrWnRkQS1GQXdVcmduNEdjNnM1TVI5aktzUnNjWDNCVkdiM1l5UHNZVTREcWZwRm1QRVR2eHI3UzRSQ1dxVVU4LXZia2wzTG95ZE5lTGFxTHYzb3NQVlFLY2dNWERZNWpNRVBSU28xaXdmbXFZYzlWVmJqcE9ydHBGcEVNVlZz0gGrAUFVX3lxTE9VMTdRM0FwZ2tuNFk2WUg4bmw2LXFPZGVYRXlPNFBESlVpQU1LUVo0TEpxSHFCd0psSnMzM2piN2twQmd5SFp1czVtVkV3WDlPVzdsa0FXYkdUc2ZzRDFaUGhDVUtScHZmd19KejFHN3htQnNkdWxnRzk1Skc4Y0NRcWwzRGs1RnVRU0l5QkZmd1h6cFhMclJJNTdOYkxoYVZFdnF6YmNyVEJTVQ?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiowFBVV95cUxPbUJSMy10TDVialY3MktYU04zTzVxNXBYMlR2cFRrWnRkQS1GQXdVcmduNEdjNnM1TVI5aktzUnNjWDNCVkdiM1l5UHNZVTREcWZwRm1QRVR2eHI3UzRSQ1dxVVU4LXZia2wzTG95ZE5lTGFxTHYzb3NQVlFLY2dNWERZNWpNRVBSU28xaXdmbXFZYzlWVmJqcE9ydHBGcEVNVlZz0gGrAUFVX3lxTE9VMTdRM0FwZ2tuNFk2WUg4bmw2LXFPZGVYRXlPNFBESlVpQU1LUVo0TEpxSHFCd0psSnMzM2piN2twQmd5SFp1czVtVkV3WDlPVzdsa0FXYkdUc2ZzRDFaUGhDVUtScHZmd19KejFHN3htQnNkdWxnRzk1Skc4Y0NRcWwzRGs1RnVRU0l5QkZmd1h6cFhMclJJNTdOYkxoYVZFdnF6YmNyVEJTVQ?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Trump weighs broader cabinet shake-up as Iran war pressure growsThe Express TribuneView Full coverage on Google News', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 10:39:46', 'https://news.google.com/rss/articles/CBMiowFBVV95cUxPbUJSMy10TDVialY3MktYU04zTzVxNXBYMlR2cFRrWnRkQS1GQXdVcmduNEdjNnM1TVI5aktzUnNjWDNCVkdiM1l5UHNZVTREcWZwRm1QRVR2eHI3UzRSQ1dxVVU4LXZia2wzTG95ZE5lTGFxTHYzb3NQVlFLY2dNWERZNWpNRVBSU28xaXdmbXFZYzlWVmJqcE9ydHBGcEVNVlZz0gGrAUFVX3lxTE9VMTdRM0FwZ2tuNFk2WUg4bmw2LXFPZGVYRXlPNFBESlVpQU1LUVo0TEpxSHFCd0psSnMzM2piN2twQmd5SFp1czVtVkV3WDlPVzdsa0FXYkdUc2ZzRDFaUGhDVUtScHZmd19KejFHN3htQnNkdWxnRzk1Skc4Y0NRcWwzRGs1RnVRU0l5QkZmd1h6cFhMclJJNTdOYkxoYVZFdnF6YmNyVEJTVQ?oc=5', NULL, '2026-04-04 20:50:09', '2026-04-04 20:50:09', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('601', 'Search continues for missing US airman from downed F-15 as Iran says strike near nuclear plant kills one - follow live - BBC', 'search-continues-for-missing-us-airman-from-downed-f-15-as-iran-says-strike-near-nuclear-plant-kills-one-follow-live-bbc', '<p>Search continues for missing US airman from downed F-15 as Iran says strike near nuclear plant kills one - follow liveBBCLive updates: One pilot rescued, one still missing after U.S. fighter jet shot down over Iran, officials sayNBC NewsHide, find water: Ex-airmen detail how to survive being shot...</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMiVEFVX3lxTE0tTlR0dkR4SnBGQVhjeTVJNUZNUGNrdWdxYlhRYUFtSm1IOURIbXlMSEttODR4ZjdOVkRidFB0cXFwek80bWx5bzBPVmFCbmpRSll5Mg?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMiVEFVX3lxTE0tTlR0dkR4SnBGQVhjeTVJNUZNUGNrdWdxYlhRYUFtSm1IOURIbXlMSEttODR4ZjdOVkRidFB0cXFwek80bWx5bzBPVmFCbmpRSll5Mg?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Search continues for missing US airman from downed F-15 as Iran says strike near nuclear plant kills one - follow liveBBCLive updates: One pilot rescued, one still missing after U.S. fighter jet shot down over Iran, officials sayNBC NewsHide, find...', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 15:39:11', 'https://news.google.com/rss/articles/CBMiVEFVX3lxTE0tTlR0dkR4SnBGQVhjeTVJNUZNUGNrdWdxYlhRYUFtSm1IOURIbXlMSEttODR4ZjdOVkRidFB0cXFwek80bWx5bzBPVmFCbmpRSll5Mg?oc=5', NULL, '2026-04-04 20:50:09', '2026-04-04 20:50:09', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('602', 'Iran thanks Pakistan, rejects US claims of refusing Islamabad talks amid escalating conflict - Dunya News', 'iran-thanks-pakistan-rejects-us-claims-of-refusing-islamabad-talks-amid-escalating-conflict-dunya-news', '<p>Iran thanks Pakistan, rejects US claims of refusing Islamabad talks amid escalating conflictDunya NewsView Full coverage on Google News</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://news.google.com/rss/articles/CBMirgFBVV95cUxQRGdrQ0FRcFM0V2tmM0dQcE1HU1MyX2lMWWJNbVR6eWZzVjdPZjBET3BoZE51OE9NSU0xUzhEMVRlbjFEU1hwb21yN2lmUGZXSEhTWVlyLVFwWktzQl90UVhuSmItM2ZPZzlmRVpTd3NocTUxa0hCOXlicU9sNk9TZmM3Z2NhaENHUGpEaWtiblZ1S2dOcHdVdDBaNFpGTnlHYkRvTUw5SXg2THFNb3fSAVZBVV95cUxNeW93djNTcmRkQ3VwVC01dXVtMmxGc3ZTWGVkNUR6NkV3aE9GMkhZY1c2VmtXak1aS05VLTlLaElVWl9NWlRqLVNsekpQdkhZQzRVcTRqZw?oc=5\" target=\"_blank\" rel=\"noopener\">Google News</a></em></p>\n\n<p><strong><a href=\"https://news.google.com/rss/articles/CBMirgFBVV95cUxQRGdrQ0FRcFM0V2tmM0dQcE1HU1MyX2lMWWJNbVR6eWZzVjdPZjBET3BoZE51OE9NSU0xUzhEMVRlbjFEU1hwb21yN2lmUGZXSEhTWVlyLVFwWktzQl90UVhuSmItM2ZPZzlmRVpTd3NocTUxa0hCOXlicU9sNk9TZmM3Z2NhaENHUGpEaWtiblZ1S2dOcHdVdDBaNFpGTnlHYkRvTUw5SXg2THFNb3fSAVZBVV95cUxNeW93djNTcmRkQ3VwVC01dXVtMmxGc3ZTWGVkNUR6NkV3aE9GMkhZY1c2VmtXak1aS05VLTlLaElVWl9NWlRqLVNsekpQdkhZQzRVcTRqZw?oc=5\" target=\"_blank\" rel=\"noopener\">Read full story on Google News</a></strong></p>', 'Iran thanks Pakistan, rejects US claims of refusing Islamabad talks amid escalating conflictDunya NewsView Full coverage on Google News', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 12:10:29', 'https://news.google.com/rss/articles/CBMirgFBVV95cUxQRGdrQ0FRcFM0V2tmM0dQcE1HU1MyX2lMWWJNbVR6eWZzVjdPZjBET3BoZE51OE9NSU0xUzhEMVRlbjFEU1hwb21yN2lmUGZXSEhTWVlyLVFwWktzQl90UVhuSmItM2ZPZzlmRVpTd3NocTUxa0hCOXlicU9sNk9TZmM3Z2NhaENHUGpEaWtiblZ1S2dOcHdVdDBaNFpGTnlHYkRvTUw5SXg2THFNb3fSAVZBVV95cUxNeW93djNTcmRkQ3VwVC01dXVtMmxGc3ZTWGVkNUR6NkV3aE9GMkhZY1c2VmtXak1aS05VLTlLaElVWl9NWlRqLVNsekpQdkhZQzRVcTRqZw?oc=5', NULL, '2026-04-04 20:50:09', '2026-04-04 20:50:09', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('603', 'After the Minnesota surge, ICE is moving to a quieter enforcement approach', 'after-the-minnesota-surge-ice-is-moving-to-a-quieter-enforcement-approach', '<p>ICE seems to be changing from aggressive immigration enforcement on city streets to an apparent return to operations that rely heavily on local law enforcement. But even in Florida, where sheriffs are required to cooperate with ICE, some conservative sheriffs have concerns about pursuing immigrants with no criminal records.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/04/nx-s1-5768273/after-minnesota-ice-surge-shift-to-quieter-enforcement\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/04/nx-s1-5768273/after-minnesota-ice-surge-shift-to-quieter-enforcement\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'ICE seems to be changing from aggressive immigration enforcement on city streets to an apparent return to operations that rely heavily on local law enforcement. But even in Florida, where sheriffs are required to cooperate with ICE, some...', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 11:34:17', 'https://www.npr.org/2026/04/04/nx-s1-5768273/after-minnesota-ice-surge-shift-to-quieter-enforcement', NULL, '2026-04-04 20:50:10', '2026-04-04 20:50:10', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('604', '\'London Falling\': A teenage imposter, an aging gangster and a body in the Thames', 'london-falling-a-teenage-imposter-an-aging-gangster-and-a-body-in-the-thames', '<p>In 2019, 19-year-old Zac Brettler leapt towards the River Thames from a fifth-floor luxury apartment in central London. Patrick Radden Keefe investigates the story of the teen\'s double life in a new book.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/04/nx-s1-5766048/london-falling-review-patrick-radden-keefe\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/04/nx-s1-5766048/london-falling-review-patrick-radden-keefe\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'In 2019, 19-year-old Zac Brettler leapt towards the River Thames from a fifth-floor luxury apartment in central London. Patrick Radden Keefe investigates the story of the teen\'s double life in a new book.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 08:00:00', 'https://www.npr.org/2026/04/04/nx-s1-5766048/london-falling-review-patrick-radden-keefe', NULL, '2026-04-04 20:50:10', '2026-04-04 20:50:10', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('605', 'Opinion: Humanity\'s hopes ascended with Artemis II', 'opinion-humanity-s-hopes-ascended-with-artemis-ii', '<p>NPR\'s Scott Simon reflects on the successful launch of NASA\'s Artemis II this week. The four astronauts aboard will travel around the moon.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/04/nx-s1-5764829/opinion-humanitys-hopes-ascended-with-artemis-ii\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/04/nx-s1-5764829/opinion-humanitys-hopes-ascended-with-artemis-ii\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'NPR\'s Scott Simon reflects on the successful launch of NASA\'s Artemis II this week. The four astronauts aboard will travel around the moon.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 08:00:00', 'https://www.npr.org/2026/04/04/nx-s1-5764829/opinion-humanitys-hopes-ascended-with-artemis-ii', NULL, '2026-04-04 20:50:10', '2026-04-04 20:50:10', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('606', 'Iran war enters its 6th week as military searches for downed jet crew member', 'iran-war-enters-its-6th-week-as-military-searches-for-downed-jet-crew-member', '<p>The war in Iran enters its 6th week as the search continues for the missing U.S. service member who bailed out of a fighter jet shot down over Iran on Friday.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/2026/04/04/nx-s1-5773436/iran-war-updates\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/2026/04/04/nx-s1-5773436/iran-war-updates\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'The war in Iran enters its 6th week as the search continues for the missing U.S. service member who bailed out of a fighter jet shot down over Iran on Friday.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 06:48:27', 'https://www.npr.org/2026/04/04/nx-s1-5773436/iran-war-updates', NULL, '2026-04-04 20:50:10', '2026-04-04 20:50:10', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('607', 'The busiest place you\'ve never seen', 'the-busiest-place-you-ve-never-seen', '<p>Photographer Julia Gunther and writer-filmmaker Nick Schnfeld chronicle the rhythms of daily life on Tristan da Cunha, the world\'s most remote inhabited island.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.npr.org/sections/the-picture-show/2026/04/04/g-s1-116218/life-on-tristan-da-cunha\" target=\"_blank\" rel=\"noopener\">NPR News</a></em></p>\n\n<p><strong><a href=\"https://www.npr.org/sections/the-picture-show/2026/04/04/g-s1-116218/life-on-tristan-da-cunha\" target=\"_blank\" rel=\"noopener\">Read full story on NPR News</a></strong></p>', 'Photographer Julia Gunther and writer-filmmaker Nick Schnfeld chronicle the rhythms of daily life on Tristan da Cunha, the world\'s most remote inhabited island.', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 06:00:00', 'https://www.npr.org/sections/the-picture-show/2026/04/04/g-s1-116218/life-on-tristan-da-cunha', NULL, '2026-04-04 20:50:10', '2026-04-04 20:50:10', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('608', 'People of Burkina Faso should forget about democracy, says military ruler', 'people-of-burkina-faso-should-forget-about-democracy-says-military-ruler', '<p>Ibrahim Traor, who took power in 2022 coup, tells state broadcaster we must tell the truth, democracy isnt for usPeople in Burkina Faso should forget about democracy as it is not for us, the military president, Ibrahim Traor, told the countrys state broadcaster.Traor took power in a coup in September 2022, toppling another junta that had taken power just nine months earlier. He has since stifled opposition and in January banned political parties outright.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.theguardian.com/world/2026/apr/03/people-burkina-faso-should-forget-about-democracy-military-ruler-ibrahim-traore\" target=\"_blank\" rel=\"noopener\">The Guardian</a></em></p>\n\n<p><strong><a href=\"https://www.theguardian.com/world/2026/apr/03/people-burkina-faso-should-forget-about-democracy-military-ruler-ibrahim-traore\" target=\"_blank\" rel=\"noopener\">Read full story on The Guardian</a></strong></p>', 'Ibrahim Traor, who took power in 2022 coup, tells state broadcaster we must tell the truth, democracy isnt for usPeople in Burkina Faso should forget about democracy as it is not for us, the military president, Ibrahim Traor, told the countrys state...', '', 'manual', '', NULL, '1', '1', NULL, 'draft', 'rss_import', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-03 10:18:56', 'https://www.theguardian.com/world/2026/apr/03/people-burkina-faso-should-forget-about-democracy-military-ruler-ibrahim-traore', NULL, '2026-04-04 20:50:13', '2026-04-04 20:50:13', '0.00', '0', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('609', 'bkuc', 'bkuc', 'bkuc', 'bkuc...', 'uploads/news/img_69d13384472f6_1775317892.jpg', 'manual', '', '', '1', '1', NULL, 'published', 'manual', '0', '0', '0', '1', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-04 20:51:32', NULL, NULL, '2026-04-04 20:51:32', '2026-04-04 22:51:48', '0.00', 'neutral', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';
INSERT INTO `news` VALUES ('610', 'bkuc', 'bkuc-1', 'bkuc', 'bkuc...', 'uploads/news/img_69d277f98765f_1775400953.jpeg', 'manual', '', '', '1', '1', NULL, 'published', 'manual', '0', '0', '0', '0', '0', '0.00', '0', '0', NULL, NULL, NULL, '2026-04-05 19:55:53', NULL, NULL, '2026-04-05 19:55:53', '2026-04-05 19:55:53', '0.00', 'neutral', 'medium', 'PENDING', '0', 'older', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, '0';


-- Table structure for `news_analytics`
CREATE TABLE `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `avg_time_on_page` int(11) DEFAULT 0,
  `engagement_rate` decimal(10,2) DEFAULT 0.00,
  `likes` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `shares` int(11) DEFAULT 0,
  `unique_views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `avg_read_time` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_date` (`news_id`,`date`),
  KEY `idx_analytics_news` (`news_id`),
  KEY `idx_analytics_date` (`date`),
  CONSTRAINT `fk_news_analytics_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_credibility_analysis`
CREATE TABLE `news_credibility_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `analysis_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `analysis_method` varchar(50) DEFAULT 'AI_MULTIMODEL',
  `ai_model_version` varchar(20) DEFAULT 'v2.1',
  `processing_time_ms` int(11) DEFAULT 0,
  `title_credibility` decimal(5,2) DEFAULT 0.00,
  `content_credibility` decimal(5,2) DEFAULT 0.00,
  `source_credibility` decimal(5,2) DEFAULT 0.00,
  `factual_accuracy` decimal(5,2) DEFAULT 0.00,
  `sensationalism_score` decimal(5,2) DEFAULT 0.00,
  `emotional_manipulation` decimal(5,2) DEFAULT 0.00,
  `clickbait_score` decimal(5,2) DEFAULT 0.00,
  `propaganda_indicators` decimal(5,2) DEFAULT 0.00,
  `grammar_score` decimal(5,2) DEFAULT 0.00,
  `readability_score` decimal(5,2) DEFAULT 0.00,
  `factual_density` decimal(5,2) DEFAULT 0.00,
  `source_verified` tinyint(1) DEFAULT 0,
  `source_reputation_score` decimal(5,2) DEFAULT 0.00,
  `cross_reference_count` int(11) DEFAULT 0,
  `credibility_score` decimal(5,2) DEFAULT 0.00,
  `confidence_level` decimal(5,2) DEFAULT 0.00,
  `risk_level` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'MEDIUM',
  `content_category` varchar(50) DEFAULT 'GENERAL',
  `requires_review` tinyint(1) DEFAULT 0,
  `auto_flagged` tinyint(1) DEFAULT 0,
  `analysis_details` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_analysis_date` (`analysis_date`),
  KEY `idx_credibility_score` (`credibility_score`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_requires_review` (`requires_review`),
  CONSTRAINT `news_credibility_analysis_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news_credibility_analysis` VALUES ('59', '570', '2026-04-03 13:58:05', 'AI_MULTIMODEL', '0', '12', '100.00', '85.00', '60.00', '83.00', '0.00', '0.00', '0.00', '0.00', '75.00', '23.93', '16.00', '0', '50.00', '3', '71.00', '80.00', 'MEDIUM', 'LIKELY_TRUE', '0', '0', NULL;
INSERT INTO `news_credibility_analysis` VALUES ('60', '539', '2026-04-03 14:04:53', 'AI_MULTIMODEL', '0', '6', '100.00', '65.00', '30.00', '75.00', '0.00', '0.00', '0.00', '0.00', '100.00', '50.00', '10.00', '0', '0.00', '0', '62.00', '80.00', 'MEDIUM', 'UNVERIFIED', '1', '0', NULL;
INSERT INTO `news_credibility_analysis` VALUES ('61', '571', '2026-04-03 14:17:18', 'AI_MULTIMODEL', '0', '2', '100.00', '90.00', '60.00', '83.00', '0.00', '0.00', '0.00', '8.00', '75.00', '34.50', '21.14', '0', '50.00', '3', '71.29', '80.00', 'MEDIUM', 'LIKELY_TRUE', '0', '0', NULL;
INSERT INTO `news_credibility_analysis` VALUES ('63', '582', '2026-04-04 20:49:34', 'AI_MULTIMODEL', '0', '12', '100.00', '65.00', '30.00', '75.00', '0.00', '0.00', '0.00', '0.00', '100.00', '45.70', '16.22', '0', '0.00', '0', '62.41', '80.00', 'MEDIUM', 'UNVERIFIED', '1', '0', NULL;
INSERT INTO `news_credibility_analysis` VALUES ('64', '609', '2026-04-04 22:51:48', 'AI_MULTIMODEL', '0', '47', '95.00', '65.00', '30.00', '75.00', '0.00', '0.00', '0.00', '0.00', '80.00', '50.00', '0.00', '0', '0.00', '0', '58.25', '65.00', 'HIGH', 'UNVERIFIED', '1', '0', NULL;
INSERT INTO `news_credibility_analysis` VALUES ('65', '544', '2026-04-05 19:46:26', 'AI_MULTIMODEL', '0', '18', '100.00', '85.00', '60.00', '83.00', '0.00', '0.00', '0.00', '0.00', '75.00', '36.21', '27.54', '0', '50.00', '4', '72.76', '80.00', 'MEDIUM', 'LIKELY_TRUE', '0', '0', NULL;


-- Table structure for `news_likes`
CREATE TABLE `news_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_like` (`news_id`,`user_id`),
  UNIQUE KEY `unique_ip_like` (`news_id`,`ip_address`,`user_id`,`created_at`),
  KEY `user_id` (`user_id`),
  KEY `idx_news_user` (`news_id`,`user_id`),
  KEY `idx_news_ip` (`news_id`,`ip_address`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `news_likes_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `news_product_relations`
CREATE TABLE `news_product_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `display_position` enum('top','bottom','sidebar','inline') DEFAULT 'sidebar',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_product_unique` (`news_id`,`product_id`),
  KEY `news_id` (`news_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_news_product_relations_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_news_product_relations_product` FOREIGN KEY (`product_id`) REFERENCES `affiliate_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_shares`
CREATE TABLE `news_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `platform` enum('facebook','twitter','whatsapp','linkedin','telegram','email','copy','unknown') DEFAULT 'unknown',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_platform` (`news_id`,`platform`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `news_shares_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `news_sources`
CREATE TABLE `news_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of news source',
  `url` varchar(500) NOT NULL COMMENT 'Main URL of news source',
  `category_id` int(11) DEFAULT NULL COMMENT 'Default category for scraped news',
  `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL if available',
  `scraping_selector` text DEFAULT NULL COMMENT 'CSS selector for content scraping',
  `title_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article titles',
  `content_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article content',
  `image_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article images',
  `link_selector` varchar(255) DEFAULT NULL COMMENT 'CSS selector for article links',
  `status` enum('active','inactive','error') DEFAULT 'active' COMMENT 'Source status',
  `last_scraped` datetime DEFAULT NULL COMMENT 'Last successful scrape time',
  `scrape_frequency` int(11) DEFAULT 60 COMMENT 'Scraping frequency in minutes',
  `total_articles` int(11) DEFAULT 0 COMMENT 'Total articles scraped',
  `error_count` int(11) DEFAULT 0 COMMENT 'Consecutive error count',
  `last_error` text DEFAULT NULL COMMENT 'Last error message',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional scraping settings',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `priority` int(11) DEFAULT 0,
  `type` enum('rss','website','api') DEFAULT 'rss',
  `total_articles_scraped` int(11) DEFAULT 0,
  `auto_generate_images` tinyint(1) DEFAULT 0 COMMENT 'Automatically generate images for RSS imports',
  `image_provider` enum('openai','stability','replicate','placeholder') DEFAULT 'openai',
  `image_style` varchar(100) DEFAULT 'realistic journalistic news photo' COMMENT 'Style for AI image generation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_url` (`url`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_last_scraped` (`last_scraped`),
  KEY `idx_name` (`name`),
  KEY `idx_error_count` (`error_count`),
  KEY `idx_news_sources_status` (`status`),
  KEY `idx_news_sources_last_scraped` (`last_scraped`),
  KEY `idx_news_sources_priority` (`priority`),
  CONSTRAINT `fk_news_sources_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='External news sources for web scraping';

INSERT INTO `news_sources` VALUES ('1', 'BBC News', 'https://feeds.bbci.co.uk/news/rss.xml', NULL, 'https://feeds.bbci.co.uk/news/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:49:58', '60', '0', '0', NULL, NULL, '2026-03-19 20:46:42', '2026-04-04 20:49:58', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('2', 'CNN', 'https://rss.cnn.com/rss/edition.rss', NULL, 'http://rss.cnn.com/rss/edition.rss', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '0', NULL, NULL, '2026-03-19 20:46:42', '2026-03-25 08:07:08', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('3', 'Reuters', 'https://feeds.reuters.com/reuters/topNews', NULL, 'https://feeds.reuters.com/reuters/topNews', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '0', NULL, NULL, '2026-03-19 20:46:42', '2026-03-20 00:11:58', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('4', 'Al Jazeera', 'https://www.aljazeera.com/xml/rss/all.xml', NULL, 'https://www.aljazeera.com/xml/rss/all.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:49:56', '60', '0', '0', NULL, NULL, '2026-03-19 20:46:42', '2026-04-04 20:49:56', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('5', 'Associated Press', 'https://feeds.apnews.com/rss/apf-topnews', NULL, 'https://feeds.apnews.com/rss/apf-topnews', NULL, NULL, NULL, NULL, NULL, 'active', NULL, '60', '0', '0', NULL, NULL, '2026-03-19 20:46:42', '2026-03-20 00:11:58', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('7', 'The Guardian', 'https://www.theguardian.com/world/rss', '1', 'https://www.theguardian.com/world/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:13', '60', '0', '0', NULL, NULL, '2026-03-20 00:05:50', '2026-04-04 20:50:13', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('8', 'Fox News', 'https://feeds.foxnews.com/foxnews/latest', '1', 'https://feeds.foxnews.com/foxnews/latest', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:04', '60', '0', '0', NULL, NULL, '2026-03-20 00:05:58', '2026-04-04 20:50:04', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('9', 'NPR News', 'https://feeds.npr.org/1001/rss.xml', '1', 'https://feeds.npr.org/1001/rss.xml', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:10', '60', '0', '0', NULL, NULL, '2026-03-20 00:06:04', '2026-04-04 20:50:10', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('10', 'CBS News', 'http://feeds.cbsnews.com/CBSNewsMain', '1', 'http://feeds.cbsnews.com/CBSNewsMain', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:00', '60', '0', '0', NULL, NULL, '2026-03-25 08:07:20', '2026-04-04 20:50:00', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('11', 'Google News', 'https://news.google.com/rss', '1', 'https://news.google.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:09', '60', '0', '0', NULL, NULL, '2026-03-25 08:13:22', '2026-04-04 20:50:09', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';
INSERT INTO `news_sources` VALUES ('12', 'Yahoo News', 'https://news.yahoo.com/rss', '1', 'https://news.yahoo.com/rss', NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-04 20:50:17', '60', '0', '0', NULL, NULL, '2026-03-25 08:13:26', '2026-04-04 20:50:17', '0', 'rss', '0', '0', 'openai', 'realistic journalistic news photo';


-- Table structure for `news_tags`
CREATE TABLE `news_tags` (
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `idx_news_tags_news` (`news_id`),
  KEY `idx_news_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_read` (`is_read`),
  KEY `idx_notifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `poll_options`
CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) DEFAULT 0,
  `order_position` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `poll_options` VALUES ('7', '5', 'ali', '0', '0';
INSERT INTO `poll_options` VALUES ('8', '5', 'kashif', '0', '0';
INSERT INTO `poll_options` VALUES ('9', '5', 'PTI', '0', '0';
INSERT INTO `poll_options` VALUES ('10', '5', 'PTI', '0', '0';
INSERT INTO `poll_options` VALUES ('11', '5', 'cs', '0', '0';
INSERT INTO `poll_options` VALUES ('12', '5', 'za', '0', '0';


-- Table structure for `poll_votes`
CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`ip_address`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `poll_votes_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `polls`
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_polls_status` (`status`),
  KEY `idx_polls_created` (`created_at`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `polls_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `polls` VALUES ('3', 'Which one is the Best leader In Pakistan', 'active', '2026-03-25 18:47:47', '', '3', '2026-03-27 18:47:00';
INSERT INTO `polls` VALUES ('5', 'do do', 'active', '2026-04-05 19:52:17', NULL, NULL, '2026-04-08 19:52:17';


-- Table structure for `post_likes`
CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_news_id` (`news_id`),
  CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `post_likes` VALUES ('36', '570', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-03 14:03:27';
INSERT INTO `post_likes` VALUES ('37', '539', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-03 14:03:29';
INSERT INTO `post_likes` VALUES ('38', '539', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-03 14:08:04';
INSERT INTO `post_likes` VALUES ('39', '570', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-03 14:08:05';
INSERT INTO `post_likes` VALUES ('40', '545', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-03 20:16:24';
INSERT INTO `post_likes` VALUES ('41', '540', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-03 21:46:30';
INSERT INTO `post_likes` VALUES ('42', '609', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-04 21:13:12';
INSERT INTO `post_likes` VALUES ('43', '610', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-05 19:58:35';


-- Table structure for `rss_sources`
CREATE TABLE `rss_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_import` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rss_sources` VALUES ('1', 'BBC News', 'http://feeds.bbci.co.uk/news/rss.xml', 'World', 'active', '2026-03-25 01:40:11', '2026-03-25 01:39:41';
INSERT INTO `rss_sources` VALUES ('2', 'CNN', 'http://rss.cnn.com/rss/edition.rss', 'World', 'active', '2026-03-25 01:40:19', '2026-03-25 01:39:41';
INSERT INTO `rss_sources` VALUES ('3', 'Reuters', 'https://www.reuters.com/rssFeed/worldNews', 'World', 'active', NULL, '2026-03-25 01:39:41';
INSERT INTO `rss_sources` VALUES ('4', 'Al Jazeera', 'https://www.aljazeera.com/xml/rss/all.xml', 'World', 'active', '2026-03-25 01:40:28', '2026-03-25 01:39:41';
INSERT INTO `rss_sources` VALUES ('5', 'Geo News', 'https://www.geo.tv/rss/feed/1.xml', 'Pakistan', 'active', NULL, '2026-03-25 01:39:41';


-- Table structure for `search_analytics`
CREATE TABLE `search_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `clicked_result_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `clicked_result_id` (`clicked_result_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_search_query` (`query`),
  KEY `idx_search_date` (`created_at`),
  CONSTRAINT `search_analytics_ibfk_1` FOREIGN KEY (`clicked_result_id`) REFERENCES `news` (`id`) ON DELETE SET NULL,
  CONSTRAINT `search_analytics_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `seo_analytics`
CREATE TABLE `seo_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `page_views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `avg_session_time` int(11) DEFAULT 0,
  `top_pages` text DEFAULT NULL,
  `traffic_sources` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `seo_settings`
CREATE TABLE `seo_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` VALUES ('1', 'site_name', 'PK Live News', 'Website name', '2026-03-19 20:30:08', '2026-03-19 20:30:08';
INSERT INTO `settings` VALUES ('2', 'site_description', 'Your trusted source for breaking news', 'Site description', '2026-03-19 20:30:08', '2026-03-19 20:30:08';
INSERT INTO `settings` VALUES ('3', 'contact_email', 'contact@pklivenews.com', 'Contact email', '2026-03-19 20:30:08', '2026-03-19 20:30:08';
INSERT INTO `settings` VALUES ('4', 'facebook_url', 'https://facebook.com/pklivenews', 'Facebook page URL', '2026-03-19 20:30:08', '2026-03-19 20:30:08';
INSERT INTO `settings` VALUES ('5', 'twitter_url', 'https://twitter.com/pklivenews', 'Twitter profile URL', '2026-03-19 20:30:08', '2026-03-19 20:30:08';
INSERT INTO `settings` VALUES ('6', 'youtube_url', 'https://youtube.com/pklivenews', 'YouTube channel URL', '2026-03-19 20:30:08', '2026-03-19 20:30:08';


-- Table structure for `site_settings`
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` VALUES ('1', 'default_language', 'en', 'text', '', '2026-03-19 19:58:02', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('2', 'enable_language_switcher', '1', 'boolean', 'Enable/disable language switcher', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('3', 'show_language_flags', '1', 'boolean', 'Show country flags in language switcher', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('4', 'auto_detect_language', '1', 'boolean', 'Auto-detect user language from browser', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('5', 'multilingual_seo', '1', 'boolean', 'Enable multilingual SEO (hreflang tags)', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('6', 'site_title', 'PK Live News', 'string', 'Website title', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('7', 'site_description', 'Latest news and updates from Pakistan and around the world', 'text', '', '2026-03-19 19:58:02', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('8', 'site_keywords', 'news, pakistan, breaking news, current affairs', 'string', 'Website meta keywords', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('9', 'contact_email', 'admin@pklivenews.com', 'text', '', '2026-03-19 19:58:02', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('10', 'social_facebook', 'https://facebook.com/pklivenews', 'string', 'Facebook page URL', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('11', 'social_twitter', 'https://twitter.com/pklivenews', 'string', 'Twitter page URL', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('12', 'social_youtube', 'https://youtube.com/pklivenews', 'string', 'YouTube channel URL', '2026-03-19 19:58:02', '2026-03-19 19:58:02';
INSERT INTO `site_settings` VALUES ('13', 'site_name', 'PK Live News', 'text', '', '2026-03-21 00:36:40', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('14', 'posts_per_page', '20', 'number', '', '2026-03-21 00:36:40', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('15', 'maintenance_mode', 'off', 'text', '', '2026-03-21 00:36:40', '2026-03-21 00:36:40';
INSERT INTO `site_settings` VALUES ('16', 'show_trending_news', 'on', 'boolean', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('17', 'show_ads', 'on', 'boolean', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('18', 'social_media_links', '{\"facebook\":\"\",\"twitter\":\"\",\"instagram\":\"\",\"youtube\":\"\"}', 'text', '', '2026-03-21 00:36:41', '2026-03-21 00:36:41';
INSERT INTO `site_settings` VALUES ('19', 'seo_meta_description', 'PK Live News - Your trusted source for latest news', 'text', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('20', 'seo_keywords', 'news, pakistan, breaking news, current affairs', 'text', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('21', 'cache_duration', '3600', 'number', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('22', 'enable_comments', 'on', 'boolean', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('23', 'enable_rss', 'on', 'boolean', '', '2026-03-21 00:36:41', '2026-03-21 00:37:34';
INSERT INTO `site_settings` VALUES ('24', 'theme_color', '#007bff', 'text', '', '2026-03-21 00:36:41', '2026-03-21 00:36:41';
INSERT INTO `site_settings` VALUES ('25', 'logo_path', 'assets/images/logo.png', 'text', '', '2026-03-21 00:36:41', '2026-03-21 00:36:41';


-- Table structure for `stream_views`
CREATE TABLE `stream_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL,
  `viewer_ip` varchar(45) DEFAULT NULL,
  `viewer_session` varchar(255) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  CONSTRAINT `stream_views_ibfk_1` FOREIGN KEY (`stream_id`) REFERENCES `live_stream` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `system_settings`
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `tags`
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tags_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `traffic_analytics`
CREATE TABLE `traffic_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `page_views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet') DEFAULT 'desktop',
  `browser` varchar(100) DEFAULT NULL,
  `session_duration` int(11) DEFAULT 0,
  `bounce_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_datetime` (`date`,`hour`,`page_url`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `traffic_analytics` VALUES ('1', '2026-03-20', '0', '230', '53', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '389', '28.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('2', '2026-03-20', '1', '185', '77', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '323', '59.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('3', '2026-03-20', '2', '396', '154', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '256', '27.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('4', '2026-03-20', '3', '245', '119', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '511', '30.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('5', '2026-03-20', '4', '196', '195', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '599', '55.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('6', '2026-03-20', '5', '205', '93', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '521', '30.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('7', '2026-03-20', '6', '390', '187', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '265', '60.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('8', '2026-03-20', '7', '304', '92', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '67', '20.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('9', '2026-03-20', '8', '111', '97', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '293', '37.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('10', '2026-03-20', '9', '72', '126', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '436', '45.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('11', '2026-03-20', '10', '454', '68', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '291', '60.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('12', '2026-03-20', '11', '399', '56', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '424', '32.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('13', '2026-03-20', '12', '101', '75', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'desktop', 'Chrome', '499', '50.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('14', '2026-03-20', '13', '310', '30', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '270', '63.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('15', '2026-03-20', '14', '462', '63', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '598', '63.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('16', '2026-03-20', '15', '178', '166', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '553', '43.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('17', '2026-03-20', '16', '92', '60', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'desktop', 'Chrome', '575', '66.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('18', '2026-03-20', '17', '394', '63', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '264', '32.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('19', '2026-03-20', '18', '220', '197', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '584', '34.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('20', '2026-03-20', '19', '386', '143', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '143', '51.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('21', '2026-03-20', '20', '262', '70', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '99', '23.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('22', '2026-03-20', '21', '341', '26', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '420', '36.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('23', '2026-03-20', '22', '71', '186', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'mobile', 'Chrome', '487', '42.00', '2026-03-20 08:46:16';
INSERT INTO `traffic_analytics` VALUES ('24', '2026-03-20', '23', '288', '165', '/', 'Direct', NULL, 'Pakistan', 'Karachi', 'tablet', 'Chrome', '242', '31.00', '2026-03-20 08:46:16';


-- Table structure for `trusted_sources`
CREATE TABLE `trusted_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL,
  `source_url` varchar(500) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `source_type` enum('NEWS_MEDIA','GOVERNMENT','ACADEMIC','FACT_CHECK','OFFICIAL','SOCIAL_MEDIA','BLOG','UNKNOWN') DEFAULT 'UNKNOWN',
  `credibility_tier` enum('TIER_1','TIER_2','TIER_3','TIER_4','TIER_5') DEFAULT 'TIER_3',
  `trust_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `reliability_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `accuracy_score` decimal(5,2) NOT NULL DEFAULT 50.00,
  `verified` tinyint(1) DEFAULT 0,
  `verification_date` datetime DEFAULT NULL,
  `verification_method` varchar(50) DEFAULT NULL,
  `reputation_score` decimal(5,2) DEFAULT NULL,
  `fact_checking_frequency` int(11) DEFAULT 0,
  `correction_frequency` int(11) DEFAULT 0,
  `country` varchar(2) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `region` varchar(100) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `founded_year` year(4) DEFAULT NULL,
  `articles_analyzed` int(11) DEFAULT 0,
  `average_credibility` decimal(5,2) DEFAULT NULL,
  `flag_count` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `blacklisted` tinyint(1) DEFAULT 0,
  `whitelist_priority` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_name` (`source_name`),
  UNIQUE KEY `domain_name` (`domain_name`),
  KEY `idx_domain_name` (`domain_name`),
  KEY `idx_trust_score` (`trust_score`),
  KEY `idx_credibility_tier` (`credibility_tier`),
  KEY `idx_active` (`active`),
  KEY `idx_blacklisted` (`blacklisted`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `trusted_sources` VALUES ('1', 'Reuters', 'https://www.reuters.com', 'reuters.com', 'NEWS_MEDIA', 'TIER_1', '95.00', '94.00', '96.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-19 20:38:58', '2026-03-19 20:38:58';
INSERT INTO `trusted_sources` VALUES ('2', 'Associated Press', 'https://www.apnews.com', 'apnews.com', 'NEWS_MEDIA', 'TIER_1', '94.00', '95.00', '93.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-19 20:38:58', '2026-03-19 20:38:58';
INSERT INTO `trusted_sources` VALUES ('3', 'BBC News', 'https://www.bbc.com/news', 'bbc.com', 'NEWS_MEDIA', 'TIER_1', '92.00', '91.00', '93.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-19 20:38:58', '2026-03-19 20:38:58';
INSERT INTO `trusted_sources` VALUES ('4', 'CNN', 'https://www.cnn.com', 'cnn.com', 'NEWS_MEDIA', 'TIER_2', '85.00', '84.00', '86.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-19 20:38:58', '2026-03-19 20:38:58';
INSERT INTO `trusted_sources` VALUES ('5', 'Al Jazeera', 'https://www.aljazeera.com', 'aljazeera.com', 'NEWS_MEDIA', 'TIER_2', '83.00', '82.00', '84.00', '1', NULL, NULL, NULL, '0', '0', NULL, 'en', NULL, NULL, NULL, NULL, '0', NULL, '0', '1', '0', '0', '2026-03-19 20:38:58', '2026-03-19 20:38:58';


-- Table structure for `user_achievements`
CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_type` varchar(50) NOT NULL,
  `achievement_title` varchar(200) NOT NULL,
  `achievement_description` text DEFAULT NULL,
  `achievement_icon` varchar(100) DEFAULT NULL,
  `earned_at` datetime DEFAULT current_timestamp(),
  `points` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_achievements_user_id` (`user_id`),
  KEY `idx_user_achievements_type` (`achievement_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `user_activity`
CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `action` enum('view','share','comment','bookmark','like','dislike') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_news` (`news_id`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_date` (`created_at`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_activity_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for `user_activity_log`
CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_activity_user_id` (`user_id`),
  KEY `idx_user_activity_action` (`action`),
  KEY `idx_user_activity_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_activity_log` VALUES ('1', '1', 'profile_updated', 'Updated basic profile information', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-21 00:25:27';
INSERT INTO `user_activity_log` VALUES ('2', '1', 'profile_updated', 'Updated basic profile information', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-21 00:27:16';
INSERT INTO `user_activity_log` VALUES ('3', '1', 'profile_updated', 'Updated basic profile information', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-21 00:27:30';
INSERT INTO `user_activity_log` VALUES ('4', '1', 'profile_updated', 'Updated basic profile information', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-21 00:28:16';


-- Table structure for `user_language_preferences`
CREATE TABLE `user_language_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `language_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_language` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=492 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_language_preferences` VALUES ('1', '4', 'en', '2026-03-21 22:18:34', '2026-03-21 22:45:31';
INSERT INTO `user_language_preferences` VALUES ('471', '1', 'en', '2026-03-24 17:38:40', '2026-03-24 17:38:50';
INSERT INTO `user_language_preferences` VALUES ('489', '5', 'ur', '2026-04-03 14:00:12', '2026-04-03 14:00:12';


-- Table structure for `user_permissions`
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission` (`user_id`,`permission`),
  KEY `idx_user_permissions_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `user_ratings`
CREATE TABLE `user_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rated_user_id` int(11) NOT NULL,
  `rater_user_id` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review` text DEFAULT NULL,
  `rating_type` enum('article_quality','professionalism','timeliness','accuracy') DEFAULT 'article_quality',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_rating_unique` (`rated_user_id`,`rater_user_id`,`rating_type`),
  KEY `idx_user_ratings_rated_user` (`rated_user_id`),
  KEY `idx_user_ratings_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `user_work_schedule`
CREATE TABLE `user_work_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_schedule_unique` (`user_id`,`day_of_week`),
  KEY `idx_user_work_schedule_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','reporter') DEFAULT 'reporter',
  `department` enum('editorial','reporting','technical','management','marketing','multimedia') DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_level` enum('junior','intermediate','senior','expert','lead') DEFAULT 'junior',
  `skills` text DEFAULT NULL,
  `social_links` text DEFAULT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0,
  `profile_views` int(11) DEFAULT 0,
  `articles_published` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified',
  `preferred_language` varchar(10) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'Asia/Karachi',
  `notification_preferences` text DEFAULT NULL,
  `working_hours` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_role_status` (`role`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES ('1', 'Admin', 'admin@pklivenews.com', NULL, '03118195630', '$2y$10$RpECYttxS37w1aRj5bi.xevmQOAQdF7oCo2AulZ5S7vC1Bdp8Hmfe', 'admin', '', '', '', '', '', 'active', '', NULL, '2026-03-19 19:48:02', '2026-03-21 00:28:16', '2026-03-21 00:28:16', '12', '0', '0', '0', 'unverified', 'en', '', NULL, '';
INSERT INTO `users` VALUES ('2', 'PK News Reporter', 'reporter@pklivenews.com', NULL, NULL, '$2y$10$MI9jd1zeOBTfQxFstciF.OciZfHtkyzmVPUpCuGNKpNuVfAvzf0/G', 'reporter', NULL, NULL, 'junior', NULL, NULL, 'active', NULL, NULL, '2026-03-20 05:53:46', '2026-03-24 16:56:45', NULL, '0', '0', '0', '0', 'unverified', 'en', 'Asia/Karachi', NULL, NULL;
INSERT INTO `users` VALUES ('3', 'PK News Editor', 'editor@pklivenews.com', 'avatar_3_1774531953.jpg', NULL, '$2y$10$Yk5j2ym/nL92tgxF2xpEWeWE8WCThclOJnyVlJ8C7l7/AWe1qF.xm', 'editor', NULL, NULL, 'junior', NULL, NULL, 'active', NULL, NULL, '2026-03-20 05:53:46', '2026-03-26 18:32:33', NULL, '0', '0', '0', '0', 'unverified', 'en', 'Asia/Karachi', NULL, NULL;
INSERT INTO `users` VALUES ('5', 'Salman', 'salman47074@gmail.com', NULL, '+92 3118195630', '$2y$10$fj1uFKy7aXCSb.LNr2ODbOvTsmTX2YOLiMGqKuFpbk/SvbOnzl49S', 'editor', NULL, NULL, 'junior', NULL, NULL, 'active', NULL, NULL, '2026-04-03 13:56:55', '2026-04-03 14:13:32', NULL, '0', '0', '0', '0', 'unverified', 'en', 'Asia/Karachi', NULL, NULL;



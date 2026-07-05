-- PK Live News Database Backup
-- Generated on: 2026-04-27 00:34:51
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
  KEY `ad_id` (`ad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `ad_impressions`
CREATE TABLE `ad_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `impression_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_id` (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_impressions` VALUES ('1', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '/PK-LIVE%20NEWS/index.php', '2026-04-27 00:01:37';
INSERT INTO `ad_impressions` VALUES ('2', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', '/PK-LIVE%20NEWS/index.php?refresh_weather=1', '2026-04-27 00:01:52';
INSERT INTO `ad_impressions` VALUES ('3', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', '/PK-LIVE%20NEWS/index.php?refresh_weather=1', '2026-04-27 00:12:55';
INSERT INTO `ad_impressions` VALUES ('4', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '/PK-LIVE%20NEWS/', '2026-04-27 00:33:16';


-- Table structure for `admin_permissions`
CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`),
  UNIQUE KEY `idx_permission_key` (`permission_key`),
  KEY `idx_permission_group` (`permission_group`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_permissions` VALUES ('1', 'all', 'Full Access', 'general', 'Complete system access', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('2', 'dashboard_view', 'View Dashboard', 'general', 'Access admin dashboard', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('3', 'user_manage', 'Manage Users', 'users', 'Create, edit, delete users', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('4', 'user_view', 'View Users', 'users', 'View user list and details', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('5', 'user_role_assign', 'Assign User Roles', 'users', 'Assign roles to users', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('6', 'content_create', 'Create Content', 'content', 'Create new articles and content', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('7', 'content_edit', 'Edit Content', 'content', 'Edit existing articles and content', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('8', 'content_publish', 'Publish Content', 'content', 'Publish and unpublish content', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('9', 'content_delete', 'Delete Content', 'content', 'Delete articles and content', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('10', 'content_manage', 'Manage All Content', 'content', 'Full content management access', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('11', 'content_moderate', 'Moderate Content', 'content', 'Review and moderate content', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('12', 'role_applications_review', 'Review Role Applications', 'applications', 'Review and approve/reject role applications', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('13', 'role_applications_manage', 'Manage Role Applications', 'applications', 'Full application management access', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('14', 'analytics_view', 'View Analytics', 'analytics', 'Access analytics and reports', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('15', 'reports_view', 'View Reports', 'analytics', 'Access system reports', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('16', 'settings_manage', 'Manage Settings', 'system', 'Access system settings', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('17', 'system_logs', 'View System Logs', 'system', 'Access system logs and audit trails', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('18', 'comments_manage', 'Manage Comments', 'content', 'Manage and moderate comments', '2026-04-10 09:14:56';
INSERT INTO `admin_permissions` VALUES ('19', 'news_articles_manage', 'Manage News Articles', 'content', 'Permission to manage news articles', '2026-04-10 09:31:39';
INSERT INTO `admin_permissions` VALUES ('20', 'polls_manage', 'Manage Polls', 'content', 'Permission to manage polls and surveys', '2026-04-10 09:31:39';


-- Table structure for `admin_roles`
CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_level` int(11) NOT NULL DEFAULT 1,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`),
  UNIQUE KEY `idx_role_name` (`role_name`),
  KEY `idx_role_level` (`role_level`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_roles` VALUES ('1', 'Super Admin', '100', '[\"all\"]', 'Full system access with all permissions', '2026-04-10 09:14:56';
INSERT INTO `admin_roles` VALUES ('2', 'Content Manager', '80', '[\"content_manage\", \"user_manage\", \"analytics_view\", \"role_applications_review\"]', 'Manage content, users, and review applications', '2026-04-10 09:14:56';
INSERT INTO `admin_roles` VALUES ('3', 'Editor', '60', '[\"news_articles_manage\",\"content_edit\",\"comments_manage\",\"polls_manage\",\"analytics_view\"]', 'Editor with content management and publishing permissions', '2026-04-10 09:14:56';
INSERT INTO `admin_roles` VALUES ('4', 'Moderator', '40', '[\"content_moderate\", \"comments_manage\"]', 'Moderate content and manage comments', '2026-04-10 09:14:56';
INSERT INTO `admin_roles` VALUES ('5', 'Reporter', '20', '[\"content_create\", \"comments_manage\"]', 'Create content and manage own comments', '2026-04-10 09:14:56';


-- Table structure for `advertisements`
CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `position` enum('header','sidebar','footer','all','live_header','live_sidebar','live_footer','live_popup','performance_header','performance_sidebar','performance_footer','performance_inline','contact_header','contact_sidebar','contact_footer','category_header','category_sidebar','category_footer','category_inline','home_hero','home_featured','home_sidebar','home_footer','news_inline','search_sidebar','profile_sidebar') DEFAULT 'sidebar',
  `category_id` int(11) DEFAULT NULL,
  `page_type` enum('all','home','category','news','live','contact','search','profile','performance') DEFAULT 'all',
  `device_type` enum('all','desktop','mobile','tablet') DEFAULT 'all',
  `image` varchar(500) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `code` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ad_category` (`category_id`),
  CONSTRAINT `fk_ad_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `advertisements` VALUES ('1', 'Sample Business Ad - Sidebar', 'sidebar', NULL, 'all', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://example-business.com', NULL, 'active', '2026-04-09', '2026-05-09', '2026-04-09 10:48:13', '2026-04-26 23:57:52';
INSERT INTO `advertisements` VALUES ('2', 'Tech Store Promotion', 'header', NULL, 'all', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://techstore.example', NULL, 'active', '2026-04-09', '2026-05-09', '2026-04-09 10:48:13', '2026-04-26 23:57:49';
INSERT INTO `advertisements` VALUES ('3', 'Local Services Ad', 'footer', NULL, 'all', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://localservices.example', NULL, 'active', '2026-04-09', '2026-05-09', '2026-04-09 10:48:13', '2026-04-26 23:57:46';
INSERT INTO `advertisements` VALUES ('4', 'Restaurant Special Offer', 'sidebar', NULL, 'all', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://restaurant.example', NULL, 'active', '2026-04-09', '2026-05-09', '2026-04-09 10:48:13', '2026-04-26 23:57:42';
INSERT INTO `advertisements` VALUES ('5', 'E-commerce Banner', 'all', NULL, 'all', 'all', 'uploads/ads/69adaaa0ab59c.jpg', 'https://shop.example', NULL, 'active', '2026-04-09', '2026-05-09', '2026-04-09 10:48:13', '2026-04-26 23:57:36';
INSERT INTO `advertisements` VALUES ('6', 'Live Stream Banner Ad', 'live_header', NULL, 'live', 'all', NULL, NULL, '<a href=\"https://example.com\"><img src=\"uploads/ads/live-banner.jpg\" alt=\"Live Stream Ad\" style=\"width:100%;height:90px;\"></a>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('7', 'Performance Analysis Widget', 'performance_sidebar', NULL, 'performance', 'all', NULL, NULL, '<div style=\"background:#f0f0f0;padding:10px;border:1px solid #ccc;\"><h4>Performance Tools</h4><a href=\"https://tools.example.com\">Try our Analytics</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('8', 'Contact Page Service Ad', 'contact_sidebar', NULL, 'contact', 'all', NULL, NULL, '<div style=\"background:#e8f4f8;padding:15px;border-radius:5px;\"><h3>Professional Services</h3><p>Get expert help with your projects</p><a href=\"https://services.example.com\" class=\"btn btn-primary\">Learn More</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('9', 'Category Featured Ad', 'category_header', NULL, 'category', 'all', NULL, NULL, '<div style=\"background:linear-gradient(45deg,#ff6b6b,#4ecdc4);color:white;padding:20px;text-align:center;\"><h2>Special Category Offer</h2><p>Exclusive deals for this category</p></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('10', 'Home Hero Banner', 'home_hero', NULL, 'home', 'all', NULL, NULL, '<div style=\"background:url(uploads/ads/hero-bg.jpg) center/cover;height:300px;display:flex;align-items:center;justify-content:center;\"><div style=\"background:rgba(0,0,0,0.7);color:white;padding:30px;border-radius:10px;\"><h1>Big Sale Event</h1><p>Limited time offers</p></div></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('11', 'News Inline Ad', 'news_inline', NULL, 'news', 'all', NULL, NULL, '<div style=\"border:1px solid #ddd;padding:10px;margin:10px 0;background:#f9f9f9;\"><p><strong>Sponsored Content:</strong> Check out these amazing products!</p><a href=\"https://shop.example.com\">Shop Now</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:32:24', '2026-04-27 00:32:24';
INSERT INTO `advertisements` VALUES ('12', 'Live Stream Banner Ad', 'live_header', NULL, 'live', 'all', NULL, NULL, '<a href=\"https://example.com\"><img src=\"uploads/ads/live-banner.jpg\" alt=\"Live Stream Ad\" style=\"width:100%;height:90px;\"></a>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';
INSERT INTO `advertisements` VALUES ('13', 'Performance Analysis Widget', 'performance_sidebar', NULL, 'performance', 'all', NULL, NULL, '<div style=\"background:#f0f0f0;padding:10px;border:1px solid #ccc;\"><h4>Performance Tools</h4><a href=\"https://tools.example.com\">Try our Analytics</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';
INSERT INTO `advertisements` VALUES ('14', 'Contact Page Service Ad', 'contact_sidebar', NULL, 'contact', 'all', NULL, NULL, '<div style=\"background:#e8f4f8;padding:15px;border-radius:5px;\"><h3>Professional Services</h3><p>Get expert help with your projects</p><a href=\"https://services.example.com\" class=\"btn btn-primary\">Learn More</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';
INSERT INTO `advertisements` VALUES ('15', 'Category Featured Ad', 'category_header', NULL, 'category', 'all', NULL, NULL, '<div style=\"background:linear-gradient(45deg,#ff6b6b,#4ecdc4);color:white;padding:20px;text-align:center;\"><h2>Special Category Offer</h2><p>Exclusive deals for this category</p></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';
INSERT INTO `advertisements` VALUES ('16', 'Home Hero Banner', 'home_hero', NULL, 'home', 'all', NULL, NULL, '<div style=\"background:url(uploads/ads/hero-bg.jpg) center/cover;height:300px;display:flex;align-items:center;justify-content:center;\"><div style=\"background:rgba(0,0,0,0.7);color:white;padding:30px;border-radius:10px;\"><h1>Big Sale Event</h1><p>Limited time offers</p></div></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';
INSERT INTO `advertisements` VALUES ('17', 'News Inline Ad', 'news_inline', NULL, 'news', 'all', NULL, NULL, '<div style=\"border:1px solid #ddd;padding:10px;margin:10px 0;background:#f9f9f9;\"><p><strong>Sponsored Content:</strong> Check out these amazing products!</p><a href=\"https://shop.example.com\">Shop Now</a></div>', 'active', '2026-04-27', '2026-05-27', '2026-04-27 00:33:13', '2026-04-27 00:33:13';


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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affiliate_categories` VALUES ('1', 'Electronics', 'electronics', 'Mobile phones, laptops, gadgets', 'fa-laptop', NULL, '1', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('2', 'Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', NULL, '2', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('3', 'Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', NULL, '3', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('4', 'Gaming', 'gaming', 'Gaming consoles and accessories', 'fa-gamepad', NULL, '4', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('5', 'Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', NULL, '5', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('6', 'Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', NULL, '6', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('7', 'Smart Home', 'smart-home', 'Smart home devices and IoT', 'fa-home', NULL, '7', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('8', 'Fashion', 'fashion', 'Clothing and accessories', 'fa-tshirt', NULL, '8', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('9', 'Sports', 'sports', 'Sports equipment and gear', 'fa-football-ball', NULL, '9', 'active', '2026-04-09 10:57:31';
INSERT INTO `affiliate_categories` VALUES ('10', 'Books', 'books', 'Books and educational materials', 'fa-book', NULL, '10', 'active', '2026-04-09 10:57:31';


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
  KEY `converted` (`converted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
  CONSTRAINT `fk_affiliate_products_category` FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `alert_categories`
CREATE TABLE `alert_categories` (
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



-- Table structure for `approved_comments_view`
-- Debug: Available keys: View, Create View, character_set_client, collation_connection
-- Error: Could not find Create Table key for `approved_comments_view`

-- Table structure for `articles`
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_type` varchar(50) DEFAULT 'standard',
  `source` varchar(255) DEFAULT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `status` enum('published','draft','pending') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `breaking_news` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



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
  KEY `fk_category_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` VALUES ('2', 'Politics', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'politics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-09 10:34:15', NULL;
INSERT INTO `categories` VALUES ('3', 'Sports', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'sports', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-26 22:48:04', NULL;
INSERT INTO `categories` VALUES ('4', 'Technology', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'technology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-13 01:31:21', NULL;
INSERT INTO `categories` VALUES ('5', 'Business', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'business', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-13 01:31:11', NULL;
INSERT INTO `categories` VALUES ('6', 'Entertainment', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'entertainment', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-13 01:31:13', NULL;
INSERT INTO `categories` VALUES ('7', 'Health', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'health', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-13 01:31:14', NULL;
INSERT INTO `categories` VALUES ('8', 'Education', NULL, NULL, NULL, NULL, '#007bff', 'fas fa-newspaper', 'education', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-04-09 10:34:15', '2026-04-13 00:22:58', NULL;


-- Table structure for `category_analytics`
CREATE TABLE `category_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `click_count` int(11) DEFAULT 0,
  `article_count` int(11) DEFAULT 0,
  `date_recorded` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_date` (`category_id`,`date_recorded`),
  KEY `category_id` (`category_id`),
  KEY `date_recorded` (`date_recorded`)
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
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `channels` VALUES ('4', 'Dunya News', 'news', 'https://youtu.be/e2XVSUYh4S0', 'youtube', NULL, 'Leading Pakistani news channel with breaking news and current affairs', 'live', '1295', 'urdu', 'PK', '6', '1', NULL, '2026-04-10 01:52:07', '2026-04-24 00:51:58';
INSERT INTO `channels` VALUES ('5', 'Samaa TV', 'news', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, '24/7 Pakistani news channel with comprehensive coverage', 'live', '3819', 'urdu', 'PK', '7', '1', NULL, '2026-04-10 01:52:07', '2026-04-10 01:57:12';
INSERT INTO `channels` VALUES ('6', 'Express News', 'news', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Fast-paced Pakistani news channel with in-depth analysis', 'live', '4354', 'urdu', 'PK', '8', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('7', '92 News HD', 'news', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'HD Pakistani news channel with modern presentation', 'live', '3015', 'urdu', 'PK', '9', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('8', 'PTV Sports', 'sports', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Pakistan\'s premier sports channel featuring cricket, hockey, and more', 'live', '4112', 'urdu', 'PK', '10', '1', NULL, '2026-04-10 01:52:07', '2026-04-10 01:57:13';
INSERT INTO `channels` VALUES ('9', 'Ten Sports', 'sports', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'International sports channel with live matches and highlights', 'live', '1484', 'english', 'PK', '11', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 07:11:57';
INSERT INTO `channels` VALUES ('10', 'Hum TV', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Popular Pakistani entertainment channel with dramas and shows', 'live', '1457', 'urdu', 'PK', '12', '1', NULL, '2026-04-10 01:52:07', '2026-04-10 01:57:07';
INSERT INTO `channels` VALUES ('11', 'ARY Digital', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Leading Pakistani entertainment channel with premium dramas', 'live', '2450', 'urdu', 'PK', '13', '1', NULL, '2026-04-10 01:52:07', '2026-04-10 01:55:31';
INSERT INTO `channels` VALUES ('12', 'Geo TV', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Popular Pakistani entertainment and drama channel', 'live', '3137', 'urdu', 'PK', '14', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('13', 'Business Plus', 'business', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Pakistani business news and financial analysis channel', 'live', '1948', 'urdu', 'PK', '15', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('14', 'BBC World News', 'international', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'International news from BBC', 'live', '1690', 'english', 'PK', '9', '1', NULL, '2026-04-10 01:52:07', '2026-04-10 07:11:05';
INSERT INTO `channels` VALUES ('15', 'CNN International', 'international', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, '24/7 international news coverage', 'live', '4015', 'english', 'PK', '10', '1', NULL, '2026-04-10 01:52:07', '2026-04-24 00:56:34';
INSERT INTO `channels` VALUES ('16', 'Al Jazeera English', 'international', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Middle Eastern perspective on global news and events', 'live', '4796', 'english', 'PK', '18', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('17', 'Peace TV', '', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Islamic religious and educational content', 'live', '3066', 'english', 'PK', '19', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('18', 'ATV Music', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Pakistani music channel with latest songs and performances', 'live', '1990', 'urdu', 'PK', '20', '0', NULL, '2026-04-10 01:52:07', '2026-04-10 01:52:07';
INSERT INTO `channels` VALUES ('19', 'Geo News Live', 'news', 'https://youtu.be/PNpD_wM1GVE', 'youtube', NULL, 'Pakistan leading news channel with 24/7 coverage', 'live', '1293', 'urdu', 'PK', '1', '1', NULL, '2026-04-10 07:11:04', '2026-04-24 00:57:20';
INSERT INTO `channels` VALUES ('20', 'ARY News Live', 'news', 'https://youtu.be/5QfmfJySn44', 'youtube', NULL, 'Fast-paced news with comprehensive coverage', 'live', '1869', 'urdu', 'PK', '2', '1', NULL, '2026-04-10 07:11:04', '2026-04-24 00:50:02';
INSERT INTO `channels` VALUES ('21', 'Dunya News Live', 'news', 'https://youtu.be/e2XVSUYh4S0', 'youtube', NULL, 'Breaking news and current affairs', 'live', '4385', 'urdu', 'PK', '3', '1', NULL, '2026-04-10 07:11:04', '2026-04-24 00:56:23';
INSERT INTO `channels` VALUES ('22', 'Samaa TV Live', 'news', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, '24/7 news and analysis', 'live', '1240', 'urdu', 'PK', '4', '0', NULL, '2026-04-10 07:11:04', '2026-04-13 10:32:02';
INSERT INTO `channels` VALUES ('23', 'Express News Live', 'news', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'News with in-depth analysis', 'live', '3944', 'urdu', 'PK', '5', '0', NULL, '2026-04-10 07:11:04', '2026-04-10 07:11:04';
INSERT INTO `channels` VALUES ('24', 'PTV Sports Live', 'sports', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Pakistan sports channel with live matches', 'live', '3112', 'urdu', 'PK', '6', '1', NULL, '2026-04-10 07:11:04', '2026-04-10 07:11:04';
INSERT INTO `channels` VALUES ('25', 'Hum TV Live', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Popular Pakistani entertainment channel', 'live', '2099', 'urdu', 'PK', '7', '1', NULL, '2026-04-10 07:11:04', '2026-04-10 07:11:04';
INSERT INTO `channels` VALUES ('26', 'ARY Digital Live', 'entertainment', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'youtube', NULL, 'Premium dramas and entertainment shows', 'live', '2589', 'urdu', 'PK', '8', '1', NULL, '2026-04-10 07:11:04', '2026-04-10 07:11:04';
INSERT INTO `channels` VALUES ('28', 'BBC News', 'news', 'https://www.youtube.com/watch?v=wGBzr_8qPm4', 'youtube', NULL, 'BBC News - Live news stream from UK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('29', 'CNN', 'news', 'https://www.youtube.com/watch?v=wuBfSOMcHqQ', 'youtube', NULL, 'CNN - Live news stream from USA', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('30', 'Al Jazeera', 'news', 'https://youtu.be/bNyUyrR0PHo', 'youtube', NULL, 'Al Jazeera - Live news stream from Qatar', 'live', '8', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-26 23:16:51';
INSERT INTO `channels` VALUES ('31', 'Reuters', 'news', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'youtube', NULL, 'Reuters - Live news stream from UK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('32', 'Fox News', 'news', 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'youtube', NULL, 'Fox News - Live news stream from USA', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('33', 'MSNBC', 'news', 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'youtube', NULL, 'MSNBC - Live news stream from USA', 'live', '1', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:45';
INSERT INTO `channels` VALUES ('34', 'NBC News', 'news', 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'youtube', NULL, 'NBC News - Live news stream from USA', 'live', '1', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:53';
INSERT INTO `channels` VALUES ('35', 'CBS News', 'news', 'https://www.youtube.com/watch?v=9bZkp7q19f0', 'youtube', NULL, 'CBS News - Live news stream from USA', 'live', '1', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:57';
INSERT INTO `channels` VALUES ('36', 'ABC News', 'news', 'https://youtu.be/P49mKO-tTNk', 'youtube', NULL, 'ABC News - Live news stream from USA', 'live', '5', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-25 08:29:20';
INSERT INTO `channels` VALUES ('37', 'The Guardian', 'news', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'youtube', NULL, 'The Guardian - Live news stream from UK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('38', 'The Times', 'news', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'youtube', NULL, 'The Times - Live news stream from UK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('39', 'France 24', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'France 24 - Live news stream from France', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('40', 'Deutsche Welle', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Deutsche Welle - Live news stream from Germany', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('41', 'RT News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'RT News - Live news stream from Russia', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('42', 'Le Monde', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Le Monde - Live news stream from France', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('43', 'Der Spiegel', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Der Spiegel - Live news stream from Germany', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('44', 'Corriere della Sera', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Corriere della Sera - Live news stream from Italy', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('45', 'El Pais', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'El Pais - Live news stream from Spain', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('46', 'CCTV', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'CCTV - Live news stream from China', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('47', 'NDTV', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'NDTV - Live news stream from India', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('48', 'Times of India', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Times of India - Live news stream from India', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('49', 'The Hindu', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'The Hindu - Live news stream from India', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('50', 'Japan Times', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Japan Times - Live news stream from Japan', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('51', 'Sydney Morning Herald', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Sydney Morning Herald - Live news stream from Australia', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('52', 'The Age', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'The Age - Live news stream from Australia', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('53', 'Toronto Star', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Toronto Star - Live news stream from Canada', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('54', 'CBC News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'CBC News - Live news stream from Canada', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('55', 'Globo News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Globo News - Live news stream from Brazil', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('56', 'The Jerusalem Post', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'The Jerusalem Post - Live news stream from Israel', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('57', 'Al Arabiya', 'news', 'https://youtu.be/n7eQejkXbnM', 'youtube', NULL, 'Al Arabiya - Live news stream from Saudi Arabia', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 01:01:45';
INSERT INTO `channels` VALUES ('58', 'Arab News', 'news', 'https://youtu.be/rXnG4eiVVdM', 'youtube', NULL, 'Arab News - Live news stream from Saudi Arabia', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 01:04:09';
INSERT INTO `channels` VALUES ('59', 'Daily Sabah', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Daily Sabah - Live news stream from Turkey', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('60', 'Hurriyet', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Hurriyet - Live news stream from Turkey', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('61', 'Dawn News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Dawn News - Live news stream from PK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('62', 'The News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'The News - Live news stream from PK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('63', 'Express Tribune', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Express Tribune - Live news stream from PK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('64', 'Geo News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'Geo News - Live news stream from PK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('65', 'ARY News', 'news', 'https://www.youtube.com/watch?v=ojX6k_6-8dI', 'youtube', NULL, 'ARY News - Live news stream from PK', 'live', '0', 'en', '0', '0', '0', NULL, '2026-04-24 00:56:06', '2026-04-24 00:56:06';
INSERT INTO `channels` VALUES ('66', 'Bkuc', 'news', 'https://web.facebook.com/reel/26453739217608751', 'youtube', NULL, '', 'live', '2', 'en', 'PK', '0', '1', NULL, '2026-04-24 01:19:07', '2026-04-24 01:42:49';


-- Table structure for `comment_likes`
CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `like_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comment_user` (`comment_id`,`user_id`),
  UNIQUE KEY `unique_comment_ip` (`comment_id`,`ip_address`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `comment_reports`
CREATE TABLE `comment_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reporter_ip` varchar(45) DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comment_id` (`comment_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `fk_comment_reports_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_comment_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_reports_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_comment_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `comments`
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected','spam') NOT NULL DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `dislikes_count` int(11) NOT NULL DEFAULT 0,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_news_status` (`news_id`,`status`),
  KEY `idx_comments_news_created` (`news_id`,`created_at`),
  KEY `idx_comments_parent_created` (`parent_id`,`created_at`),
  CONSTRAINT `fk_comments_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` VALUES ('22', '21', '1', NULL, 'Admin', 'admin@pklivenews.com', 'hi', 'approved', NULL, NULL, '0', '0', '0', NULL, '2026-04-24 00:23:01', '2026-04-24 00:23:01';


-- Table structure for `content_patterns`
CREATE TABLE `content_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(255) NOT NULL,
  `pattern_type` enum('sensationalism','bias','misinformation','clickbait','propaganda') NOT NULL,
  `pattern_regex` text DEFAULT NULL,
  `pattern_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pattern_keywords`)),
  `confidence_weight` decimal(3,2) DEFAULT 0.50,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pattern_type` (`pattern_type`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `content_patterns` VALUES ('1', 'Breaking News Alert', 'sensationalism', NULL, '[\"breaking\",\"urgent\",\"alert\",\"shocking\"]', '0.70', 'Detects sensational breaking news language', '1', '2026-04-09 19:15:39', '2026-04-09 19:15:39';
INSERT INTO `content_patterns` VALUES ('2', 'Clickbait Headlines', 'clickbait', NULL, '[\"you won\'t believe\",\"shocking\",\"revealed\",\"secret\"]', '0.75', 'Detects clickbait headline patterns', '1', '2026-04-09 19:15:39', '2026-04-09 19:15:39';
INSERT INTO `content_patterns` VALUES ('3', 'Conspiracy Language', 'misinformation', NULL, '[\"conspiracy\",\"cover up\",\"hidden truth\",\"they don\'t want you to know\"]', '0.80', 'Detects conspiracy theory language', '1', '2026-04-09 19:15:39', '2026-04-09 19:15:39';
INSERT INTO `content_patterns` VALUES ('4', 'Emotional Manipulation', 'bias', NULL, '[\"outrageous\",\"disgusting\",\"horrifying\",\"unbelievable\"]', '0.65', 'Detects emotionally manipulative language', '1', '2026-04-09 19:15:39', '2026-04-09 19:15:39';
INSERT INTO `content_patterns` VALUES ('5', 'Unverified Claims', 'misinformation', NULL, '[\"sources say\",\"rumors suggest\",\"allegedly\",\"reportedly\"]', '0.60', 'Detects unverified claim indicators', '1', '2026-04-09 19:15:39', '2026-04-09 19:15:39';


-- Table structure for `edition_articles`
CREATE TABLE `edition_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `edition_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_edition_article` (`edition_id`,`article_id`),
  KEY `idx_edition_id` (`edition_id`),
  KEY `idx_article_id` (`article_id`),
  CONSTRAINT `edition_articles_ibfk_1` FOREIGN KEY (`edition_id`) REFERENCES `news_editions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `edition_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `edition_templates`
CREATE TABLE `edition_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_html` longtext DEFAULT NULL,
  `css_styles` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_default` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `edition_templates` VALUES ('1', 'Default Template', 'Default newspaper edition template', '<div class=\"edition-header\">\n        <h1>{{edition_title}}</h1>\n        <p class=\"edition-date\">{{edition_date}}</p>\n    </div>\n    <div class=\"edition-content\">\n        {{articles_loop}}\n        <div class=\"article\">\n            <h3>{{article_title}}</h3>\n            <p>{{article_summary}}</p>\n        </div>\n        {{articles_loop_end}}\n    </div>', '.edition-header { text-align: center; margin-bottom: 30px; }\n    .edition-content { margin: 20px 0; }\n    .article { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; }', '1', '2026-04-09 10:45:06', '2026-04-09 10:45:06';


-- Table structure for `events`
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `type` enum('conference','meeting','webinar','workshop','social','sports','political','other') DEFAULT 'other',
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `current_attendees` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 1,
  `requires_registration` tinyint(1) DEFAULT 0,
  `registration_deadline` datetime DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_date` (`event_date`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `category` (`category`),
  KEY `priority` (`priority`),
  KEY `created_by` (`created_by`)
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `languages` VALUES ('1', 'en', 'English', 'English', 'us', '1', '1', '2026-04-09 11:00:30';
INSERT INTO `languages` VALUES ('2', 'ur', 'Urdu', ' Urdu', 'pk', '1', '2', '2026-04-09 11:00:30';
INSERT INTO `languages` VALUES ('3', 'hi', 'Hindi', ' ', 'in', '1', '3', '2026-04-09 11:00:30';
INSERT INTO `languages` VALUES ('4', 'zh', 'Chinese', ' ', 'cn', '1', '4', '2026-04-09 11:00:30';
INSERT INTO `languages` VALUES ('5', 'ps', 'Pashto', ' ', 'af', '1', '5', '2026-04-09 11:00:30';


-- Table structure for `live_chat`
CREATE TABLE `live_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `live_chat` VALUES ('1', '28', 'Guest', 'go', '2026-04-24 10:24:43', '0';


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
  `stopped_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `video_path` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `status` enum('published','draft','featured','archived') NOT NULL DEFAULT 'published',
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `news_type` varchar(50) DEFAULT 'article',
  `views` int(11) NOT NULL DEFAULT 0,
  `likes_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `engagement_score` decimal(10,2) DEFAULT 0.00,
  `share_count` int(11) DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `shares` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sentiment_score` decimal(3,2) DEFAULT 0.00,
  `sentiment_label` varchar(20) DEFAULT 'neutral',
  `summary_only` tinyint(1) DEFAULT 0,
  `image_type` enum('manual','rss','ai','scraped') DEFAULT 'manual',
  `media_type` enum('text','image','video') DEFAULT 'text',
  `source_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_news_type` (`news_type`),
  KEY `idx_news_status` (`status`,`news_type`),
  KEY `idx_news_created_at` (`created_at`),
  KEY `idx_news_source_url` (`source_url`(255)),
  KEY `fk_news_channel` (`channel_id`),
  CONSTRAINT `fk_news_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news` VALUES ('6', 'Muhammad Kashif: Lead Developer &amp; System Architect', 'muhammad-kashif-lead-developer-system-architect', '<p class=\"text-white-50 mb-4\">Expert in PHP, MySQL, and modern web technologies. Developed comprehensive news management system with 40+ news channels, live broadcasting, weather integration, voting/polling systems, product sharing, and job portal.</p>\r\n<div class=\"tech-skills mb-3\"><span class=\"badge bg-primary me-2 mb-2\">PHP 8+</span>&nbsp;<span class=\"badge bg-success me-2 mb-2\">MySQL</span>&nbsp;<span class=\"badge bg-info me-2 mb-2\">JavaScript</span>&nbsp;<span class=\"badge bg-warning me-2 mb-2\">Bootstrap</span></div>\r\n<div class=\"achievements\">\r\n<h6 class=\"text-white mb-3\">🏆 Key Achievements:</h6>\r\n<ul class=\"text-start text-white-50\">\r\n<li>40+ News Channel Integration</li>\r\n<li>Live Broadcasting System</li>\r\n<li>Weather API Integration</li>\r\n<li>Real-time Voting &amp; Polling</li>\r\n<li>Product Sharing Platform</li>\r\n<li>Job Portal Development</li>\r\n</ul>\r\n</div>', 'Expert in PHP, MySQL, and modern web technologies. Developed comprehensive news management system with 40+ news channels, live broadcasting, weather integration, voting/polling systems, product sharing, and job portal.\r\n\r\nPHP 8+ MySQL JavaScript Bootstrap\r\n🏆 Key Achievements:\r\n40+ News Channel Integration\r\nLive Broadcasting System\r\nWeather API Integration\r\nReal-time Voting &amp; Polling\r\nProduct Sharing Platform\r\nJob Portal Development', 'uploads/news/69d85af48f0a4.jpg', '', NULL, '2', NULL, NULL, 'https://example.com/political-news', 'published', '1', 'article', '7', '0', '0', '0.00', '0', '0', '0', '2026-04-11 07:04:41', '2026-04-09 10:41:28', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('9', 'Muhammad Ibraheem: Project Manager &amp;amp; UX Designer', 'muhammad-ibraheem-project-manager-ux-designer', '<p class=\"text-white-50 mb-4\">Experienced in project management and system architecture. Ensuring quality delivery of complex news platforms with focus on user experience, performance optimization, and advanced feature integration.</p>\r\n<div class=\"tech-skills mb-3\"><span class=\"badge bg-primary me-2 mb-2\">Project Mgmt</span>&nbsp;<span class=\"badge bg-success me-2 mb-2\">UI/UX</span>&nbsp;<span class=\"badge bg-info me-2 mb-2\">Analytics</span>&nbsp;<span class=\"badge bg-warning me-2 mb-2\">SEO</span></div>\r\n<div class=\"achievements\">\r\n<h6 class=\"text-white mb-3\">🎯 Project Excellence:</h6>\r\n<ul class=\"text-start text-white-50\">\r\n<li>Multi-Channel News Management</li>\r\n<li>Advanced Admin Panel</li>\r\n<li>User Engagement Systems</li>\r\n<li>Security Implementation</li>\r\n<li>Performance Optimization</li>\r\n<li>Mobile Responsive Design</li>\r\n</ul>\r\n</div>', 'Experienced in project management and system architecture. Ensuring quality delivery of complex news platforms with focus on user experience, performance optimization, and advanced feature integration.\r\n\r\nProject Mgmt UI/UX Analytics SEO\r\n🎯 Project Excellence:\r\nMulti-Channel News Management\r\nAdvanced Admin Panel\r\nUser Engagement Systems\r\nSecurity Implementation\r\nPerformance Optimization\r\nMobile Responsive Design', 'uploads/news/img_69d7b42d3c61c_1775744045.jpg', '', '', '2', NULL, '1', NULL, 'published', '1', 'article', '14', '0', '0', '0.00', '0', '0', '0', '2026-04-11 07:02:31', '2026-04-09 19:14:05', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('12', '🚨پشاور زلمی کے نام بڑا ریکارڈ🚨', '', 'بابر اعظم اور کوسل مینڈس کا کراچی کنگز کے خلاف شاندار مظاہرہ، ایچ بی ایل پی ایس ایل میں اب تک کی سب سے زیادہ شراکت کا ریکارڈ قائم🙌🔥', 'بابر اعظم اور کوسل مینڈس کا کراچی کنگز کے خلاف شاندار مظاہرہ، ایچ بی ایل پی ایس ایل میں اب تک کی سب سے زیادہ شرا?...', 'uploads/news/img_69d9084daca67_1775831117.jpeg', '', '', '2', NULL, '8', NULL, 'published', '0', 'article', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-10 19:25:17', '2026-04-10 19:25:17', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('15', 'Hawaii’s Kilauea erupts, blasting lava sky-high', 'hawaii-s-kilauea-erupts-blasting-lava-sky-high', '<p>Lava shot into the air, illuminating the night sky, as <strong>Hawaii\'s Kīlauea volcano erupted</strong>, with fountains reaching up to 190 metres.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Lava shot into the air, illuminating the night sky, as Hawaiis Klauea volcano erupted.', 'uploads/news/69d9a7255546b.png', '', NULL, '2', NULL, '1', 'https://www.aljazeera.com/video/newsfeed/2026/4/10/hawaiis-kilauea-erupts-blasting-lava-sky-high?traffic_source=rss', 'published', '1', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-11 06:43:08', '2026-04-10 22:30:03', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('16', 'EU airline industry warns of fuel shortages if Strait of Hormuz stays closed', 'eu-airline-industry-warns-of-fuel-shortages-if-strait-of-hormuz-stays-closed', '<p class=\"sc-1a18e57c-0 HooNV\">Europe will suffer jet fuel shortages in just three weeks if the the Strait of Hormuz does not reopen, the trade body for the continent\'s airports has warned.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">The Persian Gulf is a major source of aviation fuel, accounting for about 50% of Europe\'s imports.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">Airports Council International (ACI) Europe said its members had \"increasing concerns\" about the availability of jet fuel, particularly with the approach of the summer tourism season.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\r\n<p><strong><a href=\"https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The trade body for European airports said if the Strait of Hormuz did not open in the next three weeks, there could be shortages.', 'uploads/news/69d93c0fb24fc.webp', '', NULL, '2', NULL, '1', 'https://www.bbc.com/news/articles/c3w37ggp011o?at_medium=RSS&at_campaign=rss', 'published', '1', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-11 06:11:01', '2026-04-10 22:30:10', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('18', 'Back to Earth: What happens to the Artemis II astronauts now?', 'back-to-earth-what-happens-to-the-artemis-ii-astronauts-now', '<p class=\"sc-1a18e57c-0 HooNV\">The Artemis II crew have safely returned home after re-entering Earth\'s atmosphere at 25,000mph (40,000km/h), splashing down off the coast of California.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">They have travelled deeper into space than any humans before them - just over 4,000 miles more than the record of 248,655 set by Apollo 13 in 1970.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">Astronauts are highly trained to cope with the physical and mental strain of space.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">Although it might seem like it would be a difficult experience to endure, astronauts talk about being in space as the highlight of their lives and say they would return in an instant.</p>\r\n<p class=\"sc-1a18e57c-0 HooNV\">In a press conference before landing, Christina Koch said the inconveniences, such as freeze-dried food or a toilet without much privacy, were worth it.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\r\n<p><strong><a href=\"https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&amp;at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The astronauts will have medical checks and will be reunited with their families.', 'uploads/news/69d9a8480d1d0.webp', '', NULL, '2', NULL, '1', 'https://www.bbc.com/news/articles/cpwjvgv2d4no?at_medium=RSS&at_campaign=rss', 'published', '1', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-11 06:54:55', '2026-04-11 06:43:29', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('19', 'Wiseman joins his fellow crew members on front porch', 'iseman-joins-his-fellow-crew-members-on-front-porch', 'Commander Reid Wiseman has now exited the Orion module, shortly after the third crew member left the spacecraft.\r\nAll the astronauts are now waiting on the front porch to be escorted by two Navy helicopters.', 'Commander Reid Wiseman has now exited the Orion module, shortly after the third crew member left the spacecraft.\r\nAll the astronauts are now waiting on the front porch to be escorted by two Navy helic...', 'uploads/news/img_69d9a901d7095_1775872257.webp', '', '', '2', NULL, '1', NULL, 'published', '0', 'article', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-11 06:50:57', '2026-04-11 06:50:57', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('20', 'Rawalpindiz vs Quetta Gladiators', 'awalpindiz-vs-uetta-ladiators', '#RileeRossouw&#039;s half-century backed by a complete bowling performance kept Rawalpindiz winless in PSL 11. 🇵🇰💥', '#RileeRossouw&#039;s half-century backed by a complete bowling performance kept Rawalpindiz winless in PSL 11. 🇵🇰💥...', 'uploads/news/img_69d9ae50ae230_1775873616.jpeg', '', '', '2', NULL, '1', NULL, 'published', '0', 'article', '2', '0', '0', '0.00', '0', '0', '0', '2026-04-11 07:13:36', '2026-04-11 07:13:36', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('21', 'Pakistan ambassador speaks to Al Jazeera on eve of US-Iran talks', 'pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks', '<p><strong>Islamabad, Pakistan &ndash;&nbsp;</strong>Pavements&nbsp;are being painted, an already formidable security presence is being bolstered, and an air of anticipation &mdash; and anxiety &mdash; is gripping Pakistan&rsquo;s capital as it prepares to host meetings that the world will watch this weekend.</p>\r\n<p>Exactly six weeks after the United States and Israel launched coordinated strikes on Iran that&nbsp;<a href=\"https://www.aljazeera.com/news/2026/2/28/irans-supreme-leader-ali-khamenei-killed-in-us-israeli-attacks-reports\">killed Supreme Leader Ayatollah Ali Khamenei</a>, set off a war that has killed thousands of people across multiple countries, shut down the world&rsquo;s most critical oil passage and sent energy prices soaring, Islamabad will on Saturday host talks involving top US and Iranian officials.</p>\r\n<section class=\"more-on\">\r\n<h2 class=\"more-on__heading\">Recommended Stories</h2>\r\n<span class=\"screen-reader-text\">list of 4 items</span>\r\n<ul class=\"more-on__list\">\r\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 1 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/features/2026/4/8/how-pakistan-managed-to-get-the-us-and-iran-to-a-ceasefire\">How Pakistan managed to get the US and Iran to a ceasefire</a></li>\r\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 2 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/4/7/why-jd-vance-joined-pakistans-last-ditch-us-iran-mediation-efforts\">Why JD Vance joined Pakistan&rsquo;s last-ditch US-Iran mediation efforts</a></li>\r\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 3 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/3/27/nixon-to-trump-pakistans-long-record-as-backchannel-between-rival-powers\">Nixon to Trump: Pakistan&rsquo;s long record as backchannel between rival powers</a></li>\r\n<li class=\"more-on__article\"><span class=\"screen-reader-text\">list 4 of 4</span><a class=\"more-on__link\" href=\"https://www.aljazeera.com/news/2026/3/31/will-china-join-pakistan-led-efforts-to-mediate-us-iran-peace\">Will China join Pakistan-led efforts to mediate US-Iran peace?</a></li>\r\n</ul>\r\n<span class=\"screen-reader-text\">end of list</span></section>\r\n<p>The meetings come days after both Washington and Tehran agreed to a Pakistan-mediated two-week ceasefire, and at a time when that truce is already under strain amid different interpretations of the terms of the pause in fighting &mdash; and Israel&rsquo;s intensified bombing of Lebanon.</p>\r\n<p>Iran&rsquo;s attacks on its Gulf neighbours, apart from Israel, amid the war have also left the world&rsquo;s biggest energy export hub and a critical nerve centre of trade, tourism and innovation on edge since the fighting started on February 28. Tehran&rsquo;s decision soon after to in effect shut down the Strait of Hormuz &mdash; through which 20 percent of the world&rsquo;s oil and gas passes during peacetime &mdash; except to ships from countries that negotiated deals with it, rattled global markets and drove energy prices to record highs.</p>\r\n<p>This coming weekend, senior representatives from key players in the war will converge in Pakistan&rsquo;s leafy capital in the lower reaches of the Margalla Hills.</p>\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Pakistans ambassador to the US said weeks of intense diplomatic efforts have led to a shared commitment from all sides.', 'uploads/news/69d9d5355e60c.jpg', '', NULL, '2', NULL, '1', 'https://www.aljazeera.com/video/newsfeed/2026/4/11/pakistan-ambassador-speaks-to-al-jazeera-on-eve-of-us-iran-talks?traffic_source=rss', 'published', '1', 'rss_import', '3', '0', '0', '0.00', '0', '0', '0', '2026-04-11 09:59:56', '2026-04-11 09:52:21', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('22', 'A shadowy, pro-Iranian group claimed a spate of attacks in Europe. But it might be a facade', 'shadowy-pro-ranian-group-claimed-a-spate-of-attacks-in-urope-ut-it-might-be-a-facade', '<p>London &mdash; A shadowy, pro-Iran group has claimed responsibility for a spate of recent attacks on Jewish communities and American interests in Europe. The incidents, which the group posted about via social media accounts affiliated with pro-Iranian militias, include an arson attack on Jewish community-run ambulances in the United Kingdom, an explosive device detonated in front of a synagogue in Belgium and a foiled attack on a Bank of America office in France.</p>', 'London —  A shadowy, pro-Iran group has claimed responsibility for a spate of recent attacks on Jewish communities and American interests in Europe.\r\nThe incidents, which the group posted about via ...', 'uploads/news/69d9d68b1e728.png', '', '', '2', NULL, '1', NULL, 'published', '1', 'article', '2', '0', '0', '0.00', '0', '0', '0', '2026-04-11 10:05:59', '2026-04-11 09:54:58', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('27', 'Iranian authorities remain defiant, urge supporters to stay in streets', 'iranian-authorities-remain-defiant-urge-supporters-to-stay-in-streets', 'Tehran, Iran – Iranian authorities say the United States needs to do more if an agreement is to be made to end the war as they urge their supporters to maintain control of the streets.\r\n\r\nThe US delegation at Saturday’s marathon talks in Islamabad, Pakistan, “ultimately failed to gain the trust of the Iranian delegation in this round of negotiations”, said Mohammad Bagher Ghalibaf, the parliament speaker who led the Iranian team.\r\n\r\nRecommended Stories\r\nlist of 3 items\r\nlist 1 of 3Oil tankers exit Strait of Hormuz amid fragile US-Iran ceasefire\r\nlist 2 of 3Iran must not charge tolls in Strait of Hormuz, UN maritime chief says\r\nlist 3 of 3Ceasefire brings some relief for Iranians but economic outlook remains grim\r\nend of list\r\nUS President Donald Trump said on Sunday that the US Navy will immediately begin the process of “blockading any and all ships trying to enter, or leave, the Strait of Hormuz” in Iran’s southern waters. He also said the US military remains “locked and loaded” and will “finish up” Iran at the “appropriate moment”.\r\n\r\nThe fact that the Iranian delegation did not accede to Washington’s core demands of eliminating nuclear enrichment on Iranian soil and ending Iranian control over the Strait of Hormuz was welcomed by Iranian authorities on Sunday as they projected defiance.\r\n\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Mohammad Bagher Ghalibaf, who led Iran&#039;s delegation in talks to end the war, said US delegation &#039;failed to gain trust&#039;.', 'uploads/news/69dbe0a22d5d6.webp', '', NULL, '2', NULL, '1', 'https://www.aljazeera.com/news/2026/4/12/iranian-authorities-remain-defiant-urge-supporters-to-stay-in?traffic_source=rss', 'published', '1', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-12 23:13:01', '2026-04-12 23:03:39', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('28', 'After Iran talks falter, the big question is what happens next?', 'after-iran-talks-falter-the-big-question-is-what-happens-next', 'Twenty-one hours was not enough to end 47 years of hostility between Iran and the US.\r\n\r\nThe historic high-level talks in Islamabad, during a pause in weeks of grievous war, were always unlikely to end any other way.\r\n\r\nCalling this marathon negotiating session a failure belies the scale of the challenge in narrowing wide gaps on complex issues ranging from age-old suspicion about Iran\'s nuclear programme to new challenges this war has thrown up - most of all Iran\'s control of the strategic Strait of Hormuz, whose closure is causing economic shocks worldwide.\r\n\r\nTo do a deal, they also needed to overcome a deep chasm of distrust.\r\n\r\nA day ago, it wasn\'t even certain the two sides would meet, and even more, sit down in the same room.\r\n\r\nA longstanding political taboo was broken.\r\n\r\nThe urgent question now is: what happens next?\r\n\r\nWhat happens to the contested two-week ceasefire which pulled the world back from US President Donald Trump\'s alarming threat to destroy a \"whole civilisation\" in Iran?\r\n\r\nWould the US president be ready to send his negotiators back to the bargaining table?\r\n\r\nWe\'re hearing reports from sources here in Islamabad that some conversations have continued after US Vice-President JD Vance boarded his plane at sunrise, declaring the US delegation had made their \"final and best offer\".\r\n\r\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Twenty-one hours was not enough to end 47 years of hostility between Iran and the US, writes the BBC&#039;s Lyse Doucet.', 'uploads/news/69dbdf759d7e8.webp', '', NULL, '2', NULL, '1', 'https://www.bbc.com/news/articles/c5y943x2g8qo?at_medium=RSS&at_campaign=rss', 'published', '1', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-12 23:07:59', '2026-04-12 23:03:40', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('29', 'View image in fullscreen US-Israel war on Iran Planeloads of negotiators and too little time: US and Iran’s 21 hours of talks', 'View image in fullscreen US-Israel war on Iran Planeloads of negotiators and too little time: US and Iran’s 21 hours of talks', '<p>The two sides turned up to test one another’s resolve. It was probably unrealistic to expect a dispute that has taken up years of discussion to be settled in one marathon session</p>\r\n\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'The two sides turned up to test one another’s resolve. It was probably unrealistic to expect a dispute that has taken up years of discussion to be settled in one marathon session', 'uploads/news/69dbe92cabbcb.png', '', NULL, '2', NULL, '1', 'https://www.aljazeera.com/video/the-listening-post/2026/4/12/us-media-trapped-between-oligarchy-and-presidency?traffic_source=rss', 'published', '1', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-12 23:49:59', '2026-04-12 23:38:21', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('30', 'Trump&#039;s Strait of Hormuz blockade threat raises risks and leaves predicaments unchanged', 'trump-s-strait-of-hormuz-blockade-threat-raises-risks-and-leaves-predicaments-unchanged', '<p>After a diplomatic team led by Vice-President JD Vance tried, and failed, to reach a negotiated agreement to end the US war with Iran on Saturday, President Donald Trump had to decide his next move.</p>\r\n\r\nThat came on Sunday morning, in a series of Truth Social posts.\r\n\r\nThe US will impose a naval blockade of Iran, he wrote. \"No one who pays an illegal toll will have safe passage on the high seas,\" he wrote.\r\n\r\nHe also said that the US would continue clearing mines from the Strait of Hormuz in order to ensure a safe passage for allied shipping. The US military, he added, was \"locked and loaded\" and prepared to resume attacks against Iran at an \"appropriate moment\".\r\n\r\nHe went on to say that while progress had been made in the 20-hour negotiations in Islamabad, Iran would not meet the US demand that it abandon its nuclear ambitions.\r\n\r\nWhile his posts didn\'t have the apocalyptic bluster of last week\'s threat to end Iranian civilisation, they pose a number of new challenges – and risks – for the American side.\r\n\r\nLive updates on Trump\'s blockade threat\r\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'The conflict is now a test of wills - Irans capacity to absorb strikes versus Trumps tolerance for the war&#039;s costs.', 'uploads/news/69dbe7a3d61b3.webp', '', NULL, '2', NULL, '1', 'https://www.bbc.com/news/articles/c8dl5mly2rzo?at_medium=RSS&at_campaign=rss', 'published', '1', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-12 23:43:15', '2026-04-12 23:38:29', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('31', '“The initiative is in the hands of Tehran, not Washington”', 'the-initiative-is-in-the-hands-of-tehran-not-washington', '<p>Defence analyst Mushahid Hussain Syed says the US-Iran conflict cannot be resolved through military force.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/quotable/2026/4/12/the-initiative-is-in-the-hands-of-tehran-not?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/quotable/2026/4/12/the-initiative-is-in-the-hands-of-tehran-not?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Defence analyst Mushahid Hussain Syed says the US-Iran conflict cannot be resolved through military force.', '', '', NULL, '1', NULL, '1', 'https://www.aljazeera.com/video/quotable/2026/4/12/the-initiative-is-in-the-hands-of-tehran-not?traffic_source=rss', 'published', '0', 'rss_import', '2', '0', '0', '0.00', '0', '0', '0', '2026-04-24 01:32:46', '2026-04-13 01:36:59', '2026-04-26 00:31:38', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('32', '\'A truly historic moment\': BBC reports from Hungary as Péter Magyar set to become new PM', 'a-truly-historic-moment-bbc-reports-from-hungary-as-péter-magyar-set-to-become-new-pm', '<p>Rajini Vaidyanathan broadcasts from outside Hungary\'s parliament as crowds hear about the prime minister\'s concession.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/videos/c2lwrjwg29lo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/videos/c2lwrjwg29lo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Rajini Vaidyanathan broadcasts from outside Hungary\'s parliament as crowds hear about the prime minister\'s concession.', '', '', NULL, '1', NULL, '1', 'https://www.bbc.com/news/videos/c2lwrjwg29lo?at_medium=RSS&at_campaign=rss', 'published', '0', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-24 01:32:46', '2026-04-13 01:37:05', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('33', 'Could the Iran war pose lasting risks to global food security?', 'could-the-iran-war-pose-lasting-risks-to-global-food-security', '<p>United Nations warns impact could last well beyond conflict.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/inside-story/2026/4/12/could-the-iran-war-pose-lasting-risks-to-global-food-security?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/video/inside-story/2026/4/12/could-the-iran-war-pose-lasting-risks-to-global-food-security?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'United Nations warns impact could last well beyond conflict.', '', '', NULL, '1', NULL, '1', 'https://www.aljazeera.com/video/inside-story/2026/4/12/could-the-iran-war-pose-lasting-risks-to-global-food-security?traffic_source=rss', 'published', '0', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-24 01:32:46', '2026-04-13 01:58:43', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('34', 'Viktor Orban swept from power after 16 years ruling Hungary', 'viktor-orban-swept-from-power-after-16-years-ruling-hungary', '<p>In a record turnout at the polls, Hungarians have voted out their long-serving, far-right Prime Minister Viktor Orban.</p>\r\n\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'In a record turnout at the polls, Hungarians have voted out their long-serving, far-right Prime Minister Viktor Orban.', 'uploads/news/69dc7d0775f74.png', '', NULL, '2', NULL, '1', 'https://www.aljazeera.com/video/newsfeed/2026/4/13/viktor-orban-swept-from-power-after-16-years-ruling-hungary?traffic_source=rss', 'published', '1', 'rss_import', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-13 10:20:21', '2026-04-13 10:16:59', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('35', 'Orbán era swept away by Péter Magyar\'s Hungary election landslide', 'orbán-era-swept-away-by-péter-magyar-s-hungary-election-landslide', '<p>Viktor Orbn\'s 16-year rule is over, defeated by a 45-year-old ex-party insider who convinced a majority of Hungarians to oust him.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cd9vg782kx7o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cd9vg782kx7o?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'Viktor Orbn\'s 16-year rule is over, defeated by a 45-year-old ex-party insider who convinced a majority of Hungarians to oust him.', '', '', NULL, '1', NULL, '1', 'https://www.bbc.com/news/articles/cd9vg782kx7o?at_medium=RSS&at_campaign=rss', 'published', '0', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-24 01:32:46', '2026-04-13 10:17:02', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('36', 'TRUMP, RUBIO FACE NATO CHIEF AS U.S. MOVES TO &quot;REEXAMINE&quot; ALLIANCE AFTER IRAN CLASH', 'quot-quot', 'President Donald Trump speaks to the media after disembarking from Air Force One on April 12, 2026, at Joint Base Andrews, Maryland. Trump returns to Washington after a weekend in Florida. (Tasos Katopodis/Getty Images)\r\n\r\n&quot;There are many boats heading toward our country to fill up with oil and then go and take it,&quot; he said. \r\n\r\nThe president then expressed sharp disapproval of NATO countries, indicating that America&#039;s financial commitment to support the alliance, particularly against Russia, is going to be under &quot;very serious examination.&quot;\r\n\r\n&quot;But I&#039;m very disappointed in NATO,&quot; he said. &quot;They weren&#039;t there for us. We pay trillions of dollars for NATO, and they weren&#039;t there for us.&quot;\r\n\r\nWhile NATO countries are now stepping up to assist the U.S., Trump described the effort as too late.\r\n\r\n&quot;Now they want to come up, but there&#039;s no real threat anymore,&quot; he said.', 'President Donald Trump speaks to the media after disembarking from Air Force One on April 12, 2026, at Joint Base Andrews, Maryland. Trump returns to Washington after a weekend in Florida. (Tasos Kato...', 'uploads/news/img_69dc822b9d15f_1776058923.webp', '', '', '2', NULL, '1', NULL, 'published', '0', 'article', '2', '0', '0', '0.00', '0', '0', '0', '2026-04-13 10:42:03', '2026-04-13 10:42:03', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('37', '‘Terrible for foreign policy’: Trump attacks Pope Leo after peace appeal', 'terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal', '<p>Leo, who last year became the first US-born pope, has emerged as an outspoken critic of the US-Israeli war on Iran.</p>\r\n\r\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\r\n\r\n<p><strong><a href=\"https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Leo, who last year became the first US-born pope, has emerged as an outspoken critic of the US-Israeli war on Iran.', 'uploads/news/69dc890b0b50a.png', '', NULL, '8', NULL, '1', 'https://www.aljazeera.com/news/2026/4/13/terrible-for-foreign-policy-trump-attacks-pope-leo-after-peace-appeal?traffic_source=rss', 'published', '1', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-13 11:12:19', '2026-04-13 11:02:02', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('38', 'nissata bacha khan', 'nissata-bacha-khan', 'zxbuq iudquwd cwdqwuc uwdbd8wbcwhfuqw c wifiwq', 'zxbuq iudquwd cwdqwuc uwdbd8wbcwhfuqw c wifiwq...', 'uploads/news/img_69dc8a189e1d5_1776060952.jpeg', '', '', '7', NULL, '1', NULL, 'published', '0', 'article', '20', '0', '0', '0.00', '0', '0', '0', '2026-04-13 11:15:52', '2026-04-13 11:15:52', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('39', 'امریکی بحریہ آبنائے ہرمز کی ناکہ بندی کیسے کرے گی اور ایران پر اس کے اثرات کیا ہوں گے؟', '-1', 'جارج رائٹ، ریچل کلن\r\nعہدہ,بزنس رپورٹر\r\n3 گھنٹے قبل\r\nمطالعے کا وقت: 6 منٹ\r\nامریکی فوج کا کہنا ہے کہ سوموار سے آبنائے ہرمز کی بحری ناکہ بندی کا آغاز ہونے کے بعد ایرانی بندرگاہوں سے کسی بھی قسم کی ٹریفک کو روک دیا جائے گا تاہم کسی اور مقام سے آنے والے بحری جہازوں کو اس آبی گزرگاہ سے گزرنے کی اجازت ہو گی۔\r\n\r\nلیکن یہ ناکہ بندی ہو گی کیسے؟\r\n\r\nامریکی بحریہ کی 2022 کی ہینڈ بک کے مطابق اس قسم کی ناکہ بندی کے دوران ہر قسم کی بحری ٹریفک کو ایسی مخصوص بندرگاہوں، ساحلی علاقوں یا فضائی اڈوں میں داخل ہونے یا وہاں سے نکلنے سے روکا جاتا ہے جو دشمن کے کنٹرول میں ہوں۔\r\n\r\nامریکی صدر ٹرمپ نے پہلے اعلان کیا تھا کہ ایران سے مذاکرات کی ناکامی کے بعد فوری طور پر ناکہ بندی ہو گی لیکن اتوار کو انھوں نے فاکس نیوز سے بات کرتے ہوئے کہا کہ اس میں تھوڑا وقت لگ سکتا ہے۔\r\n\r\nبعد میں امریکی سینٹرل کمانڈ نے صدر ڈونلڈ ٹرمپ کے اعلان کے مطابق آبنائے ہرمز کی ناکہ بندی کے وقت کا اعلان کرتے ہوئے کہا کہ ایران کی بندرگاہوں کی ناکہ بندی 13 اپریل کو صبح 10 بجے (ایسٹرن ٹائم) سے تمام ممالک کے جہازوں پر یکساں طور پر نافذ کی جائے گی جو ایرانی بندرگاہوں یا ساحلی علاقوں میں داخل ہوں گے یا وہاں سے نکلیں گے۔ اس میں خلیج اور بحیرہ عمان میں واقع تمام ایرانی بندرگاہیں شامل ہیں۔\r\n\r\nبیان میں مزید کہا گیا ہے کہ ’سینٹ کام آبنائے ہرمز سے گزرنے والے اُن جہازوں کی راہ میں رکاوٹ نہیں ڈالے گا جو غیر ایرانی بندرگاہوں کی جانب یا وہاں سے آ رہے ہوں۔‘\r\n\r\nسینٹ کام کا کہنا ہے کہ ناکہ بندی کے آغاز سے قبل تجارتی جہاز رانی کے اداروں کو باضابطہ نوٹس کے ذریعے مزید معلومات فراہم کی جائیں گی۔\r\n\r\nصدر ٹرمپ نے کہا ہے کہ دیگر ممالک اس ناکہ بندی میں شامل ہوں گے تاہم انھوں نے وضاحت نہیں کی کہ یہ ممالک کون سے ہیں۔ ٹرمپ نے کہا کہ امریکہ ایسے بحری جہاز بھی لائے گا جو بارودی سرنگوں کو ہٹائیں گے جن میں برطانوی جہاز بھی شامل ہوں گے۔', 'جارج رائٹ، ریچل کلن\r\nعہدہ,بزنس رپورٹر\r\n3 گھنٹے قبل\r\nمطالعے کا وقت: 6 منٹ\r\nامریکی فوج کا کہنا ہے کہ سوموار سے آبنائے...', 'uploads/news/img_69dc8ae4e31a7_1776061156.webp', '', 'uploads/news/videos/vid_69dc8ae4e36f0_1776061156.mp4', '5', NULL, '1', NULL, 'published', '0', 'article', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-13 11:19:16', '2026-04-13 11:19:16', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('40', 'امریکہ اور ایران کے درمیان پانچ بڑے اختلافات کیا ہیں؟', '-2', 'پال ایڈم\r\nعہدہ,نامہ نگار برائے سفارتی امور\r\n11 اپريل 2026\r\nاپ ڈیٹ کی گئی 12 اپريل 2026\r\nمطالعے کا وقت: 7 منٹ\r\nامریکہ اور اسرائیل کے ساتھ ایران کی جنگ بندی کے لیے ہونے والے مذاکرات ختم ہونے سے پہلے ہی یہ واضح تھا کہ دونوں کے درمیان پہاڑ جیسی رکاوٹیں موجود ہیں۔\r\n\r\nاسلام آباد میں ہونے والے مزاکرات کے بعد ایرانی وزارت خارجہ کے ترجمان اسمائیل بقائی نے کہا کہ بہت سے موضوعات پر ’مفاہمت‘ ہو گئی تھی تاہم دو یا تین اہم معاملات پر دونوں فریقوں میں اتفاق رائے نہ ہو سکا، جس کے باعث بالآخر مذاکرات کسی معاہدے تک نہیں پہنچ سکے۔\r\n\r\nاسماعیل بقائی کے مطابق ان مذاکرات میں آبنائے ہرمز سمیت چند نئے موضوعات بھی شامل کیے گئے اور ہر موضوع کی اپنی پیچیدگیاں ہیں۔', 'پال ایڈم\r\nعہدہ,نامہ نگار برائے سفارتی امور\r\n11 اپريل 2026\r\nاپ ڈیٹ کی گئی 12 اپريل 2026\r\nمطالعے کا وقت: 7 منٹ\r\nامریکہ اور ا...', 'uploads/news/img_69dc905dcd8f5_1776062557.webp', 'https://youtu.be/nIeP0Skp3hE?si=62xqfxgRXMDDXJ4m', '', '2', NULL, '1', NULL, 'published', '0', 'article', '0', '0', '0', '0.00', '0', '0', '0', '2026-04-13 11:42:37', '2026-04-13 11:42:37', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('43', 'Bkuc Sport gala', 'kuc-port-gala', 'Sport gala 2025 champion computer science...', 'Sport gala 2025 champion computer science......', 'uploads/news/img_69e0e3240509e_1776345892.jpg', '', '', '5', NULL, '1', NULL, 'published', '0', 'article', '15', '0', '0', '0.00', '0', '0', '0', '2026-04-16 18:24:52', '2026-04-16 18:24:52', '2026-04-25 08:29:31', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('44', 'Real Betis vs Real Madrid: La Liga – teams, start time, lineup', 'real-betis-vs-real-madrid-la-liga-teams-start-time-lineup', '<p>Real Madrid could close gap on La Liga leaders Barcelona to six points on Friday, three weeks shy of a Clasico meeting.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.aljazeera.com/sports/2026/4/23/real-betis-vs-real-madrid-la-liga-teams-start-lineup?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Al Jazeera</a></em></p>\n\n<p><strong><a href=\"https://www.aljazeera.com/sports/2026/4/23/real-betis-vs-real-madrid-la-liga-teams-start-lineup?traffic_source=rss\" target=\"_blank\" rel=\"noopener\">Read full story on Al Jazeera</a></strong></p>', 'Real Madrid could close gap on La Liga leaders Barcelona to six points on Friday, three weeks shy of a Clasico meeting.', '', '', NULL, '1', NULL, '1', 'https://www.aljazeera.com/sports/2026/4/23/real-betis-vs-real-madrid-la-liga-teams-start-lineup?traffic_source=rss', 'published', '0', 'rss_import', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-23 19:20:30', '2026-04-24 01:32:41', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('45', 'Trump tells BBC that King\'s visit could \'absolutely\' help repair relations with UK', 'trump-tells-bbc-that-king-s-visit-could-absolutely-help-repair-relations-with-uk', '<p>In a phone interview with the BBC\'s North America editor, the president discussed next week\'s visit and his relationship with the UK PM.</p>\n\n<p><em><strong>Source:</strong> <a href=\"https://www.bbc.com/news/articles/cx2wdegnzzjo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">BBC News</a></em></p>\n\n<p><strong><a href=\"https://www.bbc.com/news/articles/cx2wdegnzzjo?at_medium=RSS&at_campaign=rss\" target=\"_blank\" rel=\"noopener\">Read full story on BBC News</a></strong></p>', 'In a phone interview with the BBC\'s North America editor, the president discussed next week\'s visit and his relationship with the UK PM.', '', '', NULL, '1', NULL, '1', 'https://www.bbc.com/news/articles/cx2wdegnzzjo?at_medium=RSS&at_campaign=rss', 'published', '0', 'rss_import', '4', '0', '0', '0.00', '0', '0', '0', '2026-04-23 17:36:11', '2026-04-24 01:32:42', '2026-04-25 08:29:31', '0.00', '0', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('46', 'bkuc', 'bkuc', 'bkuc', 'bkuc...', 'uploads/news/img_69eaf6467148e_1777006150.jpg', '', '', '5', NULL, '9', NULL, 'published', '0', 'article', '4', '0', '0', '0.00', '0', '0', '0', '2026-04-24 09:49:10', '2026-04-24 09:49:10', '2026-04-26 23:19:08', '0.00', 'neutral', '0', 'manual', 'text', 'PK Live News';
INSERT INTO `news` VALUES ('47', 'this is sport gala 2025', 'this-is-sport-gala-2025', 'in session 2025 is bkuc the department winner is bscs', 'in session 2025 is bkuc the department winner is bscs...', 'uploads/news/img_69ee4b77d3eab_1777224567.jpg', '', '', '7', NULL, '1', NULL, 'published', '0', 'article', '1', '0', '0', '0.00', '0', '0', '0', '2026-04-26 22:29:27', '2026-04-26 22:29:27', '2026-04-26 22:30:00', '0.00', 'neutral', '0', 'manual', 'text', NULL;


-- Table structure for `news_analytics`
CREATE TABLE `news_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `unique_visitors` int(11) DEFAULT 0,
  `avg_read_time` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_date` (`news_id`,`date`),
  KEY `idx_analytics_news` (`news_id`),
  KEY `idx_analytics_date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_credibility_analysis`
CREATE TABLE `news_credibility_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL COMMENT 'Reference to news article',
  `analysis_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When the analysis was performed',
  `credibility_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Overall credibility score (0-100)',
  `confidence_level` decimal(3,2) DEFAULT 0.50 COMMENT 'AI confidence in the analysis (0.00-1.00)',
  `title_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Title credibility score (0-100)',
  `content_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Content credibility score (0-100)',
  `source_credibility` decimal(5,2) DEFAULT 50.00 COMMENT 'Source credibility score (0-100)',
  `factual_accuracy` decimal(5,2) DEFAULT 50.00 COMMENT 'Factual accuracy score (0-100)',
  `sensationalism_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Sensationalism score (0-100)',
  `emotional_manipulation` decimal(5,2) DEFAULT 0.00 COMMENT 'Emotional manipulation score (0-100)',
  `clickbait_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Clickbait score (0-100)',
  `propaganda_indicators` decimal(5,2) DEFAULT 0.00 COMMENT 'Propaganda indicators score (0-100)',
  `grammar_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Grammar quality score (0-100)',
  `readability_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Readability score (0-100)',
  `factual_density` decimal(5,2) DEFAULT 50.00 COMMENT 'Factual density score (0-100)',
  `source_verified` tinyint(1) DEFAULT 0 COMMENT 'Whether source is verified',
  `source_reputation_score` decimal(5,2) DEFAULT 50.00 COMMENT 'Source reputation score (0-100)',
  `cross_reference_count` int(11) DEFAULT 0 COMMENT 'Number of cross-references found',
  `analysis_method` varchar(50) DEFAULT 'AI_MULTIMODEL' COMMENT 'Method used for analysis',
  `processing_time_ms` int(11) DEFAULT 0 COMMENT 'Time taken for analysis in milliseconds',
  `ai_model_version` varchar(20) DEFAULT 'v2.1' COMMENT 'Version of AI model',
  `risk_level` enum('low','medium','high','critical') DEFAULT 'medium' COMMENT 'Risk level for misinformation',
  `content_category` varchar(50) DEFAULT 'general' COMMENT 'Content category',
  `requires_review` tinyint(1) DEFAULT 0 COMMENT 'Whether manual review is required',
  `auto_flagged` tinyint(1) DEFAULT 0 COMMENT 'Whether automatically flagged as suspicious',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Record creation timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_analysis` (`news_id`),
  KEY `idx_credibility_score` (`credibility_score`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_auto_flagged` (`auto_flagged`),
  KEY `idx_analysis_date` (`analysis_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_credibility_analysis` VALUES ('1', '46', '2026-04-25 22:59:13', '58.25', '9.99', '95.00', '65.00', '30.00', '75.00', '0.00', '0.00', '0.00', '0.00', '80.00', '50.00', '0.00', '0', '0.00', '0', 'AI_MULTIMODEL', '19', '0', 'high', 'UNVERIFIED', '1', '0', '2026-04-25 22:59:13';
INSERT INTO `news_credibility_analysis` VALUES ('2', '31', '2026-04-26 00:31:38', '72.65', '9.99', '100.00', '85.00', '60.00', '83.00', '0.00', '0.00', '0.00', '0.00', '75.00', '34.74', '27.14', '0', '50.00', '4', 'AI_MULTIMODEL', '4', '0', 'medium', 'LIKELY_TRUE', '0', '0', '2026-04-26 00:31:38';
INSERT INTO `news_credibility_analysis` VALUES ('3', '47', '2026-04-26 22:30:00', '62.72', '9.99', '100.00', '65.00', '30.00', '75.00', '0.00', '0.00', '0.00', '0.00', '95.00', '50.00', '22.22', '0', '0.00', '0', 'AI_MULTIMODEL', '27', '0', 'medium', 'UNVERIFIED', '1', '0', '2026-04-26 22:30:00';


-- Table structure for `news_editions`
CREATE TABLE `news_editions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `edition_date` date NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_edition_date` (`edition_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_likes`
CREATE TABLE `news_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_user` (`news_id`,`user_id`),
  KEY `idx_news_ip` (`news_id`,`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_shares`
CREATE TABLE `news_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL DEFAULT 'unknown',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_platform` (`news_id`,`platform`),
  KEY `idx_news_user` (`news_id`,`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `news_sources`
CREATE TABLE `news_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Name of news source',
  `url` varchar(500) NOT NULL COMMENT 'Main URL of news source',
  `rss_url` varchar(500) DEFAULT NULL COMMENT 'RSS feed URL',
  `type` enum('rss','scrape') NOT NULL DEFAULT 'rss' COMMENT 'Import type',
  `category_id` int(11) DEFAULT NULL COMMENT 'Default category ID',
  `scrape_frequency` int(11) NOT NULL DEFAULT 60 COMMENT 'Scraping frequency in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Source status',
  `last_scraped` timestamp NULL DEFAULT NULL COMMENT 'Last successful scrape',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_last_scraped` (`last_scraped`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_sources` VALUES ('158', 'BBC News Pakistan', '', 'http://feeds.bbci.co.uk/news/world/south_asia/rss.xml', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '10';
INSERT INTO `news_sources` VALUES ('159', 'Dawn News', '', 'https://www.dawn.com/feed/rss/pakistan', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '9';
INSERT INTO `news_sources` VALUES ('160', 'Geo News', '', 'https://www.geo.tv/rss/pakistan', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '8';
INSERT INTO `news_sources` VALUES ('161', 'ARY News', '', 'https://arynews.tv/en/feed/', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '7';
INSERT INTO `news_sources` VALUES ('162', 'CNN World', '', 'http://rss.cnn.com/rss/edition_world.rss', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '6';
INSERT INTO `news_sources` VALUES ('163', 'Reuters World', '', 'https://www.reuters.com/world/rss.xml', 'rss', '1', '60', 'active', NULL, '2026-04-23 23:15:35', '2026-04-23 23:15:35', '1', '5';
INSERT INTO `news_sources` VALUES ('164', 'BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('165', 'BBC World News', 'https://www.bbc.com/news/world', 'https://feeds.bbci.co.uk/news/world/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('166', 'CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('167', 'Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('168', 'Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('169', 'Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('170', 'Fox News', 'https://www.foxnews.com', 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('171', 'The Guardian', 'https://www.theguardian.com', 'https://www.theguardian.com/world/rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('172', 'The New York Times', 'https://www.nytimes.com', 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('173', 'Washington Post', 'https://www.washingtonpost.com', 'https://www.washingtonpost.com/world/rss/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('174', 'NBC News', 'https://www.nbcnews.com', 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('175', 'CBS News', 'https://www.cbsnews.com', 'https://www.cbsnews.com/rss/live/rss.rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('176', 'ABC News', 'https://abcnews.go.com', 'https://abcnews.go.com/xml/rss/abc_us_topstories.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('177', 'NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('178', 'PBS NewsHour', 'https://www.pbs.org/newshour', 'https://www.pbs.org/newshour/rss/feed', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('179', 'Deutsche Welle', 'https://www.dw.com', 'https://www.dw.com/en/rss/top-stories', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('180', 'France 24', 'https://www.france24.com', 'https://www.france24.com/en/rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('181', 'Bloomberg', 'https://www.bloomberg.com', 'https://feeds.bloomberg.com/markets/news.rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('182', 'CNBC', 'https://www.cnbc.com', 'https://www.cnbc.com/id/100003114/device/rss/rss.html', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('183', 'Express Tribune', 'https://tribune.com.pk', 'https://tribune.com.pk/rss/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('184', 'The News International', 'https://www.thenews.com.pk', 'https://www.thenews.com.pk/rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('185', 'Pakistan Today', 'https://www.pakistantoday.com.pk', 'https://www.pakistantoday.com.pk/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('186', 'Dunya News', 'https://www.dunyanews.tv', 'https://www.dunyanews.tv/rss.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('187', 'Samaa TV', 'https://www.samaa.tv', 'https://www.samaa.tv/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('188', '24 News HD', 'https://www.24news.tv', 'https://www.24news.tv/feed/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('189', 'BBC Urdu', 'https://www.bbc.com/urdu', 'https://www.bbc.com/urdu/rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('190', 'VOA Urdu', 'https://www.voaurdu.com', 'https://www.voaurdu.com/a/rss', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('191', 'NDTV', 'https://www.ndtv.com', 'https://feeds.ndtv.com/ndtv/rss/top-stories.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('192', 'Times of India', 'https://timesofindia.indiatimes.com', 'https://timesofindia.indiatimes.com/rssfeedstopstories.cms', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('193', 'Hindustan Times', 'https://www.hindustantimes.com', 'https://www.hindustantimes.com/rss/topnews/rssfeed.xml', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';
INSERT INTO `news_sources` VALUES ('194', 'Toronto Star', 'https://www.thestar.com', 'https://www.thestar.com/rss?category=news', 'rss', '1', '30', 'inactive', NULL, '2026-04-24 01:28:55', '2026-04-24 21:59:02', '1', '1';
INSERT INTO `news_sources` VALUES ('195', 'CBC News', 'https://www.cbc.ca', 'https://www.cbc.ca/cmlink/rss-topstories', 'rss', '1', '30', 'inactive', NULL, '2026-04-24 01:28:55', '2026-04-24 22:00:19', '1', '1';
INSERT INTO `news_sources` VALUES ('196', 'Globo News', 'https://g1.globo.com', 'https://g1.globo.com/rss/g1/', 'rss', '1', '30', 'active', NULL, '2026-04-24 01:28:55', '2026-04-24 01:28:55', '1', '1';


-- Table structure for `news_tags`
CREATE TABLE `news_tags` (
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `idx_news_tags_news` (`news_id`),
  KEY `idx_news_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news_tags` VALUES ('1', '9';
INSERT INTO `news_tags` VALUES ('1', '18';
INSERT INTO `news_tags` VALUES ('2', '12';
INSERT INTO `news_tags` VALUES ('2', '13';
INSERT INTO `news_tags` VALUES ('3', '1';
INSERT INTO `news_tags` VALUES ('3', '5';
INSERT INTO `news_tags` VALUES ('3', '13';
INSERT INTO `news_tags` VALUES ('4', '5';
INSERT INTO `news_tags` VALUES ('4', '7';
INSERT INTO `news_tags` VALUES ('4', '10';
INSERT INTO `news_tags` VALUES ('5', '12';
INSERT INTO `news_tags` VALUES ('5', '15';
INSERT INTO `news_tags` VALUES ('5', '16';
INSERT INTO `news_tags` VALUES ('6', '10';
INSERT INTO `news_tags` VALUES ('7', '10';
INSERT INTO `news_tags` VALUES ('7', '14';
INSERT INTO `news_tags` VALUES ('7', '18';
INSERT INTO `news_tags` VALUES ('8', '1';
INSERT INTO `news_tags` VALUES ('8', '11';
INSERT INTO `news_tags` VALUES ('8', '12';
INSERT INTO `news_tags` VALUES ('9', '7';
INSERT INTO `news_tags` VALUES ('9', '10';
INSERT INTO `news_tags` VALUES ('9', '16';


-- Table structure for `notification_queue`
CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `scheduled_at` (`scheduled_at`),
  KEY `status` (`status`),
  CONSTRAINT `notification_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `notification_settings`
CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `news_notifications` tinyint(1) DEFAULT 1,
  `event_notifications` tinyint(1) DEFAULT 1,
  `system_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notification_settings` VALUES ('1', '1', '1', '1', '1', '1', '1', '2026-04-09 21:16:24', '2026-04-09 21:16:24';


-- Table structure for `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','news','event','system') DEFAULT 'info',
  `url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  KEY `type` (`type`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` VALUES ('1', '1', 'Welcome to PK Live News Notifications!', 'The notification system has been successfully installed. You will now receive important updates about news, events, and system activities.', 'success', 'admin-dashboard.php', '1', '2026-04-09 21:16:24', NULL;
INSERT INTO `notifications` VALUES ('2', '1', 'Notification System Features', 'You can manage your notification preferences, view history, and send custom notifications to users from the admin panel.', 'info', 'admin/manage-notifications.php', '1', '2026-04-09 21:16:24', NULL;
INSERT INTO `notifications` VALUES ('3', '1', 'Welcome to PK Live News Notifications!', 'The notification system has been successfully installed. You will now receive important updates about news, events, and system activities.', 'success', 'admin-dashboard.php', '1', '2026-04-09 21:16:46', NULL;
INSERT INTO `notifications` VALUES ('4', '1', 'Notification System Features', 'You can manage your notification preferences, view history, and send custom notifications to users from the admin panel.', 'info', 'admin/manage-notifications.php', '1', '2026-04-09 21:16:46', NULL;


-- Table structure for `page_views`
CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(500) NOT NULL,
  `page_type` varchar(50) DEFAULT 'page',
  `page_title` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_type` (`page_type`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_page_type_created` (`page_type`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_views` VALUES ('1', '/index.php', 'home', 'PK Live News - Home', '127.0.0.1', NULL, NULL, NULL, NULL, '2026-04-12 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('2', '/search.php?q=pakistan', 'search', 'Search Results', '127.0.0.1', NULL, NULL, NULL, NULL, '2026-04-11 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('3', '/search.php?q=politics', 'search', 'Search Results', '192.168.1.1', NULL, NULL, NULL, NULL, '2026-04-10 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('4', '/categories.php?id=1', 'category', 'Breaking News', '10.0.0.1', NULL, NULL, NULL, NULL, '2026-04-13 00:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('5', '/article.php?id=123', 'article', 'Latest News Article', '203.0.113.1', NULL, NULL, NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('6', '/search.php?q=sports', 'search', 'Search Results', '172.16.0.1', NULL, NULL, NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('7', '/index.php', 'home', 'PK Live News - Home', '192.168.1.100', NULL, NULL, NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('8', '/rss.php', 'rss', 'RSS Feed', '10.0.0.50', NULL, NULL, NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `page_views` VALUES ('9', '/index.php', 'home', 'PK Live News - Home', '127.0.0.1', NULL, NULL, NULL, NULL, '2026-04-12 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('10', '/search.php?q=pakistan', 'search', 'Search Results', '127.0.0.1', NULL, NULL, NULL, NULL, '2026-04-11 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('11', '/search.php?q=politics', 'search', 'Search Results', '192.168.1.1', NULL, NULL, NULL, NULL, '2026-04-10 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('12', '/categories.php?id=1', 'category', 'Breaking News', '10.0.0.1', NULL, NULL, NULL, NULL, '2026-04-13 00:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('13', '/article.php?id=123', 'article', 'Latest News Article', '203.0.113.1', NULL, NULL, NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('14', '/search.php?q=sports', 'search', 'Search Results', '172.16.0.1', NULL, NULL, NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('15', '/index.php', 'home', 'PK Live News - Home', '192.168.1.100', NULL, NULL, NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `page_views` VALUES ('16', '/rss.php', 'rss', 'RSS Feed', '10.0.0.50', NULL, NULL, NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';


-- Table structure for `poll_options`
CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  CONSTRAINT `poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `poll_options` VALUES ('1', '1', 'Politics', '0', '2026-04-09 10:41:28';
INSERT INTO `poll_options` VALUES ('2', '1', 'Sports', '20', '2026-04-09 10:41:28';
INSERT INTO `poll_options` VALUES ('3', '1', 'Technology', '50', '2026-04-09 10:41:28';
INSERT INTO `poll_options` VALUES ('4', '1', 'Business', '39', '2026-04-09 10:41:28';
INSERT INTO `poll_options` VALUES ('5', '1', 'Entertainment', '48', '2026-04-09 10:41:28';


-- Table structure for `poll_votes`
CREATE TABLE `poll_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`poll_id`,`ip_address`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `polls`
CREATE TABLE `polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `question` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','ended') NOT NULL DEFAULT 'active',
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `polls` VALUES ('1', NULL, 'What is your favorite news category?', NULL, 'active', NULL, '2026-04-09 10:41:28', '2026-04-09 10:41:28';


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
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `post_likes` VALUES ('1', '9', '8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 00:57:50';
INSERT INTO `post_likes` VALUES ('3', '11', '8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:38:00';
INSERT INTO `post_likes` VALUES ('88', '7', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:40:09';
INSERT INTO `post_likes` VALUES ('90', '6', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:40:40';
INSERT INTO `post_likes` VALUES ('95', '1', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:42:23';
INSERT INTO `post_likes` VALUES ('102', '15', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:45:17';
INSERT INTO `post_likes` VALUES ('103', '2', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:45:23';
INSERT INTO `post_likes` VALUES ('104', '12', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:45:45';
INSERT INTO `post_likes` VALUES ('105', '3', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:46:23';
INSERT INTO `post_likes` VALUES ('106', '14', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:46:24';
INSERT INTO `post_likes` VALUES ('107', '7', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:46:30';
INSERT INTO `post_likes` VALUES ('112', '1', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:48:46';
INSERT INTO `post_likes` VALUES ('113', '11', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:49:01';
INSERT INTO `post_likes` VALUES ('114', '2', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:49:15';
INSERT INTO `post_likes` VALUES ('115', '5', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:49:23';
INSERT INTO `post_likes` VALUES ('116', '3', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:49:45';
INSERT INTO `post_likes` VALUES ('117', '4', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:50:30';
INSERT INTO `post_likes` VALUES ('118', '5', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 06:50:44';
INSERT INTO `post_likes` VALUES ('119', '8', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:51:23';
INSERT INTO `post_likes` VALUES ('125', '15', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:55:26';
INSERT INTO `post_likes` VALUES ('126', '19', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:55:35';
INSERT INTO `post_likes` VALUES ('127', '14', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:56:21';
INSERT INTO `post_likes` VALUES ('128', '16', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 06:56:34';
INSERT INTO `post_likes` VALUES ('132', '6', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 07:16:42';
INSERT INTO `post_likes` VALUES ('133', '16', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-11 07:27:34';
INSERT INTO `post_likes` VALUES ('136', '18', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:58:55';
INSERT INTO `post_likes` VALUES ('137', '9', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:58:59';
INSERT INTO `post_likes` VALUES ('138', '19', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:59:04';
INSERT INTO `post_likes` VALUES ('139', '15', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:59:08';
INSERT INTO `post_likes` VALUES ('140', '12', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:59:13';
INSERT INTO `post_likes` VALUES ('141', '16', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 07:59:14';
INSERT INTO `post_likes` VALUES ('142', '6', '10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 08:18:11';
INSERT INTO `post_likes` VALUES ('145', '21', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 22:47:30';
INSERT INTO `post_likes` VALUES ('146', '22', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 22:47:34';
INSERT INTO `post_likes` VALUES ('147', '18', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 22:53:15';
INSERT INTO `post_likes` VALUES ('148', '21', '1', '::1', 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-12 23:33:22';
INSERT INTO `post_likes` VALUES ('149', '18', '11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-12 23:57:56';
INSERT INTO `post_likes` VALUES ('150', '28', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 00:12:28';
INSERT INTO `post_likes` VALUES ('151', '27', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-13 00:12:29';
INSERT INTO `post_likes` VALUES ('152', '29', '11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-13 00:14:15';
INSERT INTO `post_likes` VALUES ('153', '28', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 12:23:16';
INSERT INTO `post_likes` VALUES ('155', '20', '1', '::1', 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-16 19:29:16';
INSERT INTO `post_likes` VALUES ('156', '39', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', '2026-04-23 21:16:35';
INSERT INTO `post_likes` VALUES ('158', '44', '1', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-24 14:43:11';
INSERT INTO `post_likes` VALUES ('159', '35', '1', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-24 14:43:11';
INSERT INTO `post_likes` VALUES ('160', '33', '1', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-24 14:43:11';
INSERT INTO `post_likes` VALUES ('175', '34', '14', '::1', 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36 Edg/146.0.0.0', '2026-04-25 23:15:38';
INSERT INTO `post_likes` VALUES ('180', '47', '14', NULL, NULL, '2026-04-26 23:12:41';
INSERT INTO `post_likes` VALUES ('181', '32', '14', NULL, NULL, '2026-04-26 23:12:51';
INSERT INTO `post_likes` VALUES ('184', '46', '1', NULL, NULL, '2026-04-26 23:49:14';


-- Table structure for `role_applications`
CREATE TABLE `role_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applied_role` enum('editor','reporter') NOT NULL,
  `application_data` text DEFAULT NULL,
  `cv_file_path` varchar(500) DEFAULT NULL,
  `cv_file_name` varchar(255) DEFAULT NULL,
  `cv_file_size` int(11) DEFAULT NULL,
  `evidence_type` enum('cv_resume','portfolio','certificates','work_samples','references','publications','other') DEFAULT 'cv_resume',
  `evidence_description` text DEFAULT NULL,
  `evidence_files` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','withdrawn') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_applied_role` (`applied_role`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `role_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_applications_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role_applications` VALUES ('1', '14', 'editor', '{\"experience\":\"Test exphierience for role application\",\"qualifications\":\"Test qualifications\",\"reason\":\"Test reason for applying\",\"samples\":\"Test samples\",\"availability\":\"full-time\"}', 'uploads/cv/test_cv.pdf', 'test_cv.pdf', '12345', 'cv_resume', NULL, NULL, '', 'sorry', '1', '2026-04-26 01:30:17', '2026-04-26 01:10:40', '2026-04-26 01:30:17';


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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` VALUES ('1', 'site_name', 'PK Live News', 'Site name', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('2', 'site_description', 'Latest news from Pakistan', 'Site description', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('3', 'site_keywords', 'news, pakistan, breaking news', 'Site keywords', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('4', 'contact_email', 'contact@pklivenews.com', 'Contact email', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('5', 'facebook_url', 'https://facebook.com/pklivenews', 'Facebook url', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('6', 'twitter_url', 'https://twitter.com/pklivenews', 'Twitter url', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('7', 'youtube_url', 'https://youtube.com/pklivenews', 'Youtube url', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('8', 'instagram_url', 'https://instagram.com/pklivenews', 'Instagram url', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('9', 'enable_comments', '1', 'Enable comments', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('10', 'enable_rss', '1', 'Enable rss', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('11', 'enable_weather', '1', 'Enable weather', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('12', 'enable_live_tv', '1', 'Enable live tv', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('13', 'news_per_page', '10', 'News per page', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('14', 'enable_ads', '1', 'Enable ads', '2026-04-09 10:54:39', '2026-04-09 10:54:39';
INSERT INTO `settings` VALUES ('15', 'maintenance_mode', '0', 'Maintenance mode', '2026-04-09 10:54:39', '2026-04-09 10:54:39';


-- Table structure for `site_settings`
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` VALUES ('1', 'site_name', 'PK Live News', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('2', 'site_description', 'Latest news and updates from Pakistan', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('3', 'posts_per_page', '20', 'number', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('4', 'maintenance_mode', 'on', 'boolean', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('5', 'show_trending_news', 'on', 'boolean', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('6', 'show_ads', 'on', 'boolean', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('7', 'default_language', 'en', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('8', 'contact_email', 'contact@pklivenews.com', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('9', 'social_media_links', '{\"facebook\":\"\",\"twitter\":\"\",\"instagram\":\"\",\"youtube\":\"\"}', 'text', '', '2026-04-09 10:03:52', '2026-04-09 10:03:52';
INSERT INTO `site_settings` VALUES ('10', 'seo_meta_description', 'PK Live News - Your trusted source for latest news', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('11', 'seo_keywords', 'news, pakistan, breaking news, current affairs', 'text', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('12', 'cache_duration', '3600', 'number', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('13', 'enable_comments', 'on', 'boolean', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('14', 'enable_rss', 'on', 'boolean', '', '2026-04-09 10:03:52', '2026-04-13 00:07:47';
INSERT INTO `site_settings` VALUES ('15', 'theme_color', '#007bff', 'text', '', '2026-04-09 10:03:52', '2026-04-09 10:03:52';
INSERT INTO `site_settings` VALUES ('16', 'logo_path', 'assets/images/logo.png', 'text', '', '2026-04-09 10:03:52', '2026-04-09 10:03:52';


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
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` VALUES ('1', 'site_name', 'PK Live News', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('2', 'site_description', 'Latest news and updates from Pakistan', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('3', 'footer_content', '© 2024 PK Live News. All rights reserved.', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('4', 'contact_email', 'admin@pklivenews.com', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('5', 'maintenance_mode', '0', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('6', 'theme_color', '#007bff', '2026-04-09 19:12:07', NULL;
INSERT INTO `system_settings` VALUES ('7', 'max_file_size', '5242880', '2026-04-10 07:01:53', NULL;
INSERT INTO `system_settings` VALUES ('8', 'allowed_extensions', 'jpg,jpeg,png,gif,mp4,mov,avi', '2026-04-10 07:01:53', NULL;
INSERT INTO `system_settings` VALUES ('9', 'upload_path', 'C:UsersDELLOneDriveDesktoppk-news.png', '2026-04-10 07:01:53', NULL;
INSERT INTO `system_settings` VALUES ('10', 'session_timeout', '3600', '2026-04-10 07:03:45', NULL;
INSERT INTO `system_settings` VALUES ('11', 'max_login_attempts', '0', '2026-04-10 07:03:45', NULL;
INSERT INTO `system_settings` VALUES ('12', 'enable_captcha', '0', '2026-04-10 07:03:45', NULL;
INSERT INTO `system_settings` VALUES ('13', 'force_https', '1', '2026-04-10 07:03:45', NULL;


-- Table structure for `tag_cloud`
-- Debug: Available keys: View, Create View, character_set_client, collation_connection
-- Error: Could not find Create Table key for `tag_cloud`

-- Table structure for `tags`
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tags_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `trusted_sources`
CREATE TABLE `trusted_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL,
  `source_name` varchar(255) DEFAULT NULL,
  `trust_score` decimal(3,2) DEFAULT 0.50,
  `reputation_score` decimal(3,2) DEFAULT 0.50,
  `verified` tinyint(1) DEFAULT 0,
  `fact_check_rating` enum('high','medium','low','unknown') DEFAULT 'unknown',
  `bias_rating` enum('left','center-left','center','center-right','right','unknown') DEFAULT 'unknown',
  `country` varchar(100) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_info` varchar(500) DEFAULT NULL,
  `social_media_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_media_links`)),
  `alexa_rank` int(11) DEFAULT NULL,
  `monthly_visitors` int(11) DEFAULT NULL,
  `founded_year` int(4) DEFAULT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_verified` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`domain_name`),
  KEY `idx_trust_score` (`trust_score`),
  KEY `idx_verified` (`verified`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trusted_sources` VALUES ('1', 'reuters.com', 'Reuters', '0.95', '0.92', '1', 'high', 'center', 'United Kingdom', 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2026-04-09 19:14:51', '2026-04-09 19:14:51';
INSERT INTO `trusted_sources` VALUES ('2', 'ap.org', 'Associated Press', '0.94', '0.91', '1', 'high', 'center', 'United States', 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2026-04-09 19:14:51', '2026-04-09 19:14:51';
INSERT INTO `trusted_sources` VALUES ('3', 'bbc.com', 'BBC News', '0.92', '0.89', '1', 'high', 'center-left', 'United Kingdom', 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2026-04-09 19:14:52', '2026-04-09 19:14:52';
INSERT INTO `trusted_sources` VALUES ('4', 'dawn.com', 'Dawn', '0.75', '0.72', '1', 'medium', 'center', 'Pakistan', 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2026-04-09 19:14:52', '2026-04-09 19:14:52';
INSERT INTO `trusted_sources` VALUES ('5', 'geo.tv', 'Geo News', '0.70', '0.67', '1', 'medium', 'center-right', 'Pakistan', 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2026-04-09 19:14:52', '2026-04-09 19:14:52';


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
  KEY `idx_activity_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Table structure for `user_admin_roles`
CREATE TABLE `user_admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_role` (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `user_admin_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_admin_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_admin_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_admin_roles` VALUES ('1', '1', '1', '1', '2026-04-10 09:14:56';


-- Table structure for `user_analytics`
CREATE TABLE `user_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'page_view',
  `page_url` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_action_date` (`user_id`,`action`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_analytics` VALUES ('1', NULL, NULL, 'page_view', '/index.php', '127.0.0.1', NULL, NULL, '2026-04-12 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('2', NULL, NULL, 'page_view', '/search.php', '127.0.0.1', NULL, NULL, '2026-04-11 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('3', NULL, NULL, 'search', '/search.php?q=news', '127.0.0.1', NULL, NULL, '2026-04-10 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('4', '1', NULL, 'page_view', '/index.php', '192.168.1.1', NULL, NULL, '2026-04-13 00:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('5', '1', NULL, 'login', '/login.php', '192.168.1.1', NULL, NULL, '2026-04-12 23:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('6', '2', NULL, 'page_view', '/categories.php', '10.0.0.1', NULL, NULL, '2026-04-13 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('7', '2', NULL, 'page_view', '/article.php?id=123', '10.0.0.1', NULL, NULL, '2026-04-13 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('8', NULL, NULL, 'page_view', '/rss.php', '203.0.113.1', NULL, NULL, '2026-04-13 01:52:06', '2026-04-13 01:52:06';
INSERT INTO `user_analytics` VALUES ('9', NULL, NULL, 'page_view', '/index.php', '127.0.0.1', NULL, NULL, '2026-04-12 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('10', NULL, NULL, 'page_view', '/search.php', '127.0.0.1', NULL, NULL, '2026-04-11 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('11', NULL, NULL, 'search', '/search.php?q=news', '127.0.0.1', NULL, NULL, '2026-04-10 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('12', '1', NULL, 'page_view', '/index.php', '192.168.1.1', NULL, NULL, '2026-04-13 00:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('13', '1', NULL, 'login', '/login.php', '192.168.1.1', NULL, NULL, '2026-04-12 23:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('14', '2', NULL, 'page_view', '/categories.php', '10.0.0.1', NULL, NULL, '2026-04-13 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('15', '2', NULL, 'page_view', '/article.php?id=123', '10.0.0.1', NULL, NULL, '2026-04-13 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('16', NULL, NULL, 'page_view', '/rss.php', '203.0.113.1', NULL, NULL, '2026-04-13 01:52:27', '2026-04-13 01:52:27';
INSERT INTO `user_analytics` VALUES ('17', NULL, NULL, 'page_view', '/index.php', '127.0.0.1', NULL, NULL, '2026-04-12 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('18', NULL, NULL, 'page_view', '/search.php', '127.0.0.1', NULL, NULL, '2026-04-11 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('19', NULL, NULL, 'search', '/search.php?q=news', '127.0.0.1', NULL, NULL, '2026-04-10 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('20', '1', NULL, 'page_view', '/index.php', '192.168.1.1', NULL, NULL, '2026-04-13 00:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('21', '1', NULL, 'login', '/login.php', '192.168.1.1', NULL, NULL, '2026-04-12 23:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('22', '2', NULL, 'page_view', '/categories.php', '10.0.0.1', NULL, NULL, '2026-04-13 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('23', '2', NULL, 'page_view', '/article.php?id=123', '10.0.0.1', NULL, NULL, '2026-04-13 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('24', NULL, NULL, 'page_view', '/rss.php', '203.0.113.1', NULL, NULL, '2026-04-13 01:53:21', '2026-04-13 01:53:21';
INSERT INTO `user_analytics` VALUES ('25', NULL, NULL, 'page_view', '/index.php', '127.0.0.1', NULL, NULL, '2026-04-12 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('26', NULL, NULL, 'page_view', '/search.php', '127.0.0.1', NULL, NULL, '2026-04-11 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('27', NULL, NULL, 'search', '/search.php?q=news', '127.0.0.1', NULL, NULL, '2026-04-10 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('28', '1', NULL, 'page_view', '/index.php', '192.168.1.1', NULL, NULL, '2026-04-13 00:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('29', '1', NULL, 'login', '/login.php', '192.168.1.1', NULL, NULL, '2026-04-12 23:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('30', '2', NULL, 'page_view', '/categories.php', '10.0.0.1', NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('31', '2', NULL, 'page_view', '/article.php?id=123', '10.0.0.1', NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('32', NULL, NULL, 'page_view', '/rss.php', '203.0.113.1', NULL, NULL, '2026-04-13 01:54:36', '2026-04-13 01:54:36';
INSERT INTO `user_analytics` VALUES ('33', NULL, NULL, 'page_view', '/index.php', '127.0.0.1', NULL, NULL, '2026-04-12 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('34', NULL, NULL, 'page_view', '/search.php', '127.0.0.1', NULL, NULL, '2026-04-11 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('35', NULL, NULL, 'search', '/search.php?q=news', '127.0.0.1', NULL, NULL, '2026-04-10 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('36', '1', NULL, 'page_view', '/index.php', '192.168.1.1', NULL, NULL, '2026-04-13 00:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('37', '1', NULL, 'login', '/login.php', '192.168.1.1', NULL, NULL, '2026-04-12 23:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('38', '2', NULL, 'page_view', '/categories.php', '10.0.0.1', NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('39', '2', NULL, 'page_view', '/article.php?id=123', '10.0.0.1', NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';
INSERT INTO `user_analytics` VALUES ('40', NULL, NULL, 'page_view', '/rss.php', '203.0.113.1', NULL, NULL, '2026-04-13 01:54:53', '2026-04-13 01:54:53';


-- Table structure for `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 0,
  `newsletter_subscription` tinyint(1) DEFAULT 1,
  `profile_public` tinyint(1) DEFAULT 0,
  `show_activity` tinyint(1) DEFAULT 1,
  `preferred_categories` text DEFAULT NULL,
  `language_preference` varchar(10) DEFAULT 'en',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `experience_level` varchar(20) DEFAULT 'junior',
  `verification_status` enum('unverified','verified','premium') DEFAULT 'unverified',
  `specialization` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `profile_views` int(11) DEFAULT 0,
  `login_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','reporter','user') DEFAULT 'user',
  `admin_level` int(11) DEFAULT 0,
  `admin_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`admin_permissions`)),
  `application_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `applied_role` enum('editor','reporter') DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES ('1', 'Admin', 'admin@pklivenews.com', NULL, NULL, NULL, '0', '1', '1', '0', '1', '0', '1', '8', 'en', NULL, NULL, '9938e23f903f9b33f9c9114e7bb9c4ff07df4be6327ef3093d3cf61d9dd8b552', '2026-04-24 21:45:23', NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$2nQGgzSApmGTnnwcJU79bOu9i6L64bCli/qlcNBfUDEqbKvC3PK/2', 'admin', '100', NULL, 'none', NULL, 'active', '2026-04-09 10:34:15';
INSERT INTO `users` VALUES ('8', 'Muhammad Ibraheem', 'ibraheem47074@gmail.com', NULL, NULL, 'uploads/users/69d86e74b395b.jpg', '0', '0', '1', '0', '1', '0', '1', NULL, 'en', '39f5c27a69dd0275b46d54b3ce91fc079137c0b5716bab3a1e021f0f475854ae', '2026-04-11 23:57:37', NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$Me./pOd5ui2pfRe.q/X.Pe2gIQZkWGHAKLCJ.itCLCl1s1eQIBOvO', 'reporter', '0', NULL, 'none', NULL, 'active', '2026-04-10 00:52:05';
INSERT INTO `users` VALUES ('9', 'Muhammad Kashif', 'kashif47074@gmail.com', '03300394061', '', NULL, '0', '0', '1', '0', '1', '0', '1', NULL, 'en', NULL, NULL, NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$1pmwpC8HfQw01sPAvRvpS.J7nm2SWOFiDhfePxeUXADXcilxrtFyu', 'editor', '0', NULL, 'none', NULL, 'active', '2026-04-10 09:30:18';
INSERT INTO `users` VALUES ('10', 'saim iltaf', 'ibraheeem47074@gmail.com', '03300394061', NULL, NULL, '0', '0', '1', '0', '1', '0', '1', NULL, 'en', NULL, NULL, NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$LymDZYRoKOgnvei9Q4Ts2evqiqtSbNv94o4GoReEhMA/P73naemBG', 'user', '0', NULL, 'none', NULL, 'active', '2026-04-11 07:58:16';
INSERT INTO `users` VALUES ('11', 'Salman ali', 'salman47074@gmail.com', '+92 3118195630', NULL, NULL, '0', '0', '1', '0', '1', '0', '1', NULL, 'en', NULL, NULL, NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$E4uhQOSXwzNcJvG6kHZ7zuvtWwQFrlyIECMTKdK7WSDikMxeZ./f2', 'user', '0', NULL, 'none', NULL, 'active', '2026-04-12 23:35:48';
INSERT INTO `users` VALUES ('13', 'kashif khan', 'kashifkhantkking@gmail.com', '+92 3118195630', NULL, NULL, '0', '0', '1', '0', '1', '0', '1', NULL, 'en', NULL, NULL, NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$z2kCQNd6iuy2IudEqZUWuedlnguRepBK4WWYadyu.2UKIvoF1mv6K', 'reporter', '0', NULL, 'none', NULL, 'active', '2026-04-13 11:25:09';
INSERT INTO `users` VALUES ('14', 'hasnain', 'Hasnain@gmail.com', '03300394061', NULL, NULL, '0', '0', '1', '0', '1', '0', '1', '8', 'ur', NULL, NULL, NULL, NULL, NULL, 'junior', 'unverified', NULL, NULL, '0', '0', NULL, '$2y$10$QrAxfSTHsZY0icoc46ubSOk.cRxrrUuNYN9sNMZf3yZmmXkvGR5/m', 'user', '0', NULL, 'rejected', NULL, 'active', '2026-04-25 08:05:00';



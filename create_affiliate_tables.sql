-- Create affiliate_products table
CREATE TABLE IF NOT EXISTS `affiliate_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `short_description` varchar(500),
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `image_url` varchar(500),
  `affiliate_url` varchar(500) NOT NULL,
  `affiliate_network` enum('amazon','aliexpress','other') DEFAULT 'amazon',
  `category_id` int(11) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `review_count` int(11) DEFAULT '0',
  `availability` enum('in_stock','out_of_stock','limited') DEFAULT 'in_stock',
  `brand` varchar(100),
  `model` varchar(100),
  `tags` varchar(255),
  `featured` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `click_count` int(11) DEFAULT '0',
  `conversion_count` int(11) DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create affiliate_categories table
CREATE TABLE IF NOT EXISTS `affiliate_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(50),
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create affiliate_clicks table
CREATE TABLE IF NOT EXISTS `affiliate_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `referrer` varchar(500),
  `click_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `converted` tinyint(1) DEFAULT '0',
  `conversion_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `click_date` (`click_date`),
  KEY `converted` (`converted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint
ALTER TABLE `affiliate_products` ADD CONSTRAINT `fk_affiliate_products_category` FOREIGN KEY (`category_id`) REFERENCES `affiliate_categories` (`id`) ON DELETE SET NULL;

-- Insert default categories
INSERT IGNORE INTO `affiliate_categories` (name, slug, description, icon, sort_order) VALUES
('Electronics', 'electronics', 'Mobile phones, laptops, gadgets', 'fa-laptop', 1),
('Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'fa-mobile', 2),
('Laptops', 'laptops', 'Laptops and computers', 'fa-laptop', 3),
('Gaming', 'gaming', 'Gaming consoles and accessories', 'fa-gamepad', 4),
('Cameras', 'cameras', 'Digital cameras and photography', 'fa-camera', 5),
('Audio', 'audio', 'Headphones, speakers, audio equipment', 'fa-headphones', 6),
('Smart Home', 'smart-home', 'Smart home devices and IoT', 'fa-home', 7),
('Fashion', 'fashion', 'Clothing and accessories', 'fa-tshirt', 8),
('Sports', 'sports', 'Sports equipment and gear', 'fa-football-ball', 9),
('Books', 'books', 'Books and educational materials', 'fa-book', 10);

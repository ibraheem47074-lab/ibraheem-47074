-- Multi-Language Support Database Update
-- This script adds multi-language support to PK Live News

-- Create languages table
CREATE TABLE IF NOT EXISTS `languages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default languages
INSERT INTO `languages` (`code`, `name`, `native_name`, `flag_icon`, `is_active`, `sort_order`) VALUES
('en', 'English', 'English', '🇺🇸', 1, 1),
('ur', 'Urdu', 'اردو', '🇵🇰', 1, 2),
('hi', 'Hindi', 'हिन्दी', '🇮🇳', 1, 3),
('zh', 'Chinese', '中文', '🇨🇳', 1, 4),
('ps', 'Pashto', 'پښتو', '🇦🇫', 1, 5);

-- Add language columns to news table
ALTER TABLE `news` 
ADD COLUMN `language_code` varchar(10) DEFAULT 'en' AFTER `status`,
ADD COLUMN `title_ur` text DEFAULT NULL AFTER `title`,
ADD COLUMN `title_hi` text DEFAULT NULL AFTER `title_ur`,
ADD COLUMN `title_zh` text DEFAULT NULL AFTER `title_hi`,
ADD COLUMN `title_ps` text DEFAULT NULL AFTER `title_zh`,
ADD COLUMN `content_ur` longtext DEFAULT NULL AFTER `content`,
ADD COLUMN `content_hi` longtext DEFAULT NULL AFTER `content_ur`,
ADD COLUMN `content_zh` longtext DEFAULT NULL AFTER `content_hi`,
ADD COLUMN `content_ps` longtext DEFAULT NULL AFTER `content_zh`,
ADD COLUMN `summary_ur` text DEFAULT NULL AFTER `summary`,
ADD COLUMN `summary_hi` text DEFAULT NULL AFTER `summary_ur`,
ADD COLUMN `summary_zh` text DEFAULT NULL AFTER `summary_hi`,
ADD COLUMN `summary_ps` text DEFAULT NULL AFTER `summary_zh`;

-- Add language columns to categories table
ALTER TABLE `categories`
ADD COLUMN `name_ur` varchar(255) DEFAULT NULL AFTER `name`,
ADD COLUMN `name_hi` varchar(255) DEFAULT NULL AFTER `name_ur`,
ADD COLUMN `name_zh` varchar(255) DEFAULT NULL AFTER `name_hi`,
ADD COLUMN `name_ps` varchar(255) DEFAULT NULL AFTER `name_zh`,
ADD COLUMN `description_ur` text DEFAULT NULL AFTER `description`,
ADD COLUMN `description_hi` text DEFAULT NULL AFTER `description_ur`,
ADD COLUMN `description_zh` text DEFAULT NULL AFTER `description_hi`,
ADD COLUMN `description_ps` text DEFAULT NULL AFTER `description_zh`;

-- Create user language preferences table
CREATE TABLE IF NOT EXISTS `user_language_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `language_code` varchar(10) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_language` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create site settings table for language configuration
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default language settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('default_language', 'en', 'string', 'Default language for the website'),
('enable_language_switcher', '1', 'boolean', 'Enable/disable language switcher'),
('show_language_flags', '1', 'boolean', 'Show country flags in language switcher'),
('auto_detect_language', '1', 'boolean', 'Auto-detect user language from browser'),
('multilingual_seo', '1', 'boolean', 'Enable multilingual SEO (hreflang tags)');

-- Add index for better performance
ALTER TABLE `news` ADD INDEX `idx_language_code` (`language_code`);
ALTER TABLE `categories` ADD INDEX `idx_language_code` (`language_code`);

-- Update existing news to have default language
UPDATE `news` SET `language_code` = 'en' WHERE `language_code` IS NULL OR `language_code` = '';

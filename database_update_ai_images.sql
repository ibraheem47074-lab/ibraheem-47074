-- AI Image Generation System Database Update
-- Add missing fields for AI image management

-- Create ai_settings table first (required for AI system)
CREATE TABLE IF NOT EXISTS `ai_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string', 'number', 'boolean', 'json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add AI image type field to distinguish between RSS and AI generated images
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `image_type` ENUM('rss', 'ai', 'manual', 'template') DEFAULT 'manual' AFTER `image`;

-- Add AI image status field
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_status` ENUM('pending', 'generating', 'completed', 'failed', 'approved', 'rejected') DEFAULT 'pending' AFTER `image_provider`;

-- Add AI image error message field
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_error` TEXT NULL AFTER `ai_image_status`;

-- Add AI image metadata field for additional information
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_metadata` JSON NULL AFTER `ai_image_error`;

-- Insert default AI settings (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO `ai_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('ai_image_generation_enabled', 'true', 'boolean', 'Enable AI image generation for news articles'),
('ai_default_provider', 'openai', 'string', 'Default AI provider for image generation'),
('ai_image_quality', 'standard', 'string', 'Default image quality (standard, high)'),
('ai_image_style', 'realistic', 'string', 'Default image style (realistic, cartoon, infographic)'),
('ai_auto_generate_for_rss', 'true', 'boolean', 'Automatically generate images for RSS articles without images'),
('ai_watermark_enabled', 'true', 'boolean', 'Add AI watermark to generated images'),
('ai_max_generation_attempts', '3', 'number', 'Maximum attempts to generate an image'),
('ai_generation_timeout', '60', 'number', 'Timeout in seconds for AI image generation'),
('openai_api_key', '', 'string', 'OpenAI API key for DALL-E image generation'),
('stability_api_key', '', 'string', 'Stability AI API key for Stable Diffusion'),
('replicate_api_key', '', 'string', 'Replicate API key for AI models'),
('ai_prompt_template', 'Professional news photograph of: {title}. Scene: {category_context}. Style: professional news photography, high quality, realistic, photojournalistic style, clear and detailed', 'string', 'Template for AI image generation prompts'),
('ai_negative_prompt_template', 'cartoon, anime, illustration, text, watermark, signature, inappropriate content', 'string', 'Negative prompts for AI image generation');

-- Create AI image generation log table
CREATE TABLE IF NOT EXISTS `ai_image_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `prompt` text NOT NULL,
  `status` enum('pending', 'generating', 'completed', 'failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `generation_time` decimal(10,3) DEFAULT NULL COMMENT 'Generation time in seconds',
  `image_url` varchar(500) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_news_id` (`news_id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create AI image templates table for template-based generation
CREATE TABLE IF NOT EXISTS `ai_image_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `template_type` enum('background', 'overlay', 'full') NOT NULL DEFAULT 'background',
  `background_image` varchar(255) DEFAULT NULL,
  `overlay_image` varchar(255) DEFAULT NULL,
  `text_position` enum('top', 'center', 'bottom') DEFAULT 'center',
  `text_color` varchar(7) DEFAULT '#FFFFFF',
  `font_size` int(11) DEFAULT 48,
  `font_style` enum('bold', 'normal', 'italic') DEFAULT 'bold',
  `template_data` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO `ai_image_templates` (`name`, `category_id`, `template_type`, `text_position`, `text_color`, `font_size`, `font_style`) VALUES
('Breaking News Red', NULL, 'background', 'center', '#FFFFFF', 48, 'bold'),
('Politics Blue', NULL, 'background', 'bottom', '#FFFFFF', 42, 'bold'),
('Business Gray', NULL, 'background', 'center', '#333333', 36, 'normal'),
('Technology Purple', NULL, 'background', 'top', '#FFFFFF', 44, 'bold'),
('Sports Green', NULL, 'background', 'center', '#FFFFFF', 46, 'bold'),
('Entertainment Orange', NULL, 'background', 'bottom', '#FFFFFF', 40, 'bold');

-- Add indexes for better performance (using IF NOT EXISTS approach)
ALTER TABLE `news` ADD INDEX IF NOT EXISTS `idx_image_type` (`image_type`);
ALTER TABLE `news` ADD INDEX IF NOT EXISTS `idx_ai_image_status` (`ai_image_status`);
ALTER TABLE `news` ADD INDEX IF NOT EXISTS `idx_image_provider` (`image_provider`);

-- Update existing RSS imported articles to have correct image type (safe update)
UPDATE `news` SET `image_type` = 'rss' WHERE `news_type` = 'rss_import' AND `image` IS NOT NULL AND `image` != '' AND `image_type` = 'manual';

-- Set AI image status for articles that already have AI generated images (safe update)
UPDATE `news` SET `ai_image_status` = 'completed' WHERE `image_provider` IS NOT NULL AND `image_provider` != '' AND `ai_image_status` = 'pending';

-- Create function to check if AI image generation is needed (optional, can be added later)
-- DELIMITER //
-- CREATE FUNCTION `needs_ai_image`(
--     news_id INT,
--     current_image VARCHAR(255),
--     image_type VARCHAR(50),
--     auto_generate BOOLEAN
-- ) RETURNS BOOLEAN
-- READS SQL DATA
-- DETERMINISTIC
-- BEGIN
--     DECLARE has_image BOOLEAN;
--     DECLARE is_rss_import BOOLEAN;
--     
--     SET has_image = (current_image IS NOT NULL AND current_image != '');
--     SET is_rss_import = (SELECT news_type FROM news WHERE id = news_id) = 'rss_import';
--     
--     RETURN (NOT has_image AND auto_generate AND is_rss_import) OR 
--            (image_type = 'ai' AND ai_image_status = 'pending');
-- END//
-- DELIMITER ;

-- Create trigger to automatically set image type (optional, can be added later)
-- DELIMITER //
-- CREATE TRIGGER `before_news_insert` 
-- BEFORE INSERT ON `news`
-- FOR EACH ROW
-- BEGIN
--     IF NEW.image IS NOT NULL AND NEW.image != '' THEN
--         IF NEW.news_type = 'rss_import' THEN
--             SET NEW.image_type = 'rss';
--         ELSEIF NEW.image_provider IS NOT NULL THEN
--             SET NEW.image_type = 'ai';
--         ELSE
--             SET NEW.image_type = 'manual';
--         END IF;
--     ELSE
--         SET NEW.image_type = 'manual';
--     END IF;
-- END//
-- DELIMITER ;

-- Final completion message
SELECT 'AI Image Generation System Database Update Completed Successfully!' as status;

<?php
/**
 * Quick Database Setup Script
 * Creates the AI settings table to fix the error
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>AI Image System - Database Setup</h2>";

// Create ai_settings table
$createSettingsTable = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $createSettingsTable)) {
    echo "✅ ai_settings table created successfully<br>";
} else {
    echo "❌ Error creating ai_settings table: " . mysqli_error($conn) . "<br>";
}

// Insert default settings
$defaultSettings = [
    'ai_image_generation_enabled' => 'true',
    'ai_default_provider' => 'openai',
    'ai_image_quality' => 'standard',
    'ai_image_style' => 'realistic',
    'ai_auto_generate_for_rss' => 'true',
    'ai_watermark_enabled' => 'true',
    'ai_max_generation_attempts' => '3',
    'ai_generation_timeout' => '60',
    'openai_api_key' => '',
    'stability_api_key' => '',
    'replicate_api_key' => '',
    'ai_prompt_template' => 'Professional news photograph of: {title}. Scene: {category_context}. Style: professional news photography, high quality, realistic, photojournalistic style, clear and detailed',
    'ai_negative_prompt_template' => 'cartoon, anime, illustration, text, watermark, signature, inappropriate content'
];

echo "<h3>Inserting Default Settings:</h3>";
foreach ($defaultSettings as $key => $value) {
    $insertQuery = "INSERT INTO ai_settings (setting_key, setting_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "✅ Setting '$key' inserted/updated<br>";
    } else {
        echo "❌ Error inserting '$key': " . mysqli_stmt_error($stmt) . "<br>";
    }
    mysqli_stmt_close($stmt);
}

// Add missing columns to news table if they don't exist
$alterNewsQueries = [
    "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `image_type` ENUM('rss', 'ai', 'manual', 'template') DEFAULT 'manual' AFTER `image`",
    "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_status` ENUM('pending', 'generating', 'completed', 'failed', 'approved', 'rejected') DEFAULT 'pending' AFTER `image_provider`",
    "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_error` TEXT NULL AFTER `ai_image_status`",
    "ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `ai_image_metadata` JSON NULL AFTER `ai_image_error`"
];

echo "<h3>Adding Missing Columns to News Table:</h3>";
foreach ($alterNewsQueries as $query) {
    if (mysqli_query($conn, $query)) {
        echo "✅ Column added successfully<br>";
    } else {
        echo "ℹ️ " . mysqli_error($conn) . "<br>";
    }
}

// Create ai_image_logs table
$createLogsTable = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $createLogsTable)) {
    echo "✅ ai_image_logs table created successfully<br>";
} else {
    echo "❌ Error creating ai_image_logs table: " . mysqli_error($conn) . "<br>";
}

echo "<h2>✅ Database Setup Complete!</h2>";
echo "<p>You can now access the AI Image Management system at: <a href='admin/ai_image_management.php'>admin/ai_image_management.php</a></p>";
echo "<p><strong>Note:</strong> You'll need to configure your API keys in the AI Settings page.</p>";
?>

<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete Database Setup - PK Live News</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>PK Live News - Complete Database Setup</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Creating All Required Tables...</h2>";

// Function to execute SQL and show result
function executeSQL($sql, $successMessage, $errorMessage) {
    global $conn;
    try {
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ " . $successMessage . "</p>";
            return true;
        } else {
            echo "<p class='error'>✗ " . $errorMessage . ": " . mysqli_error($conn) . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ " . $errorMessage . ": " . $e->getMessage() . "</p>";
        return false;
    }
}

// Create categories table
$categories_sql = "
CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($categories_sql, "Categories table created", "Error creating categories table");

// Create news table
$news_sql = "
CREATE TABLE IF NOT EXISTS `news` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `content` longtext NOT NULL,
    `excerpt` text DEFAULT NULL,
    `category_id` int(11) DEFAULT NULL,
    `author_id` int(11) DEFAULT NULL,
    `featured_image` varchar(255) DEFAULT NULL,
    `status` enum('published','draft','archived') DEFAULT 'draft',
    `featured` tinyint(1) DEFAULT 0,
    `breaking_news` tinyint(1) DEFAULT 0,
    `views` int(11) DEFAULT 0,
    `meta_title` varchar(255) DEFAULT NULL,
    `meta_description` text DEFAULT NULL,
    `meta_keywords` varchar(255) DEFAULT NULL,
    `published_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_news_category` (`category_id`),
    KEY `idx_news_author` (`author_id`),
    KEY `idx_news_status` (`status`),
    KEY `idx_news_featured` (`featured`),
    KEY `idx_news_breaking` (`breaking_news`),
    KEY `idx_news_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($news_sql, "News table created", "Error creating news table");

// Create comments table
$comments_sql = "
CREATE TABLE IF NOT EXISTS `comments` (
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
    KEY `idx_comments_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($comments_sql, "Comments table created", "Error creating comments table");

// Create tags table
$tags_sql = "
CREATE TABLE IF NOT EXISTS `tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `slug` varchar(50) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_tags_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($tags_sql, "Tags table created", "Error creating tags table");

// Create news_tags junction table
$news_tags_sql = "
CREATE TABLE IF NOT EXISTS `news_tags` (
    `news_id` int(11) NOT NULL,
    `tag_id` int(11) NOT NULL,
    PRIMARY KEY (`news_id`, `tag_id`),
    KEY `idx_news_tags_news` (`news_id`),
    KEY `idx_news_tags_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($news_tags_sql, "News-Tags junction table created", "Error creating news-tags table");

// Create live_stream table
$live_stream_sql = "
CREATE TABLE IF NOT EXISTS `live_stream` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `stream_url` varchar(500) NOT NULL,
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
";
executeSQL($live_stream_sql, "Live stream table created", "Error creating live_stream table");

// Create bookmarks table
$bookmarks_sql = "
CREATE TABLE IF NOT EXISTS `bookmarks` (
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
";
executeSQL($bookmarks_sql, "Bookmarks table created", "Error creating bookmarks table");

// Create news_analytics table
$news_analytics_sql = "
CREATE TABLE IF NOT EXISTS `news_analytics` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($news_analytics_sql, "News analytics table created", "Error creating news_analytics table");

// Create notifications table
$notifications_sql = "
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `title` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `type` enum('info','success','warning','error') DEFAULT 'info',
    `is_read` tinyint(1) DEFAULT 0,
    `action_url` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_read` (`is_read`),
    KEY `idx_notifications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($notifications_sql, "Notifications table created", "Error creating notifications table");

// Create breaking_news_alerts table
$breaking_news_alerts_sql = "
CREATE TABLE IF NOT EXISTS `breaking_news_alerts` (
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
";
executeSQL($breaking_news_alerts_sql, "Breaking news alerts table created", "Error creating breaking_news_alerts table");

// Create languages table for multi-language support
$languages_sql = "
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
";
executeSQL($languages_sql, "Languages table created", "Error creating languages table");

// Create site_settings table
$site_settings_sql = "
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
";
executeSQL($site_settings_sql, "Site settings table created", "Error creating site_settings table");

// Create user_language_preferences table
$user_language_preferences_sql = "
CREATE TABLE IF NOT EXISTS `user_language_preferences` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `language_code` varchar(10) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_language` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($user_language_preferences_sql, "User language preferences table created", "Error creating user_language_preferences table");

// Insert default categories
$categories = [
    ['Politics', 'politics', 'Political news and updates'],
    ['Sports', 'sports', 'Sports news and coverage'],
    ['Technology', 'technology', 'Technology and tech news'],
    ['Business', 'business', 'Business and economy news'],
    ['Entertainment', 'entertainment', 'Entertainment news'],
    ['Health', 'health', 'Health and medical news'],
    ['Education', 'education', 'Education news'],
    ['International', 'international', 'International news']
];

foreach ($categories as $category) {
    $check_sql = "SELECT id FROM categories WHERE slug = '" . $category[1] . "'";
    $result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO categories (name, slug, description) VALUES ('" . $category[0] . "', '" . $category[1] . "', '" . $category[2] . "')";
        executeSQL($insert_sql, "Category '{$category[0]}' inserted", "Error inserting category '{$category[0]}'");
    }
}

// Insert sample news
$check_news = "SELECT id FROM news LIMIT 1";
$result = mysqli_query($conn, $check_news);
if (mysqli_num_rows($result) == 0) {
    $sample_news_sql = "
    INSERT INTO news (title, slug, content, excerpt, category_id, author_id, status, featured, published_at) VALUES
    ('Welcome to PK Live News', 'welcome-to-pk-live-news', '<p>This is a sample news article for PK Live News platform.</p><p>Our platform provides comprehensive news coverage across various categories including politics, sports, technology, business, entertainment, health, education, and international news.</p>', 'Welcome to our comprehensive news platform', 1, 1, 'published', 1, NOW()),
    ('Breaking: Latest Updates', 'breaking-latest-updates', '<p>This is a breaking news example.</p><p>Stay tuned for more updates on this developing story.</p>', 'Breaking news story with latest updates', 1, 1, 'published', 1, NOW()),
    ('Technology Trends 2024', 'technology-trends-2024', '<p>Explore the latest technology trends shaping our future.</p><p>From AI to quantum computing, discover what\'s next in tech.</p>', 'Latest technology trends and innovations', 3, 1, 'published', 0, NOW())
    ";
    executeSQL($sample_news_sql, "Sample news articles inserted", "Error inserting sample news");
}

// Insert sample live stream
$check_live_stream = "SELECT id FROM live_stream LIMIT 1";
$result = mysqli_query($conn, $check_live_stream);
if (mysqli_num_rows($result) == 0) {
    $live_stream_sql = "
    INSERT INTO live_stream (title, stream_url, embed_code, status, description) VALUES
    ('PK Live News Stream', 'https://www.youtube.com/embed/jfKfPfyJRdk', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/jfKfPfyJRdk\" frameborder=\"0\" allowfullscreen></iframe>', 'offline', 'Main news streaming channel')
    ";
    executeSQL($live_stream_sql, "Sample live stream inserted", "Error inserting sample live stream");
}

// Insert default languages
$check_languages = "SELECT id FROM languages LIMIT 1";
$result = mysqli_query($conn, $check_languages);
if (mysqli_num_rows($result) == 0) {
    $languages_sql = "
    INSERT INTO languages (code, name, native_name, flag_icon, is_active, sort_order) VALUES
    ('en', 'English', 'English', '🇺🇸', 1, 1),
    ('ur', 'Urdu', 'اردو', '🇵🇰', 1, 2),
    ('hi', 'Hindi', 'हिन्दी', '🇮🇳', 1, 3),
    ('zh', 'Chinese', '中文', '🇨🇳', 1, 4),
    ('ps', 'Pashto', 'پښتو', '🇦🇫', 1, 5)
    ";
    executeSQL($languages_sql, "Default languages inserted", "Error inserting default languages");
}

// Insert default site settings
$check_settings = "SELECT id FROM site_settings LIMIT 1";
$result = mysqli_query($conn, $check_settings);
if (mysqli_num_rows($result) == 0) {
    $settings_sql = "
    INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
    ('default_language', 'en', 'string', 'Default language for the website'),
    ('enable_language_switcher', '1', 'boolean', 'Enable/disable language switcher'),
    ('show_language_flags', '1', 'boolean', 'Show country flags in language switcher'),
    ('auto_detect_language', '1', 'boolean', 'Auto-detect user language from browser'),
    ('multilingual_seo', '1', 'boolean', 'Enable multilingual SEO (hreflang tags)'),
    ('site_title', 'PK Live News', 'string', 'Website title'),
    ('site_description', 'Latest news and updates from Pakistan and around the world', 'string', 'Website description'),
    ('site_keywords', 'news, pakistan, breaking news, current affairs', 'string', 'Website meta keywords'),
    ('contact_email', 'admin@pklivenews.com', 'string', 'Contact email address'),
    ('social_facebook', 'https://facebook.com/pklivenews', 'string', 'Facebook page URL'),
    ('social_twitter', 'https://twitter.com/pklivenews', 'string', 'Twitter page URL'),
    ('social_youtube', 'https://youtube.com/pklivenews', 'string', 'YouTube channel URL')
    ";
    executeSQL($settings_sql, "Default site settings inserted", "Error inserting default site settings");
}

echo "<h3 class='success'>✅ Database Setup Complete!</h3>";
echo "<div class='alert alert-success'>
    <h4>Setup Completed Successfully!</h4>
    <p>All required tables have been created and sample data has been inserted.</p>
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ul>
        <li><a href='admin/login.php' class='btn btn-primary'>Go to Admin Login</a></li>
        <li>Login with: admin@pklivenews.com / admin123</li>
        <li><a href='index.php' class='btn btn-secondary'>View Website</a></li>
    </ul>
</div>";

echo "</div></div></div></body></html>";
?>

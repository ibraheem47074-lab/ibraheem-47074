<?php
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>News Sources Installation - PK Live News</title>
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
        <h1>PK Live News - News Sources Installation</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Installing News Sources System...</h2>";

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

// Create news_sources table
$news_sources_sql = "
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
";
executeSQL($news_sources_sql, "News sources table created", "Error creating news_sources table");

// Insert default RSS sources
$default_sources = [
    ['BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml', 'rss', 1, 'active'],
    ['CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss', 'rss', 1, 'active'],
    ['Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews', 'rss', 1, 'active'],
    ['Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml', 'rss', 1, 'active'],
    ['Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews', 'rss', 1, 'active'],
    ['Fox News', 'https://www.foxnews.com', 'https://www.foxnews.com/about/rss/feedburner/foxnews/latest', 'rss', 1, 'active'],
    ['The Guardian', 'https://www.theguardian.com', 'https://www.theguardian.com/world/rss', 'rss', 1, 'active'],
    ['NBC News', 'https://www.nbcnews.com', 'https://www.nbcnews.com/id/3032091/device/rss/rss.xml', 'rss', 1, 'active'],
    ['CBS News', 'https://www.cbsnews.com', 'https://www.cbsnews.com/rss/live/rss.rss', 'rss', 1, 'active'],
    ['NPR News', 'https://www.npr.org', 'https://feeds.npr.org/1001/rss.xml', 'rss', 1, 'active']
];

foreach ($default_sources as $source) {
    $check_sql = "SELECT id FROM news_sources WHERE name = '" . $source[0] . "'";
    $result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) 
                       VALUES ('" . $source[0] . "', '" . $source[1] . "', '" . $source[2] . "', '" . $source[3] . "', " . $source[4] . ", '" . $source[5] . "')";
        executeSQL($insert_sql, "News source '{$source[0]}' added", "Error adding news source '{$source[0]}'");
    } else {
        echo "<p class='info'>ℹ News source already exists: {$source[0]}</p>";
    }
}

// Add Pakistani news sources
$pakistani_sources = [
    ['Dawn News', 'https://www.dawn.com', 'https://www.dawn.com/feed/', 'rss', 1, 'active'],
    ['Geo News', 'https://www.geo.tv', 'https://www.geo.tv/rss/feed/', 'rss', 1, 'active'],
    ['ARY News', 'https://arynews.tv', 'https://arynews.tv/feed/', 'rss', 1, 'active'],
    ['Express Tribune', 'https://tribune.com.pk', 'https://tribune.com.pk/feed/', 'rss', 1, 'active'],
    ['The News International', 'https://www.thenews.com.pk', 'https://www.thenews.com.pk/rss/1/1', 'rss', 1, 'active']
];

foreach ($pakistani_sources as $source) {
    $check_sql = "SELECT id FROM news_sources WHERE name = '" . $source[0] . "'";
    $result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) 
                       VALUES ('" . $source[0] . "', '" . $source[1] . "', '" . $source[2] . "', '" . $source[3] . "', " . $source[4] . ", '" . $source[5] . "')";
        executeSQL($insert_sql, "Pakistani news source '{$source[0]}' added", "Error adding Pakistani news source '{$source[0]}'");
    } else {
        echo "<p class='info'>ℹ Pakistani news source already exists: {$source[0]}</p>";
    }
}

// Create RSS import log table (optional)
$rss_log_sql = "
CREATE TABLE IF NOT EXISTS `rss_import_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `source_id` int(11) DEFAULT NULL,
    `articles_imported` int(11) DEFAULT 0,
    `status` enum('success','error','partial') DEFAULT 'success',
    `error_message` text DEFAULT NULL,
    `import_time` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_source_id` (`source_id`),
    KEY `idx_import_time` (`import_time`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($rss_log_sql, "RSS import log table created", "Error creating RSS import log table");

// Test RSS feeds functionality
echo "<h3>Testing RSS Feeds...</h3>";
$test_sources = [
    ['BBC News', 'https://feeds.bbci.co.uk/news/rss.xml'],
    ['Dawn News', 'https://www.dawn.com/feed/']
];

foreach ($test_sources as $source) {
    $rss_test = @simplexml_load_file($source[1]);
    if ($rss_test !== false) {
        echo "<p class='success'>✓ RSS feed test passed: {$source[0]}</p>";
    } else {
        echo "<p class='error'>✗ RSS feed test failed: {$source[0]} - {$source[1]}</p>";
    }
}

echo "<h3 class='success'>✅ News Sources Installation Complete!</h3>";
echo "<div class='alert alert-success'>
    <h4>Installation Completed Successfully!</h4>
    <p>The news sources system has been installed with international and Pakistani news sources.</p>
    <hr>
    <p><strong>Features Installed:</strong></p>
    <ul>
        <li>News sources table for RSS feed management</li>
        <li>10 international news sources (BBC, CNN, Reuters, etc.)</li>
        <li>5 Pakistani news sources (Dawn, Geo, ARY, etc.)</li>
        <li>RSS import log table for tracking imports</li>
        <li>RSS feed validation and testing</li>
    </ul>
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ul>
        <li><a href='manage-sources.php' class='btn btn-primary'>Manage News Sources</a></li>
        <li><a href='scrape-news.php' class='btn btn-success'>Import News from RSS</a></li>
        <li><a href='trusted_sources_manager.php' class='btn btn-info'>Manage Trusted Sources</a></li>
        <li><a href='dashboard.php' class='btn btn-secondary'>Admin Dashboard</a></li>
    </ul>
    <div class='mt-3'>
        <small class='text-muted'>
            <strong>Note:</strong> You can now import news articles from RSS feeds using the scrape-news.php script.
            Set up a cron job to automatically import news at regular intervals.
        </small>
    </div>
</div>";

echo "</div></div></div></body></html>";
?>

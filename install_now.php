<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Installation - PK Live News</title>
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
        <h1>PK Live News - Installation</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Installing Required Tables...</h2>";

// Function to execute SQL and show result
function executeSQL($sql, $successMessage, $errorMessage) {
    global $conn;
    try {
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>â " . $successMessage . "</p>";
            return true;
        } else {
            echo "<p class='error'>â " . $errorMessage . ": " . mysqli_error($conn) . "</p>";
            return false;
        }
    } catch (Exception $e) {
        echo "<p class='error'>â " . $errorMessage . ": " . $e->getMessage() . "</p>";
        return false;
    }
}

// Create news_editions table
$news_editions_sql = "
CREATE TABLE IF NOT EXISTS `news_editions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text,
    `edition_date` date NOT NULL,
    `status` enum('draft','published','archived') DEFAULT 'draft',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_edition_date` (`edition_date`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($news_editions_sql, "News editions table created", "Error creating news_editions table");

// Create edition_templates table
$edition_templates_sql = "
CREATE TABLE IF NOT EXISTS `edition_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `template_html` longtext,
    `css_styles` text,
    `is_default` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($edition_templates_sql, "Edition templates table created", "Error creating edition_templates table");

// Create edition_articles table (link table)
$edition_articles_sql = "
CREATE TABLE IF NOT EXISTS `edition_articles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `edition_id` int(11) NOT NULL,
    `article_id` int(11) NOT NULL,
    `order_index` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_edition_article` (`edition_id`,`article_id`),
    KEY `idx_edition_id` (`edition_id`),
    KEY `idx_article_id` (`article_id`),
    FOREIGN KEY (`edition_id`) REFERENCES `news_editions` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`article_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
executeSQL($edition_articles_sql, "Edition articles link table created", "Error creating edition_articles table");

// Insert default template
$check_template = "SELECT id FROM edition_templates LIMIT 1";
$result = mysqli_query($conn, $check_template);
if (mysqli_num_rows($result) == 0) {
    $default_template_sql = "
    INSERT INTO edition_templates (name, description, template_html, css_styles, is_default) VALUES
    ('Default Template', 'Default newspaper edition template', 
    '<div class=\"edition-header\">
        <h1>{{edition_title}}</h1>
        <p class=\"edition-date\">{{edition_date}}</p>
    </div>
    <div class=\"edition-content\">
        {{articles_loop}}
        <div class=\"article\">
            <h3>{{article_title}}</h3>
            <p>{{article_summary}}</p>
        </div>
        {{articles_loop_end}}
    </div>', 
    '.edition-header { text-align: center; margin-bottom: 30px; }
    .edition-content { margin: 20px 0; }
    .article { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; }', 
    1)";
    executeSQL($default_template_sql, "Default template inserted", "Error inserting default template");
}

echo "<h3 class='success'>â Installation Complete!</h3>";
echo "<div class='alert alert-success'>
    <h4>Installation Completed Successfully!</h4>
    <p>All required tables have been created.</p>
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ul>
        <li><a href='admin/manage-editions.php' class='btn btn-primary'>Manage Editions</a></li>
        <li><a href='admin/dashboard.php' class='btn btn-secondary'>Go to Admin Dashboard</a></li>
        <li><a href='index.php' class='btn btn-info'>View Website</a></li>
    </ul>
</div>";

echo "</div></div></div></body></html>";
?>

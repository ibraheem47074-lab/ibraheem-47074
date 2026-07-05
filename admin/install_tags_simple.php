<?php
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Tags Installation - PK Live News</title>
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
        <h1>PK Live News - Tags Installation</h1>
        <div class='card'>
            <div class='card-body'>
                <h2>Installing Tags System...</h2>";

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

// Insert sample tags
$sample_tags = [
    'Breaking News',
    'Politics',
    'Sports',
    'Technology',
    'Business',
    'Entertainment',
    'Health',
    'Education',
    'International',
    'Pakistan',
    'COVID-19',
    'Economy',
    'Elections',
    'Cricket',
    'Football',
    'Science',
    'Weather',
    'Climate',
    'Social Media',
    'Security'
];

foreach ($sample_tags as $tag_name) {
    $slug = strtolower(str_replace(' ', '-', $tag_name));
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    $check_sql = "SELECT id FROM tags WHERE slug = '" . $slug . "'";
    $result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($result) == 0) {
        $insert_sql = "INSERT INTO tags (name, slug) VALUES ('" . $tag_name . "', '" . $slug . "')";
        executeSQL($insert_sql, "Tag '{$tag_name}' inserted", "Error inserting tag '{$tag_name}'");
    } else {
        echo "<p class='info'>ℹ Tag already exists: {$tag_name}</p>";
    }
}

// Link some sample news articles with tags
$check_news_tags = "SELECT COUNT(*) as count FROM news_tags";
$result = mysqli_query($conn, $check_news_tags);
$news_tags_count = mysqli_fetch_assoc($result)['count'];

if ($news_tags_count == 0) {
    // Get some news articles and tags
    $news_query = "SELECT id FROM news WHERE status = 'published' LIMIT 10";
    $news_result = mysqli_query($conn, $news_query);
    $tags_query = "SELECT id FROM tags LIMIT 15";
    $tags_result = mysqli_query($conn, $tags_query);
    
    $news_articles = [];
    $tags = [];
    
    while ($news = mysqli_fetch_assoc($news_result)) {
        $news_articles[] = $news['id'];
    }
    
    while ($tag = mysqli_fetch_assoc($tags_result)) {
        $tags[] = $tag['id'];
    }
    
    // Link random tags to news articles
    foreach ($news_articles as $news_id) {
        $num_tags = rand(1, 4);
        $selected_tags = array_rand($tags, $num_tags);
        
        if (!is_array($selected_tags)) {
            $selected_tags = [$selected_tags];
        }
        
        foreach ($selected_tags as $tag_index) {
            $tag_id = $tags[$tag_index];
            $link_sql = "INSERT IGNORE INTO news_tags (news_id, tag_id) VALUES ({$news_id}, {$tag_id})";
            executeSQL($link_sql, "Linked news {$news_id} with tag {$tag_id}", "Error linking news with tag");
        }
    }
}

// Create tag cloud view (optional)
$tag_cloud_sql = "
CREATE OR REPLACE VIEW `tag_cloud` AS
SELECT 
    t.id,
    t.name,
    t.slug,
    COUNT(nt.news_id) as usage_count,
    COUNT(nt.news_id) * 10 as weight
FROM tags t
LEFT JOIN news_tags nt ON t.id = nt.tag_id
LEFT JOIN news n ON nt.news_id = n.id AND n.status = 'published'
GROUP BY t.id, t.name, t.slug
ORDER BY usage_count DESC;
";

if (mysqli_query($conn, $tag_cloud_sql)) {
    echo "<p class='success'>✓ Tag cloud view created</p>";
} else {
    echo "<p class='error'>✗ Error creating tag cloud view: " . mysqli_error($conn) . "</p>";
}

echo "<h3 class='success'>✅ Tags Installation Complete!</h3>";
echo "<div class='alert alert-success'>
    <h4>Installation Completed Successfully!</h4>
    <p>The tags system has been installed with sample data.</p>
    <hr>
    <p><strong>Features Installed:</strong></p>
    <ul>
        <li>Tags table for storing article tags</li>
        <li>News-Tags junction table for linking articles with tags</li>
        <li>20 sample tags inserted</li>
        <li>Sample tag assignments for existing articles</li>
        <li>Tag cloud view for analytics</li>
    </ul>
    <hr>
    <p><strong>Next Steps:</strong></p>
    <ul>
        <li><a href='manage-tags.php' class='btn btn-primary'>Manage Tags</a></li>
        <li><a href='add-tag.php' class='btn btn-success'>Add New Tag</a></li>
        <li><a href='tag-analytics.php' class='btn btn-info'>View Tag Analytics</a></li>
        <li><a href='dashboard.php' class='btn btn-secondary'>Admin Dashboard</a></li>
    </ul>
</div>";

echo "</div></div></div></body></html>";
?>

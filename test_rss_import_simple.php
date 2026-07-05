<?php
/**
 * Simple RSS Import Test
 */
require_once 'config/database.php';

echo "<h2>Simple RSS Import Test</h2>";

// Test RSS feed fetching
echo "<h3>Testing RSS Feed Connection</h3>";

$test_feeds = [
    'Google News' => 'https://news.google.com/rss',
    'NPR' => 'https://feeds.npr.org/1001/rss.xml'
];

foreach ($test_feeds as $name => $url) {
    echo "<h4>Testing $name</h4>";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]
    ]);

    $xml_content = @file_get_contents($url, false, $context);
    
    if ($xml_content !== false) {
        echo "<p class='success'>✓ Successfully fetched RSS content</p>";
        
        $xml = @simplexml_load_string($xml_content);
        if ($xml !== false) {
            $items = count($xml->channel->item);
            echo "<p class='success'>✓ Parsed XML successfully - Found $items items</p>";
            
            // Show first item as example
            if ($items > 0) {
                $first_item = $xml->channel->item[0];
                $title = (string)$first_item->title;
                $link = (string)$first_item->link;
                $description = (string)$first_item->description;
                
                echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<strong>Sample Article:</strong><br>";
                echo "Title: " . htmlspecialchars(substr($title, 0, 100)) . "...<br>";
                echo "Link: " . htmlspecialchars(substr($link, 0, 100)) . "...<br>";
                echo "Description: " . htmlspecialchars(substr(strip_tags($description), 0, 200)) . "...";
                echo "</div>";
                
                // Try to insert this article
                $slug = create_slug($title) . '-' . time();
                $clean_content = strip_tags($description);
                $excerpt = substr($clean_content, 0, 200);
                
                // Check if already exists
                $check_exists = "SELECT id FROM news WHERE slug = ? OR source_url = ?";
                $check_stmt = mysqli_prepare($conn, $check_exists);
                mysqli_stmt_bind_param($check_stmt, 'ss', $slug, $link);
                mysqli_stmt_execute($check_stmt);
                $exists_result = mysqli_stmt_get_result($check_stmt);
                
                if (mysqli_num_rows($exists_result) == 0) {
                    $insert_sql = "INSERT INTO news (title, slug, content, excerpt, status, news_type, image_type, source_url, published_at, created_at) 
                                  VALUES (?, ?, ?, ?, 'published', 'rss', 'external', ?, NOW(), NOW())";
                    
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    mysqli_stmt_bind_param($insert_stmt, 'sssss', $title, $slug, $description, $excerpt, $link);
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        echo "<p class='success'>✓ Successfully imported sample article</p>";
                    } else {
                        echo "<p class='error'>✗ Error importing article: " . mysqli_error($conn) . "</p>";
                    }
                } else {
                    echo "<p class='info'>ℹ Article already exists in database</p>";
                }
            }
        } else {
            echo "<p class='error'>✗ Failed to parse XML</p>";
        }
    } else {
        echo "<p class='error'>✗ Failed to fetch RSS content</p>";
        echo "<p>This could be due to:</p>";
        echo "<ul>";
        echo "<li>Network connectivity issues</li>";
        echo "<li>Firewall blocking external connections</li>";
        echo "<li>RSS feed URL is not accessible</li>";
        echo "</ul>";
    }
}

// Check current article count
echo "<h3>Current Database Status</h3>";
$count_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
$count_result = mysqli_query($conn, $count_query);
$total_articles = mysqli_fetch_assoc($count_result)['total'];
echo "<p>Total published articles: <strong>$total_articles</strong></p>";

// Show recent articles
echo "<h3>Recent Articles</h3>";
$recent_query = "SELECT title, slug, status, news_type, created_at FROM news ORDER BY created_at DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

if (mysqli_num_rows($recent_result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Title</th><th>Status</th><th>Type</th><th>Created</th></tr>";
    
    while ($row = mysqli_fetch_assoc($recent_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><strong style='color: green;'>" . $row['status'] . "</strong></td>";
        echo "<td>" . $row['news_type'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No articles found in database</p>";
}

echo "<h3>Next Steps</h3>";
echo "<div class='info'>";
echo "<p><a href='index.php' target='_blank'>Visit Homepage</a> to see articles</p>";
echo "<p><a href='check_news.php' target='_blank'>Check News Status</a> for detailed info</p>";
echo "<p><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron' target='_blank'>Run Full RSS Import</a></p>";
echo "</div>";

mysqli_close($conn);
?>

<style>
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; background: #f0f8ff; padding: 10px; border-radius: 5px; }
</style>

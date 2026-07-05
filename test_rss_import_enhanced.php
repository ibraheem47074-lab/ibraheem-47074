<?php
require_once 'config/database.php';
require_once 'includes/enhanced_rss_parser.php';

header('Content-Type: text/plain');

echo "=== Enhanced RSS Import Test ===\n\n";

// Test the enhanced RSS parser
$query = "SELECT id, name, url FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 2";
$result = mysqli_query($conn, $query);

$total_articles_found = 0;
$total_articles_imported = 0;
$duplicates_found = 0;

while ($source = mysqli_fetch_assoc($result)) {
    echo "Testing source: " . $source['name'] . "\n";
    echo "URL: " . $source['url'] . "\n";
    
    try {
        $parser = new EnhancedRSSParser();
        $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
        $articles = $parser->parseRSS($rss_url);
        
        echo "Articles found in feed: " . count($articles) . "\n";
        $total_articles_found += count($articles);
        
        // Test import for first 5 articles to avoid duplicates
        $import_count = 0;
        foreach (array_slice($articles, 0, 5) as $article) {
            // Check for duplicates
            $check_query = "SELECT id FROM news WHERE title = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, 's', $article['title']);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) === 0) {
                // This would be imported (we're not actually inserting to avoid test data)
                $import_count++;
                $total_articles_imported++;
            } else {
                $duplicates_found++;
            }
            mysqli_stmt_close($check_stmt);
        }
        
        echo "New articles (first 5): " . $import_count . "\n";
        echo "Duplicates (first 5): " . (5 - $import_count) . "\n";
        
        if (count($articles) > 0) {
            echo "Sample article titles:\n";
            for ($i = 0; $i < min(3, count($articles)); $i++) {
                echo "- " . substr($articles[$i]['title'], 0, 80) . "...\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "=== Summary ===\n";
echo "Total articles found in feeds: " . $total_articles_found . "\n";
echo "Total new articles (sample): " . $total_articles_imported . "\n";
echo "Total duplicates (sample): " . $duplicates_found . "\n";

// Show current RSS parser settings
echo "\n=== RSS Parser Settings ===\n";
$parser = new EnhancedRSSParser();
$settings = $parser->getTimeout();
echo "cURL Timeout: " . $settings['timeout'] . " seconds\n";
echo "Connect Timeout: " . $settings['connect_timeout'] . " seconds\n";
echo "Max Execution Time: 300 seconds\n";
echo "Article Limit: 200 articles\n";

mysqli_close($conn);
?>

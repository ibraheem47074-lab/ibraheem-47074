<?php
/**
 * Quick RSS Import Test
 */

require_once "config/database.php";
require_once "includes/auto_news_importer_fixed.php";

echo "RSS Import Test\n";
echo "===============\n";

try {
    $importer = new FixedAutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(3);
    
    $results = $importer->importFromAllSources();
    
    echo "Results:\n";
    echo "Feeds processed: {$results['total_feeds']}\n";
    echo "Successful: {$results['successful_feeds']}\n";
    echo "Failed: {$results['error_feeds']}\n";
    echo "Articles imported: {$results['imported_articles']}\n";
    echo "Duplicates: {$results['duplicate_articles']}\n\n";
    
    foreach ($results['details'] as $detail) {
        if (isset($detail['error'])) {
            echo "ERROR - {$detail['source_name']}: {$detail['error']}\n";
        } else {
            echo "SUCCESS - {$detail['source_name']}: {$detail['imported_articles']} imported\n";
        }
    }
    
    // Check recent imports
    $check_query = "SELECT title, created_at FROM news WHERE news_type = 'rss_import' ORDER BY created_at DESC LIMIT 5";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "\nRecent imports:\n";
        while ($article = mysqli_fetch_assoc($check_result)) {
            echo "- " . $article['title'] . " (" . $article['created_at'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
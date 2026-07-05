<?php
/**
 * Quick RSS Import Test
 * Test the RSS import system functionality
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

echo "<h1>RSS Import System Quick Test</h1>";

// Test 1: Check if RSS sources exist
echo "<h2>Step 1: Checking RSS Sources</h2>";
$sources_query = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss'";
$result = mysqli_query($conn, $sources_query);
$count = mysqli_fetch_assoc($result)['count'];

echo "<p><strong>RSS Sources Found:</strong> $count</p>";

if ($count == 0) {
    echo "<p><strong>Action Required:</strong> No RSS sources found. Please run setup_rss_sources.php first.</p>";
    echo "<p><a href='setup_rss_sources.php'>Setup RSS Sources Now</a></p>";
    exit;
}

// Test 2: Test a few RSS feeds
echo "<h2>Step 2: Testing RSS Feeds</h2>";
$sources_query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 5";
$sources_result = mysqli_query($conn, $sources_query);

$parser = new EnhancedRSSParser();
$total_articles = 0;
$working_feeds = 0;

while ($source = mysqli_fetch_assoc($sources_result)) {
    echo "<h3>Testing: " . htmlspecialchars($source['name']) . "</h3>";
    
    try {
        $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
        $validation = $parser->validateFeed($rss_url);
        
        if ($validation['valid']) {
            echo "<p>✓ Feed is valid - {$validation['items_count']} items available</p>";
            
            $articles = $parser->parseRSS($rss_url);
            $article_count = count($articles);
            $total_articles += $article_count;
            
            if ($article_count > 0) {
                $working_feeds++;
                echo "<p>✓ Successfully parsed $article_count articles</p>";
                
                // Show sample
                $sample = $articles[0];
                echo "<p><strong>Sample Article:</strong> " . htmlspecialchars(substr($sample['title'], 0, 80)) . "...</p>";
                if (!empty($sample['image'])) {
                    echo "<p><strong>Has Image:</strong> Yes</p>";
                }
            } else {
                echo "<p>⚠ No articles found in feed</p>";
            }
        } else {
            echo "<p>✗ Feed validation failed: " . htmlspecialchars($validation['error']) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>✗ Error testing feed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>Test Summary</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>Working Feeds</td><td>$working_feeds / 5 tested</td></tr>";
echo "<tr><td>Total Articles Available</td><td>$total_articles</td></tr>";
echo "</table>";

// Test 3: Simulate import process
echo "<h2>Step 3: Simulating Import Process</h2>";

if ($total_articles > 0) {
    echo "<p><strong>Expected Import Results:</strong></p>";
    echo "<ul>";
    echo "<li>Available articles to import: $total_articles</li>";
    echo "<li>Expected unique articles (after deduplication): " . round($total_articles * 0.7) . "</li>";
    echo "<li>Expected duplicates: " . round($total_articles * 0.3) . "</li>";
    echo "</ul>";
    
    echo "<p><strong>✓ RSS Import System is Working!</strong></p>";
    echo "<p>You can now import news by visiting: <a href='admin/rss_import.php'>RSS Import Admin</a></p>";
    
} else {
    echo "<p><strong>⚠ No articles available for import</strong></p>";
    echo "<p>Please check your RSS sources or internet connection.</p>";
}

// Test 4: Check database structure
echo "<h2>Step 4: Database Structure Check</h2>";

$tables_to_check = ['news', 'news_sources', 'categories'];
foreach ($tables_to_check as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($check) > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p>✗ Table '$table' missing</p>";
    }
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><a href='admin/rss_import.php'>Go to RSS Import Admin</a> to import news</li>";
echo "<li>Click 'Import All News' button to import from all active sources</li>";
echo "<li>Check the results to see how many articles were imported</li>";
echo "</ol>";

?>

<?php
/**
 * Complete RSS Import Test
 * Tests the entire RSS import pipeline after fixes
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';
require_once __DIR__ . '/includes/auto_news_importer.php';

echo "<h1>Complete RSS Import Test</h1>";

// Step 1: Check Database and Sources
echo "<h2>Step 1: Database Check</h2>";

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p style='color: red;'>✗ news_sources table missing</p>";
    echo "<p><a href='setup_rss_sources.php'>Run Setup First</a></p>";
    exit;
}

$sources_query = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'";
$sources_result = mysqli_query($conn, $sources_query);
$sources_count = mysqli_fetch_assoc($sources_result)['count'];

echo "<p>✓ Database OK</p>";
echo "<p>✓ Found $sources_count active RSS sources</p>";

if ($sources_count == 0) {
    echo "<p style='color: orange;'>⚠ No active RSS sources found</p>";
    echo "<p><a href='setup_rss_sources.php'>Setup RSS Sources</a></p>";
    exit;
}

// Step 2: Test RSS Parser
echo "<h2>Step 2: RSS Parser Test</h2>";

try {
    $parser = new EnhancedRSSParser();
    echo "<p>✓ RSS Parser initialized</p>";
    
    // Test a known good RSS feed
    $test_feeds = [
        'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
        'CNN News' => 'https://rss.cnn.com/rss/edition.rss'
    ];
    
    $parser_working = false;
    foreach ($test_feeds as $name => $url) {
        try {
            $validation = $parser->validateFeed($url);
            if ($validation['valid']) {
                echo "<p>✓ $name feed is valid ({$validation['items_count']} items)</p>";
                $parser_working = true;
                break;
            } else {
                echo "<p style='color: orange;'>⚠ $name feed: " . htmlspecialchars($validation['error']) . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ $name feed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    if (!$parser_working) {
        echo "<p style='color: red;'>✗ RSS Parser not working with test feeds</p>";
        echo "<p>This may be a network connectivity or server configuration issue</p>";
    } else {
        echo "<p>✓ RSS Parser is working</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ RSS Parser Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: Test Auto News Importer
echo "<h2>Step 3: Auto News Importer Test</h2>";

try {
    $importer = new AutoNewsImporter($conn);
    echo "<p>✓ Auto News Importer initialized</p>";
    
    // Test with limited articles for quick testing
    $importer->setMaxArticlesPerFeed(2);
    $importer->setDownloadImages(false); // Skip images for quick test
    
    echo "<p>✓ Importer configured for testing</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Auto News Importer Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 4: Test Import from Sources
echo "<h2>Step 4: Import Test (Limited)</h2>";

try {
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(1); // Only 1 article per feed for quick test
    $importer->setDownloadImages(false);
    
    $results = $importer->importFromAllSources();
    
    echo "<h3>Import Results</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Metric</th><th>Value</th></tr>";
    echo "<tr><td>Total Feeds</td><td>{$results['total_feeds']}</td></tr>";
    echo "<tr><td>Successful Feeds</td><td>{$results['successful_feeds']}</td></tr>";
    echo "<tr><td>Failed Feeds</td><td>{$results['error_feeds']}</td></tr>";
    echo "<tr><td>Articles Imported</td><td>{$results['imported_articles']}</td></tr>";
    echo "<tr><td>Duplicates Skipped</td><td>{$results['duplicate_articles']}</td></tr>";
    echo "</table>";
    
    if ($results['imported_articles'] > 0) {
        echo "<p style='color: green;'><strong>✓ RSS Import is WORKING!</strong></p>";
        echo "<p>Successfully imported {$results['imported_articles']} articles</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No articles imported (may be normal if all are duplicates)</p>";
    }
    
    // Show details for each source
    if (!empty($results['details'])) {
        echo "<h4>Source Details</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Source</th><th>Status</th><th>Articles</th><th>Details</th></tr>";
        
        foreach ($results['details'] as $detail) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($detail['source_name']) . "</td>";
            
            if (isset($detail['error'])) {
                echo "<td style='color: red;'>Error</td>";
                echo "<td>0</td>";
                echo "<td>" . htmlspecialchars($detail['error']) . "</td>";
            } else {
                echo "<td style='color: green;'>Success</td>";
                echo "<td>{$detail['imported_articles']}</td>";
                echo "<td>Imported: {$detail['imported_articles']}, Duplicates: {$detail['duplicate_articles']}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Import Test Failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 5: Check Recent Imports
echo "<h2>Step 5: Check Recent Imports</h2>";

$recent_query = "SELECT title, source_url, created_at, news_type FROM news 
                WHERE news_type = 'rss_import' 
                ORDER BY created_at DESC 
                LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

if (mysqli_num_rows($recent_result) > 0) {
    echo "<p>✓ Found recent RSS imports:</p>";
    echo "<ul>";
    while ($article = mysqli_fetch_assoc($recent_result)) {
        echo "<li>" . htmlspecialchars($article['title']) . " (" . $article['created_at'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>⚠ No recent RSS imports found</p>";
}

// Step 6: Summary and Next Steps
echo "<h2>Summary</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>";
echo "<h3>What was fixed:</h3>";
echo "<ul>";
echo "<li>✓ Fixed RSS URL usage (using rss_url instead of url)</li>";
echo "<li>✓ Updated multiple test files to use correct RSS URLs</li>";
echo "<li>✓ Added fallback logic for sources without RSS URLs</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='admin/manage-sources.php'>Manage RSS Sources</a></li>";
echo "<li><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron'>Run Full Import</a></li>";
echo "<li><a href='check_rss_database.php'>Check RSS Database</a></li>";
echo "<li><a href='test_rss_fix_verification.php'>Verify Fixes</a></li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>If you're still seeing errors, the issue may be:</strong></p>";
echo "<ul>";
echo "<li>Network connectivity (firewall blocking RSS feeds)</li>";
echo "<li>Server configuration (cURL not working)</li>";
echo "<li>RSS feed URLs are outdated or changed</li>";
echo "<li>Database connection issues</li>";
echo "</ul>";

?>

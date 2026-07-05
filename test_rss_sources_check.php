<?php
/**
 * Test RSS Sources and Import Functionality
 * Check available sources and test importing news from different channels
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

echo "<h1>RSS Import System Test</h1>";

// Check news_sources table
echo "<h2>Available RSS Sources</h2>";
$sources_query = "SELECT ns.*, c.name as category_name FROM news_sources ns 
                 LEFT JOIN categories c ON ns.category_id = c.id 
                 WHERE ns.type = 'rss' 
                 ORDER BY ns.name";
$sources_result = mysqli_query($conn, $sources_query);

if (mysqli_num_rows($sources_result) > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Name</th><th>URL</th><th>Category</th><th>Status</th><th>Test Feed</th></tr>";
    
    while ($source = mysqli_fetch_assoc($sources_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($source['name']) . "</td>";
        echo "<td><a href='" . htmlspecialchars($source['url']) . "' target='_blank'>" . htmlspecialchars(substr($source['url'], 0, 50)) . "...</a></td>";
        echo "<td>" . htmlspecialchars($source['category_name'] ?? 'Uncategorized') . "</td>";
        echo "<td>" . htmlspecialchars($source['status']) . "</td>";
        echo "<td><a href='?test_feed=" . $source['id'] . "'>Test Feed</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No RSS sources found. <a href='setup_rss_sources.php'>Setup RSS Sources</a></p>";
}

// Test specific feed if requested
if (isset($_GET['test_feed'])) {
    $source_id = intval($_GET['test_feed']);
    $source_query = "SELECT * FROM news_sources WHERE id = ? AND type = 'rss'";
    $stmt = mysqli_prepare($conn, $source_query);
    mysqli_stmt_bind_param($stmt, 'i', $source_id);
    mysqli_stmt_execute($stmt);
    $source_result = mysqli_stmt_get_result($stmt);
    
    if ($source = mysqli_fetch_assoc($source_result)) {
        echo "<h2>Testing Feed: " . htmlspecialchars($source['name']) . "</h2>";
        
        try {
            $parser = new EnhancedRSSParser();
            
            // Validate feed
            echo "<h3>Feed Validation</h3>";
            $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
            $validation = $parser->validateFeed($rss_url);
            
            if ($validation['valid']) {
                echo "<p><strong>✓ Feed is valid</strong></p>";
                echo "<p>Format: " . htmlspecialchars($validation['format']) . "</p>";
                echo "<p>Items available: " . $validation['items_count'] . "</p>";
                
                // Parse articles
                echo "<h3>Sample Articles (First 5)</h3>";
                $articles = $parser->parseRSS($rss_url);
                
                if (!empty($articles)) {
                    echo "<table border='1' cellpadding='5' cellspacing='0'>";
                    echo "<tr><th>Title</th><th>Image</th><th>Content Preview</th></tr>";
                    
                    $count = 0;
                    foreach ($articles as $article) {
                        if ($count >= 5) break;
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars(substr($article['title'], 0, 80)) . "</td>";
                        echo "<td>";
                        if (!empty($article['image'])) {
                            echo "<img src='" . htmlspecialchars($article['image']) . "' width='100' height='60' onerror=\"this.style.display='none'\">";
                        } else {
                            echo "No image";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) . "...</td>";
                        echo "</tr>";
                        $count++;
                    }
                    echo "</table>";
                    
                    echo "<p><strong>Total articles found:</strong> " . count($articles) . "</p>";
                    
                } else {
                    echo "<p>No articles found in feed</p>";
                }
                
            } else {
                echo "<p><strong>✗ Feed validation failed:</strong> " . htmlspecialchars($validation['error']) . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p><strong>Error testing feed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Test import from multiple sources
if (isset($_GET['test_import'])) {
    echo "<h2>Testing Import from Multiple Sources</h2>";
    
    try {
        $parser = new EnhancedRSSParser();
        $import_results = [
            'sources_tested' => 0,
            'total_articles' => 0,
            'successful_imports' => 0,
            'errors' => []
        ];
        
        // Get active RSS sources (limit to 10 for testing)
        $query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 10";
        $result = mysqli_query($conn, $query);
        
        while ($source = mysqli_fetch_assoc($result)) {
            $import_results['sources_tested']++;
            
            try {
                echo "<h3>Testing: " . htmlspecialchars($source['name']) . "</h3>";
                $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
                $articles = $parser->parseRSS($rss_url);
                $article_count = count($articles);
                
                $import_results['total_articles'] += $article_count;
                
                if ($article_count > 0) {
                    $import_results['successful_imports']++;
                    echo "<p>✓ Found $article_count articles</p>";
                    
                    // Show sample article
                    if (!empty($articles[0])) {
                        $sample = $articles[0];
                        echo "<p><strong>Sample:</strong> " . htmlspecialchars(substr($sample['title'], 0, 60)) . "...</p>";
                    }
                } else {
                    echo "<p>⚠ No articles found</p>";
                }
                
            } catch (Exception $e) {
                $import_results['errors'][] = $source['name'] . ': ' . $e->getMessage();
                echo "<p>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        
        echo "<h2>Import Test Summary</h2>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Metric</th><th>Value</th></tr>";
        echo "<tr><td>Sources Tested</td><td>" . $import_results['sources_tested'] . "</td></tr>";
        echo "<tr><td>Successful Imports</td><td>" . $import_results['successful_imports'] . "</td></tr>";
        echo "<tr><td>Total Articles Available</td><td>" . $import_results['total_articles'] . "</td></tr>";
        echo "<tr><td>Errors</td><td>" . count($import_results['errors']) . "</td></tr>";
        echo "</table>";
        
        if (!empty($import_results['errors'])) {
            echo "<h3>Errors</h3>";
            echo "<ul>";
            foreach ($import_results['errors'] as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<p><strong>Import test failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Add test import button
echo "<div style='margin-top: 20px; padding: 10px; background: #f0f0f0;'>";
echo "<h2>Test Import Functionality</h2>";
echo "<p><a href='?test_import=1'>Test Import from Multiple Sources</a></p>";
echo "<p><a href='admin/rss_import.php'>Go to RSS Import Admin</a></p>";
echo "</div>";

?>

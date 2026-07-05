<?php
/**
 * RSS Fix Verification Script
 * Test that the RSS URL fixes are working correctly
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/enhanced_rss_parser.php';

echo "<h1>RSS Fix Verification</h1>";

// Get RSS sources
$query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 5";
$result = mysqli_query($conn, $query);

echo "<h2>Testing RSS Sources (Fixed)</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Source Name</th><th>Main URL</th><th>RSS URL</th><th>Status</th></tr>";

$success_count = 0;
$error_count = 0;

while ($source = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($source['name']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($source['url'], 0, 40)) . "...</td>";
    echo "<td>" . htmlspecialchars(substr($source['rss_url'] ?? 'N/A', 0, 40)) . "...</td>";
    
    try {
        $parser = new EnhancedRSSParser();
        $rss_url = !empty($source['rss_url']) ? $source['rss_url'] : $source['url'];
        
        echo "<td>";
        echo "Testing: $rss_url<br>";
        
        $validation = $parser->validateFeed($rss_url);
        
        if ($validation['valid']) {
            echo "<span style='color: green;'>✓ VALID</span> ({$validation['items_count']} items)";
            $success_count++;
        } else {
            echo "<span style='color: red;'>✗ INVALID</span>: " . htmlspecialchars($validation['error']);
            $error_count++;
        }
        echo "</td>";
        
    } catch (Exception $e) {
        echo "<td><span style='color: red;'>✗ ERROR</span>: " . htmlspecialchars($e->getMessage()) . "</td>";
        $error_count++;
    }
    
    echo "</tr>";
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p><strong>Successful:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($success_count > 0) {
    echo "<p style='color: green;'><strong>✓ RSS URL fixes are working!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Still having issues with RSS feeds</strong></p>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='test_rss_sources_check.php?test_import=1'>Test Full Import</a></li>";
echo "<li><a href='admin/manage-sources.php'>Manage RSS Sources</a></li>";
echo "<li><a href='cron_import_news.php?cron_key=pk_live_news_2024_cron'>Run Full Import</a></li>";
echo "</ul>";

?>

<?php
/**
 * Check RSS Sources Database
 * Verify RSS sources have proper RSS URLs
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>RSS Sources Database Check</h1>";

// Check table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<p style='color: red;'>✗ news_sources table does not exist</p>";
    echo "<p><a href='setup_rss_sources.php'>Setup RSS Sources</a></p>";
    exit;
}

// Get all RSS sources
$query = "SELECT id, name, url, rss_url, status FROM news_sources WHERE type = 'rss' ORDER BY name";
$result = mysqli_query($conn, $query);

echo "<h2>RSS Sources in Database</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Main URL</th><th>RSS URL</th><th>Status</th><th>Issue</th></tr>";

$issues_found = 0;
$total_sources = 0;

while ($source = mysqli_fetch_assoc($result)) {
    $total_sources++;
    $issue = '';
    
    // Check if RSS URL is empty or invalid
    if (empty($source['rss_url'])) {
        $issue = 'Missing RSS URL';
        $issues_found++;
    } elseif (!filter_var($source['rss_url'], FILTER_VALIDATE_URL)) {
        $issue = 'Invalid RSS URL format';
        $issues_found++;
    } elseif (strpos($source['rss_url'], 'http') !== 0) {
        $issue = 'RSS URL missing protocol';
        $issues_found++;
    }
    
    echo "<tr>";
    echo "<td>" . $source['id'] . "</td>";
    echo "<td>" . htmlspecialchars($source['name']) . "</td>";
    echo "<td>" . htmlspecialchars(substr($source['url'], 0, 50)) . "...</td>";
    echo "<td>" . htmlspecialchars($source['rss_url'] ?? 'NULL') . "</td>";
    echo "<td>" . $source['status'] . "</td>";
    echo "<td>" . ($issue ? "<span style='color: red;'>$issue</span>" : "<span style='color: green;'>✓ OK</span>") . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p><strong>Total RSS Sources:</strong> $total_sources</p>";
echo "<p><strong>Issues Found:</strong> $issues_found</p>";

if ($issues_found > 0) {
    echo "<h3>Fix Issues</h3>";
    echo "<p><a href='setup_rss_sources.php'>Re-run RSS Sources Setup</a> to fix missing RSS URLs</p>";
    
    // Try to fix some common issues automatically
    echo "<h3>Auto-Fix Common Issues</h3>";
    
    $fixes_applied = 0;
    
    // Reset and get sources again for fixing
    mysqli_data_seek($result, 0);
    while ($source = mysqli_fetch_assoc($result)) {
        if (empty($source['rss_url'])) {
            // Try to guess RSS URL from main URL
            $url = $source['url'];
            $rss_url = '';
            
            // Common RSS URL patterns
            if (strpos($url, 'bbc.com') !== false) {
                $rss_url = 'https://feeds.bbci.co.uk/news/rss.xml';
            } elseif (strpos($url, 'cnn.com') !== false) {
                $rss_url = 'https://rss.cnn.com/rss/edition.rss';
            } elseif (strpos($url, 'reuters.com') !== false) {
                $rss_url = 'https://feeds.reuters.com/reuters/topNews';
            } elseif (strpos($url, 'aljazeera.com') !== false) {
                $rss_url = 'https://www.aljazeera.com/xml/rss/all.xml';
            } elseif (strpos($url, 'dawn.com') !== false) {
                $rss_url = 'https://www.dawn.com/rss';
            } elseif (strpos($url, 'geo.tv') !== false) {
                $rss_url = 'https://www.geo.tv/rss/1.xml';
            } elseif (strpos($url, 'arynews.tv') !== false) {
                $rss_url = 'https://arynews.tv/feed/';
            }
            
            if (!empty($rss_url)) {
                $update_query = "UPDATE news_sources SET rss_url = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, 'si', $rss_url, $source['id']);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<p style='color: green;'>✓ Fixed RSS URL for " . htmlspecialchars($source['name']) . "</p>";
                    $fixes_applied++;
                }
            }
        }
    }
    
    if ($fixes_applied > 0) {
        echo "<p><strong>$fixes_applied fixes applied.</strong> <a href='check_rss_database.php'>Refresh to see changes</a></p>";
    }
} else {
    echo "<p style='color: green;'><strong>✓ All RSS sources have valid URLs!</strong></p>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='test_rss_fix_verification.php'>Test RSS Import</a></li>";
echo "<li><a href='test_rss_sources_check.php?test_import=1'>Test Full Import</a></li>";
echo "<li><a href='admin/manage-sources.php'>Manage RSS Sources</a></li>";
echo "</ul>";

?>

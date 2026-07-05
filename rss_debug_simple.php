<?php
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "=== RSS Sources Analysis ===\n\n";

// Count total and active RSS sources
$query = "SELECT COUNT(*) as total_sources, COUNT(CASE WHEN status = 'active' THEN 1 END) as active_sources FROM news_sources WHERE type = 'rss'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
echo "Total RSS Sources: " . $row['total_sources'] . "\n";
echo "Active RSS Sources: " . $row['active_sources'] . "\n\n";

// Show all RSS sources
$query = "SELECT id, name, url, status, last_scraped FROM news_sources WHERE type = 'rss' ORDER BY name";
$result = mysqli_query($conn, $query);

echo "RSS Sources Details:\n";
echo "ID\tName\t\t\tStatus\tLast Scraped\n";
echo "------------------------------------------------\n";
while ($source = mysqli_fetch_assoc($result)) {
    $name = substr($source['name'], 0, 20);
    $name = str_pad($name, 20);
    $last_scraped = $source['last_scraped'] ? date('M j, Y', strtotime($source['last_scraped'])) : 'Never';
    echo $source['id'] . "\t" . $name . "\t" . $source['status'] . "\t" . $last_scraped . "\n";
}

// Check recent imports
echo "\nRecent RSS Imports (last 24 hours):\n";
$query = "SELECT COUNT(*) as recent_imports FROM news WHERE news_type = 'rss_import' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
echo "RSS imports in last 24 hours: " . $row['recent_imports'] . "\n";

// Check total RSS articles
$query = "SELECT COUNT(*) as total_rss_articles FROM news WHERE news_type = 'rss_import'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
echo "Total RSS articles in database: " . $row['total_rss_articles'] . "\n";

// Test RSS parsing with current parser
echo "\n=== Testing RSS Parser ===\n";
require_once 'includes/enhanced_rss_parser.php';

$query = "SELECT name, url FROM news_sources WHERE type = 'rss' AND status = 'active' LIMIT 3";
$result = mysqli_query($conn, $query);

while ($source = mysqli_fetch_assoc($result)) {
    echo "\nTesting: " . $source['name'] . "\n";
    echo "URL: " . $source['url'] . "\n";
    
    try {
        $parser = new EnhancedRSSParser();
        $articles = $parser->parseRSS($source['url']);
        echo "Articles found: " . count($articles) . "\n";
        
        if (count($articles) > 0) {
            echo "First 3 article titles:\n";
            for ($i = 0; $i < min(3, count($articles)); $i++) {
                echo "- " . substr($articles[$i]['title'], 0, 60) . "...\n";
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

mysqli_close($conn);
?>

<?php
/**
 * Fix RSS Feed URLs with correct RSS feed endpoints
 */

require_once __DIR__ . '/config/database.php';

echo "Fixing RSS Feed URLs\n";
echo "====================\n\n";

// Correct RSS feed URLs
$correct_feeds = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'CNN' => 'https://rss.cnn.com/rss/edition.rss',
    'CNN News' => 'https://rss.cnn.com/rss/edition.rss',
    'Reuters' => 'https://feeds.reuters.com/reuters/topNews',
    'Al Jazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',
    'The Guardian' => 'https://www.theguardian.com/world/rss',
    'Fox News' => 'https://feeds.foxnews.com/foxnews/latest',
    'ARY News' => 'https://arynews.tv/feed/', // Check if this works
    'Geo News' => 'https://www.geo.tv/feed/rss', // Check if this works
    'financialcontent' => 'https://feeds.finance.yahoo.com/rss/industries' // Yahoo Finance as fallback
];

// Update existing RSS sources
foreach ($correct_feeds as $name => $rss_url) {
    echo "Updating: $name\n";
    
    // First, try to validate the feed
    try {
        require_once __DIR__ . '/includes/enhanced_rss_parser.php';
        $parser = new EnhancedRSSParser();
        
        echo "  Testing: $rss_url\n";
        $validation = $parser->validateFeed($rss_url);
        
        if ($validation['valid']) {
            echo "  ✓ Valid feed - {$validation['items_count']} items\n";
            
            // Update database
            $update = "UPDATE news_sources SET rss_url = ?, status = 'active' WHERE name = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, 'ss', $rss_url, $name);
            
            if (mysqli_stmt_execute($stmt)) {
                $affected = mysqli_stmt_affected_rows($stmt);
                if ($affected > 0) {
                    echo "  ✓ Updated in database\n";
                } else {
                    echo "  - Source not found in database, adding new...\n";
                    
                    // Get default category
                    $cat_result = mysqli_query($conn, "SELECT id FROM categories WHERE status = 'active' LIMIT 1");
                    $category_id = 1;
                    if ($cat_row = mysqli_fetch_assoc($cat_result)) {
                        $category_id = $cat_row['id'];
                    }
                    
                    // Insert new source
                    $insert = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
                    $stmt = mysqli_prepare($conn, $insert);
                    mysqli_stmt_bind_param($stmt, 'sssi', $name, $rss_url, $rss_url, $category_id);
                    mysqli_stmt_execute($stmt);
                    echo "  ✓ Added new source\n";
                }
            } else {
                echo "  ✗ Database update failed: " . mysqli_error($conn) . "\n";
            }
            
        } else {
            echo "  ✗ Invalid feed: " . $validation['error'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error testing feed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Add some additional working feeds
echo "Adding additional working RSS feeds...\n";

$additional_feeds = [
    'Associated Press' => 'https://feeds.apnews.com/rss/apf-topnews',
    'NPR News' => 'https://feeds.npr.org/1001/rss.xml',
    'CBS News' => 'https://feeds.cbsnews.com/CBSNewsMain',
    'ABC News' => 'https://feeds.abcnews.com/abcnews/topstories',
    'NBC News' => 'https://feeds.nbcnews.com/nbcnews/public/news'
];

foreach ($additional_feeds as $name => $rss_url) {
    echo "Adding: $name\n";
    
    try {
        require_once __DIR__ . '/includes/enhanced_rss_parser.php';
        $parser = new EnhancedRSSParser();
        
        $validation = $parser->validateFeed($rss_url);
        
        if ($validation['valid']) {
            echo "  ✓ Valid feed - {$validation['items_count']} items\n";
            
            // Check if already exists
            $check = "SELECT id FROM news_sources WHERE name = ?";
            $stmt = mysqli_prepare($conn, $check);
            mysqli_stmt_bind_param($stmt, 's', $name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 0) {
                // Get default category
                $cat_result = mysqli_query($conn, "SELECT id FROM categories WHERE status = 'active' LIMIT 1");
                $category_id = 1;
                if ($cat_row = mysqli_fetch_assoc($cat_result)) {
                    $category_id = $cat_row['id'];
                }
                
                // Insert new source
                $insert = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, 'sssi', $name, $rss_url, $rss_url, $category_id);
                mysqli_stmt_execute($stmt);
                echo "  ✓ Added to database\n";
            } else {
                echo "  - Already exists\n";
            }
        } else {
            echo "  ✗ Invalid feed: " . $validation['error'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test a quick import
echo "Testing quick import...\n";
try {
    require_once __DIR__ . '/includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(1);
    
    $results = $importer->importFromAllSources();
    echo "Import Results:\n";
    echo "  Total feeds: {$results['total_feeds']}\n";
    echo "  Successful: {$results['successful_feeds']}\n";
    echo "  Articles imported: {$results['imported_articles']}\n";
    echo "  Duplicates: {$results['duplicate_articles']}\n";
    
} catch (Exception $e) {
    echo "Import error: " . $e->getMessage() . "\n";
}

echo "\nRSS feed URLs have been updated!\n";
echo "Visit admin/manage-sources.php to manage sources\n";
echo "Run test_rss_import.php to test the import\n";
?>

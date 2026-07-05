<?php
require_once 'config/database.php';

echo "<h1>PK Live News - RSS System Fix</h1>";

// Fix 1: Test basic connectivity
echo "<h2>1. Testing Network Connectivity</h2>";

// Test basic HTTP connection
$test_urls = [
    'http://example.com' => 'Basic HTTP test',
    'https://example.com' => 'Basic HTTPS test'
];

$working_connections = 0;
foreach ($test_urls as $url => $description) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<div style='color: green;'>✓ $description - Working</div>";
        $working_connections++;
    } else {
        echo "<div style='color: red;'>✗ $description - Failed: $error</div>";
    }
}

if ($working_connections === 0) {
    echo "<div style='color: orange;'>⚠ No network connectivity detected - RSS feeds will not work</div>";
    echo "<div style='color: blue;'>ℹ Check: Internet connection, firewall, proxy settings</div>";
} else {
    echo "<div style='color: green;'>✓ Basic connectivity working</div>";
}

// Fix 2: Create RSS sources if none exist
echo "<h2>2. Setting Up RSS Sources</h2>";

$rss_sources = [
    ['name' => 'BBC News', 'url' => 'http://feeds.bbci.co.uk/news/rss.xml', 'category' => 'World'],
    ['name' => 'CNN', 'url' => 'http://rss.cnn.com/rss/edition.rss', 'category' => 'World'],
    ['name' => 'Reuters', 'url' => 'https://www.reuters.com/rssFeed/worldNews', 'category' => 'World'],
    ['name' => 'Al Jazeera', 'url' => 'https://www.aljazeera.com/xml/rss/all.xml', 'category' => 'World'],
    ['name' => 'Geo News', 'url' => 'https://www.geo.tv/rss/feed/1.xml', 'category' => 'Pakistan']
];

// Check if rss_sources table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'rss_sources'");
if (mysqli_num_rows($table_check) === 0) {
    // Create the table
    $create_table = "CREATE TABLE rss_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL UNIQUE,
        category VARCHAR(50),
        status ENUM('active', 'inactive') DEFAULT 'active',
        last_import DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (mysqli_query($conn, $create_table)) {
        echo "<div style='color: green;'>✓ Created rss_sources table</div>";
    }
}

$added_sources = 0;
foreach ($rss_sources as $source) {
    // Check if source already exists
    $check_query = "SELECT id FROM rss_sources WHERE url = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 's', $source['url']);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        // Add the source
        $insert_query = "INSERT INTO rss_sources (name, url, category) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'sss', $source['name'], $source['url'], $source['category']);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "<div style='color: green;'>✓ Added RSS source: {$source['name']}</div>";
            $added_sources++;
        }
    } else {
        echo "<div style='color: blue;'>ℹ RSS source exists: {$source['name']}</div>";
    }
}

// Fix 3: Create simple RSS importer
echo "<h2>3. Creating Simple RSS Importer</h2>";

$simple_importer = '<?php
require_once "config/database.php";

// Simple RSS import function
function importRSSFeed($source_id, $source_url, $source_name) {
    global $conn;
    
    echo "Importing from: $source_name\\n";
    
    // Try to fetch RSS feed
    $context = stream_context_create([
        "http" => [
            "timeout" => 10,
            "user_agent" => "PK Live News RSS Reader"
        ]
    ]);
    
    $xml_content = @file_get_contents($source_url, false, $context);
    
    if ($xml_content === false) {
        echo "Failed to fetch RSS feed\\n";
        return false;
    }
    
    // Parse XML
    $xml = simplexml_load_string($xml_content);
    
    if ($xml === false) {
        echo "Failed to parse RSS feed\\n";
        return false;
    }
    
    $imported = 0;
    
    foreach ($xml->channel->item as $item) {
        $title = (string) $item->title;
        $link = (string) $item->link;
        $description = (string) $item->description;
        $pub_date = (string) $item->pubDate;
        
        // Skip if title is empty
        if (empty($title)) continue;
        
        // Create slug
        $slug = create_slug($title);
        
        // Check if already exists
        $check_query = "SELECT id FROM news WHERE slug = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $slug);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            continue; // Skip duplicates
        }
        
        // Parse publication date
        $published_at = date("Y-m-d H:i:s");
        if (!empty($pub_date)) {
            $timestamp = strtotime($pub_date);
            if ($timestamp !== false) {
                $published_at = date("Y-m-d H:i:s", $timestamp);
            }
        }
        
        // Insert news
        $insert_query = "INSERT INTO news (title, slug, content, excerpt, source_url, status, published_at, created_at) 
                         VALUES (?, ?, ?, ?, ?, \'published\', ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sssssss", $title, $slug, $description, $description, $link, $published_at, $published_at);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $imported++;
        }
    }
    
    echo "Imported $imported articles from $source_name\\n";
    
    // Update last import time
    $update_query = "UPDATE rss_sources SET last_import = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $source_id);
    mysqli_stmt_execute($update_stmt);
    
    return $imported;
}

// Run import for active sources
$sources_query = "SELECT id, name, url FROM rss_sources WHERE status = \'active\'";
$sources_result = mysqli_query($conn, $sources_query);

$total_imported = 0;
while ($source = mysqli_fetch_assoc($sources_result)) {
    $imported = importRSSFeed($source["id"], $source["url"], $source["name"]);
    if ($imported !== false) {
        $total_imported += $imported;
    }
}

echo "\\nTotal imported: $total_imported articles\\n";
?>';

if (file_put_contents('simple_rss_import.php', $simple_importer)) {
    echo "<div style='color: green;'>✓ Created simple RSS importer</div>";
} else {
    echo "<div style='color: red;'>✗ Failed to create RSS importer</div>";
}

// Fix 4: Test the simple importer
echo "<h2>4. Testing RSS Import</h2>";

if ($working_connections > 0) {
    echo "<div style='color: blue;'>ℹ Testing one RSS source...</div>";
    
    // Test with a simple, reliable RSS feed
    $test_url = 'http://feeds.bbci.co.uk/news/rss.xml';
    $context = stream_context_create([
        "http" => [
            "timeout" => 5,
            "user_agent" => "PK Live News RSS Reader"
        ]
    ]);
    
    $test_content = @file_get_contents($test_url, false, $context);
    
    if ($test_content) {
        echo "<div style='color: green;'>✓ RSS feed accessible</div>";
        
        $xml = @simplexml_load_string($test_content);
        if ($xml && isset($xml->channel->item)) {
            $item_count = count($xml->channel->item);
            echo "<div style='color: green;'>✓ RSS feed parsed - $item_count items found</div>";
        } else {
            echo "<div style='color: orange;'>⚠ RSS feed format issue</div>";
        }
    } else {
        echo "<div style='color: red;'>✗ Cannot access RSS feeds</div>";
    }
} else {
    echo "<div style='color: orange;'>⚠ Skipping RSS test - no network connectivity</div>";
}

echo "<h2>🎉 RSS System Setup Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px;'>";
echo "<strong>Setup Summary:</strong><br>";
echo "• Network connectivity: $working_connections/2 working<br>";
echo "• RSS sources configured: " . count($rss_sources) . "<br>";
echo "• Simple RSS importer created<br>";
echo "• Database tables ready<br><br>";

echo "<strong>To Import RSS News:</strong><br>";
if ($working_connections > 0) {
    echo "1. Run: <a href='simple_rss_import.php'>simple_rss_import.php</a><br>";
    echo "2. Or visit: <a href='rss_test_simple.php'>rss_test_simple.php</a><br>";
} else {
    echo "1. Fix network connectivity first<br>";
    echo "2. Check internet connection<br>";
    echo "3. Verify firewall settings<br>";
}
echo "</div>";
?>

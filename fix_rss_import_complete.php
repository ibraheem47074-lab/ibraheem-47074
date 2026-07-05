<?php
/**
 * Complete RSS Import Fix
 * Fixes all issues with RSS news import system
 */

require_once __DIR__ . '/config/database.php';

echo "PK Live News - RSS Import Fix\n";
echo "==============================\n\n";

// Step 1: Check and create required tables
echo "Step 1: Checking database structure...\n";

// Check news_sources table
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_sources'");
if (mysqli_num_rows($table_check) == 0) {
    echo "Creating news_sources table...\n";
    $create_table = "CREATE TABLE news_sources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(500) NOT NULL,
        rss_url VARCHAR(500),
        type ENUM('rss', 'scrape') NOT NULL DEFAULT 'rss',
        category_id INT,
        scrape_frequency INT DEFAULT 60,
        status ENUM('active', 'inactive') DEFAULT 'active',
        last_scraped TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "✓ news_sources table created\n";
    } else {
        echo "✗ Error creating news_sources table: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ news_sources table exists\n";
}

// Check if news table has required columns
echo "Checking news table structure...\n";
$columns_to_check = [
    'source_url' => "ADD COLUMN source_url VARCHAR(500) DEFAULT NULL",
    'news_type' => "ADD COLUMN news_type ENUM('internal', 'external', 'rss_import', 'scraped') DEFAULT 'internal'",
    'sentiment_score' => "ADD COLUMN sentiment_score DECIMAL(3,2) DEFAULT NULL",
    'sentiment_label' => "ADD COLUMN sentiment_label ENUM('positive', 'negative', 'neutral') DEFAULT NULL",
    'summary_only' => "ADD COLUMN summary_only TINYINT(1) DEFAULT 0",
    'image_type' => "ADD COLUMN image_type ENUM('manual', 'rss', 'ai', 'scraped') DEFAULT 'manual'",
    'video_url' => "ADD COLUMN video_url VARCHAR(500) DEFAULT NULL"
];

foreach ($columns_to_check as $column => $alter_sql) {
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE '$column'");
    if (mysqli_num_rows($column_check) == 0) {
        $alter_query = "ALTER TABLE news $alter_sql";
        if (mysqli_query($conn, $alter_query)) {
            echo "✓ Added column: $column\n";
        } else {
            echo "✗ Error adding column $column: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Column exists: $column\n";
    }
}

// Step 2: Add RSS sources if none exist
echo "\nStep 2: Setting up RSS sources...\n";

$sources_check = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss'";
$result = mysqli_query($conn, $sources_check);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    echo "No RSS sources found. Adding default sources...\n";
    
    // Get default category
    $category_query = "SELECT id FROM categories LIMIT 1";
    $cat_result = mysqli_query($conn, $category_query);
    $category = mysqli_fetch_assoc($cat_result);
    $category_id = $category['id'] ?? 1;
    
    $default_sources = [
        ['BBC News', 'https://www.bbc.com/news', 'https://feeds.bbci.co.uk/news/rss.xml'],
        ['CNN News', 'https://www.cnn.com', 'https://rss.cnn.com/rss/edition.rss'],
        ['Reuters News', 'https://www.reuters.com', 'https://feeds.reuters.com/reuters/topNews'],
        ['Al Jazeera', 'https://www.aljazeera.com', 'https://www.aljazeera.com/xml/rss/all.xml'],
        ['Associated Press', 'https://apnews.com', 'https://feeds.apnews.com/rss/apf-topnews'],
        ['Geo News', 'https://www.geo.tv', 'https://www.geo.tv/rss/1.xml'],
        ['ARY News', 'https://arynews.tv', 'https://arynews.tv/feed/'],
        ['Dawn News', 'https://www.dawn.com', 'https://www.dawn.com/rss']
    ];
    
    foreach ($default_sources as $source) {
        $insert_query = "INSERT INTO news_sources (name, url, rss_url, type, category_id, status) VALUES (?, ?, ?, 'rss', ?, 'active')";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssi', $source[0], $source[1], $source[2], $category_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Added: {$source[0]}\n";
        } else {
            echo "✗ Error adding {$source[0]}: " . mysqli_error($conn) . "\n";
        }
    }
} else {
    echo "✓ Found {$row['count']} RSS sources\n";
}

// Step 3: Test RSS connectivity
echo "\nStep 3: Testing RSS connectivity...\n";

$test_sources = [
    'BBC News' => 'https://feeds.bbci.co.uk/news/rss.xml',
    'CNN News' => 'https://rss.cnn.com/rss/edition.rss',
    'Reuters News' => 'https://feeds.reuters.com/reuters/topNews'
];

foreach ($test_sources as $name => $url) {
    echo "Testing $name...\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'PK-LIVE-NEWS-RSS-Reader/2.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ $name: cURL Error - $error\n";
    } elseif ($http_code == 200) {
        $xml = @simplexml_load_string($content);
        if ($xml !== false) {
            $items = isset($xml->channel->item) ? count($xml->channel->item) : 0;
            echo "✓ $name: OK ($items items)\n";
        } else {
            echo "✗ $name: Invalid XML\n";
        }
    } else {
        echo "✗ $name: HTTP $http_code\n";
    }
}

// Step 4: Create fixed auto_news_importer.php
echo "\nStep 4: Creating fixed auto_news_importer...\n";

$fixed_importer = '<?php
/**
 * Fixed Automatic News Import System
 */

require_once __DIR__ . "/../config/database.php";
require_once "enhanced_rss_parser.php";

class FixedAutoNewsImporter {
    private $conn;
    private $parser;
    private $maxArticlesPerFeed = 5;
    private $downloadImages = false; // Disable for testing
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->parser = new EnhancedRSSParser();
        // Set reasonable timeouts
        $this->parser->setTimeout(15, 10);
    }
    
    public function importFromAllSources() {
        $results = [
            "total_feeds" => 0,
            "successful_feeds" => 0,
            "total_articles" => 0,
            "imported_articles" => 0,
            "duplicate_articles" => 0,
            "error_feeds" => 0,
            "details" => []
        ];
        
        // Get active RSS sources
        $sources_query = "SELECT * FROM news_sources WHERE type = \'rss\' AND status = \'active\' ORDER BY name ASC";
        $sources_result = mysqli_query($this->conn, $sources_query);
        
        if (!$sources_result) {
            throw new Exception("Failed to fetch news sources: " . mysqli_error($this->conn));
        }
        
        $results["total_feeds"] = mysqli_num_rows($sources_result);
        
        while ($source = mysqli_fetch_assoc($sources_result)) {
            try {
                $feedResult = $this->importFromSource($source);
                $results["successful_feeds"]++;
                $results["total_articles"] += $feedResult["total_articles"];
                $results["imported_articles"] += $feedResult["imported_articles"];
                $results["duplicate_articles"] += $feedResult["duplicate_articles"];
                $results["details"][] = $feedResult;
                
            } catch (Exception $e) {
                $results["error_feeds"]++;
                $results["details"][] = [
                    "source_name" => $source["name"] ?? "Unknown",
                    "source_url" => $source["rss_url"] ?? "Unknown",
                    "error" => $e->getMessage(),
                    "total_articles" => 0,
                    "imported_articles" => 0,
                    "duplicate_articles" => 0
                ];
                
                error_log("RSS Import Error for " . ($source["name"] ?? "Unknown") . ": " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    public function importFromSource($source) {
        $result = [
            "source_name" => $source["name"],
            "source_url" => $source["rss_url"],
            "total_articles" => 0,
            "imported_articles" => 0,
            "duplicate_articles" => 0,
            "skipped_articles" => 0,
            "articles" => []
        ];
        
        // Use rss_url field, fallback to url
        $rss_url = !empty($source["rss_url"]) ? $source["rss_url"] : $source["url"];
        
        // Parse RSS feed
        $articles = $this->parser->parseRSS($rss_url);
        $result["total_articles"] = count($articles);
        
        // Limit articles
        $articles = array_slice($articles, 0, $this->maxArticlesPerFeed);
        
        foreach ($articles as $article) {
            try {
                $importResult = $this->importArticle($article, $source);
                
                if ($importResult["status"] === "imported") {
                    $result["imported_articles"]++;
                } elseif ($importResult["status"] === "duplicate") {
                    $result["duplicate_articles"]++;
                } else {
                    $result["skipped_articles"]++;
                }
                
                $result["articles"][] = $importResult;
                
            } catch (Exception $e) {
                $result["articles"][] = [
                    "title" => $article["title"] ?? "Unknown",
                    "status" => "error",
                    "error" => $e->getMessage()
                ];
                error_log("Article Import Error: " . $e->getMessage());
            }
        }
        
        // Update source timestamp
        if (isset($source["id"])) {
            $updateQuery = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "i", $source["id"]);
            mysqli_stmt_execute($stmt);
        }
        
        return $result;
    }
    
    private function importArticle($article, $source) {
        $result = [
            "title" => $article["title"],
            "status" => "",
            "image_downloaded" => false
        ];
        
        // Validate required fields
        if (empty($article["title"])) {
            $result["status"] = "skipped";
            $result["error"] = "Missing title";
            return $result;
        }
        
        // Check for duplicates
        if ($this->isDuplicate($article["title"])) {
            $result["status"] = "duplicate";
            return $result;
        }
        
        // Generate slug
        $slug = $this->generateSlug($article["title"]);
        
        // Clean content
        $content = !empty($article["content"]) ? $article["content"] : $article["excerpt"] ?? "";
        $excerpt = substr(strip_tags($content), 0, 300);
        
        // Add source attribution
        $attribution = "\\n\\n<p><em><strong>Source:</strong> <a href=\\"" . ($article["link"] ?? "#") . "\\" target=\\"_blank\\" rel=\\"noopener\\">" . htmlspecialchars($source["name"]) . "</a></em></p>";
        $content .= $attribution;
        
        // Insert into database
        $insertQuery = "INSERT INTO news (title, slug, content, excerpt, category_id, 
                        status, source_url, news_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, \'draft\', ?, \'rss_import\', NOW())";
        
        $stmt = mysqli_prepare($this->conn, $insertQuery);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ssssis", 
            $article["title"], 
            $slug, 
            $content, 
            $excerpt, 
            $source["category_id"], 
            $article["link"] ?? ""
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $result["status"] = "imported";
            $result["news_id"] = mysqli_insert_id($this->conn);
        } else {
            throw new Exception("Failed to insert article: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    private function isDuplicate($title) {
        $checkQuery = "SELECT id FROM news WHERE title LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = mysqli_prepare($this->conn, $checkQuery);
        $similarTitle = "%$title%";
        mysqli_stmt_bind_param($stmt, "s", $similarTitle);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }
    
    private function generateSlug($title) {
        $slug = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "-", $title));
        $slug = trim($slug, "-");
        
        // Check if slug exists
        $checkQuery = "SELECT id FROM news WHERE slug = ?";
        $stmt = mysqli_prepare($this->conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $slug);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $slug .= "-" . time();
        }
        
        return $slug;
    }
    
    public function setMaxArticlesPerFeed($max) {
        $this->maxArticlesPerFeed = (int)$max;
    }
}

// Global function
function fixed_auto_import_news($conn) {
    $importer = new FixedAutoNewsImporter($conn);
    return $importer->importFromAllSources();
}
?>';

if (file_put_contents(__DIR__ . '/includes/auto_news_importer_fixed.php', $fixed_importer)) {
    echo "✓ Created fixed auto_news_importer.php\n";
} else {
    echo "✗ Failed to create fixed auto_news_importer.php\n";
}

// Step 5: Test the fixed import system
echo "\nStep 5: Testing fixed import system...\n";

try {
    require_once __DIR__ . '/includes/auto_news_importer_fixed.php';
    
    $importer = new FixedAutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(2); // Limit for testing
    
    echo "Running import test...\n";
    $results = $importer->importFromAllSources();
    
    echo "✓ Import test completed\n";
    echo "  Total feeds: {$results['total_feeds']}\n";
    echo "  Successful feeds: {$results['successful_feeds']}\n";
    echo "  Failed feeds: {$results['error_feeds']}\n";
    echo "  Articles imported: {$results['imported_articles']}\n";
    echo "  Duplicates skipped: {$results['duplicate_articles']}\n";
    
    if ($results['imported_articles'] > 0) {
        echo "\n✓ SUCCESS: RSS import is working!\n";
        echo "  Check your admin panel for draft articles.\n";
    } else {
        echo "\n⚠ WARNING: No articles were imported.\n";
        echo "  This could be due to duplicate detection or feed issues.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Import test failed: " . $e->getMessage() . "\n";
}

echo "\nStep 6: Creating test script...\n";

$test_script = '<?php
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
    echo "Feeds processed: {$results[\'total_feeds\']}\n";
    echo "Successful: {$results[\'successful_feeds\']}\n";
    echo "Failed: {$results[\'error_feeds\']}\n";
    echo "Articles imported: {$results[\'imported_articles\']}\n";
    echo "Duplicates: {$results[\'duplicate_articles\']}\n\n";
    
    foreach ($results[\'details\'] as $detail) {
        if (isset($detail[\'error\'])) {
            echo "ERROR - {$detail[\'source_name\']}: {$detail[\'error\']}\n";
        } else {
            echo "SUCCESS - {$detail[\'source_name\']}: {$detail[\'imported_articles\']} imported\n";
        }
    }
    
    // Check recent imports
    $check_query = "SELECT title, created_at FROM news WHERE news_type = \'rss_import\' ORDER BY created_at DESC LIMIT 5";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "\nRecent imports:\n";
        while ($article = mysqli_fetch_assoc($check_result)) {
            echo "- " . $article[\'title\'] . " (" . $article[\'created_at\'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>';

if (file_put_contents(__DIR__ . '/test_rss_import_fixed.php', $test_script)) {
    echo "✓ Created test_rss_import_fixed.php\n";
} else {
    echo "✗ Failed to create test script\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Run: php test_rss_import_fixed.php\n";
echo "2. Check admin panel for draft articles\n";
echo "3. If working, set up cron job:\n";
echo "   */5 * * * * php /path/to/your/site/cron_import_news.php\n";
echo "\nFiles created/updated:\n";
echo "- includes/auto_news_importer_fixed.php\n";
echo "- test_rss_import_fixed.php\n";
echo "- Database tables and columns\n";
echo "\nRSS import system should now be working!\n";
?>

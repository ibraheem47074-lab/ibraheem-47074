<?php
require_once 'config/database.php';
require_once 'includes/enhanced_rss_parser.php';

header('Content-Type: text/html');

echo "<h1>Enhanced RSS Article Importer</h1>";
echo "<p>This script imports more articles from RSS sources with increased limits.</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    try {
        $parser = new EnhancedRSSParser();
        $imported = 0;
        $duplicates = 0;
        $errors = [];
        $sources_processed = 0;
        
        // Get all active RSS sources
        $query = "SELECT * FROM news_sources WHERE type = 'rss' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        echo "<h2>Import Results</h2>";
        
        while ($source = mysqli_fetch_assoc($result)) {
            echo "<h3>Processing: " . htmlspecialchars($source['name']) . "</h3>";
            
            try {
                $articles = $parser->parseRSS($source['url']);
                echo "<p>Articles found in feed: " . count($articles) . "</p>";
                
                $source_imported = 0;
                $source_duplicates = 0;
                
                foreach ($articles as $article) {
                    // Check for duplicates (extended to 48 hours to reduce duplicates)
                    $check_query = "SELECT id FROM news WHERE title = ? AND created_at > DATE_SUB(NOW(), INTERVAL 48 HOUR)";
                    $check_stmt = mysqli_prepare($conn, $check_query);
                    mysqli_stmt_bind_param($check_stmt, 's', $article['title']);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);
                    
                    if (mysqli_num_rows($check_result) === 0) {
                        // Insert new article
                        $insert_query = "INSERT INTO news (title, content, summary, category_id, author, image, source_url, news_type, status, created_at) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'rss_import', 'published', NOW())";
                        $insert_stmt = mysqli_prepare($conn, $insert_query);
                        
                        $summary = substr(strip_tags($article['content']), 0, 300);
                        $author = $article['author'] ?? 'RSS Import';
                        $image = $article['image'] ?? '';
                        
                        mysqli_stmt_bind_param($insert_stmt, 'sssssss', 
                            $article['title'], 
                            $article['content'], 
                            $summary, 
                            $source['category_id'], 
                            $author, 
                            $image, 
                            $article['link']
                        );
                        
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $imported++;
                            $source_imported++;
                        }
                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $duplicates++;
                        $source_duplicates++;
                    }
                    mysqli_stmt_close($check_stmt);
                }
                
                echo "<p>Imported: " . $source_imported . " | Duplicates: " . $source_duplicates . "</p>";
                
                // Update last scraped time
                $update_query = "UPDATE news_sources SET last_scraped = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, 'i', $source['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                
                $sources_processed++;
                
            } catch (Exception $e) {
                $error_msg = 'Error importing from ' . $source['name'] . ': ' . $e->getMessage();
                $errors[] = $error_msg;
                echo "<p style='color: red;'>Error: " . htmlspecialchars($error_msg) . "</p>";
            }
        }
        
        echo "<div class='summary' style='background: #f0f8ff; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h2>Import Summary</h2>";
        echo "<p><strong>Sources Processed:</strong> " . $sources_processed . "</p>";
        echo "<p><strong>Total Articles Imported:</strong> " . $imported . "</p>";
        echo "<p><strong>Total Duplicates Found:</strong> " . $duplicates . "</p>";
        echo "<p><strong>Total Errors:</strong> " . count($errors) . "</p>";
        
        if (!empty($errors)) {
            echo "<h3>Errors:</h3>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li style='color: red;'>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Import error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<form method='POST'>";
    echo "<input type='submit' name='import' value='Import More Articles' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;' onclick=\"return confirm('This will import articles from all active RSS sources. Continue?');\">";
    echo "</form>";
    
    // Show current statistics
    echo "<h2>Current Statistics</h2>";
    $query = "SELECT COUNT(*) as total FROM news WHERE news_type = 'rss_import'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total RSS articles in database: " . $row['total'] . "</p>";
    
    $query = "SELECT COUNT(*) as recent FROM news WHERE news_type = 'rss_import' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    echo "<p>RSS articles imported in last 24 hours: " . $row['recent'] . "</p>";
    
    $query = "SELECT COUNT(*) as sources FROM news_sources WHERE type = 'rss' AND status = 'active'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    echo "<p>Active RSS sources: " . $row['sources'] . "</p>";
}

mysqli_close($conn);
?>

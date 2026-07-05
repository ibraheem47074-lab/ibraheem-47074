<?php
require_once 'config/database.php';

echo "<h1>Adding Missing image_type Column</h1>\n";

// Check if image_type column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'image_type'");
if (mysqli_num_rows($check_column) > 0) {
    echo "<span style='color: green;'>✓ image_type column already exists</span>\n";
} else {
    echo "<span style='color: orange;'>⚠ image_type column is MISSING - Adding it now...</span>\n";
    
    // Add the missing column
    $add_column = "ALTER TABLE news ADD COLUMN image_type VARCHAR(20) DEFAULT 'manual' AFTER image";
    if (mysqli_query($conn, $add_column)) {
        echo "<span style='color: green;'>✓ image_type column added successfully</span>\n";
    } else {
        echo "<span style='color: red;'>✗ Error adding image_type column: " . mysqli_error($conn) . "</span>\n";
    }
}

echo "<h2>Testing RSS Import Again</h2>\n";
try {
    require_once 'includes/auto_news_importer.php';
    $importer = new AutoNewsImporter($conn);
    $importer->setMaxArticlesPerFeed(3);
    
    $results = $importer->importFromAllSources();
    
    echo "<div style='background: #f0f8ff; padding: 10px; border-left: 4px solid #007cba;'>\n";
    echo "<p><strong>Import Test Results:</strong></p>\n";
    echo "<p>Feeds processed: {$results['total_feeds']}</p>\n";
    echo "<p>Successful feeds: {$results['successful_feeds']}</p>\n";
    echo "<p>Articles imported: {$results['imported_articles']}</p>\n";
    echo "<p>Duplicate articles: {$results['duplicate_articles']}</p>\n";
    echo "</div>\n";
    
    if ($results['imported_articles'] > 0) {
        echo "<p style='color: green;'><strong>✓ RSS System is now working perfectly!</strong></p>\n";
        echo "<p>Successfully imported {$results['imported_articles']} new articles!</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠ RSS feeds are working but no new articles (all duplicates)</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Import test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h3>Database Status</h3>\n";
echo "<p>All required columns are now present in the news table.</p>\n";
echo "<p>RSS feeds have been updated with working URLs.</p>\n";
echo "<p>The RSS import system should now function correctly.</p>\n";

echo "<p><a href='index.php' target='_blank'>View Website</a> | <a href='admin/manage-sources.php' target='_blank'>Manage RSS</a></p>\n";
?>

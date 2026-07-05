<?php
/**
 * Test RSS Draft Import System
 * This script tests the RSS import functionality and saves articles as drafts
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';

echo "Testing RSS Draft Import System\n";
echo "===============================\n\n";

try {
    // Initialize database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    echo "✓ Database connected\n";

    // Check if RSS sources exist
    $sources_query = "SELECT COUNT(*) as count FROM news_sources WHERE type = 'rss' AND status = 'active'";
    $result = mysqli_query($conn, $sources_query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        echo "⚠ No active RSS sources found. Running setup...\n";
        
        // Run the setup script
        include 'setup_rss_sources.php';
        echo "\n";
    } else {
        echo "✓ Found {$row['count']} active RSS sources\n";
    }

    // Create importer instance
    $importer = new AutoNewsImporter($conn);
    
    // Configure importer for testing
    $importer->setMaxArticlesPerFeed(2); // Limit to 2 articles for testing
    $importer->setDownloadImages(true);
    
    echo "✓ Importer configured\n";
    
    // Run import test
    echo "\nStarting RSS import test...\n";
    $results = $importer->importFromAllSources();
    
    // Display results
    echo "\n=== IMPORT RESULTS ===\n";
    echo "Total feeds processed: {$results['total_feeds']}\n";
    echo "Successful feeds: {$results['successful_feeds']}\n";
    echo "Failed feeds: {$results['error_feeds']}\n";
    echo "Total articles found: {$results['total_articles']}\n";
    echo "Articles imported as drafts: {$results['imported_articles']}\n";
    echo "Duplicate articles skipped: {$results['duplicate_articles']}\n";
    
    // Show detailed results
    echo "\n=== DETAILED RESULTS ===\n";
    foreach ($results['details'] as $detail) {
        if (isset($detail['error'])) {
            echo "ERROR - {$detail['source_name']}: {$detail['error']}\n";
        } else {
            echo "SUCCESS - {$detail['source_name']}: {$detail['imported_articles']} imported, {$detail['duplicate_articles']} duplicates\n";
        }
    }
    
    // Check recent drafts
    echo "\n=== RECENT DRAFT ARTICLES ===\n";
    $drafts_query = "SELECT id, title, created_at, source_url FROM news 
                     WHERE status = 'draft' AND news_type = 'rss_import' 
                     ORDER BY created_at DESC LIMIT 5";
    $drafts_result = mysqli_query($conn, $drafts_query);
    
    if (mysqli_num_rows($drafts_result) > 0) {
        while ($draft = mysqli_fetch_assoc($drafts_result)) {
            echo "ID: {$draft['id']} - " . substr($draft['title'], 0, 60) . "...\n";
            echo "  Created: {$draft['created_at']}\n";
            echo "  Source: " . parse_url($draft['source_url'], PHP_URL_HOST) . "\n\n";
        }
    } else {
        echo "No draft articles found.\n";
    }
    
    echo "\n✓ RSS Draft Import Test Completed Successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Check the admin panel for draft articles\n";
    echo "2. Review and publish the imported articles\n";
    echo "3. Set up cron job for automatic 5-minute imports\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
?>

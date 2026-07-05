<?php
/**
 * Cron Job Script for Automatic News Import
 * Run this script every 5 minutes to fetch latest news as drafts
 * All imported news will be saved as 'draft' status for admin review
 */

// Prevent direct browser access for cron jobs
if (php_sapi_name() !== 'cli' && !isset($_GET['cron_key'])) {
    http_response_code(403);
    die('Access denied');
}

// Security key for web-based cron calls
if (isset($_GET['cron_key']) && $_GET['cron_key'] !== 'pk_live_news_2024_cron') {
    http_response_code(403);
    die('Invalid cron key');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auto_news_importer.php';

// Set execution time limit
set_time_limit(300); // 5 minutes (increased to prevent timeout errors)

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/cron_import.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    echo $logMessage; // For CLI output
    error_log($logMessage); // For error log
}

try {
    logMessage("Starting automatic news import process (5-minute interval - drafts)");
    
    // Initialize database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Create importer instance
    $importer = new AutoNewsImporter($conn);
    
    // Configure importer
    $importer->setMaxArticlesPerFeed(5); // Limit to 5 articles per feed to avoid overwhelming
    $importer->setDownloadImages(true);
    
    // Run import
    $results = $importer->importFromAllSources();
    
    // Log results
    logMessage("Import completed successfully - news saved as drafts");
    logMessage("Total feeds processed: {$results['total_feeds']}");
    logMessage("Successful feeds: {$results['successful_feeds']}");
    logMessage("Failed feeds: {$results['error_feeds']}");
    logMessage("Total articles found: {$results['total_articles']}");
    logMessage("Articles imported: {$results['imported_articles']}");
    logMessage("Duplicate articles skipped: {$results['duplicate_articles']}");
    
    // Log detailed results for each feed
    foreach ($results['details'] as $detail) {
        if (isset($detail['error'])) {
            logMessage("ERROR - {$detail['source_name']}: {$detail['error']}");
        } else {
            logMessage("SUCCESS - {$detail['source_name']}: {$detail['imported_articles']} imported, {$detail['duplicate_articles']} duplicates, {$detail['total_articles']} total");
        }
    }
    
    // Clean up old articles (optional - keep last 30 days of RSS imports)
    cleanupOldArticles($conn);
    
    logMessage("Automatic news import process completed successfully - drafts ready for review");
    
} catch (Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    
    // Send email notification for critical errors (optional)
    // sendErrorNotification($e);
    
    exit(1);
}

/**
 * Clean up old RSS imported articles
 */
function cleanupOldArticles($conn) {
    try {
        // Delete RSS imported articles older than 30 days
        $deleteQuery = "DELETE FROM news WHERE news_type = 'rss_import' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = mysqli_query($conn, $deleteQuery);
        
        if ($result) {
            $deletedCount = mysqli_affected_rows($conn);
            if ($deletedCount > 0) {
                logMessage("Cleaned up $deletedCount old RSS imported articles");
            }
        }
        
    } catch (Exception $e) {
        logMessage("Cleanup error: " . $e->getMessage());
    }
}

/**
 * Send error notification (optional implementation)
 */
function sendErrorNotification($exception) {
    // This is a placeholder for email notification
    // You can implement email sending here if needed
    
    $to = 'admin@pklivenews.com';
    $subject = 'RSS Import Error - PK Live News';
    $message = "An error occurred during RSS import:\n\n";
    $message .= "Error: " . $exception->getMessage() . "\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $message .= "Script: " . __FILE__ . "\n";
    
    // Uncomment to send email (requires mail configuration)
    // mail($to, $subject, $message);
    
    logMessage("Error notification would be sent to: $to");
}
?>

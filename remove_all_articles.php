<?php
require_once 'config/database.php';
require_once 'config/settings.php';

echo "Starting article removal process...\n";

// Get count before deletion
$count_query = "SELECT COUNT(*) as total FROM news";
$count_result = $conn->query($count_query);
$total_articles = $count_result->fetch_assoc()['total'];

echo "Total articles to remove: $total_articles\n";

if ($total_articles > 0) {
    // Confirm before proceeding (auto-confirmed for automation)
    $confirm = 'DELETE';
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete related records first (if any foreign key constraints)
            echo "Deleting related records...\n";
            
            // Delete comments if they exist
            $conn->query("DELETE FROM comments WHERE news_id IN (SELECT id FROM news)");
            
            // Delete bookmarks if they exist
            $conn->query("DELETE FROM bookmarks WHERE news_id IN (SELECT id FROM news)");
            
            // Delete analytics if they exist
            $conn->query("DELETE FROM news_analytics WHERE news_id IN (SELECT id FROM news)");
            
            // Delete all articles
            echo "Deleting articles...\n";
            $delete_query = "DELETE FROM news";
            $conn->query($delete_query);
            
            // Reset auto increment
            echo "Resetting auto increment...\n";
            $conn->query("ALTER TABLE news AUTO_INCREMENT = 1");
            
            // Commit transaction
            $conn->commit();
            
            echo "\n=== DELETION COMPLETED ===\n";
            echo "Successfully deleted $total_articles articles\n";
            echo "Database has been reset and is ready for fresh content\n";
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "\nERROR: " . $e->getMessage() . "\n";
            echo "Deletion was rolled back. No data was lost.\n";
        }
} else {
    echo "No articles found. Database is already clean.\n";
}

$conn->close();
?>

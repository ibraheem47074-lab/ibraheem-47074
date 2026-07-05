<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON output

// Prevent any HTML output before JSON
ob_start();

require_once 'config/database.php';

header('Content-Type: application/json');

// Get breaking news
$breaking_query = "SELECT n.*, c.name as category_name 
                   FROM news n 
                   LEFT JOIN categories c ON n.category_id = c.id 
                   WHERE n.status = 'published' AND n.is_breaking = 1 AND n.published_at <= NOW() 
                   ORDER BY n.published_at DESC LIMIT 10";

$result = mysqli_query($conn, $breaking_query);
$breaking_news = [];

while ($news = mysqli_fetch_assoc($result)) {
    $breaking_news[] = [
        'id' => $news['id'],
        'title' => htmlspecialchars($news['title']),
        'slug' => htmlspecialchars($news['slug']),
        'category' => htmlspecialchars($news['category_name']),
        'published_at' => $news['published_at']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $breaking_news,
    'count' => count($breaking_news)
]);

// Clean output buffer and send JSON
ob_end_flush();
?>

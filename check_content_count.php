<?php
require_once 'config/database.php';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check news articles count
    $result = $conn->query('SELECT COUNT(*) as count FROM news');
    $row = $result->fetch_assoc();
    echo "Total news articles: " . $row['count'] . PHP_EOL;
    
    // Check published articles
    $result = $conn->query('SELECT COUNT(*) as count FROM news WHERE status = "published"');
    $row = $result->fetch_assoc();
    echo "Published articles: " . $row['count'] . PHP_EOL;
    
    // Check articles with content
    $result = $conn->query('SELECT COUNT(*) as count FROM news WHERE content IS NOT NULL AND content != ""');
    $row = $result->fetch_assoc();
    echo "Articles with content: " . $row['count'] . PHP_EOL;
    
    // Check articles with images
    $result = $conn->query('SELECT COUNT(*) as count FROM news WHERE image IS NOT NULL AND image != ""');
    $row = $result->fetch_assoc();
    echo "Articles with images: " . $row['count'] . PHP_EOL;
    
    // Get sample of recent articles
    $result = $conn->query('SELECT id, title, created_at FROM news ORDER BY created_at DESC LIMIT 5');
    echo "\nRecent articles:" . PHP_EOL;
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['title'] . " (" . $row['created_at'] . ")" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

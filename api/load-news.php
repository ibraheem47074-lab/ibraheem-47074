<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Handle statistics request
if (isset($_GET['stats']) && $_GET['stats'] == '1') {
    $stats_query = "SELECT 
                   COUNT(*) as total_published,
                   COUNT(CASE WHEN DATE(published_at) = CURDATE() THEN 1 END) as today,
                   COUNT(CASE WHEN published_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as this_hour,
                   COUNT(CASE WHEN source_url IS NOT NULL AND source_url != '' THEN 1 END) as external_news,
                   COUNT(CASE WHEN source_url IS NULL OR source_url = '' THEN 1 END) as internal_news
                   FROM news 
                   WHERE status = 'published' AND published_at <= NOW()";
    
    $result = mysqli_query($conn, $stats_query);
    $stats = mysqli_fetch_assoc($result);
    
    echo json_encode(['stats' => $stats]);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 2;
$per_page = 6;
$offset = ($page - 1) * $per_page;

$query = "SELECT n.*, c.name as category_name, u.name as author_name,
          CASE 
              WHEN n.source_url IS NOT NULL AND n.source_url != '' THEN 'external'
              ELSE 'internal'
          END as news_type,
          CASE 
              WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'new'
              WHEN n.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'recent'
              ELSE 'older'
          END as time_status
          FROM news n 
          LEFT JOIN categories c ON n.category_id = c.id 
          LEFT JOIN users u ON n.author_id = u.id 
          WHERE n.status = 'published' AND n.published_at <= NOW() 
          ORDER BY n.published_at DESC 
          LIMIT $per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

$news = [];
while ($row = mysqli_fetch_assoc($result)) {
    $news[] = $row;
}

echo json_encode(['news' => $news]);
?>

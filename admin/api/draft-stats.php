<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

// Get draft statistics
$draft_stats_query = "SELECT 
                        COUNT(*) as total_drafts,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_drafts,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as weekly_drafts
                     FROM news 
                     WHERE status = 'draft'";

$result = mysqli_query($conn, $draft_stats_query);
$stats = mysqli_fetch_assoc($result);

// Get recent drafts
$recent_drafts_query = "SELECT id, title, created_at, author_name 
                        FROM news n 
                        LEFT JOIN users u ON n.author_id = u.id 
                        WHERE n.status = 'draft' 
                        ORDER BY n.created_at DESC 
                        LIMIT 5";

$recent_result = mysqli_query($conn, $recent_drafts_query);
$recent_drafts = [];

while ($draft = mysqli_fetch_assoc($recent_result)) {
    $recent_drafts[] = [
        'id' => $draft['id'],
        'title' => htmlspecialchars($draft['title']),
        'created_at' => $draft['created_at'],
        'author_name' => htmlspecialchars($draft['author_name'] ?? 'Unknown')
    ];
}

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'recent_drafts' => $recent_drafts
]);
?>

<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

try {
    // Get channel statistics with real data from news table
    $channel_stats_query = "
        SELECT 
            ch.id,
            ch.name as channel_name,
            ch.category,
            ch.country,
            ch.viewer_count,
            ch.status,
            COUNT(n.id) as news_count,
            COALESCE(SUM(n.views), 0) as total_views,
            COALESCE(AVG(n.views), 0) as avg_views,
            COALESCE(SUM(n.comments_count), 0) as total_comments,
            MAX(n.published_at) as latest_news,
            COUNT(CASE WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as articles_today,
            COUNT(CASE WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as articles_week
        FROM channels ch
        LEFT JOIN news n ON ch.id = n.channel_id AND n.status = 'published'
        GROUP BY ch.id, ch.name, ch.category, ch.country, ch.viewer_count, ch.status
        ORDER BY news_count DESC, total_views DESC
    ";

    $result = mysqli_query($conn, $channel_stats_query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    $channels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate engagement rate (comments per 1000 views)
        $engagement_rate = $row['total_views'] > 0 ? 
            round(($row['total_comments'] / $row['total_views']) * 1000, 2) : 0;
        
        // Calculate trend (simple comparison with last week)
        $trend = 'stable'; // This could be enhanced with historical data
        
        // Format the data
        $channels[] = [
            'id' => $row['id'],
            'name' => $row['channel_name'],
            'category' => $row['category'],
            'country' => $row['country'],
            'news_count' => (int)$row['news_count'],
            'total_views' => (int)$row['total_views'],
            'avg_views' => round($row['avg_views']),
            'total_comments' => (int)$row['total_comments'],
            'engagement_rate' => $engagement_rate,
            'viewer_count' => (int)$row['viewer_count'],
            'status' => $row['status'],
            'latest_news' => $row['latest_news'],
            'articles_today' => (int)$row['articles_today'],
            'articles_week' => (int)$row['articles_week'],
            'trend' => $trend,
            'performance_score' => calculatePerformanceScore($row)
        ];
    }

    // Get overall statistics
    $total_stats = [
        'total_channels' => count($channels),
        'total_articles' => array_sum(array_column($channels, 'news_count')),
        'total_views' => array_sum(array_column($channels, 'total_views')),
        'avg_engagement' => count($channels) > 0 ? 
            round(array_sum(array_column($channels, 'engagement_rate')) / count($channels), 2) : 0,
        'active_channels' => count(array_filter($channels, fn($ch) => $ch['status'] === 'live')),
        'last_updated' => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'channels' => $channels,
            'stats' => $total_stats
        ],
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ]);
}

function calculatePerformanceScore($row) {
    // Calculate a performance score based on multiple factors
    $score = 0;
    
    // News count contribution (max 30 points)
    $score += min($row['news_count'] * 2, 30);
    
    // Total views contribution (max 40 points)
    $score += min($row['total_views'] / 1000, 40);
    
    // Average views contribution (max 20 points)
    $score += min($row['avg_views'] / 50, 20);
    
    // Recent activity bonus (max 10 points)
    $score += min($row['articles_today'] * 2, 10);
    
    return round($score, 1);
}
?>

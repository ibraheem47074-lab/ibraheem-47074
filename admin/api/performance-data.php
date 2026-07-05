<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'real_time_visitors':
        // Get real-time visitors (last 30 minutes)
        $query = "SELECT COUNT(DISTINCT session_id) as active_visitors,
                 COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) as logged_in_users,
                 COUNT(DISTINCT ip_address) as unique_ips
                 FROM user_activity 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                 AND action = 'view'";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);
        
        echo json_encode([
            'active_visitors' => (int)$data['active_visitors'],
            'logged_in_users' => (int)$data['logged_in_users'],
            'unique_ips' => (int)$data['unique_ips'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        break;

    case 'most_read_articles':
        // Get most read articles
        $limit = $_GET['limit'] ?? 10;
        $query = "SELECT n.id, n.title, n.slug, n.views, n.published_at,
                 c.name as category_name, c.color as category_color,
                 ua.unique_views, ua.avg_read_time,
                 (ua.shares + ua.comments) as engagement
                 FROM news n
                 LEFT JOIN categories c ON n.category_id = c.id
                 LEFT JOIN news_analytics ua ON n.id = ua.news_id AND ua.date = CURDATE()
                 WHERE n.status = 'published'
                 ORDER BY n.views DESC
                 LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $articles = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $articles[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'views' => (int)$row['views'],
                'category_name' => $row['category_name'],
                'category_color' => $row['category_color'],
                'engagement' => (int)($row['engagement'] ?? 0),
                'published_at' => $row['published_at']
            ];
        }
        
        echo json_encode($articles);
        break;

    case 'traffic_sources':
        // Get traffic sources
        $hours = $_GET['hours'] ?? 24;
        $query = "SELECT 
                 CASE 
                     WHEN referrer LIKE '%google%' OR referrer LIKE '%bing%' OR referrer LIKE '%yahoo%' THEN 'Search Engines'
                     WHEN referrer LIKE '%facebook%' OR referrer LIKE '%twitter%' OR referrer LIKE '%instagram%' OR referrer LIKE '%linkedin%' THEN 'Social Media'
                     WHEN referrer IS NULL OR referrer = '' THEN 'Direct Traffic'
                     ELSE 'Other Referrals'
                 END as source_type,
                 COUNT(DISTINCT session_id) as visitors,
                 COUNT(*) as visits,
                 COUNT(DISTINCT news_id) as pages_viewed
                 FROM user_activity 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                 AND action = 'view'
                 GROUP BY source_type
                 ORDER BY visits DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $hours);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $sources = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sources[] = [
                'source' => $row['source_type'],
                'visitors' => (int)$row['visitors'],
                'visits' => (int)$row['visits'],
                'pages_viewed' => (int)$row['pages_viewed']
            ];
        }
        
        echo json_encode($sources);
        break;

    case 'reporter_performance':
        // Get reporter performance
        $query = "SELECT u.id, u.name as reporter_name, u.email,
                 COUNT(n.id) as articles_published,
                 SUM(n.views) as total_views,
                 AVG(n.views) as avg_views_per_article,
                 MAX(n.views) as best_article_views,
                 COUNT(CASE WHEN n.published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_articles
                 FROM users u
                 LEFT JOIN news n ON u.id = n.author_id AND n.status = 'published'
                 WHERE u.role IN ('admin', 'editor', 'reporter')
                 GROUP BY u.id, u.name, u.email
                 HAVING articles_published > 0
                 ORDER BY total_views DESC
                 LIMIT 10";
        $result = mysqli_query($conn, $query);
        
        $reporters = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reporters[] = [
                'id' => $row['id'],
                'name' => $row['reporter_name'],
                'email' => $row['email'],
                'articles_published' => (int)$row['articles_published'],
                'total_views' => (int)$row['total_views'],
                'avg_views_per_article' => round($row['avg_views_per_article'], 1),
                'best_article_views' => (int)$row['best_article_views'],
                'recent_articles' => (int)$row['recent_articles']
            ];
        }
        
        echo json_encode($reporters);
        break;

    case 'trending_topics':
        // Get trending topics
        $query = "SELECT 
                 c.name as topic, c.color,
                 COUNT(ua.id) as interactions,
                 COUNT(DISTINCT ua.news_id) as articles,
                 COUNT(DISTINCT ua.session_id) as unique_visitors,
                 AVG(ua.duration) as avg_engagement_time
                 FROM user_activity ua
                 JOIN news n ON ua.news_id = n.id
                 JOIN categories c ON n.category_id = c.id
                 WHERE ua.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 GROUP BY c.id, c.name, c.color
                 ORDER BY interactions DESC
                 LIMIT 8";
        $result = mysqli_query($conn, $query);
        
        $topics = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $topics[] = [
                'topic' => $row['topic'],
                'color' => $row['color'],
                'interactions' => (int)$row['interactions'],
                'articles' => (int)$row['articles'],
                'unique_visitors' => (int)$row['unique_visitors'],
                'avg_engagement_time' => round($row['avg_engagement_time'], 1)
            ];
        }
        
        echo json_encode($topics);
        break;

    case 'hourly_trends':
        // Get hourly visitor trends
        $query = "SELECT 
                 HOUR(created_at) as hour,
                 COUNT(DISTINCT session_id) as unique_visitors,
                 COUNT(*) as total_visits,
                 COUNT(DISTINCT news_id) as pages_viewed
                 FROM user_activity 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                 AND action = 'view'
                 GROUP BY HOUR(created_at)
                 ORDER BY hour";
        $result = mysqli_query($conn, $query);
        
        $hourly_array = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly_array[$h] = [
                'visitors' => 0,
                'visits' => 0,
                'pages' => 0
            ];
        }
        
        while ($row = mysqli_fetch_assoc($result)) {
            $hourly_array[$row['hour']] = [
                'visitors' => (int)$row['unique_visitors'],
                'visits' => (int)$row['total_visits'],
                'pages' => (int)$row['pages_viewed']
            ];
        }
        
        $hourly_data = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly_data[] = [
                'hour' => $h,
                'label' => $h . ':00',
                'visitors' => $hourly_array[$h]['visitors'],
                'visits' => $hourly_array[$h]['visits'],
                'pages' => $hourly_array[$h]['pages']
            ];
        }
        
        echo json_encode($hourly_data);
        break;

    case 'dashboard_stats':
        // Get overall dashboard statistics
        $stats = [];
        
        // Real-time visitors
        $query = "SELECT COUNT(DISTINCT session_id) as active_visitors FROM user_activity WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND action = 'view'";
        $result = mysqli_query($conn, $query);
        $stats['active_visitors'] = (int)mysqli_fetch_assoc($result)['active_visitors'];
        
        // Today's total views
        $query = "SELECT SUM(views) as total_views FROM news WHERE DATE(published_at) = CURDATE() AND status = 'published'";
        $result = mysqli_query($conn, $query);
        $stats['today_views'] = (int)(mysqli_fetch_assoc($result)['total_views'] ?? 0);
        
        // Total published articles
        $query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
        $result = mysqli_query($conn, $query);
        $stats['total_articles'] = (int)mysqli_fetch_assoc($result)['total'];
        
        // Active reporters
        $query = "SELECT COUNT(DISTINCT author_id) as active_reporters FROM news WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = mysqli_query($conn, $query);
        $stats['active_reporters'] = (int)mysqli_fetch_assoc($result)['active_reporters'];
        
        echo json_encode($stats);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'viewer_chart_data':
        getViewerChartData();
        break;
    case 'comments':
        getLiveComments();
        break;
    case 'post_comment':
        postLiveComment();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        http_response_code(400);
}

function getViewerChartData() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    $timeframe = $_GET['timeframe'] ?? '1hour'; // 1hour, 6hours, 1day, 1week
    
    if ($stream_id === 0) {
        echo json_encode(['error' => 'Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Determine time interval based on timeframe
    switch ($timeframe) {
        case '1hour':
            $interval = 'INTERVAL 1 HOUR';
            $time_limit = 'INTERVAL 1 HOUR';
            $data_points = 12; // Every 5 minutes
            break;
        case '6hours':
            $interval = 'INTERVAL 30 MINUTE';
            $time_limit = 'INTERVAL 6 HOUR';
            $data_points = 12;
            break;
        case '1day':
            $interval = 'INTERVAL 2 HOUR';
            $time_limit = 'INTERVAL 1 DAY';
            $data_points = 12;
            break;
        case '1week':
            $interval = 'INTERVAL 12 HOUR';
            $time_limit = 'INTERVAL 1 WEEK';
            $data_points = 14;
            break;
        default:
            $interval = 'INTERVAL 5 MINUTE';
            $time_limit = 'INTERVAL 1 HOUR';
            $data_points = 12;
    }
    
    // Check if live_viewer_stats table exists
    $stats_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_viewer_stats'");
    
    if (mysqli_num_rows($stats_table_check) > 0) {
        // Get historical data from stats table
        $query = "SELECT 
                    DATE_FORMAT(recorded_at, '%Y-%m-%d %H:%i:00') as time_slot,
                    AVG(viewer_count) as avg_viewers,
                    MAX(viewer_count) as max_viewers,
                    MIN(viewer_count) as min_viewers
                  FROM live_viewer_stats 
                  WHERE stream_id = ? 
                  AND recorded_at >= DATE_SUB(NOW(), $time_limit)
                  GROUP BY DATE_FORMAT(recorded_at, '%Y-%m-%d %H:%i:00')
                  ORDER BY time_slot ASC
                  LIMIT $data_points";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $stream_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $chart_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $chart_data[] = [
                'time' => $row['time_slot'],
                'viewers' => (int)$row['avg_viewers'],
                'peak' => (int)$row['max_viewers'],
                'min' => (int)$row['min_viewers']
            ];
        }
    } else {
        // Fallback: generate real-time data points
        $chart_data = [];
        $current_time = time();
        $base_viewers = getCurrentViewerCount($stream_id);
        
        for ($i = $data_points - 1; $i >= 0; $i--) {
            $time_slot = date('Y-m-d H:i:00', $current_time - ($i * 300)); // 5-minute intervals
            $variation = rand(-20, 20);
            $viewers = max(1, $base_viewers + $variation);
            
            $chart_data[] = [
                'time' => $time_slot,
                'viewers' => $viewers,
                'peak' => $viewers + rand(0, 10),
                'min' => max(1, $viewers - rand(0, 10))
            ];
        }
    }
    
    // Get current live stats
    $current_viewers = getCurrentViewerCount($stream_id);
    $peak_today = getPeakViewersToday($stream_id);
    $avg_today = getAvgViewersToday($stream_id);
    
    echo json_encode([
        'success' => true,
        'data' => $chart_data,
        'current_viewers' => $current_viewers,
        'peak_today' => $peak_today,
        'avg_today' => $avg_today,
        'timeframe' => $timeframe,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getLiveComments() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 20);
    
    if ($stream_id === 0) {
        echo json_encode(['error' => 'Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Check if live_comments table exists
    $comments_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_comments'");
    
    if (mysqli_num_rows($comments_table_check) > 0) {
        // Check if users table has profile_image column
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_image'");
        $has_profile_image = mysqli_num_rows($column_check) > 0;
        
        if ($has_profile_image) {
            $query = "SELECT 
                        lc.id,
                        lc.username,
                        lc.comment,
                        lc.created_at,
                        u.profile_image
                      FROM live_comments lc
                      LEFT JOIN users u ON lc.user_id = u.id
                      WHERE lc.stream_id = ? 
                      AND lc.status = 'approved'
                      ORDER BY lc.created_at DESC
                      LIMIT ?";
        } else {
            $query = "SELECT 
                        lc.id,
                        lc.username,
                        lc.comment,
                        lc.created_at
                      FROM live_comments lc
                      WHERE lc.stream_id = ? 
                      AND lc.status = 'approved'
                      ORDER BY lc.created_at DESC
                      LIMIT ?";
        }
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $stream_id, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $comments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = [
                'id' => $row['id'],
                'username' => htmlspecialchars($row['username']),
                'comment' => htmlspecialchars($row['comment']),
                'time' => formatTimeAgo($row['created_at']),
                'profile_image' => isset($row['profile_image']) ? $row['profile_image'] : 'assets/images/default-avatar.png'
            ];
        }
        
        echo json_encode([
            'success' => true,
            'comments' => array_reverse($comments), // Show oldest first
            'count' => count($comments)
        ]);
    } else {
        // Return demo comments if table doesn't exist
        echo json_encode([
            'success' => true,
            'comments' => [
                [
                    'id' => 1,
                    'username' => 'Ahmed',
                    'comment' => 'Great coverage! Keep up the good work.',
                    'time' => '2 minutes ago',
                    'profile_image' => 'assets/images/default-avatar.png'
                ],
                [
                    'id' => 2,
                    'username' => 'Sara',
                    'comment' => 'Very informative broadcast, thank you!',
                    'time' => '5 minutes ago',
                    'profile_image' => 'assets/images/default-avatar.png'
                ]
            ],
            'count' => 2
        ]);
    }
}

function postLiveComment() {
    global $conn;
    
    $stream_id = (int)($_POST['stream_id'] ?? 0);
    $username = clean_input($_POST['username'] ?? 'Anonymous');
    $comment = clean_input($_POST['comment'] ?? '');
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    
    if ($stream_id === 0 || empty($comment)) {
        echo json_encode(['error' => 'Stream ID and comment required']);
        http_response_code(400);
        return;
    }
    
    // Check if live_comments table exists
    $comments_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_comments'");
    
    if (mysqli_num_rows($comments_table_check) > 0) {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO live_comments (stream_id, user_id, username, comment, status, created_at) 
             VALUES (?, ?, ?, ?, 'approved', NOW())"
        );
        mysqli_stmt_bind_param($stmt, 'iiss', $stream_id, $user_id, $username, $comment);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => 'Comment posted successfully',
                'comment_id' => mysqli_insert_id($conn)
            ]);
        } else {
            echo json_encode(['error' => 'Failed to post comment']);
            http_response_code(500);
        }
    } else {
        // Simulate successful post for demo
        echo json_encode([
            'success' => true,
            'message' => 'Comment posted successfully (demo mode)',
            'comment_id' => rand(1000, 9999)
        ]);
    }
}

function getCurrentViewerCount($stream_id) {
    global $conn;
    
    // Check if live_viewers table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_viewers'");
    if (mysqli_num_rows($table_check) === 0) {
        return rand(50, 200); // Demo data
    }
    
    $stmt = mysqli_prepare($conn, 
        "SELECT COUNT(*) as count FROM live_viewers 
         WHERE stream_id = ? AND is_active = TRUE 
         AND last_activity >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return (int)mysqli_fetch_assoc($result)['count'];
}

function getPeakViewersToday($stream_id) {
    global $conn;
    
    // Check if UpdateViewerStats procedure exists
    $procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Name = 'UpdateViewerStats'");
    
    if (mysqli_num_rows($procedure_check) > 0) {
        // Use the advanced stored procedure
        $stmt = mysqli_prepare($conn, "CALL UpdateViewerStats(?)");
        mysqli_stmt_bind_param($stmt, 'i', $stream_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Fallback to manual stats recording
        $current_count = getCurrentViewerCount($stream_id);
        
        // Get today's peak
        $stmt = mysqli_prepare($conn, 
            "SELECT MAX(viewer_count) as peak FROM live_viewer_stats 
             WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
        );
        mysqli_stmt_bind_param($stmt, 'i', $stream_id);
        mysqli_stmt_execute($stmt);
        $peak_result = mysqli_stmt_get_result($stmt);
        $peak = mysqli_fetch_assoc($peak_result)['peak'] ?? $current_count;
        
        // Insert or update stats
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO live_viewer_stats (stream_id, viewer_count, peak_viewers) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE 
             viewer_count = VALUES(viewer_count),
             peak_viewers = GREATEST(peak_viewers, VALUES(peak_viewers))"
        );
        $peak_value = max($current_count, $peak);
        mysqli_stmt_bind_param($stmt, 'iii', $stream_id, $current_count, $peak_value);
        mysqli_stmt_execute($stmt);
    }
    
    // Get the updated peak
    $stmt = mysqli_prepare($conn, 
        "SELECT MAX(peak_viewers) as peak FROM live_viewer_stats 
         WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $peak = mysqli_fetch_assoc($result)['peak'];
    
    return $peak ?: getCurrentViewerCount($stream_id);
}

function getAvgViewersToday($stream_id) {
    global $conn;
    
    // Check if live_viewer_stats table exists
    $stats_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_viewer_stats'");
    if (mysqli_num_rows($stats_table_check) === 0) {
        return getCurrentViewerCount($stream_id); // Demo data
    }
    
    $stmt = mysqli_prepare($conn, 
        "SELECT AVG(viewer_count) as avg 
         FROM live_viewer_stats 
         WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $avg = mysqli_fetch_assoc($result)['avg'];
    
    return round($avg ?: getCurrentViewerCount($stream_id));
}

function formatTimeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } else {
        return date('M j, Y', $time);
    }
}

// Record viewer stats every minute
if ($action === 'record_stats') {
    recordViewerStats();
}

function recordViewerStats() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if ($stream_id === 0) return;
    
    // Check if UpdateViewerStats procedure exists
    $procedure_check = mysqli_query($conn, "SHOW PROCEDURE STATUS WHERE Name = 'UpdateViewerStats'");
    
    if (mysqli_num_rows($procedure_check) > 0) {
        // Use the advanced stored procedure
        $stmt = mysqli_prepare($conn, "CALL UpdateViewerStats(?)");
        mysqli_stmt_bind_param($stmt, 'i', $stream_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Fallback to manual stats recording
        $current_count = getCurrentViewerCount($stream_id);
        
        // Get today's peak
        $stmt = mysqli_prepare($conn, 
            "SELECT MAX(viewer_count) as peak FROM live_viewer_stats 
             WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
        );
        mysqli_stmt_bind_param($stmt, 'i', $stream_id);
        mysqli_stmt_execute($stmt);
        $peak_result = mysqli_stmt_get_result($stmt);
        $peak = mysqli_fetch_assoc($peak_result)['peak'] ?? $current_count;
        
        // Insert or update stats
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO live_viewer_stats (stream_id, viewer_count, peak_viewers) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE 
             viewer_count = VALUES(viewer_count),
             peak_viewers = GREATEST(peak_viewers, VALUES(peak_viewers))"
        );
        $peak_value = max($current_count, $peak);
        mysqli_stmt_bind_param($stmt, 'iii', $stream_id, $current_count, $peak_value);
        mysqli_stmt_execute($stmt);
    }
}
?>

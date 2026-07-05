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
    case 'join':
        handleViewerJoin();
        break;
    case 'leave':
        handleViewerLeave();
        break;
    case 'heartbeat':
        handleHeartbeat();
        break;
    case 'count':
        getViewerCount();
        break;
    case 'stats':
        getViewerStats();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        http_response_code(400);
}

function handleViewerJoin() {
    global $conn;
    
    $session_id = session_id() ?: uniqid('viewer_', true);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if ($stream_id === 0) {
        echo json_encode(['error' => 'Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Check if stream is live
    $stmt = mysqli_prepare($conn, 
        "SELECT id FROM live_stream WHERE id = ? AND status = 'online'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $stream_check = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($stream_check) === 0) {
        echo json_encode(['error' => 'Stream not live']);
        http_response_code(404);
        return;
    }
    
    // Remove any existing session for this viewer
    $stmt = mysqli_prepare($conn, 
        "DELETE FROM live_viewers WHERE session_id = ? AND stream_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $stream_id);
    mysqli_stmt_execute($stmt);
    
    // Add new viewer
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO live_viewers (session_id, ip_address, user_agent, user_id, stream_id, is_active) 
         VALUES (?, ?, ?, ?, ?, TRUE)"
    );
    mysqli_stmt_bind_param($stmt, 'ssisi', $session_id, $ip_address, $user_agent, $user_id, $stream_id);
    $insert = mysqli_stmt_execute($stmt);
    
    if ($insert) {
        // Clean inactive viewers periodically
        cleanInactiveViewers();
        
        echo json_encode([
            'success' => true,
            'session_id' => $session_id,
            'viewer_count' => getCurrentViewerCount($stream_id),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['error' => 'Failed to join stream']);
        http_response_code(500);
    }
}

function handleViewerLeave() {
    global $conn;
    
    $session_id = $_GET['session_id'] ?? '';
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if (empty($session_id) || $stream_id === 0) {
        echo json_encode(['error' => 'Session ID and Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Mark viewer as inactive
    $stmt = mysqli_prepare($conn, 
        "UPDATE live_viewers SET is_active = FALSE, last_activity = NOW() 
         WHERE session_id = ? AND stream_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $stream_id);
    $update = mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => $update,
        'viewer_count' => getCurrentViewerCount($stream_id),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function handleHeartbeat() {
    global $conn;
    
    $session_id = $_GET['session_id'] ?? '';
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if (empty($session_id) || $stream_id === 0) {
        echo json_encode(['error' => 'Session ID and Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Update last activity
    $stmt = mysqli_prepare($conn, 
        "UPDATE live_viewers SET last_activity = NOW(), is_active = TRUE 
         WHERE session_id = ? AND stream_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'si', $session_id, $stream_id);
    $update = mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => $update,
        'viewer_count' => getCurrentViewerCount($stream_id),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getViewerCount() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if ($stream_id === 0) {
        echo json_encode(['error' => 'Stream ID required']);
        http_response_code(400);
        return;
    }
    
    $count = getCurrentViewerCount($stream_id);
    
    echo json_encode([
        'viewer_count' => $count,
        'timestamp' => date('Y-m-d H:i:s'),
        'stream_id' => $stream_id
    ]);
}

function getViewerStats() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if ($stream_id === 0) {
        echo json_encode(['error' => 'Stream ID required']);
        http_response_code(400);
        return;
    }
    
    // Check if live_viewer_stats table exists
    $stats_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_viewer_stats'");
    if (mysqli_num_rows($stats_table_check) === 0) {
        // Return default values if table doesn't exist
        echo json_encode([
            'current_viewers' => getCurrentViewerCount($stream_id),
            'peak_viewers_today' => getCurrentViewerCount($stream_id),
            'avg_viewers_today' => round(getCurrentViewerCount($stream_id), 1),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        return;
    }
    
    // Get current stats
    $current = getCurrentViewerCount($stream_id);
    
    // Get peak viewers today
    $stmt = mysqli_prepare($conn, 
        "SELECT MAX(viewer_count) as peak 
         FROM live_viewer_stats 
         WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $peak_result = mysqli_stmt_get_result($stmt);
    $peak_count = mysqli_fetch_assoc($peak_result)['peak'] ?? $current;
    
    // Get average viewers today
    $stmt = mysqli_prepare($conn, 
        "SELECT AVG(viewer_count) as avg 
         FROM live_viewer_stats 
         WHERE stream_id = ? AND DATE(recorded_at) = CURDATE()"
    );
    mysqli_stmt_bind_param($stmt, 'i', $stream_id);
    mysqli_stmt_execute($stmt);
    $avg_result = mysqli_stmt_get_result($stmt);
    $avg_count = round(mysqli_fetch_assoc($avg_result)['avg'] ?? $current);
    
    echo json_encode([
        'current_viewers' => $current,
        'peak_viewers_today' => $peak_count,
        'avg_viewers_today' => $avg_count,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getCurrentViewerCount($stream_id) {
    global $conn;
    
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

function cleanInactiveViewers() {
    global $conn;
    
    // Clean viewers inactive for more than 5 minutes
    $stmt = mysqli_prepare($conn, 
        "DELETE FROM live_viewers 
         WHERE is_active = TRUE 
         AND last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
    );
    mysqli_stmt_execute($stmt);
    
    // Mark viewers inactive if no activity for 2 minutes
    $stmt = mysqli_prepare($conn, 
        "UPDATE live_viewers 
         SET is_active = FALSE 
         WHERE is_active = TRUE 
         AND last_activity < DATE_SUB(NOW(), INTERVAL 2 MINUTE)"
    );
    mysqli_stmt_execute($stmt);
}

// Record viewer stats every minute
if ($action === 'record_stats') {
    recordViewerStats();
}

function recordViewerStats() {
    global $conn;
    
    $stream_id = (int)($_GET['stream_id'] ?? 0);
    
    if ($stream_id === 0) return;
    
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
?>

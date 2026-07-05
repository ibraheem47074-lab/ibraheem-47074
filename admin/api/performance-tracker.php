<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

// Enable CORS for tracking requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
$requiredFields = ['action', 'session_id'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Sanitize input data
    $action = clean_input($data['action']);
    $sessionId = clean_input($data['sessionId'] ?? $data['session_id']);
    $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
    $newsId = isset($data['news_id']) ? (int)$data['news_id'] : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $data['referrer'] ?? '';
    $pageUrl = $data['page_url'] ?? '';
    $pageTitle = $data['page_title'] ?? '';
    $duration = isset($data['time_on_page']) ? (int)$data['time_on_page'] : 0;

    // Insert into user_activity table
    $query = "INSERT INTO user_activity (
        user_id, 
        session_id, 
        news_id, 
        action, 
        ip_address, 
        user_agent, 
        referrer, 
        duration,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isissssi", 
        $userId, 
        $sessionId, 
        $newsId, 
        $action, 
        $ipAddress, 
        $userAgent, 
        $referrer, 
        $duration
    );

    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        // Update news views if this is a page view action
        if ($action === 'view' && $newsId) {
            $updateQuery = "UPDATE news SET views = views + 1 WHERE id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "i", $newsId);
            mysqli_stmt_execute($updateStmt);

            // Update or insert into news_analytics for today
            $today = date('Y-m-d');
            $analyticsQuery = "INSERT INTO news_analytics 
                (news_id, date, views, unique_views, created_at, updated_at) 
                VALUES (?, ?, 1, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                views = views + 1,
                unique_views = unique_views + 1,
                updated_at = NOW()";
            
            $analyticsStmt = mysqli_prepare($conn, $analyticsQuery);
            mysqli_stmt_bind_param($analyticsStmt, "is", $newsId, $today);
            mysqli_stmt_execute($analyticsStmt);
        }

        // Handle specific actions
        switch ($action) {
            case 'social_share':
                if ($newsId) {
                    $updateQuery = "UPDATE news_analytics SET shares = shares + 1 WHERE news_id = ? AND date = CURDATE()";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "i", $newsId);
                    mysqli_stmt_execute($updateStmt);
                }
                break;

            case 'comment':
                if ($newsId) {
                    $updateQuery = "UPDATE news_analytics SET comments = comments + 1 WHERE news_id = ? AND date = CURDATE()";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, "i", $newsId);
                    mysqli_stmt_execute($updateStmt);
                }
                break;

            case 'search':
                if (isset($data['query'])) {
                    $searchQuery = clean_input($data['query']);
                    $searchInsert = "INSERT INTO search_analytics (query, results_count, user_id, ip_address, created_at) 
                                   VALUES (?, 0, ?, ?, NOW())";
                    $searchStmt = mysqli_prepare($conn, $searchInsert);
                    mysqli_stmt_bind_param($searchStmt, "sis", $searchQuery, $userId, $ipAddress);
                    mysqli_stmt_execute($searchStmt);
                }
                break;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Activity tracked successfully',
            'data' => [
                'action' => $action,
                'session_id' => $sessionId,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to insert activity data');
    }

} catch (Exception $e) {
    error_log("Performance tracking error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'Failed to track activity'
    ]);
}
?>

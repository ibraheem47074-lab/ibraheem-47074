<?php
// Disable error display and log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Prevent any output before JSON
if (ob_get_length()) ob_clean();

// Set headers first
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config/database.php';
require_once '../config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['news_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$news_id = (int)$data['news_id'];
$user_id = is_logged_in() ? $_SESSION['user_id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Verify news article exists
$check_query = "SELECT id FROM news WHERE id = ? AND status = 'published'";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, 'i', $news_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'News article not found']);
    exit;
}

// For guest users, create a consistent identifier
if ($user_id === null) {
    // Use session-based tracking for guests to be more reliable
    session_start();
    if (!isset($_SESSION['guest_identifier'])) {
        $_SESSION['guest_identifier'] = 'guest_' . uniqid() . '_' . md5($ip_address);
    }
    $guest_identifier = $_SESSION['guest_identifier'];
    
    // Check if guest already liked this post using session identifier stored in user_agent field
    $check_like_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
    $stmt = mysqli_prepare($conn, $check_like_query);
    mysqli_stmt_bind_param($stmt, 'is', $news_id, $guest_identifier);
} else {
    // Check if registered user already liked this post
    $check_like_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_like_query);
    mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
}
mysqli_stmt_execute($stmt);
$like_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($like_result) > 0) {
    // Unlike the post
    if ($user_id === null) {
        $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id IS NULL AND user_agent = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'is', $news_id, $guest_identifier);
    } else {
        $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Get updated like count
        $count_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
        $stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_stmt_get_result($stmt);
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode([
            'success' => true, 
            'action' => 'unliked', 
            'likes_count' => $count,
            'message' => 'Post unliked'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error unliking post']);
    }
} else {
    // Like the post
    if ($user_id === null) {
        $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, NULL, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iss', $news_id, $ip_address, $guest_identifier);
    } else {
        $insert_query = "INSERT INTO post_likes (news_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iiss', $news_id, $user_id, $ip_address, $user_agent);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Get updated like count
        $count_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
        $stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_stmt_get_result($stmt);
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode([
            'success' => true, 
            'action' => 'liked', 
            'likes_count' => $count,
            'message' => 'Post liked'
        ]);
    } else {
        if (ob_get_length()) ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error liking post']);
    }
}

// Handle any unexpected errors
if (ob_get_length()) ob_end_clean();
mysqli_close($conn);
?>

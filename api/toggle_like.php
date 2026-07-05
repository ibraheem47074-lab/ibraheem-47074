<?php
// Disable error display and log errors instead
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unwanted output
ob_start();

// Set headers first
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config/database.php';
require_once '../config/helpers.php';

session_start();

// For development/testing, allow guest users with session-based tracking
if (!isset($_SESSION['user_id'])) {
    // Create a temporary guest user ID for testing
    if (!isset($_SESSION['guest_user_id'])) {
        $_SESSION['guest_user_id'] = 'guest_' . uniqid();
    }
    $user_id = $_SESSION['guest_user_id'];
    $is_guest = true;
} else {
    $user_id = $_SESSION['user_id'];
    $is_guest = false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;

if ($news_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid news ID']);
    exit();
}

try {
    if ($is_guest) {
        // For guest users, use a different approach - store in session
        $session_key = 'guest_likes_' . $news_id;
        
        if (isset($_SESSION[$session_key])) {
            // Guest already liked, remove the like
            unset($_SESSION[$session_key]);
            $action = 'unliked';
        } else {
            // Guest hasn't liked, add the like
            $_SESSION[$session_key] = true;
            $action = 'liked';
        }
        
        // Count likes from both registered users and guest sessions
        $db_count_query = "SELECT COUNT(*) as db_likes FROM post_likes WHERE news_id = ?";
        $db_count_stmt = mysqli_prepare($conn, $db_count_query);
        mysqli_stmt_bind_param($db_count_stmt, 'i', $news_id);
        mysqli_stmt_execute($db_count_stmt);
        $db_result = mysqli_stmt_get_result($db_count_stmt);
        $db_likes = mysqli_fetch_assoc($db_result)['db_likes'];
        
        // Count guest likes from session for this specific article only
        $guest_likes = 0;
        $session_key = 'guest_likes_' . $news_id;
        if (isset($_SESSION[$session_key]) && $_SESSION[$session_key] === true) {
            $guest_likes = 1;
        }
        
        $likes_count = $db_likes + $guest_likes;
        
    } else {
        // For registered users, use database
        $check_query = "SELECT id FROM post_likes WHERE news_id = ? AND user_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'ii', $news_id, $user_id);
        mysqli_stmt_execute($check_stmt);
        $existing_like = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($existing_like) > 0) {
            // User already liked, so remove the like
            $delete_query = "DELETE FROM post_likes WHERE news_id = ? AND user_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, 'ii', $news_id, $user_id);
            mysqli_stmt_execute($delete_stmt);
            
            $action = 'unliked';
        } else {
            // User hasn't liked, so add the like
            $insert_query = "INSERT INTO post_likes (news_id, user_id, created_at) VALUES (?, ?, NOW())";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'ii', $news_id, $user_id);
            mysqli_stmt_execute($insert_stmt);
            
            $action = 'liked';
        }

        // Get updated like count
        $count_query = "SELECT COUNT(*) as likes_count FROM post_likes WHERE news_id = ?";
        $count_stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($count_stmt, 'i', $news_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $likes_count = mysqli_fetch_assoc($count_result)['likes_count'];
    }

} catch (Exception $e) {
    // Clean any output that might have been generated
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit();
} catch (Error $e) {
    // Clean any output that might have been generated
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'System error occurred']);
    exit();
}

// Clean output buffer and send JSON
ob_clean();
echo json_encode([
    'success' => true,
    'action' => $action,
    'likes_count' => $likes_count,
    'is_guest' => $is_guest
]);
mysqli_close($conn);
?>

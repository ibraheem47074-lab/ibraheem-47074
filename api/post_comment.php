<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');

session_start();

// For development/testing, allow guest users
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
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($news_id <= 0 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    if ($is_guest) {
        // Store guest comment in session
        $guest_comments_key = 'guest_comments_' . $news_id;
        if (!isset($_SESSION[$guest_comments_key])) {
            $_SESSION[$guest_comments_key] = [];
        }
        
        $comment_id = uniqid();
        $_SESSION[$guest_comments_key][] = [
            'id' => $comment_id,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            'success' => true,
            'comment_id' => 'guest_' . $comment_id,
            'user_name' => 'Guest User',
            'message' => 'Comment posted successfully',
            'is_guest' => true
        ]);
        
    } else {
        // Insert the comment for registered users
        $insert_query = "INSERT INTO comments (news_id, user_id, comment, status, created_at) 
                          VALUES (?, ?, ?, 'approved', NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iis', $news_id, $user_id, $comment);
        mysqli_stmt_execute($insert_stmt);

        $comment_id = mysqli_insert_id($conn);

        // Get user name for response
        $user_query = "SELECT name FROM users WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_data = mysqli_fetch_assoc($user_result);
        $user_name = $user_data['name'] ?: 'User';

        echo json_encode([
            'success' => true,
            'comment_id' => $comment_id,
            'user_name' => $user_name,
            'message' => 'Comment posted successfully',
            'is_guest' => false
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>

<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');

session_start();

$news_id = isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0;

if ($news_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid news ID']);
    exit();
}

try {
    // Get database comments
    $query = "SELECT c.*, u.name as user_name 
              FROM comments c 
              LEFT JOIN users u ON c.user_id = u.id 
              WHERE c.news_id = ? AND c.status = 'approved' 
              ORDER BY c.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = [
            'id' => $row['id'],
            'comment' => $row['comment'],
            'user_name' => $row['user_name'] ?: 'Anonymous',
            'created_at' => $row['created_at']
        ];
    }

    // Add guest comments from session if any
    $guest_comments_key = 'guest_comments_' . $news_id;
    if (isset($_SESSION[$guest_comments_key])) {
        foreach ($_SESSION[$guest_comments_key] as $guest_comment) {
            $comments[] = [
                'id' => 'guest_' . $guest_comment['id'],
                'comment' => $guest_comment['comment'],
                'user_name' => 'Guest User',
                'created_at' => $guest_comment['created_at']
            ];
        }
    }

    // Sort all comments by date
    usort($comments, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>

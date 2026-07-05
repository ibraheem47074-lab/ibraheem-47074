<?php
require_once '../config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        // Get unread notifications (both user-specific and all-users notifications)
        $query = "SELECT * FROM notifications 
                 WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 
                 ORDER BY created_at DESC 
                 LIMIT 10";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $notifications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = [
                'id' => $row['id'],
                'type' => $row['type'],
                'title' => $row['title'],
                'message' => $row['message'],
                'url' => $row['url'],
                'created_at' => $row['created_at'],
                'time_ago' => time_ago($row['created_at'])
            ];
        }
        
        // Get unread count
        $count_query = "SELECT COUNT(*) as count FROM notifications 
                       WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
        
        $count_stmt = mysqli_prepare($conn, $count_query);
        mysqli_stmt_bind_param($count_stmt, 'i', $user_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $unread_count = mysqli_fetch_assoc($count_result)['count'];
        
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => (int)$unread_count
        ]);
        break;
        
    case 'mark_read':
        // Mark notification as read
        $notification_id = $_POST['notification_id'] ?? 0;
        
        if ($notification_id) {
            $update_query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                           WHERE id = ? AND (user_id = ? OR user_id IS NULL)";
            
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, 'ii', $notification_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to mark as read']);
            }
        } else {
            // Mark all notifications as read
            $update_all_query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                               WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
            
            $stmt = mysqli_prepare($conn, $update_all_query);
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to mark all as read']);
            }
        }
        break;
        
    case 'mark_all_read':
        // Mark all notifications as read
        $update_query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                       WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to mark all as read']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>

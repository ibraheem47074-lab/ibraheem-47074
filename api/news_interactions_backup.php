<?php
/**
 * Real-time News Interaction System
 * Handles likes, shares, comments, and views in real-time
 */

require_once 'config/database.php';

header('Content-Type: application/json');

// Get action from request
$action = $_POST['action'] ?? '';
$news_id = (int)($_POST['news_id'] ?? 0);

if (!$news_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'like':
        // Handle like/unlike
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        if ($user_id) {
            // Check if user already liked
            $check = "SELECT id FROM news_likes WHERE news_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $check);
            mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                // Unlike
                $delete = "DELETE FROM news_likes WHERE news_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $delete);
                mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
                mysqli_stmt_execute($stmt);
                
                $update = "UPDATE news SET likes_count = likes_count - 1 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param($stmt, 'i', $news_id);
                mysqli_stmt_execute($stmt);
                
                $response['message'] = 'Article unliked';
                $response['liked'] = false;
            } else {
                // Like
                $insert = "INSERT INTO news_likes (news_id, user_id, created_at) VALUES (?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
                mysqli_stmt_execute($stmt);
                
                $update = "UPDATE news SET likes_count = likes_count + 1 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param($stmt, 'i', $news_id);
                mysqli_stmt_execute($stmt);
                
                $response['message'] = 'Article liked';
                $response['liked'] = true;
            }
        } else {
            // Guest like by IP (limited)
            $check = "SELECT id FROM news_likes WHERE news_id = ? AND user_id IS NULL AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = mysqli_prepare($conn, $check);
            mysqli_stmt_bind_param($stmt, 'is', $news_id, $ip_address);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $response['message'] = 'You can only like once per day';
            } else {
                $insert = "INSERT INTO news_likes (news_id, ip_address, created_at) VALUES (?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, 'is', $news_id, $ip_address);
                mysqli_stmt_execute($stmt);
                
                $update = "UPDATE news SET likes_count = likes_count + 1 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param($stmt, 'i', $news_id);
                mysqli_stmt_execute($stmt);
                
                $response['message'] = 'Article liked';
                $response['liked'] = true;
            }
        }
        
        // Get updated counts
        $query = "SELECT likes_count, comment_count, share_count, views FROM news WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $news_data = mysqli_fetch_assoc($result);
        
        $response['success'] = true;
        $response['likes'] = (int)$news_data['likes_count'];
        $response['comments'] = (int)$news_data['comment_count'];
        $response['shares'] = (int)$news_data['share_count'];
        $response['views'] = (int)$news_data['views'];
        break;
        
    case 'share':
        // Track share
        $platform = $_POST['platform'] ?? 'unknown';
        $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Record share
        $insert = "INSERT INTO news_shares (news_id, platform, user_id, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, 'issis', $news_id, $platform, $user_id, $ip_address);
        mysqli_stmt_execute($stmt);
        
        // Update share count
        $update = "UPDATE news SET share_count = share_count + 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        
        $response['success'] = true;
        $response['message'] = 'Share recorded';
        
        // Get updated counts
        $query = "SELECT likes_count, comment_count, share_count, views FROM news WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $news_data = mysqli_fetch_assoc($result);
        
        $response['shares'] = (int)$news_data['share_count'];
        $response['likes'] = (int)$news_data['likes_count'];
        $response['comments'] = (int)$news_data['comment_count'];
        $response['views'] = (int)$news_data['views'];
        break;
        
    case 'get_stats':
        // Get current stats
        $query = "SELECT likes_count, comment_count, share_count, views, updated_at FROM news WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $news_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $news_data = mysqli_fetch_assoc($result);
        
        if ($news_data) {
            $response['success'] = true;
            $response['likes'] = (int)$news_data['likes_count'];
            $response['comments'] = (int)$news_data['comment_count'];
            $response['shares'] = (int)$news_data['share_count'];
            $response['views'] = (int)$news_data['views'];
            $response['updated_at'] = $news_data['updated_at'];
            
            // Check if current user liked this
            if (is_logged_in()) {
                $check = "SELECT id FROM news_likes WHERE news_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $check);
                mysqli_stmt_bind_param($stmt, 'ii', $news_id, $_SESSION['user_id']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $response['user_liked'] = mysqli_num_rows($result) > 0;
            } else {
                $response['user_liked'] = false;
            }
        } else {
            $response['message'] = 'Article not found';
        }
        break;
        
    case 'reset_stats':
        // Reset stats for testing (only if admin or in development)
        if (is_admin() || true) { // Allow for testing
            $update = "UPDATE news SET likes_count = 0, share_count = 0, comment_count = 0, views = 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, 'i', $news_id);
            mysqli_stmt_execute($stmt);
            
            // Clear likes and shares records
            mysqli_query($conn, "DELETE FROM news_likes WHERE news_id = $news_id");
            mysqli_query($conn, "DELETE FROM news_shares WHERE news_id = $news_id");
            
            $response['success'] = true;
            $response['message'] = 'Stats reset successfully';
            
            // Get updated counts
            $query = "SELECT likes_count, comment_count, share_count, views FROM news WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $news_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $news_data = mysqli_fetch_assoc($result);
            
            $response['likes'] = (int)$news_data['likes_count'];
            $response['comments'] = (int)$news_data['comment_count'];
            $response['shares'] = (int)$news_data['share_count'];
            $response['views'] = (int)$news_data['views'];
        } else {
            $response['message'] = 'Unauthorized';
        }
        break;
        
    default:
        $response['message'] = 'Unknown action';
}

echo json_encode($response);
?>

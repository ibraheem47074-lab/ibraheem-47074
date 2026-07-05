<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || !is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get submission ID
$submission_id = $_GET['id'] ?? '';
if (!is_numeric($submission_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit();
}

// Get submission details with comprehensive information
$query = "SELECT ra.*, u.name, u.email, u.created_at as user_joined, u.role as current_role,
          (SELECT COUNT(*) FROM news WHERE author_id = u.id) as user_news_count,
          (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as user_comments_count
          FROM role_applications ra 
          JOIN users u ON ra.user_id = u.id 
          WHERE ra.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $submission_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($submission = mysqli_fetch_assoc($result)) {
    // Decode application data
    $submission['application_data'] = json_decode($submission['application_data'], true) ?: [];
    
    // Add additional user activity data
    $submission['user_activity'] = [
        'news_count' => $submission['user_news_count'],
        'comments_count' => $submission['user_comments_count'],
        'member_since' => $submission['user_joined']
    ];
    
    echo json_encode(['success' => true, 'submission' => $submission]);
} else {
    echo json_encode(['success' => false, 'message' => 'Submission not found']);
}
?>

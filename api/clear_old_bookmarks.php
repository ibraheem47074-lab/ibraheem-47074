<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if bookmarks table exists before trying to delete
$bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;

if (!$bookmarks_table_exists) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'No bookmarks table found - nothing to clear',
        'count' => 0
    ]);
    exit;
}

// Delete bookmarks older than 6 months
$query = "DELETE FROM bookmarks WHERE user_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);

if (mysqli_stmt_execute($stmt)) {
    $deleted_count = mysqli_stmt_affected_rows($stmt);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Old bookmarks cleared successfully',
        'count' => $deleted_count
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>

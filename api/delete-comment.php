<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Get POST data
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if ($comment_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
    exit;
}

// Verify comment exists
$check_query = "SELECT id FROM comments WHERE id = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, 'i', $comment_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit;
}

// Delete comment (and replies due to foreign key cascade)
$delete_query = "DELETE FROM comments WHERE id = ?";
$stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($stmt, 'i', $comment_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting comment']);
}
?>

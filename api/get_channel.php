<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Channel ID is required']);
    exit;
}

$channel_id = intval($_GET['id']);

// Get channel details
$query = "SELECT * FROM channels WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $channel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($channel = mysqli_fetch_assoc($result)) {
    // Update viewer count
    $update_viewers = "UPDATE channels SET viewer_count = viewer_count + 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_viewers);
    mysqli_stmt_bind_param($stmt, 'i', $channel_id);
    mysqli_stmt_execute($stmt);
    
    echo json_encode([
        'success' => true,
        'channel' => $channel
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Channel not found']);
}
?>

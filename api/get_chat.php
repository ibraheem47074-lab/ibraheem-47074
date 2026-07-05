<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['channel_id']) || empty($_GET['channel_id'])) {
    echo json_encode(['success' => false, 'message' => 'Channel ID is required']);
    exit;
}

$channel_id = intval($_GET['channel_id']);

// Get chat messages
$query = "SELECT * FROM live_chat WHERE channel_id = ? AND is_deleted = 0 ORDER BY timestamp DESC LIMIT 50";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $channel_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'username' => htmlspecialchars($row['username']),
        'message' => htmlspecialchars($row['message']),
        'time' => date('h:i A', strtotime($row['timestamp']))
    ];
}

echo json_encode([
    'success' => true,
    'messages' => array_reverse($messages)
]);
?>

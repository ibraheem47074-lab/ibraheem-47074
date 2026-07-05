<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['channel_id']) || !isset($data['message']) || !isset($data['username'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$channel_id = intval($data['channel_id']);
$message = trim($data['message']);
$username = trim($data['username']);

if (empty($message) || empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Message and username are required']);
    exit;
}

// Validate channel exists
$channel_query = "SELECT id FROM channels WHERE id = ?";
$stmt = mysqli_prepare($conn, $channel_query);
mysqli_stmt_bind_param($stmt, 'i', $channel_id);
mysqli_stmt_execute($stmt);
$channel_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($channel_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Channel not found']);
    exit;
}

// Insert chat message
$insert_query = "INSERT INTO live_chat (channel_id, username, message) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, 'iss', $channel_id, $username, $message);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>

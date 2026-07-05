<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON output

// Prevent any HTML output before JSON
ob_start();

require_once '../config/database.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');

// Session is already started in database.php

// Get POST data - support both JSON and form data
$data = null;

// Try JSON first
$json = file_get_contents('php://input');
if ($json) {
    $data = json_decode($json, true);
}

// If JSON failed, try form data
if (!$data) {
    $data = $_POST;
}

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$news_id = isset($data['news_id']) ? (int)$data['news_id'] : 0;
$comment = isset($data['comment']) ? clean_input($data['comment']) : '';
$parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;

// Debug logging
error_log("Comment submission: news_id=$news_id, comment_length=" . strlen($comment));

// Check if user is logged in
$user_id = is_logged_in() ? $_SESSION['user_id'] : null;
$name = '';
$email = '';

if ($news_id === 0 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'News ID and comment are required']);
    exit;
}

if (is_logged_in()) {
    // Get user info from database
    $user_query = "SELECT name, email FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($user_result);
    
    if ($user_data) {
        $name = $user_data['name'];
        $email = $user_data['email'];
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
} else {
    // For guest users, use anonymous info
    $name = 'Anonymous User';
    $email = 'anonymous@example.com';
}

// Verify news article exists
$check_query = "SELECT id FROM news WHERE id = ? AND status = 'published'";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, 'i', $news_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'News article not found']);
    exit;
}

// Auto-approve comments for published articles
$insert_query = "INSERT INTO comments (news_id, user_id, name, email, comment, status, parent_id) VALUES (?, ?, ?, ?, ?, 'approved', ?)";
$stmt = mysqli_prepare($conn, $insert_query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: Failed to prepare statement']);
    exit;
}

// Handle NULL user_id properly
if ($user_id === null) {
    mysqli_stmt_bind_param($stmt, 'isssi', $news_id, $user_id, $name, $email, $comment, $parent_id);
} else {
    mysqli_stmt_bind_param($stmt, 'iisssi', $news_id, $user_id, $name, $email, $comment, $parent_id);
}

if (mysqli_stmt_execute($stmt)) {
    $comment_id = mysqli_insert_id($conn);
    echo json_encode([
        'success' => true, 
        'message' => 'Comment posted successfully',
        'comment_id' => $comment_id,
        'comment' => [
            'id' => $comment_id,
            'name' => $name,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
            'parent_id' => $parent_id
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting comment']);
}

// Clean output buffer and send JSON
ob_end_flush();
?>

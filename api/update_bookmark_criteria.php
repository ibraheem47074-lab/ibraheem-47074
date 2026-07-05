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

// Get JSON data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Check if bookmark_settings table exists, create if not
$create_table_query = "CREATE TABLE IF NOT EXISTS bookmark_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    auto_bookmark_read BOOLEAN DEFAULT FALSE,
    auto_bookmark_liked BOOLEAN DEFAULT FALSE,
    auto_bookmark_commented BOOLEAN DEFAULT FALSE,
    notify_bookmark_reminder BOOLEAN DEFAULT TRUE,
    notify_bookmark_full BOOLEAN DEFAULT FALSE,
    notify_bookmark_digest BOOLEAN DEFAULT FALSE,
    public_bookmarks BOOLEAN DEFAULT FALSE,
    share_bookmarks BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
)";

mysqli_query($conn, $create_table_query);

// Update or insert bookmark settings
$update_query = "INSERT INTO bookmark_settings 
    (user_id, auto_bookmark_read, auto_bookmark_liked, auto_bookmark_commented, 
     notify_bookmark_reminder, notify_bookmark_full, notify_bookmark_digest, 
     public_bookmarks, share_bookmarks)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    auto_bookmark_read = VALUES(auto_bookmark_read),
    auto_bookmark_liked = VALUES(auto_bookmark_liked),
    auto_bookmark_commented = VALUES(auto_bookmark_commented),
    notify_bookmark_reminder = VALUES(notify_bookmark_reminder),
    notify_bookmark_full = VALUES(notify_bookmark_full),
    notify_bookmark_digest = VALUES(notify_bookmark_digest),
    public_bookmarks = VALUES(public_bookmarks),
    share_bookmarks = VALUES(share_bookmarks),
    updated_at = CURRENT_TIMESTAMP";

$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, 'iiiiiiiii', 
    $user_id,
    $data['auto_bookmark_read'],
    $data['auto_bookmark_liked'], 
    $data['auto_bookmark_commented'],
    $data['notify_bookmark_reminder'],
    $data['notify_bookmark_full'],
    $data['notify_bookmark_digest'],
    $data['public_bookmarks'],
    $data['share_bookmarks']
);

if (mysqli_stmt_execute($stmt)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Bookmark criteria updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>

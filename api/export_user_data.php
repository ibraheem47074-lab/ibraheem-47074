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

// Get user's complete data
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_result);

// Remove sensitive data
unset($user_data['password']);
unset($user_data['reset_token']);
unset($user_data['reset_expires']);

// Get user's bookmarks - check if tables exist first
$bookmarks = [];
$bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;
$bookmark_folders_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmark_folders'")->num_rows > 0;

if ($bookmarks_table_exists) {
    if ($bookmark_folders_table_exists) {
        $bookmarks_query = "SELECT b.*, n.title, n.slug, n.excerpt, n.published_at, 
                               c.name as category_name, bf.name as folder_name
                        FROM bookmarks b 
                        LEFT JOIN news n ON b.news_id = n.id 
                        LEFT JOIN categories c ON n.category_id = c.id
                        LEFT JOIN bookmark_folders bf ON b.folder_id = bf.id
                        WHERE b.user_id = ? 
                        ORDER BY b.created_at DESC";
    } else {
        $bookmarks_query = "SELECT b.*, n.title, n.slug, n.excerpt, n.published_at, 
                               c.name as category_name, NULL as folder_name
                        FROM bookmarks b 
                        LEFT JOIN news n ON b.news_id = n.id 
                        LEFT JOIN categories c ON n.category_id = c.id
                        WHERE b.user_id = ? 
                        ORDER BY b.created_at DESC";
    }
    
    $bookmarks_stmt = mysqli_prepare($conn, $bookmarks_query);
    mysqli_stmt_bind_param($bookmarks_stmt, 'i', $user_id);
    mysqli_stmt_execute($bookmarks_stmt);
    $bookmarks_result = mysqli_stmt_get_result($bookmarks_stmt);
    
    while ($row = mysqli_fetch_assoc($bookmarks_result)) {
        $bookmarks[] = $row;
    }
}

// Get user's comments - check if tables exist first
$comments = [];
$comments_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'comments'")->num_rows > 0;

if ($comments_table_exists) {
    $comments_query = "SELECT c.*, n.title as news_title, n.slug as news_slug
                    FROM comments c 
                    LEFT JOIN news n ON c.news_id = n.id
                    WHERE c.user_id = ? 
                    ORDER BY c.created_at DESC";

    $comments_stmt = mysqli_prepare($conn, $comments_query);
    mysqli_stmt_bind_param($comments_stmt, 'i', $user_id);
    mysqli_stmt_execute($comments_stmt);
    $comments_result = mysqli_stmt_get_result($comments_stmt);

    while ($row = mysqli_fetch_assoc($comments_result)) {
        $comments[] = $row;
    }
}

// Get user's bookmark folders - check if table exists first
$folders = [];
if ($bookmark_folders_table_exists) {
    $folders_query = "SELECT * FROM bookmark_folders WHERE user_id = ? ORDER BY name ASC";
    $folders_stmt = mysqli_prepare($conn, $folders_query);
    mysqli_stmt_bind_param($folders_stmt, 'i', $user_id);
    mysqli_stmt_execute($folders_stmt);
    $folders_result = mysqli_stmt_get_result($folders_stmt);

    while ($row = mysqli_fetch_assoc($folders_result)) {
        $folders[] = $row;
    }
}

// Get settings - check if tables exist first
$bookmark_settings_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmark_settings'")->num_rows > 0;
$advanced_settings_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'advanced_settings'")->num_rows > 0;

$bookmark_settings = null;
if ($bookmark_settings_table_exists) {
    $bookmark_settings_query = "SELECT * FROM bookmark_settings WHERE user_id = ?";
    $bookmark_settings_stmt = mysqli_prepare($conn, $bookmark_settings_query);
    mysqli_stmt_bind_param($bookmark_settings_stmt, 'i', $user_id);
    mysqli_stmt_execute($bookmark_settings_stmt);
    $bookmark_settings_result = mysqli_stmt_get_result($bookmark_settings_stmt);
    $bookmark_settings = mysqli_fetch_assoc($bookmark_settings_result);
}

$advanced_settings = null;
if ($advanced_settings_table_exists) {
    $advanced_settings_query = "SELECT * FROM advanced_settings WHERE user_id = ?";
    $advanced_settings_stmt = mysqli_prepare($conn, $advanced_settings_query);
    mysqli_stmt_bind_param($advanced_settings_stmt, 'i', $user_id);
    mysqli_stmt_execute($advanced_settings_stmt);
    $advanced_settings_result = mysqli_stmt_get_result($advanced_settings_stmt);
    $advanced_settings = mysqli_fetch_assoc($advanced_settings_result);
}

// Create comprehensive export data
$export_data = [
    'export_date' => date('Y-m-d H:i:s'),
    'user_id' => $user_id,
    'user_profile' => $user_data,
    'bookmarks' => [
        'total_count' => count($bookmarks),
        'items' => $bookmarks
    ],
    'comments' => [
        'total_count' => count($comments),
        'items' => $comments
    ],
    'bookmark_folders' => [
        'total_count' => count($folders),
        'items' => $folders
    ],
    'settings' => [
        'bookmark_settings' => $bookmark_settings,
        'advanced_settings' => $advanced_settings
    ]
];

// Create JSON file
$filename = 'user_data_export_' . date('Y-m-d_H-i-s') . '.json';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo json_encode($export_data, JSON_PRETTY_PRINT);
?>

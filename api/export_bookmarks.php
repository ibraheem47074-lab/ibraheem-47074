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

// Check if tables exist before querying
$bookmarks_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmarks'")->num_rows > 0;
$bookmark_folders_table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'bookmark_folders'")->num_rows > 0;

if (!$bookmarks_table_exists) {
    // Return empty bookmarks if table doesn't exist
    $bookmarks = [];
} else {
    if ($bookmark_folders_table_exists) {
        $query = "SELECT b.*, n.title, n.slug, n.content, n.excerpt, n.published_at, 
                         c.name as category_name, bf.name as folder_name
                  FROM bookmarks b 
                  LEFT JOIN news n ON b.news_id = n.id 
                  LEFT JOIN categories c ON n.category_id = c.id
                  LEFT JOIN bookmark_folders bf ON b.folder_id = bf.id
                  WHERE b.user_id = ? 
                  ORDER BY b.created_at DESC";
    } else {
        $query = "SELECT b.*, n.title, n.slug, n.content, n.excerpt, n.published_at, 
                         c.name as category_name, NULL as folder_name
                  FROM bookmarks b 
                  LEFT JOIN news n ON b.news_id = n.id 
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE b.user_id = ? 
                  ORDER BY b.created_at DESC";
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $bookmarks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookmarks[] = [
            'title' => $row['title'],
            'url' => 'news.php?slug=' . $row['slug'],
            'category' => $row['category_name'],
            'folder' => $row['folder_name'] ?: 'Uncategorized',
            'excerpt' => $row['excerpt'],
            'bookmarked_at' => $row['created_at'],
            'published_at' => $row['published_at']
        ];
    }
}

// Create JSON file
$filename = 'bookmarks_export_' . date('Y-m-d_H-i-s') . '.json';
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$export_data = [
    'export_date' => date('Y-m-d H:i:s'),
    'user_id' => $user_id,
    'total_bookmarks' => count($bookmarks),
    'bookmarks' => $bookmarks
];

echo json_encode($export_data, JSON_PRETTY_PRINT);
?>

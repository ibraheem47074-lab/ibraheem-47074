<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON output

// Prevent any HTML output before JSON
ob_start();

require_once '../config/database.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

$news_id = isset($_GET['news_id']) ? (int)$_GET['news_id'] : 0;

if ($news_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid news ID']);
    exit;
}

// Check database connection
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get news details
$news_query = "SELECT n.title, n.published_at, c.name as category_name 
               FROM news n 
               LEFT JOIN categories c ON n.category_id = c.id 
               WHERE n.id = ?";
$stmt = mysqli_prepare($conn, $news_query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare news query: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $news_id);
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to execute news query: ' . mysqli_stmt_error($stmt)]);
    exit;
}

$news_result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($news_result);

if (!$news) {
    echo json_encode(['success' => false, 'message' => 'News article not found']);
    exit;
}

// Get comments
$comments_query = "SELECT c.*, u.name as user_name, u.role as user_role
                   FROM comments c 
                   LEFT JOIN users u ON c.user_id = u.id 
                   WHERE c.news_id = ? AND c.status = 'approved' 
                   ORDER BY c.created_at DESC";
$stmt = mysqli_prepare($conn, $comments_query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare comments query: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $news_id);
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Failed to execute comments query: ' . mysqli_stmt_error($stmt)]);
    exit;
}

$comments_result = mysqli_stmt_get_result($stmt);

$comments = [];
while ($comment = mysqli_fetch_assoc($comments_result)) {
    $comment['is_admin'] = ($comment['user_role'] === 'admin');
    $comments[] = $comment;
}

echo json_encode([
    'success' => true,
    'news' => $news,
    'comments' => $comments
]);

// Clean output buffer and send JSON
ob_end_flush();
?>

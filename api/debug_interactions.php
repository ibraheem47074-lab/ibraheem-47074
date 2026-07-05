<?php
// Debug version of news_interactions.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Test database connection
    require_once '../config/database.php';
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get action from request
    $action = $_POST['action'] ?? '';
    $news_id = (int)($_POST['news_id'] ?? 0);
    
    if (!$news_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Invalid request', 'debug' => ['action' => $action, 'news_id' => $news_id]]);
        exit;
    }
    
    // Test basic query
    $test_query = "SELECT id, title FROM news WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $test_query);
    mysqli_stmt_bind_param($stmt, 'i', $news_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'News article not found']);
        exit;
    }
    
    $news = mysqli_fetch_assoc($result);
    
    // Return success with basic data
    echo json_encode([
        'success' => true,
        'message' => 'API working',
        'debug' => [
            'news_id' => $news_id,
            'action' => $action,
            'news_title' => $news['title'],
            'likes' => 0,
            'shares' => 0,
            'comments' => 0,
            'views' => 1
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'API Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>

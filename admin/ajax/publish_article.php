<?php
/**
 * AJAX handler to publish RSS imported article
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and has proper permissions
session_start();
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Check if user has permission to publish articles (admin or editor)
if (!is_admin() && !is_editor()) {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$articleId = isset($input['id']) ? (int)$input['id'] : 0;

if ($articleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID']);
    exit;
}

try {
    // Update article status to published
    $query = "UPDATE news SET status = 'published', published_at = NOW() WHERE id = ? AND news_type = 'rss_import'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $articleId);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true, 'message' => 'Article published successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Article not found or already published']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

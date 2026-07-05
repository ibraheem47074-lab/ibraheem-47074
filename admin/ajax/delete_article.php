<?php
/**
 * AJAX handler to delete RSS imported article
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and has proper permissions
session_start();
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Check if user has permission to delete articles (admin or editor)
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
    // Get article info for cleanup
    $selectQuery = "SELECT image FROM news WHERE id = ? AND news_type = 'rss_import'";
    $selectStmt = mysqli_prepare($conn, $selectQuery);
    mysqli_stmt_bind_param($selectStmt, 'i', $articleId);
    mysqli_stmt_execute($selectStmt);
    $result = mysqli_stmt_get_result($selectStmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Delete image file if exists
        if (!empty($row['image'])) {
            $imagePath = __DIR__ . '/../../' . $row['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
    
    // Delete article
    $query = "DELETE FROM news WHERE id = ? AND news_type = 'rss_import'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $articleId);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true, 'message' => 'Article deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Article not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($selectStmt);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

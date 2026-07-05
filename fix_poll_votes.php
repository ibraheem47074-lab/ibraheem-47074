<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Get active poll
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT id FROM polls WHERE status = 'active' AND (ends_at IS NULL OR ends_at > NOW()) ORDER BY id DESC LIMIT 1"
));

if (!$active_poll) {
    echo json_encode(['success' => false, 'message' => 'No active poll found']);
    exit;
}

$poll_id = $active_poll['id'];

try {
    // Update vote counts for all options in this poll
    $update_query = "UPDATE poll_options po 
                     SET votes = (
                         SELECT COUNT(*) 
                         FROM poll_votes pv 
                         WHERE pv.option_id = po.id
                     ) 
                     WHERE po.poll_id = ?";
    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $poll_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Vote counts synchronized successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating vote counts: ' . mysqli_error($conn)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>

<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Start session for user tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get poll data
$poll_id = (int)$_POST['poll_id'];
$option_id = (int)$_POST['poll_option'];

// Validate inputs
if (empty($poll_id) || empty($option_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid poll or option']);
    exit;
}

// Check if poll exists and is active
$poll_check = mysqli_prepare($conn, "SELECT id, status FROM polls WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($poll_check, 'i', $poll_id);
mysqli_stmt_execute($poll_check);
$poll_result = mysqli_stmt_get_result($poll_check);

if (mysqli_num_rows($poll_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Poll not found or inactive']);
    exit;
}

// Check if option exists for this poll
$option_check = mysqli_prepare($conn, "SELECT id FROM poll_options WHERE id = ? AND poll_id = ?");
mysqli_stmt_bind_param($option_check, 'ii', $option_id, $poll_id);
mysqli_stmt_execute($option_check);
$option_result = mysqli_stmt_get_result($option_check);

if (mysqli_num_rows($option_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid option']);
    exit;
}

// Get user IP for tracking
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Check if user has already voted (by IP or user ID)
$vote_check = mysqli_prepare($conn, 
    "SELECT id FROM poll_votes WHERE poll_id = ? AND (ip_address = ? OR (user_id IS NOT NULL AND user_id = ?))"
);
mysqli_stmt_bind_param($vote_check, 'isi', $poll_id, $ip_address, $user_id);
mysqli_stmt_execute($vote_check);
$existing_vote = mysqli_stmt_get_result($vote_check);

if (mysqli_num_rows($existing_vote) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already voted in this poll']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert the vote
    $vote_insert = mysqli_prepare($conn, 
        "INSERT INTO poll_votes (poll_id, option_id, user_id, ip_address) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($vote_insert, 'iiis', $poll_id, $option_id, $user_id, $ip_address);
    mysqli_stmt_execute($vote_insert);
    
    // Update the vote count for the option
    $vote_update = mysqli_prepare($conn, 
        "UPDATE poll_options SET votes = votes + 1 WHERE id = ?"
    );
    mysqli_stmt_bind_param($vote_update, 'i', $option_id);
    mysqli_stmt_execute($vote_update);
    
    // Get updated results
    $results_query = mysqli_prepare($conn, "
        SELECT po.id as option_id, po.option_text, po.votes,
               (SELECT COUNT(*) FROM poll_votes WHERE poll_id = ?) as total_votes
        FROM poll_options po 
        WHERE po.poll_id = ?
        ORDER BY po.id
    ");
    mysqli_stmt_bind_param($results_query, 'ii', $poll_id, $poll_id);
    mysqli_stmt_execute($results_query);
    $results_result = mysqli_stmt_get_result($results_query);
    
    $results = [];
    $total_votes = 0;
    
    while ($row = mysqli_fetch_assoc($results_result)) {
        $total_votes = $row['total_votes'];
        $percentage = $total_votes > 0 ? round(($row['votes'] / $total_votes) * 100, 1) : 0;
        $results[] = [
            'option_id' => $row['option_id'],
            'option_text' => $row['option_text'],
            'votes' => $row['votes'],
            'percentage' => $percentage
        ];
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Vote recorded successfully',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    error_log("Vote submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error recording vote']);
}

mysqli_close($conn);
?>

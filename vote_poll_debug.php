<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: application/json');

// Debug function
function debug_log($message) {
    error_log("VOTE_DEBUG: " . $message);
}

debug_log("Vote submission started");

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

debug_log("Request method is POST");

// Get poll data
$poll_id = (int)$_POST['poll_id'];
$option_id = (int)$_POST['poll_option'];

debug_log("Poll ID: $poll_id, Option ID: $option_id");

// Validate inputs
if (empty($poll_id) || empty($option_id)) {
    debug_log("Empty poll_id or option_id");
    echo json_encode(['success' => false, 'message' => 'Invalid poll or option']);
    exit;
}

debug_log("Inputs validated");

// Check if poll exists and is active
$poll_check = mysqli_prepare($conn, "SELECT id, status FROM polls WHERE id = ? AND status = 'active'");
if (!$poll_check) {
    debug_log("Failed to prepare poll check query: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error preparing poll check']);
    exit;
}

mysqli_stmt_bind_param($poll_check, 'i', $poll_id);
if (!mysqli_stmt_execute($poll_check)) {
    debug_log("Failed to execute poll check query: " . mysqli_stmt_error($poll_check));
    echo json_encode(['success' => false, 'message' => 'Database error executing poll check']);
    exit;
}

$poll_result = mysqli_stmt_get_result($poll_check);

if (mysqli_num_rows($poll_result) === 0) {
    debug_log("Poll not found or inactive");
    echo json_encode(['success' => false, 'message' => 'Poll not found or inactive']);
    exit;
}

debug_log("Poll found and active");

// Check if option exists for this poll
$option_check = mysqli_prepare($conn, "SELECT id FROM poll_options WHERE id = ? AND poll_id = ?");
if (!$option_check) {
    debug_log("Failed to prepare option check query: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error preparing option check']);
    exit;
}

mysqli_stmt_bind_param($option_check, 'ii', $option_id, $poll_id);
if (!mysqli_stmt_execute($option_check)) {
    debug_log("Failed to execute option check query: " . mysqli_stmt_error($option_check));
    echo json_encode(['success' => false, 'message' => 'Database error executing option check']);
    exit;
}

$option_result = mysqli_stmt_get_result($option_check);

if (mysqli_num_rows($option_result) === 0) {
    debug_log("Invalid option");
    echo json_encode(['success' => false, 'message' => 'Invalid option']);
    exit;
}

debug_log("Option found for this poll");

// Get user IP for tracking
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

debug_log("IP: $ip_address, User ID: " . ($user_id ?? 'null'));

// Check if user has already voted (by IP or user ID)
$vote_check = mysqli_prepare($conn, 
    "SELECT id FROM poll_votes WHERE poll_id = ? AND (ip_address = ? OR (user_id IS NOT NULL AND user_id = ?))"
);
if (!$vote_check) {
    debug_log("Failed to prepare vote check query: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error preparing vote check']);
    exit;
}

mysqli_stmt_bind_param($vote_check, 'isi', $poll_id, $ip_address, $user_id);
if (!mysqli_stmt_execute($vote_check)) {
    debug_log("Failed to execute vote check query: " . mysqli_stmt_error($vote_check));
    echo json_encode(['success' => false, 'message' => 'Database error executing vote check']);
    exit;
}

$existing_vote = mysqli_stmt_get_result($vote_check);

if (mysqli_num_rows($existing_vote) > 0) {
    debug_log("User has already voted");
    echo json_encode(['success' => false, 'message' => 'You have already voted in this poll']);
    exit;
}

debug_log("User has not voted yet, proceeding with vote");

// Start transaction
if (!mysqli_begin_transaction($conn)) {
    debug_log("Failed to begin transaction: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error starting transaction']);
    exit;
}

debug_log("Transaction started");

try {
    // Insert the vote
    $vote_insert = mysqli_prepare($conn, 
        "INSERT INTO poll_votes (poll_id, option_id, user_id, ip_address) VALUES (?, ?, ?, ?)"
    );
    if (!$vote_insert) {
        debug_log("Failed to prepare vote insert query: " . mysqli_error($conn));
        throw new Exception('Database error preparing vote insert');
    }

    mysqli_stmt_bind_param($vote_insert, 'iiis', $poll_id, $option_id, $user_id, $ip_address);
    if (!mysqli_stmt_execute($vote_insert)) {
        debug_log("Failed to execute vote insert query: " . mysqli_stmt_error($vote_insert));
        throw new Exception('Database error executing vote insert');
    }

    debug_log("Vote inserted successfully");

    // Update the vote count for the option
    $vote_update = mysqli_prepare($conn, 
        "UPDATE poll_options SET votes = votes + 1 WHERE id = ?"
    );
    if (!$vote_update) {
        debug_log("Failed to prepare vote update query: " . mysqli_error($conn));
        throw new Exception('Database error preparing vote update');
    }

    mysqli_stmt_bind_param($vote_update, 'i', $option_id);
    if (!mysqli_stmt_execute($vote_update)) {
        debug_log("Failed to execute vote update query: " . mysqli_stmt_error($vote_update));
        throw new Exception('Database error executing vote update');
    }

    debug_log("Vote count updated successfully");

    // Commit transaction
    if (!mysqli_commit($conn)) {
        debug_log("Failed to commit transaction: " . mysqli_error($conn));
        throw new Exception('Database error committing transaction');
    }

    debug_log("Transaction committed successfully");

    echo json_encode(['success' => true, 'message' => 'Vote recorded successfully']);

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    debug_log("Exception occurred: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error recording vote: ' . $e->getMessage()]);
}

mysqli_close($conn);
debug_log("Database connection closed");
?>

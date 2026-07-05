<?php
require_once 'config/database.php';

// Check if there's an active poll
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT id, question FROM polls WHERE status = 'active' AND (ends_at IS NULL OR ends_at > NOW()) ORDER BY id DESC LIMIT 1"
));

if (!$active_poll) {
    echo "No active poll found. Creating one...\n";
    
    // Create a test poll
    mysqli_query($conn, "INSERT INTO polls (question, status, created_at) VALUES ('What is your favorite news category?', 'active', NOW())");
    $poll_id = mysqli_insert_id($conn);
    
    // Create options
    $options = ['Politics', 'Sports', 'Technology', 'Business', 'Entertainment'];
    foreach ($options as $index => $option_text) {
        mysqli_query($conn, "INSERT INTO poll_options (poll_id, option_text, votes, order_position) VALUES ($poll_id, '$option_text', 0, " . ($index + 1) . ")");
    }
    
    echo "Created test poll with ID: $poll_id\n";
    $active_poll = ['id' => $poll_id, 'question' => 'What is your favorite news category?'];
}

echo "Active Poll ID: " . $active_poll['id'] . "\n";
echo "Question: " . $active_poll['question'] . "\n\n";

// Check poll options
$options = mysqli_query($conn, "SELECT * FROM poll_options WHERE poll_id = " . $active_poll['id']);
echo "Poll Options:\n";
while ($option = mysqli_fetch_assoc($options)) {
    echo "- Option ID: " . $option['id'] . " - Text: " . $option['option_text'] . " - Votes: " . $option['votes'] . "\n";
}

echo "\n=== Testing Vote Submission ===\n";

// Simulate a vote submission
$_POST['poll_id'] = $active_poll['id'];
$_POST['poll_option'] = 1; // Vote for first option
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "Simulating vote submission:\n";
echo "Poll ID: " . $_POST['poll_id'] . "\n";
echo "Option ID: " . $_POST['poll_option'] . "\n";
echo "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

// Test the vote_poll.php logic
echo "Checking vote_poll.php logic...\n";

// 1. Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "❌ Invalid request method\n";
} else {
    echo "✅ Request method is POST\n";
}

// 2. Get and validate poll data
$poll_id = (int)$_POST['poll_id'];
$option_id = (int)$_POST['poll_option'];

if (empty($poll_id) || empty($option_id)) {
    echo "❌ Invalid poll or option\n";
} else {
    echo "✅ Poll ID: $poll_id, Option ID: $option_id\n";
}

// 3. Check if poll exists and is active
$poll_check = mysqli_prepare($conn, "SELECT id, status FROM polls WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($poll_check, 'i', $poll_id);
mysqli_stmt_execute($poll_check);
$poll_result = mysqli_stmt_get_result($poll_check);

if (mysqli_num_rows($poll_result) === 0) {
    echo "❌ Poll not found or inactive\n";
} else {
    echo "✅ Poll found and active\n";
}

// 4. Check if option exists for this poll
$option_check = mysqli_prepare($conn, "SELECT id FROM poll_options WHERE id = ? AND poll_id = ?");
mysqli_stmt_bind_param($option_check, 'ii', $option_id, $poll_id);
mysqli_stmt_execute($option_check);
$option_result = mysqli_stmt_get_result($option_check);

if (mysqli_num_rows($option_result) === 0) {
    echo "❌ Invalid option\n";
} else {
    echo "✅ Option found for this poll\n";
}

// 5. Check if user has already voted
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_id = null; // Not logged in

$vote_check = mysqli_prepare($conn, 
    "SELECT id FROM poll_votes WHERE poll_id = ? AND (ip_address = ? OR (user_id IS NOT NULL AND user_id = ?))"
);
mysqli_stmt_bind_param($vote_check, 'isi', $poll_id, $ip_address, $user_id);
mysqli_stmt_execute($vote_check);
$existing_vote = mysqli_stmt_get_result($vote_check);

if (mysqli_num_rows($existing_vote) > 0) {
    echo "❌ User has already voted\n";
} else {
    echo "✅ User has not voted yet\n";
    
    // 6. Try to insert the vote
    mysqli_begin_transaction($conn);
    
    try {
        // Insert the vote
        $vote_insert = mysqli_prepare($conn, 
            "INSERT INTO poll_votes (poll_id, option_id, user_id, ip_address) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($vote_insert, 'iiis', $poll_id, $option_id, $user_id, $ip_address);
        
        if (mysqli_stmt_execute($vote_insert)) {
            echo "✅ Vote inserted successfully\n";
        } else {
            echo "❌ Failed to insert vote: " . mysqli_stmt_error($vote_insert) . "\n";
        }
        
        // Update the vote count
        $vote_update = mysqli_prepare($conn, 
            "UPDATE poll_options SET votes = votes + 1 WHERE id = ?"
        );
        mysqli_stmt_bind_param($vote_update, 'i', $option_id);
        
        if (mysqli_stmt_execute($vote_update)) {
            echo "✅ Vote count updated successfully\n";
        } else {
            echo "❌ Failed to update vote count: " . mysqli_stmt_error($vote_update) . "\n";
        }
        
        // Commit transaction
        mysqli_commit($conn);
        echo "✅ Transaction committed\n";
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Final Status ===\n";
// Check final vote counts
$final_options = mysqli_query($conn, "SELECT * FROM poll_options WHERE poll_id = " . $active_poll['id']);
echo "Final vote counts:\n";
while ($option = mysqli_fetch_assoc($final_options)) {
    echo "- Option ID: " . $option['id'] . " - Text: " . $option['option_text'] . " - Votes: " . $option['votes'] . "\n";
}

// Check poll_votes table
$votes_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM poll_votes WHERE poll_id = " . $active_poll['id']);
$total_votes = mysqli_fetch_assoc($votes_count)['total'];
echo "Total votes in poll_votes table: $total_votes\n";
?>

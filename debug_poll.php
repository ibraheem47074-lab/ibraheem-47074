<?php
require_once 'config/database.php';

echo "=== POLL DEBUGGING ===\n\n";

// Check if tables exist
$tables = ['polls', 'poll_options', 'poll_votes'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    echo "Table '$table': " . (mysqli_num_rows($result) > 0 ? "EXISTS" : "MISSING") . "\n";
}

echo "\n=== ACTIVE POLL DATA ===\n";
$poll_query = "SELECT * FROM polls WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$poll_result = mysqli_query($conn, $poll_query);

if ($poll = mysqli_fetch_assoc($poll_result)) {
    echo "Poll ID: " . $poll['id'] . "\n";
    echo "Question: " . $poll['question'] . "\n";
    echo "Status: " . $poll['status'] . "\n";
    echo "Created: " . $poll['created_at'] . "\n";
    
    echo "\n=== POLL OPTIONS ===\n";
    $options_query = "SELECT * FROM poll_options WHERE poll_id = " . $poll['id'] . " ORDER BY id";
    $options_result = mysqli_query($conn, $options_query);
    
    $total_votes_in_options = 0;
    while ($option = mysqli_fetch_assoc($options_result)) {
        echo "Option ID: " . $option['id'] . "\n";
        echo "Text: " . $option['option_text'] . "\n";
        echo "Votes (in poll_options): " . $option['votes'] . "\n";
        $total_votes_in_options += $option['votes'];
        echo "---\n";
    }
    
    echo "\n=== ACTUAL VOTES IN poll_votes TABLE ===\n";
    $votes_query = "SELECT option_id, COUNT(*) as vote_count FROM poll_votes WHERE poll_id = " . $poll['id'] . " GROUP BY option_id";
    $votes_result = mysqli_query($conn, $votes_query);
    
    $total_votes_in_votes_table = 0;
    while ($vote = mysqli_fetch_assoc($votes_result)) {
        echo "Option ID: " . $vote['option_id'] . " - Actual votes: " . $vote['vote_count'] . "\n";
        $total_votes_in_votes_table += $vote['vote_count'];
    }
    
    echo "\n=== COMPARISON ===\n";
    echo "Total votes in poll_options table: " . $total_votes_in_options . "\n";
    echo "Total votes in poll_votes table: " . $total_votes_in_votes_table . "\n";
    
    if ($total_votes_in_options != $total_votes_in_votes_table) {
        echo "\n!!! ISSUE DETECTED: Vote counts don't match !!!\n";
        echo "The poll_options.votes field is not synchronized with poll_votes table\n";
        
        // Fix the vote counts
        echo "\n=== FIXING VOTE COUNTS ===\n";
        $update_query = "UPDATE poll_options po 
                         SET votes = (
                             SELECT COUNT(*) 
                             FROM poll_votes pv 
                             WHERE pv.option_id = po.id
                         ) 
                         WHERE po.poll_id = " . $poll['id'];
        
        if (mysqli_query($conn, $update_query)) {
            echo "Vote counts have been synchronized!\n";
        } else {
            echo "Error updating vote counts: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "\n Vote counts are synchronized correctly.\n";
    }
    
} else {
    echo "No active polls found.\n";
}

echo "\n=== TESTING POLL DISPLAY LOGIC ===\n";
// Test the same query used in index.php
$active_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT p.*, po.option_text, po.votes, po.id as option_id 
     FROM polls p 
     LEFT JOIN poll_options po ON p.id = po.poll_id 
     WHERE p.status = 'active' AND (p.ends_at IS NULL OR p.ends_at > NOW()) 
     ORDER BY p.id DESC, po.id ASC LIMIT 1"
));

if ($active_poll && $active_poll['option_id']) {
    echo "Poll query works correctly\n";
    echo "Sample option: " . $active_poll['option_text'] . " - Votes: " . $active_poll['votes'] . "\n";
} else {
    echo "Poll query issue detected\n";
}

?>

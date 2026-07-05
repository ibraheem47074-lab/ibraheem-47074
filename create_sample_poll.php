<?php
require_once 'config/database.php';

// Check if there's already an active poll
$existing_poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT id FROM polls WHERE status = 'active' LIMIT 1"
));

if ($existing_poll) {
    echo "Active poll already exists. Poll ID: " . $existing_poll['id'];
} else {
    // Create a sample poll
    $insert_poll = "INSERT INTO polls (question, description, status, created_at) 
                    VALUES ('What is your favorite news category?', 
                           'Help us understand your preferences better', 
                           'active', 
                           NOW())";
    
    if (mysqli_query($conn, $insert_poll)) {
        $poll_id = mysqli_insert_id($conn);
        echo "Created poll with ID: " . $poll_id . "\n";
        
        // Insert poll options
        $options = [
            'Politics',
            'Sports', 
            'Technology',
            'Business',
            'Entertainment'
        ];
        
        foreach ($options as $index => $option_text) {
            $insert_option = "INSERT INTO poll_options (poll_id, option_text, votes, order_position) 
                             VALUES ($poll_id, '$option_text', 0, " . ($index + 1) . ")";
            mysqli_query($conn, $insert_option);
        }
        
        echo "Created 5 poll options\n";
        echo "Sample poll created successfully!\n";
    } else {
        echo "Error creating poll: " . mysqli_error($conn);
    }
}

// Show current poll data
echo "\n=== Current Poll Data ===\n";
$poll = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM polls WHERE status = 'active' ORDER BY id DESC LIMIT 1"
));

if ($poll) {
    echo "Poll ID: " . $poll['id'] . "\n";
    echo "Question: " . $poll['question'] . "\n";
    
    $options = mysqli_query($conn, "SELECT * FROM poll_options WHERE poll_id = " . $poll['id'] . " ORDER BY order_position");
    while ($option = mysqli_fetch_assoc($options)) {
        echo "Option: " . $option['option_text'] . " - Votes: " . $option['votes'] . "\n";
    }
}
?>

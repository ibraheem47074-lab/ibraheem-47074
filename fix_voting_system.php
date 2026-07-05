<?php
/**
 * Fix Voting System - Diagnose and Repair Vote Submission Issues
 */

require_once 'config/database.php';

echo "<h1>Voting System Diagnostic & Fix</h1>";

// Check if polls tables exist and create them if needed
function ensure_poll_tables($conn) {
    $tables_created = [];
    
    // Check polls table
    $polls_check = mysqli_query($conn, "SHOW TABLES LIKE 'polls'");
    if (mysqli_num_rows($polls_check) == 0) {
        $create_polls = "CREATE TABLE `polls` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `question` varchar(500) NOT NULL,
            `description` text DEFAULT NULL,
            `status` enum('active','inactive','closed') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `ends_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $create_polls)) {
            $tables_created[] = 'polls';
        }
    }
    
    // Check poll_options table
    $options_check = mysqli_query($conn, "SHOW TABLES LIKE 'poll_options'");
    if (mysqli_num_rows($options_check) == 0) {
        $create_options = "CREATE TABLE `poll_options` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `poll_id` int(11) NOT NULL,
            `option_text` varchar(255) NOT NULL,
            `votes` int(11) DEFAULT 0,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $create_options)) {
            $tables_created[] = 'poll_options';
        }
    }
    
    // Check poll_votes table
    $votes_check = mysqli_query($conn, "SHOW TABLES LIKE 'poll_votes'");
    if (mysqli_num_rows($votes_check) == 0) {
        $create_votes = "CREATE TABLE `poll_votes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `poll_id` int(11) NOT NULL,
            `option_id` int(11) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`option_id`) REFERENCES `poll_options`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            UNIQUE KEY `unique_poll_vote` (`poll_id`, COALESCE(`user_id`, 0), `ip_address`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if (mysqli_query($conn, $create_votes)) {
            $tables_created[] = 'poll_votes';
        }
    }
    
    return $tables_created;
}

// Check voting system status
echo "<h2>System Status Check</h2>";

// Check tables
$tables_status = [];
$tables = ['polls', 'poll_options', 'poll_votes'];

foreach ($tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    $exists = mysqli_num_rows($check) > 0;
    $tables_status[$table] = $exists;
    
    echo "<p><strong>$table table:</strong> " . ($exists ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";
}

// Create missing tables
if (!empty(array_filter($tables_status, function($status) { return !$status; }))) {
    echo "<h3>Creating Missing Tables</h3>";
    $created = ensure_poll_tables($conn);
    
    foreach ($created as $table) {
        echo "<p style='color: green;'>â Created table: $table</p>";
    }
}

// Check API file
$api_file = __DIR__ . '/api/vote-poll.php';
echo "<p><strong>API endpoint (api/vote-poll.php):</strong> " . (file_exists($api_file) ? "<span style='color: green;'>â Exists</span>" : "<span style='color: red;'>â Missing</span>") . "</p>";

// Check for active polls
$polls_query = "SELECT COUNT(*) as count FROM polls WHERE status = 'active'";
$polls_result = mysqli_query($conn, $polls_query);
$active_polls = mysqli_fetch_assoc($polls_result)['count'];
echo "<p><strong>Active polls:</strong> $active_polls</p>";

// Check for poll options
$options_query = "SELECT COUNT(*) as count FROM poll_options";
$options_result = mysqli_query($conn, $options_query);
$total_options = mysqli_fetch_assoc($options_result)['count'];
echo "<p><strong>Total poll options:</strong> $total_options</p>";

// Check for votes
$votes_query = "SELECT COUNT(*) as count FROM poll_votes";
$votes_result = mysqli_query($conn, $votes_query);
$total_votes = mysqli_fetch_assoc($votes_result)['count'];
echo "<p><strong>Total votes recorded:</strong> $total_votes</p>";

// Create sample poll if none exist
if ($active_polls == 0 && isset($_POST['create_sample'])) {
    echo "<h3>Creating Sample Poll</h3>";
    
    // Insert sample poll
    $poll_insert = "INSERT INTO polls (question, description, status) VALUES (?, ?, 'active')";
    $stmt = mysqli_prepare($conn, $poll_insert);
    mysqli_stmt_bind_param($stmt, "ss", $question, $description);
    
    $question = "What is your favorite news category?";
    $description = "Help us understand your preferences better";
    
    if (mysqli_stmt_execute($stmt)) {
        $poll_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>â Created sample poll ID: $poll_id</p>";
        
        // Add sample options
        $options = ['Politics', 'Sports', 'Technology', 'Entertainment', 'Business'];
        foreach ($options as $option_text) {
            $option_insert = "INSERT INTO poll_options (poll_id, option_text, votes) VALUES (?, ?, 0)";
            $stmt = mysqli_prepare($conn, $option_insert);
            mysqli_stmt_bind_param($stmt, "is", $poll_id, $option_text);
            mysqli_stmt_execute($stmt);
        }
        
        echo "<p style='color: green;'>â Added 5 sample options</p>";
    }
}

// Test vote submission
if (isset($_POST['test_vote'])) {
    echo "<h3>Testing Vote Submission</h3>";
    
    // Get a sample poll
    $sample_poll = mysqli_query($conn, "SELECT id FROM polls WHERE status = 'active' LIMIT 1");
    if (mysqli_num_rows($sample_poll) > 0) {
        $poll_id = mysqli_fetch_assoc($sample_poll)['id'];
        
        // Get first option
        $sample_option = mysqli_query($conn, "SELECT id FROM poll_options WHERE poll_id = $poll_id LIMIT 1");
        if (mysqli_num_rows($sample_option) > 0) {
            $option_id = mysqli_fetch_assoc($sample_option)['id'];
            
            // Test API call
            $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/vote-poll.php';
            $post_data = [
                'poll_id' => $poll_id,
                'poll_option' => $option_id
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "<p><strong>API Response:</strong></p>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
            echo "HTTP Status: $http_code\n";
            echo "Response: " . htmlspecialchars($response);
            echo "</pre>";
            
            // Parse response
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                echo "<p style='color: green;'>â Vote submission test successful!</p>";
            } else {
                echo "<p style='color: red;'>â Vote submission test failed: " . ($result['message'] ?? 'Unknown error') . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>â No poll options found for testing</p>";
        }
    } else {
        echo "<p style='color: orange;'>â No active polls found for testing</p>";
    }
}

// Show current polls
echo "<h2>Current Polls</h2>";

$polls_list = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM poll_options WHERE poll_id = p.id) as options_count,
           (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.id) as votes_count
    FROM polls p 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

if (mysqli_num_rows($polls_list) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Question</th><th>Status</th><th>Options</th><th>Votes</th></tr>";
    
    while ($poll = mysqli_fetch_assoc($polls_list)) {
        echo "<tr>";
        echo "<td>" . $poll['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($poll['question'], 0, 50)) . "...</td>";
        echo "<td><span style='color: " . ($poll['status'] == 'active' ? 'green' : 'orange') . ";'>" . $poll['status'] . "</span></td>";
        echo "<td>" . $poll['options_count'] . "</td>";
        echo "<td>" . $poll['votes_count'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No polls found</p>";
}

// Action buttons
echo "<div style='margin-top: 30px;'>";
echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='create_sample' value='Create Sample Poll' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";

echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
echo "<input type='submit' name='test_vote' value='Test Vote Submission' style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";
echo "</div>";

// Troubleshooting tips
echo "<h2>Troubleshooting Tips</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
echo "<h3>Common Issues & Solutions:</h3>";
echo "<ul>";
echo "<li><strong>'Error submitting vote' - API endpoint missing:</strong> Fixed - created api/vote-poll.php</li>";
echo "<li><strong>Database tables missing:</strong> Fixed - auto-creates missing tables</li>";
echo "<li><strong>No active polls:</strong> Use 'Create Sample Poll' button</li>";
echo "<li><strong>JavaScript errors:</strong> Check browser console for network errors</li>";
echo "<li><strong>Permission issues:</strong> Ensure api/ folder has proper permissions</li>";
echo "</ul>";
echo "</div>";

echo "<p><small>This diagnostic tool helps identify and fix common voting system issues.</small></p>";
?>

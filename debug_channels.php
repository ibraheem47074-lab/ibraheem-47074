<?php
require_once 'config/database.php';

echo "<h2>Channel Database Debug</h2>";

// Check if tables exist
$tables = ['channels', 'live_chat', 'channel_schedule'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        
        // Show table structure and data
        if ($table === 'channels') {
            $result = mysqli_query($conn, "SELECT * FROM channels");
            $count = mysqli_num_rows($result);
            echo "<p>Channels in database: $count</p>";
            
            if ($count > 0) {
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Status</th><th>Viewers</th><th>Language</th><th>Stream URL</th></tr>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . $row['category'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['viewer_count'] . "</td>";
                    echo "<td>" . $row['language'] . "</td>";
                    echo "<td>" . htmlspecialchars(substr($row['stream_url'], 0, 50)) . "...</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
    }
}

// Test current channel logic
echo "<h3>Current Channel Logic Test</h3>";

// Get current active channel (first live or featured)
$current_channel = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM channels WHERE status = 'live' ORDER BY is_featured DESC, sort_order ASC LIMIT 1"
));

if (!$current_channel) {
    $current_channel = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM channels WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 1"
    ));
}

if ($current_channel) {
    echo "<p style='color: green;'>✓ Current channel found: " . htmlspecialchars($current_channel['name']) . "</p>";
    echo "<p>Description: " . htmlspecialchars($current_channel['description']) . "</p>";
    echo "<p>Category: " . $current_channel['category'] . "</p>";
    echo "<p>Status: " . $current_channel['status'] . "</p>";
    echo "<p>Language: " . $current_channel['language'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ No current channel found</p>";
}

echo "<hr>";
echo "<p><a href='live.php'>Go to Live TV</a> | <a href='test_db.php'>Run Setup Again</a></p>";
?>

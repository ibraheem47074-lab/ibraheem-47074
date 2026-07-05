<?php
require_once 'config/database.php';

echo "Checking channels table structure...\n";

$result = mysqli_query($conn, 'DESCRIBE channels');
if ($result) {
    echo "Channels table columns:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo "Channels table does not exist or error: " . mysqli_error($conn) . "\n";
}

echo "\nChecking if channels table exists...\n";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'channels'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    echo "Channels table exists\n";
    
    echo "\nFetching all channels:\n";
    $channels = mysqli_query($conn, 'SELECT id, name, stream_url, stream_type, status, category, is_featured FROM channels ORDER BY name');
    if ($channels && mysqli_num_rows($channels) > 0) {
        echo "Found " . mysqli_num_rows($channels) . " channels:\n\n";
        while ($channel = mysqli_fetch_assoc($channels)) {
            echo "ID: " . $channel['id'] . "\n";
            echo "Name: " . $channel['name'] . "\n";
            echo "Stream Type: " . $channel['stream_type'] . "\n";
            echo "Stream URL: " . $channel['stream_url'] . "\n";
            echo "Status: " . $channel['status'] . "\n";
            echo "Category: " . $channel['category'] . "\n";
            echo "Featured: " . ($channel['is_featured'] ? 'Yes' : 'No') . "\n";
            echo "----------------------------------------\n";
        }
    } else {
        echo "No channels found in the database.\n";
    }
} else {
    echo "Channels table does not exist\n";
}
?>

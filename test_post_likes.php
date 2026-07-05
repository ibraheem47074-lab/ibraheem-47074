<?php
require_once 'config/database.php';

echo "<h2>Post Likes Table Check</h2>";

// Check if table exists
$result = mysqli_query($conn, 'SHOW TABLES LIKE "post_likes"');
echo 'Table exists: ' . (mysqli_num_rows($result) > 0 ? 'YES' : 'NO') . '<br>';

if(mysqli_num_rows($result) > 0) {
    // Get record count
    $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM post_likes');
    $row = mysqli_fetch_assoc($count);
    echo 'Total records: ' . $row['count'] . '<br>';
    
    // Show table structure
    echo '<h3>Table Structure:</h3>';
    $structure = mysqli_query($conn, 'DESCRIBE post_likes');
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
    while($row = mysqli_fetch_assoc($structure)) {
        echo '<tr>';
        echo '<td>' . $row['Field'] . '</td>';
        echo '<td>' . $row['Type'] . '</td>';
        echo '<td>' . $row['Null'] . '</td>';
        echo '<td>' . $row['Key'] . '</td>';
        echo '<td>' . $row['Default'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Test the query from index.php
    echo '<h3>Test Query from index.php:</h3>';
    $test_query = "SELECT n.*, (SELECT COUNT(*) FROM post_likes WHERE news_id = n.id) as likes_count FROM news n LIMIT 5";
    $test_result = mysqli_query($conn, $test_query);
    
    if($test_result) {
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>News ID</th><th>Title</th><th>Likes Count</th></tr>';
        while($row = mysqli_fetch_assoc($test_result)) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . substr($row['title'] ?? 'No title', 0, 40) . '...</td>';
            echo '<td>' . $row['likes_count'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo 'Error in test query: ' . mysqli_error($conn);
    }
    
    // Show some sample data from post_likes table
    echo '<h3>Sample post_likes data:</h3>';
    $sample_data = mysqli_query($conn, 'SELECT * FROM post_likes LIMIT 10');
    if(mysqli_num_rows($sample_data) > 0) {
        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>ID</th><th>News ID</th><th>User ID</th><th>IP Address</th><th>Created At</th></tr>';
        while($row = mysqli_fetch_assoc($sample_data)) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['news_id'] . '</td>';
            echo '<td>' . $row['user_id'] . '</td>';
            echo '<td>' . $row['ip_address'] . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo 'No data in post_likes table';
    }
} else {
    echo '<p style="color:red">post_likes table does not exist!</p>';
}

mysqli_close($conn);
?>

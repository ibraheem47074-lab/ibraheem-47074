<?php
require_once 'config/database.php';

echo "<h2>Channels Table Structure</h2>";

$result = mysqli_query($conn, 'DESCRIBE channels');
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Sample Data</h3>";
    $channels = mysqli_query($conn, "SELECT * FROM channels LIMIT 5");
    if ($channels && mysqli_num_rows($channels) > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Category</th><th>Stream Type</th><th>Status</th><th>Stream URL</th></tr>";
        while ($channel = mysqli_fetch_assoc($channels)) {
            echo "<tr>";
            echo "<td>" . $channel['id'] . "</td>";
            echo "<td>" . htmlspecialchars($channel['name']) . "</td>";
            echo "<td>" . $channel['category'] . "</td>";
            echo "<td>" . $channel['stream_type'] . "</td>";
            echo "<td>" . $channel['status'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($channel['stream_url'], 0, 50)) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No channels found in database.</p>";
    }
} else {
    echo "<p style='color: red;'>Channels table does not exist or error: " . mysqli_error($conn) . "</p>";
}
?>

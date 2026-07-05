<?php
require_once 'config/database.php';

// Check if live_stream table exists and show its structure
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'live_stream'");
if (mysqli_num_rows($table_check) > 0) {
    echo "Live Stream table exists.<br>";
    
    // Show table structure
    $result = mysqli_query($conn, "DESCRIBE live_stream");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $result = mysqli_query($conn, "SELECT * FROM live_stream LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        echo "<h3>Sample Data:</h3>";
        $row = mysqli_fetch_assoc($result);
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "Live Stream table does not exist.";
}
?>

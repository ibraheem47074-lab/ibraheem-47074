<?php
/**
 * Check users table structure
 */
require_once __DIR__ . '/config/database.php';

echo "<h2>Users Table Structure</h2>";

// Get table structure
$result = mysqli_query($conn, "DESCRIBE users");

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show sample data
    echo "<h3>Sample Data</h3>";
    $sampleResult = mysqli_query($conn, "SELECT * FROM users LIMIT 3");
    if ($sampleResult && mysqli_num_rows($sampleResult) > 0) {
        echo "<table border='1' cellpadding='5'>";
        
        // Header
        $fields = mysqli_fetch_fields($sampleResult);
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        // Data
        mysqli_data_seek($sampleResult, 0);
        while ($row = mysqli_fetch_assoc($sampleResult)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No users found in table.</p>";
    }
    
} else {
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
}
?>

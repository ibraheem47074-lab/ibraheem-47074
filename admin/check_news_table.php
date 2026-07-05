<?php
require_once '../config/database.php';

echo "<h1>Check News Table Structure</h1>";

// Check table structure
$structure_query = "DESCRIBE news";
$structure_result = mysqli_query($conn, $structure_query);

if ($structure_result) {
    echo "<h2>News Table Columns:</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Could not get table structure.</p>";
}

echo "<h2>Sample Data:</h2>";
$sample_query = "SELECT * FROM news ORDER BY id DESC LIMIT 3";
$sample_result = mysqli_query($conn, $sample_query);

if ($sample_result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    $first_row = true;
    
    while ($row = mysqli_fetch_assoc($sample_result)) {
        if ($first_row) {
            echo "<tr style='background: #f0f0f0;'>";
            foreach (array_keys($row) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='add-news.php'>← Back to Add News</a></p>";
?>

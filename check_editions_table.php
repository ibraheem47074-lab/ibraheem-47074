<?php
require_once 'config/database.php';

echo "<h2>News Editions Table Structure</h2>";

// Check if table exists first
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'news_editions'");
if (mysqli_num_rows($table_check) === 0) {
    echo "<p style='color: red;'>Table 'news_editions' does not exist!</p>";
    
    // Look for similar tables
    echo "<h3>Looking for similar tables:</h3>";
    $tables_query = "SHOW TABLES";
    $result = mysqli_query($conn, $tables_query);
    echo "<ul>";
    while ($row = mysqli_fetch_array($result)) {
        if (strpos(strtolower($row[0]), 'edition') !== false) {
            echo "<li><strong>" . $row[0] . "</strong></li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: green;'>Table 'news_editions' exists!</p>";
    
    // Get actual table structure
    echo "<h3>Actual table structure:</h3>";
    $structure_query = "DESCRIBE news_editions";
    $result = mysqli_query($conn, $structure_query);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check existing data
    echo "<h3>Sample data from table:</h3>";
    $data_query = "SELECT * FROM news_editions LIMIT 3";
    $data_result = mysqli_query($conn, $data_query);
    if (mysqli_num_rows($data_result) > 0) {
        // Get column names
        $fields = mysqli_fetch_fields($data_result);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Reset result pointer and display data
        mysqli_data_seek($data_result, 0);
        while ($row = mysqli_fetch_assoc($data_result)) {
            echo "<tr>";
            foreach ($fields as $field) {
                $value = $row[$field->name];
                if ($field->name === 'additional_images' && !empty($value)) {
                    $images = json_decode($value, true);
                    echo "<td>" . (is_array($images) ? count($images) . " images" : 'No images') . "</td>";
                } else {
                    echo "<td>" . htmlspecialchars(substr($value ?? '', 0, 50)) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in news_editions table.</p>";
    }
}

// Check for SQL files that might have the correct structure
echo "<h3>SQL files that might contain table structure:</h3>";
$sql_files = glob('*.sql');
foreach ($sql_files as $file) {
    if (strpos($file, 'edition') !== false) {
        echo "<p><strong>$file</strong></p>";
        $content = file_get_contents($file);
        if (strpos($content, 'news_editions') !== false) {
            echo "<p style='color: green;'>Contains news_editions table definition</p>";
        }
    }
}
?>

<?php
require_once '../config/database.php';

echo "Debugging news table structure:<br><br>";

// Get table structure
$result = mysqli_query($conn, "DESCRIBE news");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Key'] . "</td></tr>";
}
echo "</table>";

echo "<br><br>Testing simple insert:<br>";

// Test a simple insert with minimal columns
$test_query = "INSERT INTO news (title, slug, content, status, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $test_query);
if ($stmt) {
    echo "Simple prepare successful<br>";
    
    $test_title = "Test Article";
    $test_slug = "test-article-" . time();
    $test_content = "This is a test content.";
    $test_status = "draft";
    
    mysqli_stmt_bind_param($stmt, 'ssss', $test_title, $test_slug, $test_content, $test_status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Simple insert successful<br>";
        $insert_id = mysqli_insert_id($conn);
        echo "Inserted ID: " . $insert_id . "<br>";
        
        // Clean up
        mysqli_query($conn, "DELETE FROM news WHERE id = $insert_id");
        echo "Test record cleaned up<br>";
    } else {
        echo "Simple insert failed: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Simple prepare failed: " . mysqli_error($conn) . "<br>";
}
?>

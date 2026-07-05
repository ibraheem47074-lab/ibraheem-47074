<?php
require_once '../config/database.php';

echo "<h1>Test Bind Parameter Fix</h1>";

// Read the current add-news.php file to check the actual content
$file_path = 'add-news.php';
$file_content = file_get_contents($file_path);

if ($file_content) {
    echo "<h2>Checking Line 415 in add-news.php:</h2>";
    
    // Find line 415
    $lines = explode("\n", $file_content);
    $line_415 = isset($lines[414]) ? $lines[414] : ''; // 0-indexed array
    
    echo "<p><strong>Line 415 content:</strong></p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($line_415);
    echo "</pre>";
    
    // Check if it contains the wrong type string
    if (strpos($line_415, "'sssssisissisdss'") !== false) {
        echo "<p style='color: red;'><strong>❌ PROBLEM FOUND:</strong> Line 415 still contains the old 13-character type string 'sssssisissisdss'</p>";
        echo "<p style='color: orange;'><strong>SOLUTION:</strong> The file needs to be manually edited or the server cache cleared.</p>";
    } elseif (strpos($line_415, "'sssssisissisdss'") !== false) {
        echo "<p style='color: green;'><strong>✅ GOOD:</strong> Line 415 contains the correct 13-character type string 'sssssisissisdss'</p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ UNCLEAR:</strong> Could not determine the type string on line 415</p>";
    }
    
    echo "<h2>Manual Fix Instructions:</h2>";
    echo "<ol>";
    echo "<li><strong>Option 1:</strong> Clear browser cache and reload the page</li>";
    echo "<li><strong>Option 2:</strong> Restart the web server</li>";
    echo "<li><strong>Option 3:</strong> Manually edit line 415 in add-news.php</li>";
    echo "<li><strong>Option 4:</strong> Replace 'sssssisissisdss' with 'sssssisissisdss' (13 characters)</li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red;'>Could not read the add-news.php file.</p>";
}

echo "<p><a href='add-news.php'>← Test Add News Form</a></p>";
echo "<p><a href='../index.php'>→ View Index Page</a></p>";
?>

<?php
require_once 'config/database.php';

// Simulate a like request to test the API
session_start();

// Test data
$test_news_id = 1;

// Create test POST data like the JavaScript would send
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['news_id'] = $test_news_id;

// Include the API file to test it
echo "<h2>Testing Like API Response</h2>";

// Test with JSON input (like the JavaScript sends)
$json_data = json_encode(['news_id' => $test_news_id]);

// Simulate the API environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';

// Capture the API output
ob_start();
include 'api/toggle-like.php';
$api_response = ob_get_clean();

echo "<h3>API Response:</h3>";
echo "<pre>" . htmlspecialchars($api_response) . "</pre>";

// Parse the JSON response
$response_data = json_decode($api_response, true);
echo "<h3>Parsed Response:</h3>";
echo "<pre>";
print_r($response_data);
echo "</pre>";

// Check if the response format matches what JavaScript expects
if (isset($response_data['success']) && $response_data['success']) {
    echo "✅ API returned success<br>";
    if (isset($response_data['likes_count'])) {
        echo "✅ likes_count field present: " . $response_data['likes_count'] . "<br>";
    } else {
        echo "❌ likes_count field missing<br>";
    }
    if (isset($response_data['action'])) {
        echo "✅ action field present: " . $response_data['action'] . "<br>";
    } else {
        echo "❌ action field missing<br>";
    }
} else {
    echo "❌ API returned error<br>";
}

// Test the database directly
echo "<h3>Direct Database Test:</h3>";
$count_query = "SELECT COUNT(*) as count FROM post_likes WHERE news_id = ?";
$stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($stmt, 'i', $test_news_id);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$count = mysqli_fetch_assoc($count_result)['count'];
echo "Actual likes in database for news ID $test_news_id: $count<br>";

mysqli_close($conn);
?>

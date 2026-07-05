<?php
require_once 'config/database.php';

// Test the news_interactions.php API directly
echo "Testing news_interactions.php API...\n";

// Test with a simple request
$data = [
    'action' => 'get_stats',
    'news_id' => 1
];

// Build POST data
$post_data = http_build_query($data);

// Create context
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $post_data
    ]
]);

// Make request
$url = 'http://localhost/PK-LIVE%20NEWS/api/news_interactions.php';
$response = file_get_contents($url, false, $context);

echo "Response: " . $response . "\n";
echo "Response length: " . strlen($response) . "\n";

// Check if it's valid JSON
$json_data = json_decode($response, true);
if ($json_data === null) {
    echo "Invalid JSON response!\n";
    echo "JSON error: " . json_last_error_msg() . "\n";
} else {
    echo "Valid JSON response!\n";
    print_r($json_data);
}
?>

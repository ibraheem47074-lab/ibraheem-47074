<?php
// Test the comment API directly
require_once 'config/database.php';

echo "Testing Comment API\n";
echo "===================\n\n";

// Get a sample news article
$news_query = "SELECT id, title FROM news WHERE status = 'published' LIMIT 1";
$news_result = mysqli_query($conn, $news_query);

if (mysqli_num_rows($news_result) > 0) {
    $news = mysqli_fetch_assoc($news_result);
    echo "Found news article: {$news['title']} (ID: {$news['id']})\n\n";
    
    // Test API call
    $api_url = "http://localhost/pk-live-news/api/submit-comment.php";
    
    $test_data = [
        'news_id' => $news['id'],
        'name' => 'Test User',
        'email' => 'test@example.com',
        'comment' => 'This is a test comment from API test script.'
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($test_data),
            'timeout' => 10
        ]
    ];
    
    $context  = stream_context_create($options);
    $response = file_get_contents($api_url, false, $context);
    
    if ($response) {
        $result = json_decode($response, true);
        echo "API Response:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
        
        if ($result && isset($result['success']) && $result['success']) {
            echo "✅ API test successful!\n";
        } else {
            echo "❌ API test failed!\n";
        }
    } else {
        echo "❌ No response from API (web server may not be running)\n";
    }
} else {
    echo "❌ No published news articles found\n";
}

echo "\nDone!\n";
?>

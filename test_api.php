<?php
// Test the news performance API
echo "Testing news performance API...\n";

// Call the API
$api_url = 'http://localhost/pk-live-news/api/news-performance.php';
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

$response = file_get_contents($api_url, false, $context);

if ($response === false) {
    echo "Failed to call API\n";
    echo "Check if web server is running and accessible\n";
} else {
    echo "API Response:\n";
    echo $response . "\n";
    
    // Try to decode JSON
    $data = json_decode($response, true);
    if ($data !== null) {
        echo "\nJSON decoded successfully!\n";
        if (isset($data['success']) && $data['success']) {
            echo "API returned success: TRUE\n";
            if (isset($data['data']['total_stats'])) {
                $stats = $data['data']['total_stats'];
                echo "Total sources: " . $stats['total_sources'] . "\n";
                echo "Total articles: " . $stats['total_articles'] . "\n";
                echo "Total views: " . $stats['total_views'] . "\n";
            }
        } else {
            echo "API returned error: " . ($data['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Failed to decode JSON response\n";
    }
}
?>

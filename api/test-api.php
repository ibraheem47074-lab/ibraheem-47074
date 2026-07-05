<?php
// Simple API test
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $response = [
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => date('Y-m-d H:i:s'),
        'test_data' => [
            'total_sources' => 11,
            'active_sources' => 11,
            'total_articles' => 1321,
            'total_views' => 376890
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

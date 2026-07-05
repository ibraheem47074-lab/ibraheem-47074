<?php
// Simple test for the like API
header('Content-Type: application/json');

// Test the API directly
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/PK-LIVE%20NEWS/api/toggle_like.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'news_id=38');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

echo json_encode([
    'headers' => $headers,
    'body' => $body,
    'parsed_json' => json_decode($body, true)
]);
?>

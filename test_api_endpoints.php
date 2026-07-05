<?php
// Simple test script to verify API endpoints are working
header('Content-Type: text/plain');

echo "Testing API Endpoints...\n\n";

// Test breaking news API
echo "1. Testing breaking-news.php:\n";
$ch = curl_init('http://localhost/PK-LIVE%20NEWS/api/breaking-news.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Content-Type: " . (preg_match('/Content-Type: ([^\r\n]+)/', $headers, $matches) ? $matches[1] : 'Not found') . "\n";
echo "Response body: " . substr($body, 0, 200) . "...\n\n";

// Test toggle like API
echo "2. Testing toggle_like.php:\n";
$ch = curl_init('http://localhost/PK-LIVE%20NEWS/api/toggle_like.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'news_id=37');
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Content-Type: " . (preg_match('/Content-Type: ([^\r\n]+)/', $headers, $matches) ? $matches[1] : 'Not found') . "\n";
echo "Response body: " . substr($body, 0, 200) . "...\n\n";

// Test get comments API
echo "3. Testing get-comments.php:\n";
$ch = curl_init('http://localhost/PK-LIVE%20NEWS/api/get-comments.php?news_id=37');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Content-Type: " . (preg_match('/Content-Type: ([^\r\n]+)/', $headers, $matches) ? $matches[1] : 'Not found') . "\n";
echo "Response body: " . substr($body, 0, 200) . "...\n\n";

echo "Test completed.\n";
?>

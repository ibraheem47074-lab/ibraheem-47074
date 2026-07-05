<?php
// Web-based Connectivity Test
header('Content-Type: text/plain');

echo "=== WEB CONNECTIVITY TEST ===\n\n";

// Test basic connectivity
echo "Testing basic connectivity...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://httpbin.org/ip',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT => 'PK-LIVE-NEWS-Test/1.0',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$start_time = microtime(true);
$result = curl_exec($ch);
$end_time = microtime(true);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    echo "Error Code: " . curl_errno($ch) . "\n";
} else {
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $time_ms = round(($end_time - $start_time) * 1000, 2);
    echo "SUCCESS: HTTP $http_code ($time_ms ms)\n";
    echo "Response: " . substr($result, 0, 200) . "...\n";
}
curl_close($ch);

echo "\n=== TEST COMPLETE ===\n";
?>

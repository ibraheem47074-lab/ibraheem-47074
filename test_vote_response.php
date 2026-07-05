<?php
// Test what vote_poll.php is actually outputting
$url = 'http://localhost/PK-LIVE%20NEWS/vote_poll.php';
$data = [
    'poll_id' => 1,
    'poll_option' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

echo "=== HEADERS ===\n";
echo $headers . "\n\n";

echo "=== BODY ===\n";
echo $body . "\n\n";

echo "=== ANALYSIS ===\n";
if (strpos($body, '<') === 0) {
    echo "❌ Response starts with HTML tag, not JSON\n";
    echo "This indicates a PHP error or warning is being output\n";
} elseif (json_decode($body) !== null) {
    echo "✅ Valid JSON response\n";
} else {
    echo "❌ Invalid JSON response\n";
}

curl_close($ch);
?>

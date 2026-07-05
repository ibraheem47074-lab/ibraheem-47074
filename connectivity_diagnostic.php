<?php
// Comprehensive Server Connectivity Diagnostic Tool
header('Content-Type: text/plain');

echo "=== SERVER CONNECTIVITY DIAGNOSTIC ===\n\n";

// 1. PHP Environment Check
echo "1. PHP ENVIRONMENT CHECK\n";
echo "PHP Version: " . PHP_VERSION . "\n";

$required_extensions = ['curl', 'openssl', 'mbstring', 'json', 'simplexml'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? 'YES' : 'NO';
    echo "$ext extension: $status\n";
}

// 2. PHP Configuration Check
echo "\n2. PHP CONFIGURATION CHECK\n";
$ini_settings = [
    'default_socket_timeout' => ini_get('default_socket_timeout'),
    'max_execution_time' => ini_get('max_execution_time'),
    'allow_url_fopen' => ini_get('allow_url_fopen'),
    'curl.cainfo' => ini_get('curl.cainfo'),
    'openssl.cafile' => ini_get('openssl.cafile'),
    'openssl.capath' => ini_get('openssl.capath')
];

foreach ($ini_settings as $setting => $value) {
    echo "$setting: $value\n";
}

// 3. Basic Network Tests
echo "\n3. BASIC NETWORK TESTS\n";

// Test with file_get_contents
echo "Testing file_get_contents()...\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'PK-LIVE-NEWS-Diagnostic/1.0'
    ]
]);

$start_time = microtime(true);
try {
    $response = file_get_contents('http://httpbin.org/ip', false, $context);
    $end_time = microtime(true);
    echo "file_get_contents() SUCCESS: " . round(($end_time - $start_time) * 1000, 2) . "ms\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
} catch (Exception $e) {
    echo "file_get_contents() FAILED: " . $e->getMessage() . "\n";
}

// Test with cURL
echo "\nTesting cURL...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://httpbin.org/ip',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_USERAGENT => 'PK-LIVE-NEWS-Diagnostic/1.0',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3
]);

$start_time = microtime(true);
$result = curl_exec($ch);
$end_time = microtime(true);

if (curl_errno($ch)) {
    echo "cURL FAILED: " . curl_error($ch) . "\n";
    echo "cURL Error Code: " . curl_errno($ch) . "\n";
} else {
    echo "cURL SUCCESS: " . round(($end_time - $start_time) * 1000, 2) . "ms\n";
    echo "HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    echo "Response: " . substr($result, 0, 200) . "...\n";
}
curl_close($ch);

// 4. DNS Resolution Test
echo "\n4. DNS RESOLUTION TEST\n";
$hosts = ['google.com', 'httpbin.org', 'example.com'];
foreach ($hosts as $host) {
    $start_time = microtime(true);
    $ip = gethostbyname($host);
    $end_time = microtime(true);
    $time_ms = round(($end_time - $start_time) * 1000, 2);
    
    if ($ip === $host) {
        echo "$host: DNS FAILED ($time_ms ms)\n";
    } else {
        echo "$host: $ip ($time_ms ms)\n";
    }
}

// 5. Port Connectivity Test
echo "\n5. PORT CONNECTIVITY TEST\n";
$tests = [
    ['google.com', 80, 'HTTP'],
    ['google.com', 443, 'HTTPS'],
    ['httpbin.org', 80, 'HTTP'],
    ['httpbin.org', 443, 'HTTPS']
];

foreach ($tests as $test) {
    list($host, $port, $service) = $test;
    $timeout = 5;
    
    $start_time = microtime(true);
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $end_time = microtime(true);
    
    if ($socket) {
        fclose($socket);
        $time_ms = round(($end_time - $start_time) * 1000, 2);
        echo "$host:$port ($service): CONNECTED ($time_ms ms)\n";
    } else {
        echo "$host:$port ($service): FAILED - $errstr ($time_ms ms)\n";
    }
}

// 6. SSL Certificate Test
echo "\n6. SSL CERTIFICATE TEST\n";
$ssl_hosts = ['google.com', 'httpbin.org'];

foreach ($ssl_hosts as $host) {
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'capture_peer_cert' => true
        ]
    ]);
    
    $start_time = microtime(true);
    $socket = stream_socket_client(
        "ssl://$host:443",
        $errno,
        $errstr,
        10,
        STREAM_CLIENT_CONNECT,
        $context
    );
    $end_time = microtime(true);
    
    if ($socket) {
        $options = stream_context_get_options($context);
        $cert = $options['ssl']['peer_certificate'];
        $cert_data = openssl_x509_parse($cert);
        
        echo "$host SSL: SUCCESS\n";
        echo "  Issuer: " . $cert_data['issuer']['CN'] . "\n";
        echo "  Valid Until: " . date('Y-m-d', $cert_data['validTo_time_t']) . "\n";
        echo "  Connection Time: " . round(($end_time - $start_time) * 1000, 2) . "ms\n";
        
        fclose($socket);
    } else {
        echo "$host SSL: FAILED - $errstr\n";
    }
}

// 7. RSS Feed Test
echo "\n7. RSS FEED TEST\n";
$rss_feeds = [
    'https://feeds.bbci.co.uk/news/rss.xml',
    'https://rss.cnn.com/rss/edition.rss',
    'https://feeds.reuters.com/reuters/topNews'
];

foreach ($rss_feeds as $feed) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $feed,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'PK-LIVE-NEWS-RSS-Reader/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $start_time = microtime(true);
    $result = curl_exec($ch);
    $end_time = microtime(true);

    if (curl_errno($ch)) {
        echo "$feed: FAILED - " . curl_error($ch) . "\n";
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $time_ms = round(($end_time - $start_time) * 1000, 2);
        
        if ($http_code === 200) {
            $xml = @simplexml_load_string($result);
            if ($xml) {
                $title = (string)$xml->channel->title;
                echo "$feed: SUCCESS ($time_ms ms) - $title\n";
            } else {
                echo "$feed: SUCCESS but invalid XML ($time_ms ms)\n";
            }
        } else {
            echo "$feed: HTTP $http_code ($time_ms ms)\n";
        }
    }
    curl_close($ch);
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
?>

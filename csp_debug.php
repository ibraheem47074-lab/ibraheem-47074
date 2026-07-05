<?php
// CSP Debug Tool
header('Content-Type: text/plain');

echo "=== CSP Debug Information ===\n\n";

// Check if headers module is available
echo "1. Apache Headers Module:\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "   mod_headers: " . (in_array('mod_headers', $modules) ? 'Available' : 'NOT Available') . "\n";
    echo "   mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'Available' : 'NOT Available') . "\n";
} else {
    echo "   Cannot check Apache modules (apache_get_modules not available)\n";
}

echo "\n2. Current HTTP Headers:\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    if (stripos($name, 'content-security-policy') !== false || 
        stripos($name, 'x-content-type') !== false ||
        stripos($name, 'x-frame') !== false ||
        stripos($name, 'x-xss') !== false) {
        echo "   $name: $value\n";
    }
}

echo "\n3. Test Bootstrap CDN Access:\n";
$bootstrap_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css';
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

$bootstrap_response = @file_get_contents($bootstrap_url, false, $context);
if ($bootstrap_response !== false) {
    echo "   Bootstrap CSS: ACCESSIBLE\n";
    echo "   Size: " . strlen($bootstrap_response) . " bytes\n";
    echo "   First 100 chars: " . substr($bootstrap_response, 0, 100) . "...\n";
} else {
    echo "   Bootstrap CSS: FAILED TO ACCESS\n";
    $error = error_get_last();
    if ($error) {
        echo "   Error: " . $error['message'] . "\n";
    }
}

echo "\n4. Test Font Awesome CDN Access:\n";
$fa_url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
$fa_response = @file_get_contents($fa_url, false, $context);
if ($fa_response !== false) {
    echo "   Font Awesome CSS: ACCESSIBLE\n";
    echo "   Size: " . strlen($fa_response) . " bytes\n";
} else {
    echo "   Font Awesome CSS: FAILED TO ACCESS\n";
    $error = error_get_last();
    if ($error) {
        echo "   Error: " . $error['message'] . "\n";
    }
}

echo "\n5. .htaccess File Check:\n";
$htaccess_file = __DIR__ . '/.htaccess';
if (file_exists($htaccess_file)) {
    echo "   .htaccess: EXISTS\n";
    $htaccess_content = file_get_contents($htaccess_file);
    if (strpos($htaccess_content, 'Content-Security-Policy') !== false) {
        echo "   CSP Directive: FOUND\n";
        // Extract CSP line
        preg_match('/Content-Security-Policy\s+"([^"]+)"/', $htaccess_content, $matches);
        if (isset($matches[1])) {
            echo "   CSP Value: " . substr($matches[1], 0, 100) . "...\n";
        }
    } else {
        echo "   CSP Directive: NOT FOUND\n";
    }
} else {
    echo "   .htaccess: NOT FOUND\n";
}

echo "\n6. PHP Configuration:\n";
echo "   allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled') . "\n";
echo "   curl: " . (extension_loaded('curl') ? 'Available' : 'Not Available') . "\n";

echo "\n7. Test Local Bootstrap Alternative:\n";
$local_bootstrap = __DIR__ . '/assets/css/bootstrap.min.css';
if (file_exists($local_bootstrap)) {
    echo "   Local Bootstrap: EXISTS\n";
} else {
    echo "   Local Bootstrap: NOT FOUND\n";
}

echo "\n=== Debug Complete ===\n";
?>

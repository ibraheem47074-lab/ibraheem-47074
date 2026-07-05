<?php
// Security Configuration Check
echo "<h2>PHP Security Configuration Status</h2>";

echo "<h3>Current Settings:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Status</th></tr>";

// Check display_errors
$display_errors = ini_get('display_errors');
$display_status = ($display_errors === '0' || $display_errors === 'Off' || $display_errors === '') ? '✅ Secure' : '❌ Insecure';
echo "<tr><td>display_errors</td><td>$display_errors</td><td>$display_status</td></tr>";

// Check expose_php
$expose_php = ini_get('expose_php');
$expose_status = ($expose_php === '0' || $expose_php === 'Off' || $expose_php === '') ? '✅ Secure' : '❌ Insecure';
echo "<tr><td>expose_php</td><td>$expose_php</td><td>$expose_status</td></tr>";

// Check allow_url_fopen
$allow_url_fopen = ini_get('allow_url_fopen');
$url_fopen_status = ($allow_url_fopen === '1' || $allow_url_fopen === 'On') ? '✅ Enabled' : '⚠️ Disabled';
echo "<tr><td>allow_url_fopen</td><td>$allow_url_fopen</td><td>$url_fopen_status</td></tr>";

// Check file_uploads
$file_uploads = ini_get('file_uploads');
$uploads_status = ($file_uploads === '1' || $file_uploads === 'On') ? '✅ Enabled' : '⚠️ Disabled';
echo "<tr><td>file_uploads</td><td>$file_uploads</td><td>$uploads_status</td></tr>";

echo "</table>";

echo "<h3>Security Recommendations:</h3>";
echo "<ul>";
echo "<li><strong>display_errors</strong>: Should be 'Off' in production to prevent sensitive information leakage</li>";
echo "<li><strong>expose_php</strong>: Should be 'Off' to hide PHP version from HTTP headers</li>";
echo "<li><strong>allow_url_fopen</strong>: Enable only if your application needs to access remote files</li>";
echo "<li><strong>file_uploads</strong>: Enable only if your application accepts file uploads</li>";
echo "</ul>";

echo "<h3>Additional Security Headers Check:</h3>";
$headers = headers_list();
$security_headers = [
    'X-Frame-Options',
    'X-Content-Type-Options',
    'X-XSS-Protection',
    'Strict-Transport-Security'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Security Header</th><th>Status</th></tr>";

foreach ($security_headers as $header) {
    $found = false;
    foreach ($headers as $sent_header) {
        if (stripos($sent_header, $header) === 0) {
            $found = true;
            break;
        }
    }
    $status = $found ? '✅ Set' : '⚠️ Not Set';
    echo "<tr><td>$header</td><td>$status</td></tr>";
}

echo "</table>";

echo "<p><em>Note: You may need to restart Apache for php.ini changes to take effect.</em></p>";
?>

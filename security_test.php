<?php
// Security Configuration Test Script
// This script checks the current PHP security settings

echo "<!DOCTYPE html>
<html>
<head>
    <title>PHP Security Configuration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .secure { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .insecure { color: red; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>PHP Security Configuration Test</h1>
    <table>
        <tr>
            <th>Setting</th>
            <th>Current Value</th>
            <th>Status</th>
            <th>Recommendation</th>
        </tr>";

$security_settings = [
    'display_errors' => [
        'current' => ini_get('display_errors'),
        'secure' => '0',
        'recommended' => 'Off (production)',
        'description' => 'Controls whether errors are displayed to users'
    ],
    'expose_php' => [
        'current' => ini_get('expose_php'),
        'secure' => '0',
        'recommended' => 'Off',
        'description' => 'Hides PHP version from HTTP headers'
    ],
    'allow_url_fopen' => [
        'current' => ini_get('allow_url_fopen'),
        'secure' => '1',
        'recommended' => 'On (if needed)',
        'description' => 'Allows opening URLs like files'
    ],
    'file_uploads' => [
        'current' => ini_get('file_uploads'),
        'secure' => '1',
        'recommended' => 'On (if needed)',
        'description' => 'Allows HTTP file uploads'
    ],
    'allow_url_include' => [
        'current' => ini_get('allow_url_include'),
        'secure' => '0',
        'recommended' => 'Off',
        'description' => 'Allows including URLs as files'
    ],
    'error_reporting' => [
        'current' => ini_get('error_reporting'),
        'secure' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
        'recommended' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
        'description' => 'Controls which errors are reported'
    ]
];

foreach ($security_settings as $setting => $config) {
    $current = $config['current'];
    $secure = $config['secure'];
    $recommended = $config['recommended'];
    
    if ($current === $secure) {
        $status = '<span class="secure">SECURE</span>';
    } elseif (in_array($setting, ['allow_url_fopen', 'file_uploads']) && $current === '1') {
        $status = '<span class="secure">SECURE</span>';
    } else {
        $status = '<span class="insecure">INSECURE</span>';
    }
    
    echo "<tr>
        <td>{$setting}</td>
        <td>{$current}</td>
        <td>{$status}</td>
        <td>{$recommended}</td>
    </tr>";
}

echo "</table>
    <h2>Additional Security Information</h2>
    <p><strong>PHP Version:</strong> " . phpversion() . "</p>
    <p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>
    <p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>
    
    <h2>Security Headers Check</h2>";
    
$headers = [
    'X-Powered-By' => $_SERVER['HTTP_X_POWERED_BY'] ?? 'Not set',
    'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Not set'
];

echo "<table>
    <tr>
        <th>Header</th>
        <th>Value</th>
        <th>Status</th>
    </tr>";

foreach ($headers as $header => $value) {
    if ($header === 'X-Powered-By' && strpos($value, 'PHP') !== false) {
        $status = '<span class="warning">WARNING - PHP version exposed</span>';
    } else {
        $status = '<span class="secure">OK</span>';
    }
    
    echo "<tr>
        <td>{$header}</td>
        <td>{$value}</td>
        <td>{$status}</td>
    </tr>";
}

echo "</table>
    <p><em>Note: This script should be removed from production servers after testing.</em></p>
</body>
</html>";
?>

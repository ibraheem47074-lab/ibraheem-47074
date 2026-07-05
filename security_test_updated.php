<?php
// Updated Security Configuration Test Script
// This script checks the current PHP security settings after .htaccess fixes

echo "<!DOCTYPE html>
<html>
<head>
    <title>PHP Security Configuration Test - Updated</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .secure { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .insecure { color: red; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-box { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <h1>PHP Security Configuration Test - Updated</h1>
    
    <div class='status-box success'>
        <strong>✅ Security fixes applied to .htaccess file</strong><br>
        Added missing security settings: expose_php, allow_url_include, error_reporting
    </div>

    <table>
        <tr>
            <th>Setting</th>
            <th>Current Value</th>
            <th>Expected Value</th>
            <th>Status</th>
            <th>Source</th>
        </tr>";

$security_settings = [
    'display_errors' => [
        'current' => ini_get('display_errors'),
        'expected' => '0',
        'recommended' => 'Off (production)',
        'description' => 'Controls whether errors are displayed to users',
        'source' => 'php.ini + .htaccess'
    ],
    'expose_php' => [
        'current' => ini_get('expose_php'),
        'expected' => '0',
        'recommended' => 'Off',
        'description' => 'Hides PHP version from HTTP headers',
        'source' => 'php.ini + .htaccess'
    ],
    'allow_url_fopen' => [
        'current' => ini_get('allow_url_fopen'),
        'expected' => '1',
        'recommended' => 'On (if needed)',
        'description' => 'Allows opening URLs like files',
        'source' => 'php.ini'
    ],
    'file_uploads' => [
        'current' => ini_get('file_uploads'),
        'expected' => '1',
        'recommended' => 'On (if needed)',
        'description' => 'Allows HTTP file uploads',
        'source' => 'php.ini'
    ],
    'allow_url_include' => [
        'current' => ini_get('allow_url_include'),
        'expected' => '0',
        'recommended' => 'Off',
        'description' => 'Allows including URLs as files',
        'source' => 'php.ini + .htaccess'
    ],
    'error_reporting' => [
        'current' => ini_get('error_reporting'),
        'expected' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
        'recommended' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
        'description' => 'Controls which errors are reported',
        'source' => 'php.ini + .htaccess'
    ]
];

$secure_count = 0;
$total_count = count($security_settings);

foreach ($security_settings as $setting => $config) {
    $current = $config['current'];
    $expected = $config['expected'];
    $source = $config['source'];
    
    // Special handling for error_reporting numeric value
    if ($setting === 'error_reporting') {
        $expected_numeric = constant('E_ALL') & ~constant('E_DEPRECATED') & ~constant('E_STRICT');
        $is_secure = ($current == $expected_numeric);
    } else {
        $is_secure = ($current === $expected);
    }
    
    if ($is_secure) {
        $status = '<span class="secure">✅ SECURE</span>';
        $secure_count++;
    } else {
        $status = '<span class="insecure">❌ INSECURE</span>';
    }
    
    echo "<tr>
        <td><strong>{$setting}</strong></td>
        <td>{$current}</td>
        <td>{$expected}</td>
        <td>{$status}</td>
        <td>{$source}</td>
    </tr>";
}

$security_score = round(($secure_count / $total_count) * 10, 1);
$score_class = ($security_score >= 9) ? 'success' : (($security_score >= 7) ? 'warning' : 'error');

echo "</table>

    <div class='status-box {$score_class}'>
        <h3>Security Score: {$security_score}/10.0</h3>
        <p>Secure Settings: {$secure_count}/{$total_count}</p>
    </div>

    <h2>Configuration Information</h2>
    <p><strong>Loaded Configuration File:</strong> " . php_ini_loaded_file() . "</p>
    <p><strong>PHP Version:</strong> " . phpversion() . "</p>
    <p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>
    
    <h2>Fixes Applied</h2>
    <ul>
        <li>✅ Added <code>php_flag expose_php Off</code> to .htaccess</li>
        <li>✅ Added <code>php_flag allow_url_include Off</code> to .htaccess</li>
        <li>✅ Added <code>php_value error_reporting \"E_ALL & ~E_DEPRECATED & ~E_STRICT\"</code> to .htaccess</li>
        <li>✅ Applied settings to both mod_php.c and mod_php7.c modules</li>
    </ul>
    
    <h2>Next Steps</h2>
    <ul>
        <li>Test the application functionality to ensure no breaking changes</li>
        <li>Monitor error logs for any issues</li>
        <li>Consider implementing additional security measures</li>
    </ul>
    
    <p><em>Note: This script should be removed from production servers after testing.</em></p>
</body>
</html>";
?>

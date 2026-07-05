<?php
echo "<h2>PHP Configuration Check</h2>";
echo "<p><strong>Loaded Configuration File:</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>Additional .ini files:</strong> " . (php_ini_scanned_files() ? php_ini_scanned_files() : 'None') . "</p>";

echo "<h3>Current Security Settings:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Expected Value</th><th>Status</th></tr>";

$settings = [
    'display_errors' => ['expected' => '0', 'name' => 'display_errors'],
    'expose_php' => ['expected' => '0', 'name' => 'expose_php'],
    'allow_url_fopen' => ['expected' => '1', 'name' => 'allow_url_fopen'],
    'file_uploads' => ['expected' => '1', 'name' => 'file_uploads'],
    'allow_url_include' => ['expected' => '0', 'name' => 'allow_url_include'],
    'error_reporting' => ['expected' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT', 'name' => 'error_reporting']
];

foreach ($settings as $setting => $config) {
    $current = ini_get($setting);
    $expected = $config['expected'];
    $status = ($current === $expected) ? '✅ SECURE' : '❌ INSECURE';
    
    echo "<tr>";
    echo "<td>{$setting}</td>";
    echo "<td>{$current}</td>";
    echo "<td>{$expected}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Runtime Changes:</h3>";
echo "<p>If settings don't match the php.ini file, they might be changed at runtime by:</p>";
echo "<ul>";
echo "<li>Application code using ini_set()</li>";
echo "<li>.htaccess files with php_value/php_flag directives</li>";
echo "<li>Apache configuration files</li>";
echo "<li>Other .ini files in the scanned directory</li>";
echo "</ul>";
?>

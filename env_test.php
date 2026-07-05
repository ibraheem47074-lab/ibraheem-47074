<?php
// Environment Test for PK Live News
echo "<!DOCTYPE html>
<html>
<head>
    <title>Environment Test - PK Live News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Environment Configuration Test</h1>
    
    <div class='test-section'>
        <h2>Environment File Check</h2>";

// Test if .env file exists
$envFile = '.env';
if (file_exists($envFile)) {
    echo "<p class='success'>✅ .env file found at: " . realpath($envFile) . "</p>";
    echo "<p class='info'>File size: " . filesize($envFile) . " bytes</p>";
    echo "<p class='info'>Last modified: " . date('Y-m-d H:i:s', filemtime($envFile)) . "</p>";
} else {
    echo "<p class='error'>❌ .env file not found!</p>";
}

echo "</div>";

echo "<div class='test-section'>
        <h2>Loading Environment</h2>";

try {
    require_once 'config/env.php';
    echo "<p class='success'>✅ Environment loader executed successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Environment loading failed: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='test-section'>
        <h2>Environment Variables Status</h2>";

$vars = [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'u129650532_ibraheem',
    'DB_PASS' => 'Khan47074$',
    'DB_NAME' => 'u129650532_ibraheem',
    'SITE_URL' => 'https://pk-news.com',
    'SITE_NAME' => 'PK Live News',
    'APP_ENV' => 'production'
];

foreach ($vars as $var => $expected) {
    if (defined($var)) {
        $value = constant($var);
        if ($var === 'DB_PASS') {
            $display = str_repeat('*', strlen($value));
        } else {
            $display = $value;
        }
        echo "<p class='success'>✅ $var: $display</p>";
    } else {
        echo "<p class='error'>❌ $var: Not defined</p>";
    }
}

echo "</div>";

echo "<div class='test-section'>
        <h2>Raw Environment Data</h2>";

echo "<h3>getenv() results:</h3>";
echo "<pre>";
foreach ($vars as $var => $expected) {
    $value = getenv($var);
    if ($var === 'DB_PASS') {
        $display = $value ? str_repeat('*', strlen($value)) : 'NULL';
    } else {
        $display = $value ? $value : 'NULL';
    }
    echo "$var = $display\n";
}
echo "</pre>";

echo "<h3>\$_ENV results:</h3>";
echo "<pre>";
foreach ($vars as $var => $expected) {
    $value = isset($_ENV[$var]) ? $_ENV[$var] : 'NULL';
    if ($var === 'DB_PASS') {
        $display = $value !== 'NULL' ? str_repeat('*', strlen($value)) : 'NULL';
    } else {
        $display = $value;
    }
    echo "$var = $display\n";
}
echo "</pre>";

echo "</div>";

echo "<div class='test-section'>
        <h2>.env File Content (sanitized)</h2>";

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<pre>";
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            echo $line . "\n";
        } elseif (strpos($line, 'DB_PASS') !== false) {
            echo "DB_PASS=" . str_repeat('*', strlen(explode('=', $line)[1])) . "\n";
        } else {
            echo $line . "\n";
        }
    }
    echo "</pre>";
}

echo "</div>";

echo "<div class='test-section'>
        <h2>Debug Information</h2>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>Script path: " . __FILE__ . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "</div>";

echo "</body></html>";
?>

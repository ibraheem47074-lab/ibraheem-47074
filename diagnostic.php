<?php
echo "<h2>Path Diagnostic Tool</h2>";

echo "<h3>Current Directory Info:</h3>";
echo "getcwd(): " . getcwd() . "<br>";
echo "__FILE__: " . __FILE__ . "<br>";
echo "__DIR__: " . __DIR__ . "<br>";

echo "<h3>Testing Database Connection:</h3>";

// Test different paths
$paths = [
    'config/database.php',
    './config/database.php',
    '../config/database.php',
    dirname(__FILE__) . '/config/database.php'
];

foreach ($paths as $path) {
    echo "Testing path: '$path' - ";
    if (file_exists($path)) {
        echo "✅ EXISTS<br>";
        try {
            require_once $path;
            echo "✅ Successfully included<br>";
            if (isset($conn)) {
                echo "✅ Database connection established<br>";
            } else {
                echo "❌ Database connection not found<br>";
            }
        } catch (Exception $e) {
            echo "❌ Include failed: " . $e->getMessage() . "<br>";
        }
        break; // Stop at first successful path
    } else {
        echo "❌ NOT FOUND<br>";
    }
}

echo "<h3>Directory Structure:</h3>";
echo "Files in current directory:<br>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        if (is_dir($file)) {
            echo "📁 $file/<br>";
        } else {
            echo "📄 $file<br>";
        }
    }
}

echo "<h3>Config Directory Check:</h3>";
if (is_dir('config')) {
    echo "✅ Config directory exists<br>";
    $config_files = scandir('config');
    foreach ($config_files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file<br>";
        }
    }
} else {
    echo "❌ Config directory not found<br>";
}
?>

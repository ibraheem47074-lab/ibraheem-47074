<?php
// Debug file to check paths
echo "Current working directory: " . getcwd() . "\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "__DIR__: " . __DIR__ . "\n";

// Check if database.php exists using different paths
$paths_to_check = [
    '../config/database.php',
    'config/database.php',
    './config/database.php',
    dirname(__DIR__) . '/config/database.php'
];

foreach ($paths_to_check as $path) {
    echo "Checking '$path': ";
    echo file_exists($path) ? "EXISTS" : "NOT FOUND";
    echo "\n";
}

// List files in config directory
$config_dir = '../config';
if (is_dir($config_dir)) {
    echo "\nFiles in config directory:\n";
    $files = scandir($config_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
} else {
    echo "\nConfig directory not found at: $config_dir\n";
}
?>

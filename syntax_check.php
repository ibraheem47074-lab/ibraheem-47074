<?php
// Syntax check for login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Syntax Check for login.php</h2>";

// Check if file exists
if (!file_exists('admin/login.php')) {
    echo "<span style='color: red;'>admin/login.php does not exist!</span><br>";
} else {
    echo "<span style='color: green;'>admin/login.php exists!</span><br>";
    
    // Check syntax
    $output = shell_exec('php -l admin/login.php 2>&1');
    echo "<h3>PHP Syntax Check:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to include the file to check for runtime errors
    echo "<h3>Runtime Check:</h3>";
    try {
        ob_start();
        include 'admin/login.php';
        $output = ob_get_clean();
        echo "<span style='color: green;'>File included successfully!</span><br>";
        if (!empty($output)) {
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
    } catch (Error $e) {
        echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>Exception: " . $e->getMessage() . "</span><br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
    }
}

echo "<h3>Required Files Check:</h3>";
$required_files = [
    'config/database.php',
    'config/env.php',
    'config/settings.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>$file exists!</span><br>";
    } else {
        echo "<span style='color: red;'>$file does not exist!</span><br>";
    }
}
?>

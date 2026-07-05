<?php
// Simple diagnostic test for index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PK Live News - Index.php Diagnostic</h1>";

// Test 1: Check PHP version
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

// Test 2: Check required files
$required_files = [
    'config/database.php',
    'config/weather.php',
    'includes/language_functions.php',
    'includes/header.php'
];

echo "<h2>Required Files Check:</h2>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>✗ $file - MISSING</p>";
    }
}

// Test 3: Database connection
echo "<h2>Database Connection:</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        if (isset($conn) && $conn) {
            echo "<p style='color: green;'>✓ Database connection: SUCCESS</p>";
            
            // Test basic query
            $result = mysqli_query($conn, "SELECT 1 as test");
            if ($result) {
                echo "<p style='color: green;'>✓ Database query: SUCCESS</p>";
            } else {
                echo "<p style='color: red;'>✗ Database query: FAILED</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Database connection: FAILED</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database Error: " . $e->getMessage() . "</p>";
}

// Test 4: Check .env file
echo "<h2>Environment File:</h2>";
if (file_exists('.env')) {
    echo "<p style='color: green;'>✓ .env file exists</p>";
} else {
    echo "<p style='color: red;'>✗ .env file missing</p>";
}

// Test 5: Try to load language functions
echo "<h2>Language Functions:</h2>";
try {
    if (file_exists('includes/language_functions.php')) {
        require_once 'includes/language_functions.php';
        echo "<p style='color: green;'>✓ Language functions loaded</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Language functions error: " . $e->getMessage() . "</p>";
}

echo "<h2>Complete!</h2>";
echo "<p><a href='index.php'>Try to load index.php</a></p>";
?>

<?php
session_start();
require_once '../config/database.php';

echo "PK Live News - Add News Test\n";
echo "===========================\n\n";

// Test basic functionality
echo "1. Testing Database Connection: ";
echo $conn ? "✅ Connected\n" : "❌ Failed\n";

echo "2. Testing Session: ";
echo isset($_SESSION['user_id']) ? "✅ User logged in (ID: " . $_SESSION['user_id'] . ")\n" : "❌ No user session\n";

echo "3. Testing User Role: ";
echo isset($_SESSION['user_role']) ? "✅ Role: " . $_SESSION['user_role'] . "\n" : "❌ No role set\n";

echo "4. Testing Permission Functions: ";
if (function_exists('is_logged_in')) {
    echo "✅ is_logged_in exists\n";
} else {
    echo "❌ is_logged_in missing\n";
}

// Test if we can include the original file
echo "\n5. Testing add-news.php inclusion:\n";
$error_level = error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    ob_start();
    include '../admin/add-news.php';
    $output = ob_get_clean();
    echo "✅ File included successfully\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
    if (strpos($output, '<!DOCTYPE html') !== false) {
        echo "✅ HTML structure found\n";
    } else {
        echo "❌ No HTML structure found\n";
    }
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

error_reporting($error_level);

echo "\n=== Test Complete ===\n";
?>

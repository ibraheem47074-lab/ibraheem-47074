<?php
// Test file to verify email functions work
require_once 'includes/email_functions.php';

echo "<h2>Testing Email Functions</h2>";

// Test if constants are defined
if (defined('ADMIN_EMAIL')) {
    echo "<p class='text-success'>✓ ADMIN_EMAIL defined: " . ADMIN_EMAIL . "</p>";
} else {
    echo "<p class='text-danger'>✗ ADMIN_EMAIL not defined</p>";
}

if (defined('SUPPORT_EMAIL')) {
    echo "<p class='text-success'>✓ SUPPORT_EMAIL defined: " . SUPPORT_EMAIL . "</p>";
} else {
    echo "<p class='text-danger'>✗ SUPPORT_EMAIL not defined</p>";
}

if (defined('ADVERTISING_EMAIL')) {
    echo "<p class='text-success'>✓ ADVERTISING_EMAIL defined: " . ADVERTISING_EMAIL . "</p>";
} else {
    echo "<p class='text-danger'>✗ ADVERTISING_EMAIL not defined</p>";
}

// Test if functions exist
if (function_exists('sendEmail')) {
    echo "<p class='text-success'>✓ sendEmail function exists</p>";
} else {
    echo "<p class='text-danger'>✗ sendEmail function not found</p>";
}

if (function_exists('sendAdvertisingInquiry')) {
    echo "<p class='text-success'>✓ sendAdvertisingInquiry function exists</p>";
} else {
    echo "<p class='text-danger'>✗ sendAdvertisingInquiry function not found</p>";
}

echo "<h3>Test Complete!</h3>";
echo "<p><a href='contact.php'>Test Contact Form</a></p>";
?>

<style>
.text-success { color: #28a745; }
.text-danger { color: #dc3545; }
body { font-family: Arial, sans-serif; padding: 20px; }
</style>
